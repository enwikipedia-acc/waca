#!/bin/bash
# Usage: ./test_db.sh <createonly> <server> <dbname> <user> <pass>
# Arguments other than the first are optional
# Set <createonly> to 1 if you do not need to test the build
# Set <createonly> to 0 if you do need to test the build

set -e

if [ $# -lt 1 ]; then
	echo "Usage: ./test_db.sh <createonly> <server> <dbname> <user> <pass>\n"
	echo "Only the first parameter is required. It should be set to a 1 if\n"
	echo "you are only creating a database, or 0 if you are testing the build.\n"
	exit 1
fi

if [ $1 -eq 0 ]; then
	echo "Testing database build."
else
	echo "Creating database."
fi

# Default to values given in variables used by TravisCI if no args are given
if [ $# -ge 2 ]; then
	SQL_SERVER=$2
else
	SQL_SERVER=$MYSQL_HOST
fi

if [ $# -ge 3 ]; then
	SQL_DBNAME=$3
else
	SQL_DBNAME=$MYSQL_SCHEMA
fi

if [ $# -ge 4 ]; then
	SQL_USERNAME=$4
else
	SQL_USERNAME=$MYSQL_USER
fi

if [ $# -ge 5 ]; then
	SQL_PASSWORD=-p$5
elif [ -n "$MYSQL_PASSWORD" ]; then
	SQL_PASSWORD=-p$MYSQL_PASSWORD
else
	SQL_PASSWORD=
fi

echo "Check a few configuration flags"
mysql -h $SQL_SERVER -u $SQL_USERNAME $SQL_PASSWORD -e "SELECT @@sql_mode;"
mysql -h $SQL_SERVER -u $SQL_USERNAME $SQL_PASSWORD -e "SELECT @@version;"

if [[ $SQL_USERNAME == "root" ]]; then
	echo "Forcing SQL mode"
	mysql -h $SQL_SERVER -u $SQL_USERNAME $SQL_PASSWORD -e "SET GLOBAL sql_mode = 'NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION,STRICT_ALL_TABLES,ONLY_FULL_GROUP_BY,ERROR_FOR_DIVISION_BY_ZERO';"

	mysql -h $SQL_SERVER -u $SQL_USERNAME $SQL_PASSWORD -e "SELECT @@sql_mode;"
fi

echo "Dropping old database..."
mysql -h $SQL_SERVER -u $SQL_USERNAME $SQL_PASSWORD -e "DROP DATABASE IF EXISTS $SQL_DBNAME;"

echo "Creating database..."
mysql -h $SQL_SERVER -u $SQL_USERNAME $SQL_PASSWORD -e "CREATE DATABASE $SQL_DBNAME;"

echo "Loading initial schema..."
mysql -h $SQL_SERVER -u $SQL_USERNAME $SQL_PASSWORD $SQL_DBNAME < db-structure.sql

echo "Loading initial seed data..."
for f in `ls seed/*_data.sql`; do
	echo "  * $f"
	mysql -h $SQL_SERVER -u $SQL_USERNAME $SQL_PASSWORD $SQL_DBNAME < $f
done

echo "Applying patches..."
for f in `ls patches/patch*.sql`; do
	if [ "$f" == "patches/patch00-example.sql" ]; then
		continue;
	fi

	echo "  * $f"
	mysql -h $SQL_SERVER -u $SQL_USERNAME $SQL_PASSWORD $SQL_DBNAME < $f
done

if [ $1 -eq 0 ]; then
	echo "Dumping schema to file..."
	mysqldump --compact -h $SQL_SERVER -u $SQL_USERNAME $SQL_PASSWORD $SQL_DBNAME > schema.sql

	echo "Dropping database from server..."
	mysql -h $SQL_SERVER -u $SQL_USERNAME $SQL_PASSWORD -e "DROP DATABASE IF EXISTS $SQL_DBNAME;"

	echo "Creating database..."
	mysql -h $SQL_SERVER -u $SQL_USERNAME $SQL_PASSWORD -e "CREATE DATABASE $SQL_DBNAME;"

	echo "Reloading database from file..."
	mysql -h $SQL_SERVER -u $SQL_USERNAME $SQL_PASSWORD $SQL_DBNAME < schema.sql

	echo "Dumping schema to file..."
	mysqldump --compact -h $SQL_SERVER -u $SQL_USERNAME $SQL_PASSWORD $SQL_DBNAME > schema2.sql

	echo "Comparing dumps..."
	diff -q schema.sql schema2.sql

	echo "Rewriting definer..."
	cat schema.sql | sed "s/!50013 DEFINER=/DISABLED: 50013 DEFINER=/" > database.sql

	echo "Removing unneeded files..."
	rm schema.sql schema2.sql
fi

echo "Done."
