#!/bin/bash

# ============================================
# Windows Server 2019 - Fully Automated Installer
# With Telegram Bot Notifications
# ============================================

# ============= إعدادات تيليجرام (غيرها حسب حسابك) =============
TELEGRAM_BOT_TOKEN="8757406744:AAF5WcQteTyEgy4gssr7Jf5vi8TpUJi8nSo"  # ضع توكن البوت هنا
TELEGRAM_CHAT_ID="8425986907"      # ضع معرف الشات هنا
# ==============================================================

# إعدادات الويندوز
USERNAME="admin"
PASSWORD="hohoHOHO2013@@"
RDP_PORT="3389"  # تغيير إلى 3389 لتجنب مشكلة SSH

# الألوان
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

# دالة إرسال رسالة للتليجرام
send_telegram() {
    local message="$1"
    local time=$(date "+%Y-%m-%d %H:%M:%S")
    local full_message="🖥️ **VPS Windows Installer**%0A⏰ Time: $time%0A%0A$message"
    
    curl -s -X POST "https://api.telegram.org/bot${TELEGRAM_BOT_TOKEN}/sendMessage" \
        -d "chat_id=${TELEGRAM_CHAT_ID}" \
        -d "text=${full_message}" \
        -d "parse_mode=Markdown" > /dev/null 2>&1
}

# دالة إرسال ملف للتليجرام (للlogs)
send_telegram_file() {
    local file_path="$1"
    local caption="$2"
    curl -s -F "chat_id=${TELEGRAM_CHAT_ID}" \
        -F "document=@${file_path}" \
        -F "caption=${caption}" \
        "https://api.telegram.org/bot${TELEGRAM_BOT_TOKEN}/sendDocument" > /dev/null 2>&1
}

# التحقق من وجود توكن التليجرام
if [ "$TELEGRAM_BOT_TOKEN" = "YOUR_BOT_TOKEN_HERE" ] || [ "$TELEGRAM_CHAT_ID" = "YOUR_CHAT_ID_HERE" ]; then
    echo -e "${YELLOW}⚠️ Telegram not configured. Continuing without notifications...${NC}"
    TELEGRAM_ENABLED=false
else
    TELEGRAM_ENABLED=true
    send_telegram "✅ **Script Started**%0ASystem is preparing for Windows Server 2019 installation..."
fi

# تسجيل الأخطاء
exec 2> >(tee /root/install_error.log)

# دالة للتعامل مع الأخطاء
error_handler() {
    local line=$1
    local error=$2
    local message="❌ **ERROR** at line $line%0ACommand: $error%0AInstallation FAILED!"
    
    echo -e "${RED}Error at line $line: $error${NC}"
    if [ "$TELEGRAM_ENABLED" = true ]; then
        send_telegram "$message"
        send_telegram_file "/root/install_error.log" "Error log attached"
    fi
    exit 1
}

trap 'error_handler ${LINENO} "$BASH_COMMAND"' ERR

# التحقق من صلاحيات الجذر
if [[ $EUID -ne 0 ]]; then
    echo -e "${RED}❌ Please run as root!${NC}"
    exit 1
fi

# إرسال بداية التثبيت
if [ "$TELEGRAM_ENABLED" = true ]; then
    send_telegram "🔄 **Step 1/8**: Preparing disk and stopping services..."
fi

echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}Windows Server 2019 Automated Installer${NC}"
echo -e "${GREEN}========================================${NC}"

# إيقاف أي عمليات تستخدم القرص
echo -e "${YELLOW}🔧 Preparing disk...${NC}"
fuser -km /dev/sda 2>/dev/null
swapoff -a 2>/dev/null
umount /dev/sda1 2>/dev/null
umount /dev/sda2 2>/dev/null
umount /mnt 2>/dev/null
umount /root/windisk 2>/dev/null

# مسح جدول التقسيم القديم
dd if=/dev/zero of=/dev/sda bs=1M count=1 2>/dev/null
sleep 2
partprobe /dev/sda 2>/dev/null
sleep 2

if [ "$TELEGRAM_ENABLED" = true ]; then
    send_telegram "✅ **Step 1/8 Complete**: Disk prepared successfully"
    send_telegram "🔄 **Step 2/8**: Installing requirements..."
fi

# تثبيت المتطلبات
echo -e "${YELLOW}📦 Installing requirements...${NC}"
apt update -y
apt install -y grub2 wimtools ntfs-3g wget rsync parted gdisk curl dosfstools

if [ "$TELEGRAM_ENABLED" = true ]; then
    send_telegram "✅ **Step 2/8 Complete**: Requirements installed"
    send_telegram "🔄 **Step 3/8**: Creating partitions..."
fi

# إنشاء التقسيمات
echo -e "${YELLOW}💾 Creating partitions...${NC}"
disk_size_bytes=$(blockdev --getsize64 /dev/sda)
disk_size_gb=$((disk_size_bytes / 1024 / 1024 / 1024))
part_size_mb=$(((disk_size_gb * 1024) / 4))

# إنشاء جدول تقسيم جديد
parted /dev/sda --script -- mklabel gpt
parted /dev/sda --script -- mkpart primary ntfs 1MB ${part_size_mb}MB
parted /dev/sda --script -- mkpart primary ntfs ${part_size_mb}MB $((2 * part_size_mb))MB

sleep 3
partprobe /dev/sda
sleep 5

if [ "$TELEGRAM_ENABLED" = true ]; then
    send_telegram "✅ **Step 3/8 Complete**: Partitions created (${disk_size_gb}GB total)"
    send_telegram "🔄 **Step 4/8**: Formatting partitions..."
fi

# تنسيق التقسيمات
echo -e "${YELLOW}💿 Formatting partitions...${NC}"
mkfs.ntfs -F -f /dev/sda1
mkfs.ntfs -F -f /dev/sda2

# تحميل التقسيمات
mount /dev/sda1 /mnt
mkdir -p /root/windisk
mount /dev/sda2 /root/windisk

if [ "$TELEGRAM_ENABLED" = true ]; then
    send_telegram "✅ **Step 4/8 Complete**: Partitions formatted as NTFS"
    send_telegram "🔄 **Step 5/8**: Downloading Windows Server 2019 ISO (15-20 minutes)..."
fi

# تحميل ويندوز
echo -e "${YELLOW}📥 Downloading Windows Server 2019...${NC}"
cd /root/windisk
mkdir -p winfile

# محاولة تحميل من مصادر متعددة
send_telegram "📥 Downloading ISO from Microsoft servers..."
wget --timeout=30 --tries=3 -O win2019.iso "https://software-download.microsoft.com/download/pr/17763.3650.221105-1747.rs5_release_svc_refresh_SERVER_EVAL_x64FRE_en-us.iso" || \
wget --timeout=30 --tries=3 -O win2019.iso "https://archive.org/download/windows-server-2019/WS2019.iso" || \
{
    send_telegram "❌ Failed to download Windows ISO! Check internet connection."
    exit 1
}

if [ "$TELEGRAM_ENABLED" = true ]; then
    send_telegram "✅ **Step 5/8 Complete**: ISO downloaded successfully"
    send_telegram "🔄 **Step 6/8**: Mounting ISO and copying files..."
fi

# تحميل الـ ISO
mount -o loop win2019.iso winfile

# نسخ الملفات
echo -e "${YELLOW}📋 Copying Windows files...${NC}"
rsync -a --info=progress2 winfile/* /mnt/ 2>&1 | while read line; do
    if [[ $line =~ [0-9]+% ]]; then
        echo "$line"
    fi
done

umount winfile

if [ "$TELEGRAM_ENABLED" = true ]; then
    send_telegram "✅ **Step 6/8 Complete**: Windows files copied"
    send_telegram "🔄 **Step 7/8**: Installing VirtIO drivers..."
fi

# تحميل تعريفات VirtIO
echo -e "${YELLOW}🔄 Installing VirtIO drivers...${NC}"
wget -O virtio.iso "https://fedorapeople.org/groups/virt/virtio-win/direct-downloads/stable-virtio/virtio-win.iso"
mount -o loop virtio.iso winfile
mkdir -p /mnt/sources/virtio
rsync -a winfile/* /mnt/sources/virtio/
umount winfile

if [ "$TELEGRAM_ENABLED" = true ]; then
    send_telegram "✅ **Step 7/8 Complete**: VirtIO drivers installed"
    send_telegram "🔄 **Step 8/8**: Creating unattended setup and configuring boot..."
fi

# إنشاء ملف الإجابة الآلي
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

# تثبيت GRUB
echo -e "${YELLOW}📀 Installing GRUB...${NC}"
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

# الحصول على عنوان IP للـ VPS للإرسال
VPS_IP=$(curl -s ifconfig.me)

# إرسال تقرير نهائي
if [ "$TELEGRAM_ENABLED" = true ]; then
    send_telegram "✅ **INSTALLATION PREPARATION COMPLETE!**%0A%0A📊 **Summary:**%0A✅ All 8 steps completed successfully%0A💻 Windows Server 2019 ready to install%0A%0A🔐 **Login Credentials:**%0A👤 Username: \`${USERNAME}\`%0A🔑 Password: \`${PASSWORD}\`%0A🌐 RDP Port: \`${RDP_PORT}\`%0A📡 IP Address: \`${VPS_IP}\`%0A%0A⚠️ **Note:** The system will reboot in 10 seconds.%0AWindows installation will take 10-20 minutes.%0AYou will be able to connect via RDP after completion."
fi

# عرض المعلومات النهائية
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
echo -e "${YELLOW}⚠️  Check Telegram for installation updates${NC}"
echo -e "${GREEN}========================================${NC}"

sleep 10

# إعادة التشغيل القسري
reboot -f
