#!/usr/bin/env bash

: '
 This Scripts is used to configure a centos linux machine to a spider development hosts automatically!
'
cpath=$(dirname $(readlink -f $0))
source $cpath/util.sh
declare -a packages=("sshpass" "git" "docker"  "wget" "curl" "gcc" "make" "bzip2" "autoconf")

#install inotify 
function inotify_install() {
   exist_command inotifywatch
   if [ $? -eq 0 ];then
       echo -e "Installing inotify"
       cd /tmp
       wget  http://github.com/downloads/rvoicilas/inotify-tools/inotify-tools-3.14.tar.gz
       if [ $? -ne 0 ];then
          echo -e "Get inotify failed"
          exit 0
       fi;
       tar -zxvf inotify-tools-3.14.tar.gz
       cd inotify-tools-3.14
       ./configure && make && make install 
       if [ $? -ne 0 ];then
          echo -e "Compile inotify failed"
          exit 0
       else 
          echo -e "inotify installed sucessfully"
       fi;
   else
       echo -e "Installing inotify ... done"
   fi;
 }

function phpmodule_exist() {
    if [ -z $1 ];then
        echo -e "Argument: a module name must be provided"
        exit 0;
    fi;
    $status=$(php -m $1 | grep -i "$1")
    if [ -z "$status" ];then
         return 0
    else 
         return 1
    fi;
}
#install php and config php
function php_config() {
    #check if system has pre-installed a php version
    if_pre=$(exist_command php && exist_package php)
    if [ $? -ne 0 ];then
         yum erase php php-common php-cli -y 
    fi
    #check if this script has been executed,avoid being compiled over and over again.
    if [ -e '/usr/local/php5.6' ] && [ -e '/usr/local/php7.1' ] && [[ $(exist_command php5.6) == 1 ]] && [[ $(exist_command php7.1) == 1 ]];then
         return 1
    fi;
    echo  "Installing php 5.6 ..."
    echo  "Prepare it's library ..."
    yum install -y libxml2-devel  openssl-devel libcurl-devel libpng-devel  libjpeg-devel freetype-devel
    echo  "preparing it's library ... done"
    cd /usr/local/src 
    if [ ! -e "/usr/local/src/php-5.6.21.tar.gz" ];then
       wget -c http://cn2.php.net/distributions/php-5.6.21.tar.gz
    fi
    if [[ $? != 0 ]];then
       echo -e "Failed to download php-5.6 source file.Please recheck you network." 
       exit 0;
    fi;
    tar -zxvf php-5.6.21.tar.gz 
    #Compile php    
    cd php-5.6.21/ 
    ./configure  --prefix=/usr/local/php5.6 --enable-fpm --with-mysql  --with-mysqli --with-zlib --with-curl --with-gd --with-jpeg-dir --with-png-dir --with-freetype-dir --with-openssl --enable-mbstring --enable-xml --enable-session --enable-ftp --enable-pdo --enable-shmop --enable-maintainer-zts  
    make clean &&  make && make install 
    if [[ $? -eq 0 ]];then
       ln -sf /usr/local/php5.6/bin/php  /usr/bin/php5.6
       ln -sf /usr/local/php5.6/bin/phpize /usr/bin/phpize5.6
       ln -sf /usr/local/php5.6/bin/pecl  /usr/bin/pecl5.6
       ln -sf /usr/local/php5.6/bin/pear  /usr/bin/pear5.6
       echo -e "Installing php 5.6 ... done"
       #check php5.6 pcntl if installed
       echo -e "Checking php5.6 pcntl if installed ..."
       if [ -e "/usr/local/php5.6/lib/php/extensions/no-debug-zts-20131226/pcntl.so" ]; then
           echo -e "Checking php5.6 pcntl if installed ... yes"
           #check php5.6 pcntl if enabled
           status=$(php5.6 -m | grep -i "pcntl")
              if [ ! -z "$status" ];then
                 echo -e "Check if php5.6 pcntl extension enabled ... yes"
              else 
                 echo -e "Check if php5.6 pcntl extension ... no"
                 echo -e "Modifying the configuration file to activate php5.6 pcntl extension ... "
                 if [ ! -e "/usr/local/php5.6/lib/php.ini" ];then
                    cp /usr/local/src/php-5.6.21/php.ini-development  /usr/local/php5.6/lib/php.ini
                 fi;
                 sed '912  a \ extension=pcntl.so' /usr/local/php5.6/lib/php.ini>/usr/local/php5.6/lib/php.ini.tmp && mv /usr/local/php5.6/lib/php.ini.tmp /usr/local/php5.6/lib/php.ini 
                 if [ $? == 0 ];then   
                     echo -e "Modifying the configuration file to activate  php5.6 pcntl extension ... done"
                 else 
                     echo -e "Modifying the configuration file to activate  php5.6 pcntl extension ... failed"
                 fi; 
              fi
       else
          #Compile pcntl extension for php5.6 for multi-process
          echo -e "Checking if php5.6 pcntl installed ... no "
          echo -e "Compiling pcntl for php5.6  multi-processes ... " 
          cd /usr/local/src/php-5.6.21/ext/pcntl
          phpize5.6 . 
          ./configure --with-php-config=/usr/local/php5.6/bin/php-config
          make && make install 
          if [[ $? == 0 ]];then
               echo -e "Compiling pcntl for php5.6  mulit-process ... done"
               echo -e "Modifying the configuration file to activate php5.6 pcntl extension ... "
               if [ ! -e "/usr/local/php5.6/lib/php.ini" ];then
                  cp /usr/local/src/php-5.6.21/php.ini-development  /usr/local/php5.6/lib/php.ini
               fi;
               sed '912  a \ extension=pcntl.so' /usr/local/php5.6/lib/php.ini>/usr/local/php5.6/lib/php.ini.tmp && mv /usr/local/php5.6/lib/php.ini.tmp /usr/local/php5.6/lib/php.ini 
               if [ $? == 0 ];then   
                   echo -e "Modifying the configuration file to activate  php5.6 pcntl extension ... done"
               else 
                   echo -e "Modifying the configuration file to activate  php5.6 pcntl extension ... failed"
               fi; 
               #check if the plugin is enabled
               status=$(php5.6 -m | grep -i "pcntl")
               if [ ! -z "$status" ];then
                   echo -e "Check php5.6 pcntl extension ... on"
               else 
                   echo -e "Check php5.6 pcntl extension ... off"
               fi
          else 
               echo -e "Failed to compile php5.6  pcntl extension."
          fi;
 
       fi;
     
       if [ -e "/usr/local/php5.6/lib/php/extensions/no-debug-zts-20131226/pthreads.so" ];then
              echo -e "Check if php5.6 pthreads installed ... yes"
              #check php5.6 pthreads if enabled
              status=$(php5.6 -m | grep -i "pthreads")
                if [ ! -z "$status" ];then
                   echo -e "Check if php5.6 pthreads extension enabled ... yes"
                else 
                   echo -e "Check if php5.6 pthreads extension ... no"
                   echo -e "Modifying the configuration file to activate php5.6 pthreads extension ... "
                   if [ ! -e "/usr/local/php5.6/lib/php.ini" ];then
                      cp /usr/local/src/php-5.6.21/php.ini-development  /usr/local/php5.6/lib/php.ini
                   fi;
                   sed '912  a \ extension=pthreads.so' /usr/local/php5.6/lib/php.ini>/usr/local/php5.6/lib/php.ini.tmp && mv /usr/local/php5.6/lib/php.ini.tmp /usr/local/php5.6/lib/php.ini 
                   if [ $? == 0 ];then   
                       echo -e "Modifying the configuration file to activate  php5.6 pthreads extension ... done"
                   else 
                       echo -e "Modifying the configuration file to activate  php5.6 pthreads extension ... failed"
                   fi; 
                fi
       else 
              echo -e "Check if php5.6 pthreads installed ... no"
              echo -e "Compiling pthreads for php5.6 multi-threads ..."
              pecl5.6 install pthreads-1.0.0 
              if [[ $? == 0 ]];then
                   echo -e "Compiling pthreads for php5.6 multi-threads ... done"
                   if [ ! -e "/usr/local/php5.6/lib/php.ini" ];then
                      cp /usr/local/src/php-5.6.21/php.ini-development  /usr/local/php5.6/lib/php.ini
                   fi;
                   sed '912  a \ extension=pthreads.so' /usr/local/php5.6/lib/php.ini>/usr/local/php5.6/lib/php.ini.tmp && mv /usr/local/php5.6/lib/php.ini.tmp /usr/local/php5.6/lib/php.ini 
                   if [ $? == 0 ];then   
                       echo -e "Modifying the configuration file to activate php5.6 pthreads extension ... done"
                   else 
                       echo -e "Modifying the configuration file to activate php5.6 pthreads extension ... failed"
                   fi; 
                   #check if the plugin is enabled
                   status=$(php5.6 -m | grep -i "pthreads")
                   if [ ! -z "$status" ];then
                       echo -e "Check php5.6 pthreads extension ... on"
                   else 
                       echo -e "Check php5.6 pthreads extension ... off"
                   fi
              else 
                   echo -e "Compiling pthreads for php5.6 multi-threads ... failed"
              fi
        fi
    fi
    echo  "Installing php 7.1 ..."
    cd /usr/local/src 
    if [ ! -e "/usr/local/src/php-7.1.9.tar.bz2" ];then
        wget -c http://cn2.php.net/distributions/php-7.1.9.tar.bz2
        if [[ $? != 0 ]];then
        echo -e "Failed to download php7.1.9 source file."
        exit 0
        fi;
    fi;
    if [ ! -e "/usr/local/src/php-7.1.9" ];then
       tar xvf  php-7.1.9.tar.bz2
    fi;
    cd php-7.1.9
    ./configure  --prefix=/usr/local/php7.1 --enable-fpm --with-mysql  --with-mysqli --with-zlib --with-curl --with-gd --with-jpeg-dir --with-png-dir --with-freetype-dir --with-openssl --enable-mbstring --enable-xml --enable-session --enable-ftp --enable-pdo --enable-shmop --enable-maintainer-zts  
    make clean && make && make install 
    if [ $? -eq 0 ];then
       ln -sf /usr/local/php7.1/bin/php  /usr/bin/php7.1 
       ln -sf /usr/local/php7.1/bin/phpize /usr/bin/phpize7.1
       ln -sf /usr/local/php7.1/bin/pecl  /usr/bin/pecl7.1
       ln -sf /usr/local/php7.1/bin/pear  /usr/bin/pear7.1
       ln -sf /usr/bin/php7.1  /usr/bin/php
       ln -sf /usr/bin/phpize7.1  /usr/bin/phpize
       ln -sf /usr/bin/pecl7.1  /usr/bin/pecl
       ln -sf /usr/bin/pear7.1  /usr/bin/pear
       echo -e "Installing php 7.1 ... done"
       # Check if php7.1 pcntl is installed  
       echo -e "Checking php7.1 pcntl extension if installed ... "
       if [ -e "/usr/local/php7.1/lib/php/extensions/no-debug-zts-20160303/pcntl.so" ];then
            echo -e "Checking php7.1 pcntl extension if installed ... yes"
            #check if the plugin is enabled
            status=$(php -m | grep -i "pcntl")
               if [ -z "$status" ];then
                  echo -e "Check php7.1 pcntl extension if enabled  ... no"
                  echo -e "Modifying the configuration file to activate php7.1 pcntl extension ... "
                  if [ ! -e "/usr/local/php7.1/lib/php.ini" ];then
                     cp /usr/local/src/php-7.1.9/php.ini-development  /usr/local/php7.1/lib/php.ini
                  fi;
                  sed '912  a \ extension=pcntl.so' /usr/local/php7.1/lib/php.ini>/usr/local/php7.1/lib/php.ini.tmp && mv /usr/local/php7.1/lib/php.ini.tmp /usr/local/php7.1/lib/php.ini 
                  if [ $? == 0 ];then   
                     echo -e "Modifying the configuration file to activate php7.1 pcntl extension ... done"
                  else 
                     echo -e "Modifying the configuration file to activate php7.1 pcntl extension ... failed"
                  fi; 
               else 
                  echo -e "Check php7.1 pcntl extension if enabled ... yes"
               fi
       else 
            echo -e "Checking php7.1 pcntl extension if installed ... no"
            #Compile pcntl extension for php7.1 for multi-process
            echo -e "Compiling pcntl for php7.1  multi-processes ... " 
            cd /usr/local/src/php-7.1.9/ext/pcntl
            phpize . 
            ./configure --with-php-config=/usr/local/php7.1/bin/php-config && make clean && make && make install 
            if [[ $? == 0 ]];then
               echo -e "Compiling pcntl for php7.1 mulit-process ... done"
               echo -e "Modifying the configuration file to activate php7.1 pcntl extension ... "
               if [ ! -e "/usr/local/php7.1/lib/php.ini" ];then
                  cp /usr/local/src/php-7.1.9/php.ini-development  /usr/local/php7.1/lib/php.ini
               fi;
               sed '912  a \ extension=pcntl.so' /usr/local/php7.1/lib/php.ini>/usr/local/php7.1/lib/php.ini.tmp && mv /usr/local/php7.1/lib/php.ini.tmp /usr/local/php7.1/lib/php.ini 
               if [ $? == 0 ];then   
                  echo -e "Modifying the configuration file to activate php7.1 pcntl extension ... done"
               else 
                  echo -e "Modifying the configuration file to activate php7.1 pcntl extension ... failed"
               fi; 
               #check if the plugin is enabled
               status=$(php -m | grep -i "pcntl")
               if [ ! -z "$status" ];then
                  echo -e "Check if php7.1 pcntl extension enabled ... on"
               else 
                  echo -e "Check php7.1 pcntl extension enabled ... off"
               fi
            else 
              echo -e "Failed to compile pcntl extension."
            fi;
       fi

      #Check if pthread extension enabled
      echo -e "Checking  php7.1 pthread extension if installed ... "
      if [ -e "/usr/local/php7.1/lib/php/extensions/no-debug-zts-20160303/pthreads.so" ];then
          echo -e "Checking  php7.1 pthread extension if installed ... yes "
          echo  -e "Checking  php7.1 pthreads extension if enabled ..."
          status=$(php -m | grep -i "pthreads")
          if [ ! -z "$status" ];then
              echo -e "Check php7.1 pthreads extension if enabled ... yes"
          else 
              echo -e "Check php7.1 pthreads extension if enabled ... no"
              echo -e "Modifying the configuration file to activate php7.1 pthreads extension ... "
                if [ ! -e "/usr/local/php7.1/lib/php.ini" ];then
                  cp /usr/local/src/php-7.1.9/php.ini-development  /usr/local/php7.1/lib/php.ini
                fi;
                   sed '912  a \ extension=pthreads.so' /usr/local/php7.1/lib/php.ini>/usr/local/php7.1/lib/php.ini.tmp && mv /usr/local/php7.1/lib/php.ini.tmp /usr/local/php7.1/lib/php.ini 
                if [ $? == 0 ];then   
                   echo -e "Modifying the configuration file to activate php7.1 pthreads extension ... done"
                else 
                   echo -e "Modifying the configuration file to activate php7.1 pthreads extension ... failed"
                fi; 
          fi
      else 
          echo -e "Checking  php7.1 pthread extension if installed ... no "
          #Because this pthread version in pecl repositry have some incompatiable problems with php7.1,so proning to compile it from source hosted in github
          echo -e "Compiling pthreads for php7.1 multi-threads ..."
	  if [ ! -e "/tmp/pthreads" ];then
              cd /tmp/
              git clone https://github.com/krakjoe/pthreads.git
              if [[ $? != 0 ]];then 
                 echo -e "Failed to download php7.1 pthreads source file."
                 exit 0
              fi;
          fi;
          #Check out for the specified version,the newest version have some incompatiable problems with php7.1
          cd /tmp/pthreads 
          git checkout c521adc7b645b9a60f8c3e9b6f1331c7dc6b428b
          phpize . && ./configure --with-php-config=/usr/local/php7.1/bin/php-config && make clean &&  make && make install
          if [[ $? == 0 ]];then
               echo -e "Compiling pthreads for php7.1  multi-threads ... done"
               echo -e "Modifying the configuration file to activate php7.1 pthreads extension ... "
               if [ ! -e "/usr/local/php7.1/lib/php.ini" ];then
                  cp /usr/local/src/php-7.1.9/php.ini-development  /usr/local/php7.1/lib/php.ini
               fi;
               sed '912  a \ extension=pthreads.so' /usr/local/php7.1/lib/php.ini>/usr/local/php7.1/lib/php.ini.tmp && mv /usr/local/php7.1/lib/php.ini.tmp /usr/local/php7.1/lib/php.ini 
               if [ $? == 0 ];then   
                 echo -e "Modifying the configuration file to activate php7.1 pthreads extension ... done"
               else 
                 echo -e "Modifying the configuration file to activate php7.1 pthreads extension ... failed"
               fi; 
               #check if the plugin is enabled
               status=$(php -m | grep -i "pthreads")
               if [ ! -z "$status" ];then
                  echo -e "Check php7.1 pthreads extension ... on"
               else 
                  echo -e "Check php7.1 pthreads extension ... off"
               fi
          else 
               echo -e "Compiling pthreads for php7.1 multi-threads ... failed"
          fi
 
      fi;
   else 
       echo -e "php7.1 installation failed"
       exit 0
    fi
    return 1;
}
function package_install() {
  echo -e "Adding essential repository ... " 
  echo -e "Adding epel-release repository ..."
  exist_package epel-release
  if [[ $? == 0 ]];then
     yum install  epel-release -y 
     if [[ $? == 0 ]];then
       echo -e "Adding epel-realse repository ... done"
     else 
       echo -e "Failed to install epel-realse repository"
     fi;
     echo -e "Adding epel-rease repository ... done"
  fi;
  echo -e "Adding essential repository ... done"
  echo -e "Installing base pages ..."
  declare -a unins_packages=()
  for i in ${!packages[@]}; do 
      exist_command ${packages[$i]}
      if [[ $? == 0 ]]; then
         unins_packages[$i]=${packages[$i]};
      else 
         echo -e "package  ${packages[$i]}... installed"  
      fi 
  done
  if [[ ${#unins_packages[@]} > 0 ]];then
        yum install ${unins_packages[@]} -y
  fi
  inotify_install
  php_config
  source $cpath/devtools_install.sh
  echo -e "Installing base pages ... done"
}

function cosmetic_terminal() {
    exist_command zsh
    if [ $? -eq 0 ];then
      echo  "Installing zsh ..."
      yum install zsh -y
      if [ $? -eq 0 ];then
        echo "Installing zsh ... done"
      else
        echo -e "Failed to install zsh"
        exit 0
      fi;
    fi;
    if [  ! -e "$HOME/.oh-my-zsh" ];then
      echo "Installing oh-my-zsh ..."
      cd ~
      sh -c "$(curl -fsSL https://raw.githubusercontent.com/robbyrussell/oh-my-zsh/master/tools/install.sh)"
      if [[ $? != 0 ]]; then
        echo -e "Failed to install oh-my-zsh";
        exit 0;
      else
        echo -e "Installing oh-my-zsh ... done"
        #diable git status hints
 	git config  --global --add oh-my-zsh.hide-status 1
        git config  --global --add oh-my-zsh.hide-dirty 1
      fi;
    fi;
    exist_command tmux
    if [ $? -eq 0 ];then
       echo "Installing tmux"
       yum install tmux -y
       if [ $? -eq 0 ];then
	  echo "Installing tmux ... done"
       else 
          echo  -e "Failed to install tmux"
          exit 0
       fi;
    fi;
}

#config docker environment
function config_docker() {
   #Check kernel version if lower 2.6.32-696.10.2.el6.x86_64
   expected_ver="2.6.32-696.10.2.el6.x86_64"
   current_ver=$(uname -r)
   if [[ $(echo -e "${expected_ver}\n${current_ver}"| sort -V | head -n 1) != $expected_ver ]];then
      echo -e "Current kernel is ${current_ver}, but >=$expected_ver is expected ..."
      echo  "Updating kernel ..."
      yum update kernel
      echo -e "Updating kernel ... done"
      echo -e "Please reboot machine and execute this script again"
      echo -e "Rebooting  ..."
      reboot
   fi;
   echo  "Starting docker ..."
   exist_command service
   #Use old service tool
   if [ $? == 0 ];then
       echo -e "Installing service tool ..."
       yum install -y initscripts
       if [ $? == 0 ];then
         echo -e "Installing service tool ... done"
       else 
         echo -e "Failed to install service tool"
         exit 0
       fi;
   fi
   service docker start >/dev/null 2>&1
   if [ $? == 0 ]; then 
	   echo -e "Starting docker ... done"
   else 
           echo -e  "Failed to start docker as service"
           echo -e  "Trying to start docker as daemon directly ... "
           nohup dockerd >/dev/null& 2>&1
           if [ $? == 0 ];then 
                echo -e "Trying to start docker as daemon directly ... done"
           else 
                echo -e "Failed to start docker as daemon"
           fi
   fi
   echo  "Adding docker as autostart service ..."
   chkconfig  docker on >/dev/null 2>&1
   if [ $? = 0 ]; then 
     echo  "Adding docker as autostart service ... done"
   else 
     echo -e "Failed to add docker as autostart service"
   fi;
}

#config env and some tweaking configurations for software
function preconfig() {
   echo -e "Configuring ..."
   # start docker  service and append it to autostart service
   config_docker
   cosmetic_terminal
   echo -e "Configuring ... done"
}
function develop_os() {
  #Check if the host is centos
  eval OS_INFO=("$(os_type)")
  if [[ ${OS_INFO[0]} == 'Centos' ]]; then
       package_install 
       if [ $? != 0 ];then
           echo -e "Error: Unknow error occured in processing package installation!"
       fi;
       preconfig
  fi
}
develop_os
