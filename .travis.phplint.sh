#!/bin/bash

files=$(find . \
        \( -path ./vendor -o -path ./lib \) -prune -o \
        -type f -name \*.php \
        | grep -v '^./vendor$' \
        | grep -v '^./lib$'
)

fail=0

for fileName in ${files}; do
    php -l ${fileName} | grep "Errors parsing "
    if [ $? -eq 1 ]
    then
        fail=1
    fi
done

if [ ${fail} -eq 0 ]; then
    exit 0
else
    exit 1
fi
