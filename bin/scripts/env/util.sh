: 'some useful util function'


#Detect Os distribution
function os_type()
{
   if [ -f /etc/os-release ]; then
      # freedesktop.org and systemd
      . /etc/os-release
      OS=$(sed 's/\(.\)/\U\1/' <<< "$ID")
      VER="$VERSION_ID"
   elif [ $(type lsb_release >/dev/null 2>&1) ]; then
      # linuxbase.org
      OS=$(lsb_release -si)
      VER=$(lsb_release -sr)
   elif [ -f /etc/lsb-release ]; then
       # For some versions of Debian/Ubuntu without lsb_release command
       . /etc/lsb-release
       OS=$DISTRIB_ID
       VER=$DISTRIB_RELEASE
   elif [ -f /etc/debian_version ]; then
       # Older Debian/Ubuntu/etc.
       OS=Debian
       VER=$(cat /etc/debian_version)
   elif [ -f /etc/SuSe-release ]; then
       # @TODO to recognize suse system
       # Older SuSE/etc.
       OS=Suse
       VER=Unkown
   elif [ -f /etc/redhat-release ]; then
       # Older Red Hat, CentOS, etc.
       if_centos=$(cat /etc/redhat-release | grep -i 'centos')
       if [ ! -z "${if_centos}" ]; then
          OS=Centos
       else
          OS=Redhat
       fi;
       VER=$(cat /etc/redhat-release | grep -iP '[[:digit:].-]+' -o)
   else
       # Fall back to uname, e.g. "Linux <version>", also works for BSD, etc.
       OS=$(uname -s)
       VER=$(uname -r)
   fi
   declare -a OS_INFO=()
   OS_INFO[0]="$OS"
   OS_INFO[1]="$VER"
   echo ${OS_INFO[@]}
}

#check existence of a command

function  exist_command() {
     if [ -z "$1" ];then
         echo -e "Argumet Error: command must given"
         exit 0;
     fi;
     if [ -z "$(command -v $1)" ];then
         return 0;
     else 
         return 1;
     fi
}

#check existence of a package managed by Linux distribution's packagemanager
function exist_package() {
    if [ -z "$1" ]; then
        echo -e "Argument: package must be provided"
        exit 0
    fi;
    status=$(yum list installed $1 2>/dev/null | grep -i ${1/\*/\.\*} )
    if [ -z "$status" ];then
        return 0
    fi;
        return 1
}
