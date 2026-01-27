#!/bin/bash
# This script created by raihnkhalid (https://github.com/raihnkhalid)
# Modified by https://github.com/AfnorsGG

function confirm() {
    read -p "$1 ([y]es or [N]o): "
    case $(echo $REPLY | tr '[A-Z]' '[a-z]') in
        y|yes) echo "yes" ;;
        *)     echo "no" ;;
    esac
}

while getopts "t:p:y" opt
do
   case "$opt" in
      t ) paramT="$OPTARG" ;;
      p ) paramP="$OPTARG" ;;
      y ) paramY="y"
   esac
done

if [ "$paramT" == "2012" ]; then
     OSname="Windows Server 2012 R2"
     Targeturl="http://files.rklucky.com/2012.gz"
elif [ "$paramT" == "2016" ]; then
     OSname="Windows Server 2016"
     Targeturl="http://files.rklucky.com/2016.gz"
elif [ "$paramT" == "2019" ]; then
     OSname="Windows Server 2019"
     Targeturl="http://files.rklucky.com/2019.gz"
elif [ "$paramT" == "2022" ]; then
     OSname="Windows Server 2022"
     Targeturl="http://files.rklucky.com/2022.gz"
elif [ "$paramT" == "10" ]; then
     OSname="Windows 10 Pro"
     Targeturl="http://files.rklucky.com/10.gz"
elif [ "$paramT" == "2008" ]; then
     OSname="Windows Server 2008 R2"
     Targeturl="http://files.rklucky.com/2008.gz"
else
     echo "Error not found!"
fi

interface=eth0
iAddr=`ip addr show dev $interface |grep "inet.*" |head -n1 |grep -o '[0-9]\{1,3\}\.[0-9]\{1,3\}\.[0-9]\{1,3\}\.[0-9]\{1,3\}\/[0-9]\{1,2\}'`
IPv4=`echo ${iAddr} |cut -d'/' -f1`
printf "\n  %-20s \n" "`echo \">>> Script created by raihnkhalid <<<\"`"
printf "\n  %-20s \n" "`echo \">>> Modified by https://github.com/AfnorsGG <<<\"`"
printf "\n  %-20s" "`echo \"OS Selected : $OSname\"`"
printf "\n  %-20s" "`echo \"#Login Info : \"`"
printf "\n  %-20s" "`echo \"============= \"`"
printf "\n  %-20s" "`echo -en \"[IP]      : [ \033[32m ${IPv4}:7210 \033[0m ] \"`"
printf "\n  %-20s" "`echo -en \"[Username]    : [ \033[32m Administrator \033[0m ] \"`"
printf "\n  %-20s" "`echo -en \"[Password]    : [ \033[32m $paramP \033[0m ] \"`"
printf "\n  %-20s" "`echo \"------------------------------------------------------------------------------------ \"`"
printf "\n  %-20s" "`echo -en \"[*] Tracking Link 2: [ \033[32m http://$IPv4 \033[0m ]. \"`"
printf "\n  %-20s \n" "`echo \"------------------------------------------------------------------------------------ \"`"

if [ "$paramY" ] 
then
  wget -qO- https://raw.githubusercontent.com/afnorsgg/winvps/master/1keyddmod.sh | bash -s - -t $Targeturl -w "$paramP"
elif [[ "no" == $(confirm " Confirm Action?") ]]
then
  echo "Cancelled."
else
  wget -qO- https://raw.githubusercontent.com/afnorsgg/winvps/master/1keyddmod.sh | bash -s - -t $Targeturl -w "$paramP"
fi
exit 0
