#!/bin/bash -ex

cd /var/www/html

chown -R www-data templates_c errorlog

if [[ ! -f config.local.inc.php ]]; then
    cp docker/config.local.inc.php config.local.inc.php
fi

php /opt/composer.phar install
php maintenance/RegenerateStylesheets.php

exec apache2-foreground
