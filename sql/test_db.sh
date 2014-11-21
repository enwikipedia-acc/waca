#!/bin/bash

set -e


echo "Testing database build."

echo "Dropping old database..."
mysql -h $MYSQL_HOST -u $MYSQL_USER -p$MYSQL_PASSWORD -e "DROP DATABASE IF EXISTS $MYSQL_SCHEMA;"

echo "Creating database..."
mysql -h $MYSQL_HOST -u $MYSQL_USER -p$MYSQL_PASSWORD -e "CREATE DATABASE $MYSQL_SCHEMA;"

echo "Loading initial schema..."
mysql -h $MYSQL_HOST -u $MYSQL_USER -p$MYSQL_PASSWORD $MYSQL_SCHEMA < db-structure.sql

echo "Loading initial seed data..."
for f in `ls seed/*_data.sql`; do
	echo "  * $f"
	mysql -h $MYSQL_HOST -u $MYSQL_USER -p$MYSQL_PASSWORD $MYSQL_SCHEMA < $f
done

echo "Applying patches..."
for f in `ls patches/patch*.sql`; do
	echo "  * $f"
	mysql -h $MYSQL_HOST -u $MYSQL_USER -p$MYSQL_PASSWORD $MYSQL_SCHEMA < $f
done



