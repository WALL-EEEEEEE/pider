- [安装](#org2e9d1f0)
  - [需求](#org5f2faad)
  - [安装](#org671959c)


<a id="org2e9d1f0"></a>

# 安装


<a id="org5f2faad"></a>

## 需求

-   PHP >= 7.1
-   pcntl(可选，多进程需要)


<a id="org671959c"></a>

## 安装

```shell
git clone https://github.com/duanqiaobb/pider.git
//安装composer,如果出现问题，请参考 [composer官方文档](https://getcomposer.org/download/) 进行安装
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php -r "if (hash_file('SHA384', 'composer-setup.php') === '544e09ee996cdf60ece3804abc52599c22b1f40f4323403c44d44fdfdd586475ca9813a858088ffbc1f233e9b180f061') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
php composer-setup.php
php -r "unlink('composer-setup.php');"
//安装
cd pider
chmod u+x install.sh
./install.sh
```
