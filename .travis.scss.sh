#!/bin/bash

echo -e "\033[32mRunning SCSS rebuild...\033[0m"
cd "$(dirname "$0")/maintenance" || { echo "Failed to find maintenance directory!"; exit 1; }

find ../resources/generated -type f -name '*.css' -delete

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





