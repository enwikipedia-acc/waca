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

echo "Dumping schema to file..."
mysqldump --compact -h $MYSQL_HOST -u $MYSQL_USER -p$MYSQL_PASSWORD $MYSQL_SCHEMA > schema.sql

echo "Dropping database from server..."
mysql -h $MYSQL_HOST -u $MYSQL_USER -p$MYSQL_PASSWORD -e "DROP DATABASE IF EXISTS $MYSQL_SCHEMA;"

echo "Creating database..."
mysql -h $MYSQL_HOST -u $MYSQL_USER -p$MYSQL_PASSWORD -e "CREATE DATABASE $MYSQL_SCHEMA;"

echo "Reloading database from file..."
mysql -h $MYSQL_HOST -u $MYSQL_USER -p$MYSQL_PASSWORD $MYSQL_SCHEMA < schema.sql

echo "Dumping schema to file..."
mysqldump --compact -h $MYSQL_HOST -u $MYSQL_USER -p$MYSQL_PASSWORD $MYSQL_SCHEMA > schema2.sql

echo "Comparing dumps..."
diff -q schema.sql schema2.sql

echo "Rewriting definer..."
cat schema.sql | sed "s/!50013 DEFINER=/DISABLED: 50013 DEFINER=/" > schema.sql

echo "Done."
