cd ~/domains/snow-n-stuff.com/public_html

git pull origin main
/opt/alt/php84/usr/bin/php composer.phar install --no-dev --optimize-autoloader
/opt/alt/php84/usr/bin/php artisan migrate --force
/opt/alt/php84/usr/bin/php artisan config:cache
/opt/alt/php84/usr/bin/php artisan route:cache
/opt/alt/php84/usr/bin/php artisan view:cache