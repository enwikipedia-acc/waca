#!/bin/bash

echo -e "\033[32mRunning SCSS rebuild...\033[0m"
cd "$(dirname "$0")" || { echo "Failed to find root directory!"; exit 1; }

echo "Constructing minimal configuration"
echo "<?php" > config.local.inc.php
echo "\$toolserver_host = '${MYSQL_HOST}';" >> config.local.inc.php
echo "\$toolserver_username = '${MYSQL_USER}';" >> config.local.inc.php
echo "\$toolserver_password = '${MYSQL_PASSWORD}';" >> config.local.inc.php
echo "\$toolserver_database = '${MYSQL_SCHEMA}';" >> config.local.inc.php
echo '$toolserver_notification_dbhost = $toolserver_host;' >> config.local.inc.php
echo '$toolserver_notification_database = $toolserver_database;' >> config.local.inc.php
echo '$notifications_username = $toolserver_username;' >> config.local.inc.php
echo '$notifications_password = $toolserver_password;' >> config.local.inc.php


echo "Cleaning up existing files for test"
find resources/generated -type f -name '*.css' -delete

cd maintenance || { echo "Failed to find maintenance directory!"; exit 1; }
php RegenerateStylesheets.php

cd ../resources/generated || { echo "Failed to find generated resources directory!" ; exit 1; }

cssFiles=$(find . -mindepth 1 -maxdepth 1 -name '*.css' | wc -l)
if [[ ${cssFiles} -gt 0 ]]; then
  echo -e "\nLooks good, ${cssFiles} files found.\n"
  find . -mindepth 1 -maxdepth 1 -name '*.css'
  exit 0
else
  echo -e "\nNo files generated!"
  exit 1
fi

