# XDAG Block Explorer
This software provides a detailed view on the xdag network presented in a nice, comfortable UI for the users.

# Features
- responsive design
- network statistics on the home page, including a list of 20 latest main blocks, with creation time
- network graphs (network hashrate, number of blocks created each minute)
- balance checker
- mining estimation based on miner's hashrate
- block kind estimation (Main block, Transaction block, Wallet)
- extended block statistics (balance, earnings and spendings change in last 24 hours, balance, earnings and spendings graph for last 7 days, for transaction blocks, sum of inputs, fees and outputs)
- block listing filters, and pagination
- block listing summaries (earnings / inputs, spendings / outputs on each page)
- switching of block as address and block as transaction panels based on block kind
- extensive caching for best performance, blocks are cached for 3 minutes, balances and other commands are cached for 1 minute
- ability to handle very large blocks, data is never fully loaded into memory
- rich and documented API
- text mode for older / mobile browsers
- support for local development (without a real xdag daemon)
- support for test net (differences in reported "normal" daemon state)

# Planned features
- internationalization

# Expected skills
In order to run the block explorer you should be fluent in Unix / Linux administration and have basic understanding of computer programming.
You should be familiar with the xdag daemon and it's settings.

# Pull requests
Please submit your pull requests with new features, improvements and / or bugfixes. Utilize the GitHub issue tracker if necessary. Please note that in order to develop the block explorer,
good Laravel 5, webpack, mix, blade, sass, javascript and tailwind experience is needed. All pull requests must have reasonable code quality and security.

# Dependencies
- pool version at least 0.2.1
- nginx, php7.1+, mariadb or mysql, nodejs 8.x

# How the block explorer works
Block explorer connects directly to xdag daemon's socket file, where it issues necessary commands. Command outputs are most often cached for further processing.
Cron scripts run each minute and log current network state, newly created blocks and main blocks, this information is stored in a database, wich allows
the application to present the data to the end user as fast as possible.

# Installation
This giude expects that your xdag daemon is already running. For local development, follow the appropriate steps.
The guide can't go in-depth on every step, however all important details are provided.

Perform the following steps in order to install the block explorer:
1. set your system timezone to `UTC`, execute `dpkg-reconfigure tzdata` and choose `UTC`.
2. install all PHP7.1 requirements, for Ubuntu 16.04, use `apt-get install php7.1-bcmath php7.1-cli php7.1-common php7.1-fpm php7.1-json php7.1-mbstring php7.1-mcrypt php7.1-mysql php7.1-opcache php7.1-readline php7.1-xml autoconf libtool nasm`. Next configure `php.ini` to your preference. Set `memory_limit` to at least `256M`, `expose_php` to `Off`, set `error_reporting` to `E_ALL`.
3. install mysql 5.7 or newer or mariadb. Create a new database, for example `xdagexplorer`, with `CREATE DATABASE xdagexplorer CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;` run as mysql's `root` user. Grant all privileges to a new user: `GRANT ALL ON xdagexplorer.* TO xdagexplorer@'%' IDENTIFIED BY 'PWD!!!!';`. Choose your own password!
4. install nginx and set up a PHP FPM pool running as the same user as the xdag daemon itself.
5. configure nginx to properly execute the block explorer. Add `merge_slashes off;` in the `server` block to support xdag addresses containing multiple consesuctive slashes.
6. install [composer](https://getcomposer.org/download/) and [nodejs 8.x](https://nodejs.org/en/download/package-manager/#debian-and-ubuntu-based-linux-distributions)
7. proceed as `pool` or other user that xdag daemon runs as. Clone this project into `/var/www/explorer`. `cd` to this folder.
8. execute `cp .env.example .env`
9. edit `.env` and set up correct values. Set database connection info. For usage with real xdag daemon, set `XDAG_USE_REAL_SERVICE` to `true` and set `XDAG_SOCKET_FILE` path properly. For local development, leave `XDAG_SOCKET_FILE` empty and set `XDAG_USE_REAL_SERVICE` to `false`. Make sure you set `APP_DEBUG` to `false` for production usage.
10. in `/var/www/explorer`, run `composer install`
11. run `php artisan key:generate`
12. run `php artisan migrate`
13. run `npm install` and then `npm run production`
14. run `php artisan explorer:log-network` and `explorer:fetch-new-last-blocks`, make sure these commands completed successfully.
15. install a letsencrypt certificate or other https certificate (optional)
16. as the same user the xdag pool daemon runs as, execute `crontab -e` and enter one cron line: `* * * * * php /var/www/explorer/artisan schedule:run >> /dev/null 2>&1`

Done! Enjoy your new XDAG Block Explorer instance! ;-)
