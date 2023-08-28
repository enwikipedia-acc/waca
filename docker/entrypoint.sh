#!/bin/bash -ex

#*******************************************************************************
# Wikipedia Account Creation Assistance tool                                   *
# ACC Development Team. Please see team.json for a list of contributors.       *
#                                                                              *
# This is free and unencumbered software released into the public domain.      *
# Please see LICENSE.md for the full licencing statement.                      *
#*******************************************************************************

cd /var/www/html
git config --global --replace-all safe.directory /var/www/html
cp ~/.gitconfig /etc/gitconfig

if [[ ! -f config.local.inc.php ]]; then
    cp docker/config.local.inc.php config.local.inc.php
fi

php /opt/composer.phar install --no-progress
npm install

chown -R www-data templates_c errorlog

npm run build-scss

exec apache2-foreground
