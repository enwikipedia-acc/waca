#!/bin/bash
set -e

#*******************************************************************************
# Wikipedia Account Creation Assistance tool                                   *
# ACC Development Team. Please see team.json for a list of contributors.       *
#                                                                              *
# This is free and unencumbered software released into the public domain.      *
# Please see LICENSE.md for the full licencing statement.                      *
#*******************************************************************************

if [ -n "${DEVELOPMENT_MODE}" ]; then
    set -x
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

    exec php-fpm
fi

# Configure msmtp to relay through the MX_TARGET if that environment variable is set.
if [ -n "${MX_TARGET}" ]; then
    cat > /tmp/msmtprc << EOF
defaults
auth           off
tls            off
tls_starttls   off

account        default
host           ${MX_TARGET}
port           25
from           accounts@wmflabs.org
EOF
    chmod 600 /tmp/msmtprc
fi

if [ $# -gt 0 ]; then
    exec "$@"
else
    exec php-fpm
fi
