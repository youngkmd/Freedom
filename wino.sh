cat > wino_fixed.sh << 'EOF'
#!/bin/bash

# ============================================
# Windows Server 2019 - Fully Automated Installer
# Fixed version (no fuser required)
# ============================================

# Telegram settings
TELEGRAM_BOT_TOKEN="8757406744:AAF5WcQteTyEgy4gssr7Jf5vi8TpUJi8nSo"
TELEGRAM_CHAT_ID="8425986907"

# Windows settings
USERNAME="admin"
PASSWORD="hohoHOHO2013@@"
RDP_PORT="3389"

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

# Function to send Telegram messages (without Markdown to avoid issues)
send_telegram() {
    local message="$1"
    curl -s -X POST "https://api.telegram.org/bot${TELEGRAM_BOT_TOKEN}/sendMessage" \
        -d "chat_id=${TELEGRAM_CHAT_ID}" \
        -d "text=${message}" > /dev/null 2>&1
}

# Check if Telegram is configured
TELEGRAM_ENABLED=true
send_telegram "✅ VPS Windows Installer Started! Preparing system..."

echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}Windows Server 2019 Automated Installer${NC}"
echo -e "${GREEN}========================================${NC}"

# Check root
if [[ $EUID -ne 0 ]]; then
    echo -e "${RED}❌ Please run as root!${NC}"
    exit 1
fi

# Stop all processes using the disk (without fuser)
echo -e "${YELLOW}🔧 Preparing disk...${NC}"
send_telegram "Step 1/7: Preparing disk and stopping services..."

# Kill any process using /dev/sda (alternative to fuser)
for pid in $(lsof -t /dev/sda 2>/dev/null); do
    kill -9 $pid 2>/dev/null
done

swapoff -a 2>/dev/null
umount /dev/sda1 2>/dev/null
umount /dev/sda2 2>/dev/null
umount /mnt 2>/dev/null
umount /root/windisk 2>/dev/null

# Clear old partition table
dd if=/dev/zero of=/dev/sda bs=1M count=1 2>/dev/null
sleep 2
partprobe /dev/sda 2>/dev/null
sleep 2

send_telegram "✅ Step 1/7 Complete: Disk prepared"

# Install requirements
echo -e "${YELLOW}📦 Installing requirements...${NC}"
send_telegram "Step 2/7: Installing requirements (apt update, grub, wimtools, etc.)"

apt update -y
apt install -y grub2 wimtools ntfs-3g wget rsync parted gdisk curl dosfstools lsof

send_telegram "✅ Step 2/7 Complete: Requirements installed"

# Create partitions
echo -e "${YELLOW}💾 Creating partitions...${NC}"
send_telegram "Step 3/7: Creating disk partitions"

disk_size_bytes=$(blockdev --getsize64 /dev/sda)
disk_size_gb=$((disk_size_bytes / 1024 / 1024 / 1024))
part_size_mb=$(((disk_size_gb * 1024) / 4))

parted /dev/sda --script -- mklabel gpt
parted /dev/sda --script -- mkpart primary ntfs 1MB ${part_size_mb}MB
parted /dev/sda --script -- mkpart primary ntfs ${part_size_mb}MB $((2 * part_size_mb))MB

sleep 3
partprobe /dev/sda
sleep 5

send_telegram "✅ Step 3/7 Complete: Partitions created (${disk_size_gb}GB total)"

# Format partitions
echo -e "${YELLOW}💿 Formatting partitions...${NC}"
send_telegram "Step 4/7: Formatting partitions as NTFS"

mkfs.ntfs -F -f /dev/sda1
mkfs.ntfs -F -f /dev/sda2

# Mount partitions
mount /dev/sda1 /mnt
mkdir -p /root/windisk
mount /dev/sda2 /root/windisk

send_telegram "✅ Step 4/7 Complete: Partitions formatted and mounted"

# Download Windows ISO
echo -e "${YELLOW}📥 Downloading Windows Server 2019...${NC}"
send_telegram "Step 5/7: Downloading Windows Server 2019 ISO (15-20 minutes, please wait)"

cd /root/windisk
mkdir -p winfile

wget --timeout=30 --tries=3 -O win2019.iso "https://software-download.microsoft.com/download/pr/17763.3650.221105-1747.rs5_release_svc_refresh_SERVER_EVAL_x64FRE_en-us.iso" || \
wget --timeout=30 --tries=3 -O win2019.iso "https://archive.org/download/windows-server-2019/WS2019.iso" || \
{
    send_telegram "❌ Failed to download Windows ISO! Installation aborted."
    exit 1
}

send_telegram "✅ Step 5/7 Complete: ISO downloaded successfully"

# Mount and copy files
echo -e "${YELLOW}📋 Copying Windows files...${NC}"
send_telegram "Step 6/7: Copying Windows installation files (this may take 5-10 minutes)"

mount -o loop win2019.iso winfile
rsync -a winfile/* /mnt/
umount winfile

send_telegram "✅ Step 6/7 Complete: Windows files copied"

# Install VirtIO drivers
echo -e "${YELLOW}🔄 Installing VirtIO drivers...${NC}"
send_telegram "Installing VirtIO drivers for network and storage"

wget -O virtio.iso "https://fedorapeople.org/groups/virt/virtio-win/direct-downloads/stable-virtio/virtio-win.iso"
mount -o loop virtio.iso winfile
mkdir -p /mnt/sources/virtio
rsync -a winfile/* /mnt/sources/virtio/
umount winfile

# Create unattended answer file
echo -e "${YELLOW}⚙️ Creating unattended setup...${NC}"
cd /mnt/sources

cat > autounattend.xml << EOF
<?xml version="1.0" encoding="utf-8"?>
<unattend xmlns="urn:schemas-microsoft-com:unattend">
  <settings pass="windowsPE">
    <component name="Microsoft-Windows-Setup" processorArchitecture="amd64">
      <DiskConfiguration>
        <Disk wcm:action="add">
          <DiskID>0</DiskID>
          <WillWipeDisk>true</WillWipeDisk>
          <CreatePartitions>
            <CreatePartition wcm:action="add"><Order>1</Order><Size>500</Size><Type>Primary</Type></CreatePartition>
            <CreatePartition wcm:action="add"><Order>2</Order><Extend>true</Extend><Type>Primary</Type></CreatePartition>
          </CreatePartitions>
          <ModifyPartitions>
            <ModifyPartition wcm:action="add"><Order>1</Order><PartitionID>1</PartitionID><Format>NTFS</Format><Active>true</Active></ModifyPartition>
            <ModifyPartition wcm:action="add"><Order>2</Order><PartitionID>2</PartitionID><Format>NTFS</Format></ModifyPartition>
          </ModifyPartitions>
        </Disk>
      </DiskConfiguration>
      <ImageInstall><OSImage><InstallTo><DiskID>0</DiskID><PartitionID>2</PartitionID></InstallTo></OSImage></ImageInstall>
      <UserData><AcceptEula>true</AcceptEula></UserData>
    </component>
  </settings>
  <settings pass="oobeSystem">
    <component name="Microsoft-Windows-Shell-Setup" processorArchitecture="amd64">
      <UserAccounts>
        <AdministratorPassword><Value>${PASSWORD}</Value><PlainText>true</PlainText></AdministratorPassword>
        <LocalAccounts>
          <LocalAccount wcm:action="add">
            <Name>${USERNAME}</Name>
            <Group>Administrators</Group>
            <Password><Value>${PASSWORD}</Value><PlainText>true</PlainText></Password>
          </LocalAccount>
        </LocalAccounts>
      </UserAccounts>
      <AutoLogon>
        <Password><Value>${PASSWORD}</Value><PlainText>true</PlainText></Password>
        <Enabled>true</Enabled>
        <Username>${USERNAME}</Username>
      </AutoLogon>
      <FirstLogonCommands>
        <SynchronousCommand wcm:action="add"><Order>1</Order><CommandLine>cmd /c wmic useraccount where "name='${USERNAME}'" set PasswordExpires=false</CommandLine></SynchronousCommand>
        <SynchronousCommand wcm:action="add"><Order>2</Order><CommandLine>cmd /c reg add "HKLM\SYSTEM\CurrentControlSet\Control\Terminal Server" /v fDenyTSConnections /t REG_DWORD /d 0 /f</CommandLine></SynchronousCommand>
        <SynchronousCommand wcm:action="add"><Order>3</Order><CommandLine>cmd /c netsh advfirewall firewall set rule group="remote desktop" new enable=Yes</CommandLine></SynchronousCommand>
        <SynchronousCommand wcm:action="add"><Order>4</Order><CommandLine>cmd /c netsh advfirewall set allprofiles state off</CommandLine></SynchronousCommand>
      </FirstLogonCommands>
      <OOBE><HideEULAPage>true</HideEULAPage><SkipMachineOOBE>true</SkipMachineOOBE></OOBE>
    </component>
  </settings>
</unattend>
EOF

cp autounattend.xml /mnt/autounattend.xml

# Install GRUB
echo -e "${YELLOW}📀 Installing GRUB bootloader...${NC}"
grub-install --root-directory=/mnt /dev/sda

cat > /mnt/boot/grub/grub.cfg << EOF
set timeout=2
set default=0
menuentry "Windows Server 2019" {
    insmod ntfs
    search --set=root --file=/bootmgr
    ntldr /bootmgr
}
EOF

cd /
umount /root/windisk 2>/dev/null

# Get VPS IP
VPS_IP=$(curl -s ifconfig.me)

# Send final report
send_telegram "✅ INSTALLATION PREPARATION COMPLETE!%0A%0AAll steps completed successfully!%0A%0ALogin Credentials:%0AUsername: ${USERNAME}%0APassword: ${PASSWORD}%0ARDP Port: ${RDP_PORT}%0AIP Address: ${VPS_IP}%0A%0AThe system will reboot in 10 seconds.%0AWindows installation will take 10-20 minutes."

# Display final info
echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}✅ Setup completed successfully!${NC}"
echo -e "${GREEN}========================================${NC}"
echo -e "📝 Login credentials after installation:"
echo -e "   👤 Username: ${GREEN}${USERNAME}${NC}"
echo -e "   🔑 Password: ${GREEN}${PASSWORD}${NC}"
echo -e "   🔌 RDP Port: ${GREEN}${RDP_PORT}${NC}"
echo -e "   🌐 IP Address: ${GREEN}${VPS_IP}${NC}"
echo -e "${GREEN}========================================${NC}"
echo -e "${YELLOW}⚠️  The system will reboot in 10 seconds...${NC}"
echo -e "${GREEN}========================================${NC}"

sleep 10
reboot -f
EOF

chmod +x wino_fixed.sh && sudo ./wino_fixed.sh
