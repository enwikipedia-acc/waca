#!/bin/bash -x

cd /wacadb
./test_db.sh 1 localhost ${MYSQL_DATABASE} ${MYSQL_USER} ${MYSQL_PASSWORD}