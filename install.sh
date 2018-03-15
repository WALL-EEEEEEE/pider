#!/usr/bin/env bash

: '
This Scripts is used to configure a centos linux machine to a spider development hosts automatically!
'
cpath=$(dirname $(readlink -f $0))/setup
source $cpath/util.sh
declare -a packages=("git" "wget" "curl" "gcc" "make" "bzip2" "autoconf")
declare -a package_manager='yum'
declare -a package_manager_remove='yum erase'
declare -a package_manager_install='yum install'
declare -a os=Centos

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
    set -o pipefail
    if_pre=$(exist_package php)
    if [ $? -ne 0 ];then
        $package_manager_remove php php-common php-cli -y 
    fi
    if_installed=$(exist_command php7.1)
    #check if this script has been executed,avoid being compiled over and over again.
    if [[  $? == 1 ]] || [ -e '/usr/local/php7.1' ];then
        return 1
    fi;
    echo  "Installing php 7.1 ..."
    cd /usr/local/src 
    if [ ! -e "/usr/local/src/php-7.1.9.tar.bz2" ];then
        sudo wget -c http://cn2.php.net/distributions/php-7.1.9.tar.bz2
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
    if [[ $os == 'Centos' ]]; then
        echo -e "Adding essential repository ... " 
        echo -e "Adding epel-release repository ..."
        exist_package epel-release
        if [[ $? == 0 ]];then
            $package_manager_install  epel-release -y 
            if [[ $? == 0 ]];then
                echo -e "Adding epel-realse repository ... done"
            else 
                echo -e "Failed to install epel-realse repository"
            fi;
            echo -e "Adding epel-rease repository ... done"
        fi;
        echo -e "Adding essential repository ... done"
    fi
    echo -e "Installing base pakages ..."
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
        $package_manager_install ${unins_packages[@]} -y
    fi
    php_config
    echo -e "Installing base packagees ... done"
}

function dependency_install() {
    #Check if the host is centos
    eval OS_INFO=("$(os_type)")
    os=${OS_INFO[0]}
    if [[ ${OS_INFO[0]} == 'Centos' ]]; then
        package_manager='yum'
        package_manager_install='yum install'
        package_manager_remove='yum erase'
    elif [[ ${OS_INFO[0]} == 'Ubuntu' ]]; then 
        package_madnager='apt'
        package_manager_install='apt-get install'
        package_manager_remove='apt-get remove'
    fi
    package_install 
    if [ $? != 0 ];then
        echo -e "Error: Unknow error occured in processing package installation!"
    fi;
    echo "Installing composer dependency ..."
    composer install
    echo "Installing composer dependency ... done"
}
dependency_install
