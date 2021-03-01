#!/bin/bash -ex

cd /var/www/html

chown -R www-data templates_c errorlog

php /opt/composer.phar install
php maintenance/RegenerateStylesheets.php

exec apache2-foreground