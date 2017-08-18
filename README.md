### 框架安装

- 安装方法

    一、直接安装

    * 克隆代码

            git clone git@192.168.1.12:crm/crm-admin.git

    * conposer安装组件

            composer install #第一次安装 
            composer update

    * 初始化项目

            php init
            
    * 数据库配置
    
            common/config/main-local.php

    * 配置服务器（nginx）

            server {
               charset utf-8;
               client_max_body_size 128M;

               listen 80; 
               server_name ###;
               root        /path/to/crm-admin/frontend/web/;
               index       index.php;


               location / {
                   try_files $uri $uri/ /index.php?$args;
               }

  
               location ~ \.php$ {
                   include fastcgi_params;
                   fastcgi_param SCRIPT_FILENAME $document_root/$fastcgi_script_name;
                   #fastcgi_pass   127.0.0.1:9000;
                   fastcgi_pass unix:/var/run/php5-fpm.sock;
                   try_files $uri =404;
               }

               location ~ /\.(ht|svn|git) {
                   deny all;
               }
            }
            
    * 配置服务器（apache）
     
     
            <VirtualHost *:80>
               ServerName ###;
               DocumentRoot "/path/to/yii-application/backend/web/"
            
               <Directory "/path/to/yii-application/backend/web/">
                   # use mod_rewrite for pretty URL support
                   RewriteEngine on
                   # If a directory or a file exists, use the request directly
                   RewriteCond %{REQUEST_FILENAME} !-f
                   RewriteCond %{REQUEST_FILENAME} !-d
                   # Otherwise forward the request to index.php
                   RewriteRule . index.php
            
                   # use index.php as index file
                   DirectoryIndex index.php
            
                   # ...other settings...
               </Directory>
            </VirtualHost>