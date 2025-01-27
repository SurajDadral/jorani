#!/bin/bash

echo "Entered in file launcher.sh"

# Create a bunch of folders under the clean /var that php, nginx, and mysql expect to exist
mkdir -p /var/lib/mysql
mkdir -p /var/lib/mysql-files
mkdir -p /var/lib/nginx
mkdir -p /var/lib/php/sessions
mkdir -p /var/log
mkdir -p /var/log/mysql
mkdir -p /var/log/nginx
# Wipe /var/run, since pidfiles and socket files from previous launches should go away
# TODO someday: I'd prefer a tmpfs for these.
rm -rf /var/run
mkdir -p /var/run/php
rm -rf /var/tmp
mkdir -p /var/tmp
mkdir -p /var/run/mysqld

# Ensure mysql tables created
# HOME=/etc/mysql /usr/bin/mysql_install_db
HOME=/etc/mysql /usr/sbin/mysqld --initialize-insecure

# Spawn mysqld, php
HOME=/etc/mysql /usr/sbin/mysqld &
/usr/sbin/php-fpm7.3 --nodaemonize --fpm-config /etc/php/7.3/fpm/php-fpm.conf &
# Wait until mysql and php have bound their sockets, indicating readiness
while [ ! -e /var/run/mysqld/mysqld.sock ] ; do
    echo "waiting for mysql to be available at /var/run/mysqld/mysqld.sock"
    sleep .2
done
while [ ! -e /var/run/php/php7.3-fpm.sock ] ; do
    echo "waiting for php-fpm7.0 to be available at /var/run/php/php7.3-fpm.sock"
    sleep .2
done

echo "Lets create database.."
# Create database for jorani
echo "CREATE DATABASE IF NOT EXISTS jorani;CREATE USER 'jorani'@'localhost' IDENTIFIED BY 'jorani';GRANT ALL PRIVILEGES ON jorani.* TO 'jorani'@'localhost';use jorani;source /opt/app/sql/jorani.sql;
" | mysql -u root
echo "Database created"
echo "ALTER USER 'jorani'@'localhost' IDENTIFIED WITH mysql_native_password BY 'jorani';" | mysql -u root

echo "Lets host jorani in /var/www/ directory"
mkdir -p /var/www
cp -rf /opt/app/* /var/www/

echo "Lets start nginx"
# Start nginx.
/usr/sbin/nginx -c /opt/app/.sandstorm/service-config/nginx.conf -g "daemon off;"
echo "nginx configured"
