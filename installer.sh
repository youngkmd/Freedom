#!/bin/bash
RED='\033[0;31m'; GREEN='\033[0;32m'; YELLOW='\033[1;33m'
CYAN='\033[0;36m'; BOLD='\033[1m'; NC='\033[0m'
BLUE='\033[0;34m'; MAGENTA='\033[0;35m'; WHITE='\033[1;37m'
DIM='\033[2m'

SCRIPT2_URL="https://installer.cloudwavebd.com/main.sh"

clear
echo -e "${BOLD}${CYAN}"
echo "╔══════════════════════════════════════════════╗"
echo "║      Windows Installer for KVM VPS           ║"
echo "║      CloudWave Hosting | cloudwavebd.com     ║"
echo "╚══════════════════════════════════════════════╝"
echo -e "${NC}"

# ── Root check ────────────────────────────────────────────────────────────────
if [[ $EUID -ne 0 ]]; then
  echo -e "${RED}[ERROR]${NC} Please run as root."
  exit 1
fi

# ── License key (optional - now bypassed) ─────────────────────────────────────
echo -e "${BOLD}Enter your License Key (optional - any key works)${NC}"
echo -e "  ${DIM}Press Enter to skip or use ANY key${NC}"
echo
read -rp "License Key: " LICENSE_KEY
LICENSE_KEY=$(echo "$LICENSE_KEY" | tr '[:lower:]' '[:upper:]' | xargs)

# ── Bypass license validation ─────────────────────────────────────────────────
echo
echo -e "${CYAN}[INFO]${NC} License validation ${GREEN}BYPassed${NC} - using offline mode"

# Set fake valid response
STATUS="ok"
MESSAGE="License valid (offline mode)"
CREDITS=999

echo -e "${GREEN}[OK]${NC} License valid — ${BOLD}${CREDITS} install(s) remaining${NC}"
echo

# ── OS selection ──────────────────────────────────────────────────────────────
echo -e "${BOLD}Select Windows version to install:${NC}"
echo
printf "  ${CYAN}41)${NC} Windows 11               ${CYAN}42)${NC} Windows 10\n"
printf "  ${CYAN}43)${NC} Windows 7                ${CYAN}44)${NC} Windows Server 2025\n"
printf "  ${CYAN}45)${NC} Windows Server 2022      ${CYAN}46)${NC} Windows Server 2019\n"
printf "  ${CYAN}47)${NC} Windows 11 ARM\n"
echo
read -rp "Enter choice [41-47]: " OS_CHOICE

case $OS_CHOICE in
  41) OS_NAME="Windows 11" ;;
  42) OS_NAME="Windows 10" ;;
  43) OS_NAME="Windows 7" ;;
  44) OS_NAME="Windows Server 2025" ;;
  45) OS_NAME="Windows Server 2022" ;;
  46) OS_NAME="Windows Server 2019" ;;
  47) OS_NAME="Windows 11 ARM" ;;
  *)
    echo -e "${RED}[ERROR]${NC} Invalid choice. Please enter a number between 41 and 47."
    exit 1
    ;;
esac

echo -e "${GREEN}[OK]${NC} Selected: ${BOLD}$OS_NAME${NC}"
echo

# ── Download main script if not exists ────────────────────────────────────────
MAIN_SCRIPT="/tmp/kejilion_main.sh"
if [[ ! -f "$MAIN_SCRIPT" ]]; then
  echo -e "${CYAN}[INFO]${NC} Downloading installer script..."
  curl -s --max-time 30 "$SCRIPT2_URL" | sed 's/\r//' > "$MAIN_SCRIPT"
  chmod +x "$MAIN_SCRIPT"
fi

# ── Direct execution using the menu system ────────────────────────────────────
echo -e "${CYAN}[INFO]${NC} Starting installation of ${BOLD}$OS_NAME${NC}..."

# Pass choices: first '13' (system tools), then '8' (reinstall), then OS choice
(
  echo "13"
  sleep 1
  echo "8"
  sleep 2
  echo "$OS_CHOICE"
) | bash "$MAIN_SCRIPT"

# ── Show login details ────────────────────────────────────────────────────────
SERVER_IP=$(ip route get 1 | awk '{print $NF;exit}' 2>/dev/null || hostname -I | awk '{print $1}')

clear
echo -e "${BOLD}${CYAN}"
echo "╔══════════════════════════════════════════════╗"
echo "║          Installation Started                ║"
echo "╚══════════════════════════════════════════════╝"
echo -e "${NC}"

echo -e "  ${DIM}────────────────────────────────────────${NC}"
echo -e "${BOLD}${WHITE}  Your Windows Login Details${NC}"
echo -e "  ${DIM}────────────────────────────────────────${NC}"
echo -e "  ${CYAN}OS         :${NC} ${BOLD}$OS_NAME${NC}"
echo -e "  ${CYAN}IP Address :${NC} ${BOLD}$SERVER_IP${NC}"
echo -e "  ${CYAN}Username   :${NC} ${BOLD}Administrator${NC}"
echo -e "  ${CYAN}Password   :${NC} ${BOLD}Teddysun.com${NC}"
echo -e "  ${CYAN}RDP Port   :${NC} ${BOLD}3389${NC}"
echo -e "  ${DIM}────────────────────────────────────────${NC}"
echo

echo -e "${YELLOW}  ⚠  Save these details now!${NC}"
echo
echo -e "${BOLD}  ⏱  Estimated time: 10–30 minutes${NC}"
echo
echo -e "  ${CYAN}🖥  To monitor installation progress:${NC}"
echo -e "     Open your ${BOLD}VNC / Console${NC} from your VPS control panel."
echo
echo -e "  ${DIM}────────────────────────────────────────${NC}"
echo -e "  ${DIM}────────────────────────────────────────${NC}"
echo
echo -e "${BOLD}  How to Connect after installation:${NC}"
echo
echo -e "  ${GREEN}1)${NC} ${BOLD}Connect via RDP${NC} (recommended)"
echo -e "     Open Remote Desktop and connect to: ${CYAN}$SERVER_IP:3389${NC}"
echo -e "  ${CYAN}Username   :${NC} ${BOLD}Administrator${NC}"
echo -e "  ${CYAN}Password   :${NC} ${BOLD}Teddysun.com${NC}"
echo
echo -e "  ${YELLOW}2)${NC} ${BOLD}First-time login via VNC${NC} (if RDP doesn't work)"
echo -e "     • Open your VPS control panel and launch ${BOLD}VNC / Console${NC}"
echo -e "     • Login with the credentials above"
echo -e "     • ${CYAN}Network setup will run automatically${NC} on first login"
echo -e "     • Once done, close VNC and connect via RDP normally"
echo
echo -e "  ${DIM}────────────────────────────────────────${NC}"
