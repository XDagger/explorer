# XDAG Block Explorer
This software provides a detailed view of the xdag network presented in a nice UI.

# Features
- network statistics and graphs
- list of latest main blocks
- mining calculator
- balance checker
- block details
- API interface
- responsive design

# Installation on Ubuntu 22.04
1. install, configure and run [XdagJ](https://github.com/XDagger/xdagj)
2. `adduser explorer` - explorer runs as regular user, sudo premissions should not be given. Continue as root or as regular user with sudo permissions.
3. install PHP8.2
- `sudo apt install lsb-release ca-certificates apt-transport-https software-properties-common`
- `sudo add-apt-repository ppa:ondrej/php`
- `sudo apt install php8.2-fpm php8.2-cli php8.2-bcmath php8.2-curl php8.2-gd php8.2-mbstring php8.2-mysql php8.2-opcache php8.2-readline`
- create PHP-FPM pool: `nano /etc/php/8.2/fpm/pool.d/explorer.conf`
```
[explorer]
user = $pool
group = $pool
listen = /run/php/php8.2-fpm-$pool.sock

listen.owner = www-data
listen.group = www-data
listen.mode = 0660
pm = dynamic
pm.max_children = 10
pm.start_servers = 3
pm.min_spare_servers = 1
pm.max_spare_servers = 4
```
- `sudo systemctl enable php8.2-fpm`
- `sudo systemctl restart php8.2-fpm`
4. install MySQL 8.0+
- `sudo apt install mysql-server mysql-client`
- `sudo mysql`
- `ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY '...............';` - choose a strong password
- `exit`
- `sudo mysql_secure_installation`
5. configure MySQL 8.0+
- `sudo nano /etc/mysql/mysql.conf.d/mysqld.cnf`
- add `disable_log_bin` at end of file
- add `tmp_table_size = 2G` at end of file
- add `max_heap_table_size = 2G` at end of file
- `sudo systemctl enable mysql`
- `sudo systemctl restart mysql`
6. create database and MySQL user for explorer app
- `sudo mysql -p` - enter root password
- `CREATE USER explorer@'%' IDENTIFIED BY '...............';` - choose a strong password
- `CREATE DATABASE explorer CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;`
- `GRANT ALL ON explorer.* TO explorer@'%';`
- `FLUSH PRIVILEGES;`
- `exit`
7. install [composer](https://getcomposer.org/download/)
8. install NojdeJS 18
- `curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -`
- `sudo apt install -y nodejs`
9. prepare explorer app
- `sudo mkdir /var/www/explorer && sudo chown explorer:explorer /var/www/explorer`
- as `explorer` user, change into `/var/www/explorer` folder
- execute `git clone https://github.com/XDagger/explorer.git .`
- execute `composer install`, `npm ci`, `npm run production`, `cp .env.example .env`, `php artisan key:generate`
- edit `.env` and supply MySQL connection parameters and XdagJ RPC URL
- execute `php artisan migrate`
- add crontab entry: `* * * * * /usr/bin/php8.2 /var/www/explorer/artisan schedule:run >> /dev/null 2>&1`
10. install and configure nginx
- `sudo apt install nginx`
- replace default server: `truncate -s 0 /etc/nginx/sites-available/default`, `nano /etc/nginx/sites-available/default`
```
server {
	listen 80 default_server;
	listen [::]:80 default_server;
	server_name _;

	merge_slashes off;
	root /var/www/explorer/public;
	index index.php;

	location / {
		try_files $uri $uri/ /index.php?$query_string;
	}

	location = /index.php {
		include fastcgi_params;
		fastcgi_read_timeout 320;
		fastcgi_param DOCUMENT_ROOT $realpath_root;
		fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
		fastcgi_pass unix:/run/php/php8.2-fpm-explorer.sock;
	}

	location = /favicon.ico {
		access_log off;
		log_not_found off;
	}

	location = /robots.txt {
		access_log off;
		log_not_found off;
	}
}
```
- `sudo systemctl enable nginx`
- `sudo systemctl restart nginx`
10. optionally install Let's Encrypt certificate, configure https redirects

# Updating to latest version
As `explorer` user, change into `/var/www/explorer` folder
- `git pull`
- `composer install`
- `php artisan migrate`
- `npm ci`
- `npm run production`
