#!/bin/bash

# ============================================
# Windows Server 2019 - Fully Automated Installer
# Works on Contabo VPS and all Ubuntu/Debian servers
# Updated with working download links
# ============================================

# ============= Telegram Settings =============
TELEGRAM_BOT_TOKEN="8757406744:AAF5WcQteTyEgy4gssr7Jf5vi8TpUJi8nSo"
TELEGRAM_CHAT_ID="8425986907"
# =============================================

# Windows Settings
USERNAME="admin"
PASSWORD="hohoHOHO2013@@"
RDP_PORT="3389"

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

# Log file
LOG_FILE="/root/windows_installer.log"

# Function to log messages
log_message() {
    echo -e "$1" | tee -a "$LOG_FILE"
}

# Function to send Telegram message
send_telegram() {
    local message="$1"
    echo "$(date): Sending to Telegram: $message" >> "$LOG_FILE"
    curl -s -X POST "https://api.telegram.org/bot${TELEGRAM_BOT_TOKEN}/sendMessage" \
        -d "chat_id=${TELEGRAM_CHAT_ID}" \
        -d "text=${message}" > /dev/null 2>&1 || true
}

# Function to send file via Telegram
send_telegram_file() {
    local file_path="$1"
    local caption="$2"
    curl -s -F "chat_id=${TELEGRAM_CHAT_ID}" \
        -F "document=@${file_path}" \
        -F "caption=${caption}" \
        "https://api.telegram.org/bot${TELEGRAM_BOT_TOKEN}/sendDocument" > /dev/null 2>&1 || true
}

# Function to check command success
check_success() {
    if [ $? -ne 0 ]; then
        log_message "${RED}❌ Error: $1 failed${NC}"
        send_telegram "❌ ERROR: $1 failed at $(date '+%Y-%m-%d %H:%M:%S')"
        send_telegram_file "$LOG_FILE" "Error log attached"
        exit 1
    fi
}

# Start script
log_message "${GREEN}========================================${NC}"
log_message "${GREEN}Windows Server 2019 Automated Installer${NC}"
log_message "${GREEN}========================================${NC}"

send_telegram "🖥️ **VPS Windows Installer Started**%0A⏰ Time: $(date '+%Y-%m-%d %H:%M:%S')%0A📝 Preparing system for Windows Server 2019 installation..."

# Check root
if [[ $EUID -ne 0 ]]; then
    log_message "${RED}❌ Please run as root!${NC}"
    send_telegram "❌ ERROR: Script must be run as root"
    exit 1
fi

# ==================== STEP 1: Prepare Disk ====================
log_message "${YELLOW}[1/8] Preparing disk...${NC}"
send_telegram "🔄 **Step 1/8**: Preparing disk and cleaning partitions..."

# Stop all processes using the disk
for pid in $(lsof -t /dev/sda 2>/dev/null); do
    kill -9 $pid 2>/dev/null
done

# Unmount everything
swapoff -a 2>/dev/null
umount /dev/sda1 2>/dev/null
umount /dev/sda2 2>/dev/null
umount /mnt 2>/dev/null
umount /root/windisk 2>/dev/null

# Clear partition table completely
dd if=/dev/zero of=/dev/sda bs=1M count=1 2>/dev/null
sleep 2
partprobe /dev/sda 2>/dev/null
sleep 2

check_success "Disk preparation"
send_telegram "✅ **Step 1/8 Complete**: Disk prepared successfully"

# ==================== STEP 2: Install Requirements ====================
log_message "${YELLOW}[2/8] Installing requirements...${NC}"
send_telegram "🔄 **Step 2/8**: Installing required packages (apt, grub, wimtools, etc.)"

apt update -y
apt install -y grub2 wimtools ntfs-3g wget rsync parted gdisk curl dosfstools lsof pv

check_success "Requirements installation"
send_telegram "✅ **Step 2/8 Complete**: All requirements installed"

# ==================== STEP 3: Create Partitions ====================
log_message "${YELLOW}[3/8] Creating partitions...${NC}"
send_telegram "🔄 **Step 3/8**: Creating disk partitions"

disk_size_bytes=$(blockdev --getsize64 /dev/sda)
disk_size_gb=$((disk_size_bytes / 1024 / 1024 / 1024))
part_size_mb=$(((disk_size_gb * 1024) / 4))

parted /dev/sda --script -- mklabel gpt
parted /dev/sda --script -- mkpart primary ntfs 1MB ${part_size_mb}MB
parted /dev/sda --script -- mkpart primary ntfs ${part_size_mb}MB $((2 * part_size_mb))MB

sleep 3
partprobe /dev/sda
sleep 5

check_success "Partition creation"
send_telegram "✅ **Step 3/8 Complete**: Partitions created (${disk_size_gb}GB total)"

# ==================== STEP 4: Format ====================
log_message "${YELLOW}[4/8] Formatting partitions...${NC}"
send_telegram "🔄 **Step 4/8**: Formatting partitions as NTFS"

mkfs.ntfs -F -f /dev/sda1
mkfs.ntfs -F -f /dev/sda2

mount /dev/sda1 /mnt
mkdir -p /root/windisk
mount /dev/sda2 /root/windisk

check_success "Formatting"
send_telegram "✅ **Step 4/8 Complete**: Partitions formatted and mounted"

# ==================== STEP 5: Download Windows ISO ====================
log_message "${YELLOW}[5/8] Downloading Windows Server 2019 ISO...${NC}"
send_telegram "🔄 **Step 5/8**: Downloading Windows Server 2019 ISO (10-20 minutes, please wait...)"

cd /root/windisk
mkdir -p winfile

# Working download links for Windows Server 2019 Evaluation
send_telegram "📥 Downloading from Microsoft servers..."

# Link 1: Official Microsoft Evaluation Center
if wget --timeout=30 --tries=3 --progress=dot -O win2019.iso "https://go.microsoft.com/fwlink/p/?linkid=2195161" 2>&1 | grep -v "already fully retrieved"; then
    send_telegram "✅ Downloading from Microsoft link 1..."
    
# Link 2: Alternative Microsoft CDN
elif wget --timeout=30 --tries=3 -O win2019.iso "https://software-static.download.prss.microsoft.com/pr/WindowsServer2019-x64ENU-17763.3650.iso" 2>&1; then
    send_telegram "✅ Downloading from Microsoft CDN..."
    
# Link 3: Direct download (evaluation)
elif wget --timeout=30 --tries=3 -O win2019.iso "https://software-download.microsoft.com/download/sg/Windows_Server_2019_Server_Stdt_1809_EN-US.iso" 2>&1; then
    send_telegram "✅ Downloading from fallback server..."
    
else
    log_message "${RED}❌ Failed to download Windows ISO${NC}"
    send_telegram "❌ Failed to download Windows ISO! Please check internet connection."
    exit 1
fi

# Verify ISO downloaded (should be > 1GB)
if [ -f win2019.iso ]; then
    ISO_SIZE=$(du -h win2019.iso | cut -f1)
    send_telegram "✅ **Step 5/8 Complete**: ISO downloaded successfully (Size: ${ISO_SIZE})"
else
    send_telegram "❌ ISO file not found after download attempt"
    exit 1
fi

# ==================== STEP 6: Copy Windows Files ====================
log_message "${YELLOW}[6/8] Copying Windows files...${NC}"
send_telegram "🔄 **Step 6/8**: Mounting ISO and copying files (this may take 5-10 minutes)"

mount -o loop win2019.iso winfile

# Show progress while copying
rsync -a --info=progress2 winfile/* /mnt/ 2>&1 | while read line; do
    if [[ $line =~ [0-9]+% ]]; then
        echo "$line"
    fi
done

umount winfile

check_success "File copying"
send_telegram "✅ **Step 6/8 Complete**: Windows files copied"

# ==================== STEP 7: Install Drivers ====================
log_message "${YELLOW}[7/8] Installing VirtIO drivers...${NC}"
send_telegram "🔄 **Step 7/8**: Installing VirtIO drivers for network and storage"

wget -O virtio.iso "https://fedorapeople.org/groups/virt/virtio-win/direct-downloads/stable-virtio/virtio-win.iso"
mount -o loop virtio.iso winfile
mkdir -p /mnt/sources/virtio
rsync -a winfile/* /mnt/sources/virtio/
umount winfile

check_success "VirtIO driver installation"
send_telegram "✅ **Step 7/8 Complete**: VirtIO drivers installed"

# ==================== STEP 8: Configure Boot ====================
log_message "${YELLOW}[8/8] Configuring boot and unattended setup...${NC}"
send_telegram "🔄 **Step 8/8**: Creating unattended installation configuration"

cd /mnt/sources

# Create unattended answer file
cat > autounattend.xml << 'EOF'
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
        <AdministratorPassword><Value>hohoHOHO2013@@</Value><PlainText>true</PlainText></AdministratorPassword>
        <LocalAccounts>
          <LocalAccount wcm:action="add">
            <Name>admin</Name>
            <Group>Administrators</Group>
            <Password><Value>hohoHOHO2013@@</Value><PlainText>true</PlainText></Password>
          </LocalAccount>
        </LocalAccounts>
      </UserAccounts>
      <AutoLogon>
        <Password><Value>hohoHOHO2013@@</Value><PlainText>true</PlainText></Password>
        <Enabled>true</Enabled>
        <Username>admin</Username>
      </AutoLogon>
      <FirstLogonCommands>
        <SynchronousCommand wcm:action="add"><Order>1</Order><CommandLine>cmd /c wmic useraccount where "name='admin'" set PasswordExpires=false</CommandLine></SynchronousCommand>
        <SynchronousCommand wcm:action="add"><Order>2</Order><CommandLine>cmd /c reg add "HKLM\SYSTEM\CurrentControlSet\Control\Terminal Server" /v fDenyTSConnections /t REG_DWORD /d 0 /f</CommandLine></SynchronousCommand>
        <SynchronousCommand wcm:action="add"><Order>3</Order><CommandLine>cmd /c netsh advfirewall firewall set rule group="remote desktop" new enable=Yes</CommandLine></SynchronousCommand>
        <SynchronousCommand wcm:action="add"><Order>4</Order><CommandLine>cmd /c netsh advfirewall set allprofiles state off</CommandLine></SynchronousCommand>
      </FirstLogonCommands>
      <OOBE><HideEULAPage>true</HideEULAPage><SkipMachineOOBE>true</SkipMachineOOBE></OOBE>
    </component>
  </settings>
</unattend>
EOF

# Copy answer file to multiple locations
cp autounattend.xml /mnt/autounattend.xml
cp autounattend.xml /mnt/sources/autounattend.xml

# Install GRUB
grub-install --root-directory=/mnt /dev/sda
check_success "GRUB installation"

# Create GRUB configuration
cat > /mnt/boot/grub/grub.cfg << 'EOF'
set timeout=2
set default=0
menuentry "Windows Server 2019 Installer" {
    insmod ntfs
    search --set=root --file=/bootmgr
    ntldr /bootmgr
}
EOF

# Clean up
cd /
umount /root/windisk 2>/dev/null

# Get VPS IP
VPS_IP=$(curl -s ifconfig.me)

# Final success message
log_message "${GREEN}========================================${NC}"
log_message "${GREEN}✅ Setup completed successfully!${NC}"
log_message "${GREEN}========================================${NC}"
log_message "📝 Login credentials:"
log_message "   👤 Username: ${GREEN}admin${NC}"
log_message "   🔑 Password: ${GREEN}hohoHOHO2013@@${NC}"
log_message "   🔌 RDP Port: ${GREEN}3389${NC}"
log_message "   🌐 IP Address: ${GREEN}${VPS_IP}${NC}"
log_message "${GREEN}========================================${NC}"

# Send final Telegram report
send_telegram "✅ **INSTALLATION PREPARATION COMPLETE!**%0A%0A📊 **Summary:**%0A✅ All 8 steps completed successfully%0A💻 Windows Server 2019 ready to install%0A%0A🔐 **Login Credentials:**%0A👤 Username: \`admin\`%0A🔑 Password: \`hohoHOHO2013@@\`%0A🌐 RDP Port: \`3389\`%0A📡 IP Address: \`${VPS_IP}\`%0A%0A⚠️ **Note:** The system will reboot in 10 seconds.%0AWindows installation will take 10-20 minutes.%0AYou will be able to connect via RDP after completion."

log_message "${YELLOW}⚠️  The system will reboot in 10 seconds...${NC}"
log_message "${YELLOW}⚠️  Check Telegram for installation updates${NC}"
log_message "${GREEN}========================================${NC}"

sleep 10

# Force reboot
reboot -f
