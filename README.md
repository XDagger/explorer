# XDAG Block Explorer

To install:
- install PHP processing, at least PHP 7.1 and install composer. This project doesn't need a database.
- clone this repository
- `cp .env.dist .env`, set `APP_DEBUG=0`, set `APP_ENV=prod` and write your own random string in `APP_SECRET`, at least 32 characters
- `cp config/services.yaml.dist config/services.yaml`. Set your socket path. 
- `composer install`

If you're using nginx, make sure to set `merge_slashes off;` in `server` block to allow raw URLs to be passed to the block explorer.

All should be done! :)
