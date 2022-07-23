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

# Installation on Ubuntu 20.04
1. install, configure and run [XdagJ](https://github.com/XDagger/xdagj)
2. `adduser explorer` - explorer runs as regular user, sudo premissions should not be given. Continue as root or as regular user with sudo permissions.
3. install PHP8.1
- `sudo apt install lsb-release ca-certificates apt-transport-https software-properties-common`
- `sudo add-apt-repository ppa:ondrej/php`
- `sudo apt install php8.1-fpm php8.1-cli php8.1-bcmath php8.1-bz2 php8.1-curl php8.1-gd php8.1-gmp php8.1-imap php8.1-intl php8.1-ldap php8.1-mbstring php8.1-mysql php8.1-opcache php8.1-pgsql php8.1-readline php8.1-soap php8.1-sqlite3 php8.1-xml php8.1-zip php8.1-imagick php8.1-redis`
- create PHP-FPM pool: `nano /etc/php/8.1/fpm/pool.d/explorer.conf`
```
[explorer]
user = explorer
group = explorer
listen = /run/php/php8.1-fpm-$pool.sock

listen.owner = www-data
listen.group = www-data
listen.mode = 0660
pm = dynamic
pm.max_children = 10
pm.start_servers = 3
pm.min_spare_servers = 1
pm.max_spare_servers = 4
```
- `sudo systemctl enable php8.1-fpm`
- `sudo systemctl restart php8.1-fpm`
4. install MySQL 8.0+
- `sudo apt install mysql-server mysql-client`
- `sudo mysql_secure_installation`
5. create database and MySQL user for explorer app
- `sudo mysql`
- `CREATE USER explorer@'%' IDENTIFIED BY '...............';` - choose a strong password
- `CREATE DATABASE explorer CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;`
- `GRANT ALL ON explorer.* TO explorer@'%';`
- `FLUSH PRIVILEGES;`
- `exit`
6. install [composer](https://getcomposer.org/download/)
7. install NojdeJS 16
- `curl -fsSL https://deb.nodesource.com/setup_16.x | sudo -E bash -`
- `sudo apt install -y nodejs`
8. prepare explorer app
- `sudo mkdir /var/www/explorer && sudo chown explorer:explorer /var/www/explorer`
- as `explorer` user, change into `/var/www/explorer` folder
- execute `git clone git@github.com:XDagger/explorer.git .`
- execute `composer install`, `npm ci`, `npm run prod`, `cp .env.example .env`, `php artisan key:generate`
- edit `.env` and supply MySQL connection parameters and XdagJ RPC URL
- execute `php artisan migrate`
- add crontab entry: `* * * * * /usr/bin/php /var/www/explorer/artisan schedule:run >> /dev/null 2>&1`
9. install and configure nginx
- `sudo apt install nginx`
- replace default server: `truncate -s 0 /etc/nginx/sites-available/default`, `nano /etc/nginx/sites-available/default`
```
server {
	merge_slashes off;

	listen 80 default_server;
	listen [::]:80 default_server;
	server_name _;

	client_max_body_size 512M;

	root /var/www/explorer/public;

	index index.php;

	location / {
		try_files $uri $uri/ /index.php?$query_string;
	}

	location ~ /\.ht {
		deny all;
	}

	location ~ \.php$ {
		try_files $uri =404;
		fastcgi_pass unix:/run/php/php8.1-fpm-explorer.sock;
		fastcgi_index index.php;
		fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
		include fastcgi_params;
		fastcgi_read_timeout 300;
	}

	location ~ /\.git {
		deny all;
	}
}
```
- `sudo systemctl enable nginx`
- `sudo systemctl restart nginx`
10. optionally install Let's Encrypt certificate, configure https redirects

# Updating to latest version
As `explorer` user, change into `/var/www/explorer` folder
- `git pull`
- `php artisan migrate`
- `npm ci`
- `npm run prod`
