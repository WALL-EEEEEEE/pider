#!/bin/bash 

#This file is used to install some development tools 

source util.sh

function vim_install(){
 
   echo -e "Checking if have vim in this computer ..."
   exist_package *vim*
   if [[ $? != 0 ]];then
       echo "Checking if have vim in this computer ... yes"
       yum erase -y *vim* 2>/dev/null 
   else 
       echo "Checking if have vim in this computer ... no"
   fi

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

   echo "Checking git ..."
   exist_package git && exist_command git
   if [[ $? == 0 ]];then
       echo "Checking git ... no"
       yum install -y git
   else 
      echo "Checking git ... yes"
   fi

   echo "Checking make ..."
   exist_package make && exist_command make
   if [[ $? == 0 ]];then
       echo "Checking make ... no"
       yum install -y make
   else
       echo "Checking make ... yes"
   fi
   echo "Checking cmake ..."
   exist_package cmake && exist_command cmake
   if [[ $? == 0 ]];then
       echo "Checking cmake ... no"
       yum install -y cmake
   else
       echo "Checking cmake ... yes"
   fi
   
   echo "Checking g++ ..."
   exist_package gcc-c++ && exist_command g++
   if [[ $? == 0 ]];then
       echo "Checking g++ ... no"
       yum install -y gcc-c++
   else
       echo "Checking g++ ... yes"
   fi

   echo "Checking gcc ..."
   exist_package gcc && exist_command gcc
   if [[ $? == 0 ]];then
       echo "Checking gcc ... no"
       yum install -y gcc
   else 
       echo "Checking gcc ... yes"
   fi



   echo -e "Installing vim source ..."
   cd /tmp/
   #check vim directory whether empty or not 
   if [[ ! -e '/tmp/vim' ]];then
       echo -e "Downloading vim source ..."
       git clone https://github.com/vim/vim.git
       if [ $? -ne 0 ];then
           echo -e "Failed to download vim source file ..."
           exit 0
       fi;
   fi;
   cd /tmp/vim
   #prepare its dependency
   #detect  pecl 
   echo "Checking perl ..."
   exist_command perl && exist_package perl
   if [[ $? == 0 ]];then
       echo "Checking perl ... no"
       yum install perl -y 
   else
       echo "Checking perl ... yes"
   fi

   #detect pecl-ExtUtils-Embed 
   echo "Checking perl-ExtUtils-Embed ..."
   exist_package perl-ExtUtils-Embed
   if [[ $? == 0 ]];then
       echo "Checking  perl-ExtUtils-Embed ... no"
       yum install perl-ExtUtils-Embed -y 
   else
       echo "Checking perl-ExtUtils-Embed ... yes"
   fi



   #detect pecl developement file
   echo "Checking perl-devel ..."
   exist_package perl-devel
   if [[  $? == 0 ]];then
       echo "Checking perl-devel ... no"
       #yum install perl-ExtUtils-Embed -y
       yum install perl-devel -y
 else 
       echo "Checking perl-devel ... yes"
   fi;


   echo "Checking ruby ... "
   #detect ruby 
   exist_command ruby  &&exist_package  ruby
   if [[ $? == 0 ]];then
      echo "Checking ruby ... no"
      yum install  ruby -y
  else
      echo "Checking ruby ... yes"
   fi
   echo "Checking ruby-devel  ... "
   #detect ruby development file
   exist_package ruby-devel
   if [[ $? == 0 ]];then
       echo "Checking ruby-devel ... no "
       yum install ruby-devel -y
   else
       echo "Checking ruby-devel ... yes"
   fi;

   echo "Checking lua ..."
   #detect ruby 
   exist_command lua  &&exist_package lua 
   if [[ $? == 0 ]];then
      echo "Checking lua ... no"
      yum install  lua -y
  else 
      echo "Checking lua ... yes"
   fi
   echo "Checking lua-devel ... "
   #detect ruby development file
   exist_package lua-devel
   if [[ $? == 0 ]];then
       echo "Checking lua-devel ... no"
       yum install lua-devel -y
   else 
       echo "Checking lua-devel ... yes"
   fi;
   echo "Checking python ..."
   #detect python
   exist_command python && exist_package python
   if [[ $? == 0  ]] ;then
       echo "Checking python ... no"
       yum install python -y
   else 
       echo "Checking python ... yes"
   fi; 
   echo "Checking python-devel ..."
   #detect python-devel 
   exist_package python-devel
   if [[ $? == 0 ]];then
       echo "Checking python-devel ... no"
       yum install python-devel -y
   else 
       echo "Checking python-devel ... yes"
   fi;
   echo "Checking python3 ..."
   #detect python3
   exist_command python3 && exist_package python3*
   if [[ $? == 0  ]] ;then
       echo "Checking python3 ... no"
       yum install python3* -y
   else
       echo "Checking python3 ... yes"
   fi; 
   echo "Checking python3-devel ..."
   #detect python3-devel 
   exist_package python3*-devel
   if [[  $? == 0 ]];then
       echo "Checking python3-devel ... no"
       yum install python3*-devel -y
   else
       echo "Checking python3-devel ... yes"
   fi;

   echo "Checking go ..."
   #detect python3-devel 
   exist_package golang && exist_command go
   if [[  $? == 0 ]];then
       echo "Checking go ... no"
       yum install golang -y
   else
       echo "Checking go ... yes"
   fi;

   echo "Checking nodejs ..."
   #detect nodejs 
   exist_package nodejs
   if [[  $? == 0 ]];then
       echo "Checking nodejs ... no"
       yum install nodejs -y
   else
       echo "Checking nodejs ... yes"
   fi;

   echo "Checking php ..."
   exist_package php && exist_command php
   if [[ $? == 0 ]];then
       echo "Checking php ... no"
       yum install -y php
   else
       echo "Checking php ... yes"
   fi


   echo "Checking ncurses-devel "
   exist_package ncurses-devel
   if [[  $? == 0 ]];then
       echo "Checking ncurses-devel ... no"
       yum install ncurses-devel -y
   else
       echo "Checking ncurses-devel ... yes"
   fi;

   echo "Checking libX11-devel ... "
   exist_package libX11-devel
   if [[  $? == 0 ]];then
       echo "Checking libX11-devel ... no"
       yum install libX11-devel -y
   else
       echo "Checking libX11-devel ... yes"
   fi;

   echo "Checking gtk3-devel ... "
   exist_package gtk3-devel
   if [[  $? == 0 ]];then
       echo "Checking gtk3-devel ... no"
       yum install gtk3-devel -y
   else
       echo "Checking gtk3-devel ... yes"
   fi;
   echo "Checking libxt-devel ... "
   exist_package libXt-devel
   if [[ $? == 0 ]];then
       echo "Checking libxt-devel ... no"
       yum install libXt-devel -y
   else
       echo "Checking libxt-devel ... yes"
   fi
   #compile vim
   make distclean && ./configure --prefix=/usr/local --enable-luainterp=dynamic --enable-perlinterp=dynamic --enable-rubyinterp=dynamic --enable-pythoninterp=dynamic --enable-python3interp=dynamic --enable-gui=gtk3  --enable-gtk3-check  --enable-cscope --with-python-config-dir=/usr/lib64/python2.7/config/ --with-python3-config-dir=/usr/lib64/python3.4/config-3.4m --with-x 
  make clean &&  make && make install 
   if [[ $? != 0 ]];then
       echo "Compiling vim failed"
       exit 0
   fi
   echo "Compiliing vim successful."
   ln -sf /usr/local/bin/vim /usr/bin/vim
   ln -sf /usr/local/bin/vimdiff /usr/bin/vimdiff
   echo "Installing vim ... done"
   echo "Configuring vim ..."
   if [[ -e $HOME/.vim ]];then
       echo "Old vim configuration finded"
       echo "Backing up your old vim configurations ..."
       if [[ -e $HOME/.vim_backup ]];then
           rm -rf ~/.vim_backup
       fi
       mv -f ~/.vim ~/.vim_backup
       if [ $? == 0 ];then
           echo "Backing up your old vim configurations ... done"
       else
           echo "Backing up your old vim configurations ... failed"
           exit 0
       fi
   fi
   git clone https://github.com/duanqiaobb/Mvim.git ~/.vim  
   if [[ $? != 0  ]];then
       echo "Network Error: Can't download Mvim.Please Check you network"
   fi
   ln -sf ~/.vim/.vimrc ~/.vimrc
   echo "Configuring YoucompleteMe for you ..."
   cd ~/.vim/bundle/YouCompleteMe
   python ./install.py  --clang-completer --gocode-completer --tern-completer
   if [[ ! $? == 0  ]];then
       echo "Compilation Error: Compiling YoucompleteMe Error"
       exit 0
   fi
   echo "Configuring YouCompleteMe for you ... done"
   echo "Configuring vim .... done "
}
vim_install
