#!/bin/bash

# ============================================
# Windows Server Installer - Like TinyInstaller
# RDP Port: 22 | Username: admin | Password: hohoHOHO2013@@
# ============================================

# Fixed settings (you can change these)
USERNAME="admin"
PASSWORD="hohoHOHO2013@@"
RDP_PORT="22"
WINDOWS_VERSION="2019"  # or 2022

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}Windows Server ${WINDOWS_VERSION} Installer${NC}"
echo -e "${GREEN}RDP Port: ${RDP_PORT}${NC}"
echo -e "${GREEN}========================================${NC}"

# Check root privileges
if [[ $EUID -ne 0 ]]; then
   echo -e "${RED}❌ Please run as root (use: sudo bash $0)${NC}" 
   exit 1
fi

# Install requirements
echo -e "${YELLOW}📦 Installing requirements...${NC}"
apt update -y && apt upgrade -y
apt install -y grub2 wimtools ntfs-3g wget rsync parted gdisk curl

# Prepare disk
echo -e "${YELLOW}💾 Preparing disk...${NC}"
disk_size_gb=$(parted /dev/sda --script print | awk '/^Disk \/dev\/sda:/ {print int($3)}')
disk_size_mb=$((disk_size_gb * 1024))
part_size_mb=$((disk_size_mb / 4))

parted /dev/sda --script -- mklabel gpt
parted /dev/sda --script -- mkpart primary ntfs 1MB ${part_size_mb}MB
parted /dev/sda --script -- mkpart primary ntfs ${part_size_mb}MB $((2 * part_size_mb))MB

partprobe /dev/sda && sleep 10
partprobe /dev/sda && sleep 10
partprobe /dev/sda && sleep 10

mkfs.ntfs -f /dev/sda1
mkfs.ntfs -f /dev/sda2

mount /dev/sda1 /mnt
mkdir ~/windisk
mount /dev/sda2 ~/windisk

# Download Windows ISO
echo -e "${YELLOW}📥 Downloading Windows Server...${NC}"
cd ~/windisk
mkdir winfile

if [ "$WINDOWS_VERSION" = "2022" ]; then
    wget -O win2022.iso "https://software-static.download.prss.microsoft.com/dbazure/888969d5-f34g-4e03-ac9d-1f9786c66749/20348.587.220507-1407.fe_release_svc_refresh_SERVER_EVAL_x64FRE_en-us.iso"
    mount -o loop win2022.iso winfile
else
    wget -O win2019.iso "https://software-download.microsoft.com/download/pr/17763.3650.221105-1747.rs5_release_svc_refresh_SERVER_EVAL_x64FRE_en-us.iso"
    mount -o loop win2019.iso winfile
fi

rsync -avz --progress winfile/* /mnt/
umount winfile

# Download VirtIO drivers
echo -e "${YELLOW}🔄 Downloading VirtIO drivers...${NC}"
wget -O virtio.iso "https://fedorapeople.org/groups/virt/virtio-win/direct-downloads/stable-virtio/virtio-win.iso"
mount -o loop virtio.iso winfile
mkdir -p /mnt/sources/virtio
rsync -avz --progress winfile/* /mnt/sources/virtio/
umount winfile

# Create unattended answer file with port 22
echo -e "${YELLOW}⚙️  Creating unattended installation (RDP Port: ${RDP_PORT})...${NC}"
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
        <SynchronousCommand wcm:action="add">
          <Order>1</Order>
          <CommandLine>cmd /c wmic useraccount where "name='${USERNAME}'" set PasswordExpires=false</CommandLine>
        </SynchronousCommand>
        <SynchronousCommand wcm:action="add">
          <Order>2</Order>
          <CommandLine>cmd /c reg add "HKLM\SYSTEM\CurrentControlSet\Control\Terminal Server" /v fDenyTSConnections /t REG_DWORD /d 0 /f</CommandLine>
        </SynchronousCommand>
        <SynchronousCommand wcm:action="add">
          <Order>3</Order>
          <CommandLine>cmd /c netsh advfirewall firewall set rule group="remote desktop" new enable=Yes</CommandLine>
        </SynchronousCommand>
        <SynchronousCommand wcm:action="add">
          <Order>4</Order>
          <CommandLine>cmd /c reg add "HKLM\SYSTEM\CurrentControlSet\Control\Terminal Server\WinStations\RDP-Tcp" /v PortNumber /t REG_DWORD /d ${RDP_PORT} /f</CommandLine>
        </SynchronousCommand>
        <SynchronousCommand wcm:action="add">
          <Order>5</Order>
          <CommandLine>cmd /c netsh advfirewall set allprofiles state off</CommandLine>
        </SynchronousCommand>
      </FirstLogonCommands>
      <OOBE><HideEULAPage>true</HideEULAPage><SkipMachineOOBE>true</SkipMachineOOBE></OOBE>
    </component>
  </settings>
</unattend>
EOF

cp autounattend.xml /mnt/autounattend.xml

# Install GRUB
echo -e "${YELLOW}📀 Installing GRUB...${NC}"
grub-install --root-directory=/mnt /dev/sda

cat > /mnt/boot/grub/grub.cfg << EOF
set timeout=2
set default=0
menuentry "Windows Installer" {
    insmod ntfs
    search --set=root --file=/bootmgr
    ntldr /bootmgr
}
EOF

# Display login information
echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}✅ Setup completed successfully!${NC}"
echo -e "${GREEN}========================================${NC}"
echo -e "📝 Login credentials after installation:"
echo -e "   👤 Username: ${YELLOW}${USERNAME}${NC}"
echo -e "   🔑 Password: ${YELLOW}${PASSWORD}${NC}"
echo -e "   🔌 RDP Port: ${YELLOW}${RDP_PORT}${NC}"
echo -e "${GREEN}========================================${NC}"
echo -e "${YELLOW}⚠️  Note: Port 22 is typically used for SSH"
echo -e "   Change SSH port first if you want to use port 22 for RDP${NC}"
echo -e "${GREEN}========================================${NC}"

read -p "Press Enter to reboot (or Ctrl+C to cancel)..." 

reboot
