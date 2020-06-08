#!/bin/bash

echo -e "\033[32mRunning lint test on all PHP files...\033[0m"
echo -e "\033[32mCurrent directory is \033[37;1m`pwd`\033[0m"

files=$(find . \
        \( -path ./vendor -o -path ./lib \) -prune -o \
        -type f -name \*.php \
        | grep -v '^./vendor$' \
        | grep -v '^./lib$'
)

fail=0

for fileName in ${files}; do
    php -l ${fileName}
    if [ $? -ne 0 ]
    then
        echo -e "\033[31mError detected in lint, marking build as \033[41;37mFAILED\033[m"
        fail=1
    fi
done

if [ ${fail} -eq 0 ]; then
    echo -e "\033[32mGlobal lint check \033[1mPASSED\033[m"
    exit 0
else
    echo -e "\033[31mGlobal lint check \033[1mFAILED\033[m"
    exit 1
fi
