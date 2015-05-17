#!/bin/bash
# usage: ./create_db.sh <server> <dbname> <user> <pass>
# arguments are optional

set -e

# Default to values given in variables used by TravisCI if no args are given
if [ $# -ge 1 ]; then
	$SQL_SERVER = $1
else
	$SQL_SERVER = $DB_SERV
fi

if [ $# -ge 2 ]; then
	$SQL_DBNAME = $2
else
	$SQL_DBNAME = $DB_NAME
fi

if [ $# -ge 3 ]; then
	$SQL_USERNAME = $3
else
	$SQL_USERNAME = $DB_USER
fi

if [ $# -ge 4 ]; then
	$SQL_PASSWORD = $4
else
	$SQL_PASSWORD = $DB_PASS
fi

echo "Creating database..."
mysql -h $SQL_SERVER -u $SQL_USERNAME -p$SQL_PASSWORD -e "CREATE DATABASE $SQL_DBNAME;"

echo "Loading initial schema..."
mysql -h $SQL_SERVER -u $SQL_USERNAME -p$SQL_PASSWORD $SQL_DBNAME < db-structure.sql

echo "Loading initial seed data..."
for f in `ls seed/*_data.sql`; do
	echo "  * $f"
	mysql -h $SQL_SERVER -u $SQL_USERNAME -p$SQL_PASSWORD $SQL_DBNAME < $f
done

echo "Applying patches..."
for f in `ls patches/patch*.sql`; do
	echo "  * $f"
	mysql -h $SQL_SERVER -u $SQL_USERNAME -p$SQL_PASSWORD $SQL_DBNAME < $f
done

echo "Done."
