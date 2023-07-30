#!/bin/bash

cd "$(dirname "$0")"

function usage {
    cat <<EEOOFF
Usage: $0 [options]

One of the following three options is required:
  --ci              : Performs a CI build. This will pull credentials from the environment.
  --create          : Interactively create a database
  --test            : Interactively build and test a database

For all options other than \`--ci\` above, these three parameters are also required.
  --host=HOSTNAME   : The MariaDB server hostname
  --user=USERNAME   : The username to connect to MariaDB with
  --schema=DATABASE : The database name to use on MariaDB
EEOOFF
}

PARAMS=$(getopt -o ic --long ci,test,create,host:,user:,schema: -- "$@")

if [[ $? -ne 0 ]]; then
    usage
    exit 1
fi

eval set -- "$PARAMS"

CreateOnly=-1
UseEnvironmentCredentials=0

MySqlHost=""
MySqlSchema=""
MySqlUsername=""
MySqlPassword=""

while true; do
    case "$1" in
    --ci)
        CreateOnly=0
        UseEnvironmentCredentials=1
        ;;
    --test)
        CreateOnly=0
        ;;
    --create)
        CreateOnly=1
        ;;
    --host)
        MySqlHost=$2
        shift
        ;;
    --user)
        MySqlUsername=$2
        shift
        ;;
    --schema)
        MySqlSchema=$2
        shift
        ;;
    --)
        shift
        break
        ;;
    esac
    shift
done

if [[ $CreateOnly -eq -1 ]]; then
    # Legacy mode
    if [[ $# -eq 0 ]]; then
        # No legacy parameters and no mode specified.
        usage
        exit 1
    fi

    CreateOnly=$1

    if [[ $# -gt 1 ]]; then
        if [ $# -ge 2 ]; then
            MySqlHost=$2
        fi

        if [ $# -ge 3 ]; then
            MySqlSchema=$3
        fi

        if [ $# -ge 4 ]; then
            MySqlUsername=$4
        fi

        if [ $# -ge 5 ]; then
            MySqlPassword=$5
        fi
    fi
fi

if [[ $UseEnvironmentCredentials -eq 1 ]]; then
    MySqlHost=$MYSQL_HOST
    MySqlSchema=$MYSQL_SCHEMA
    MySqlUsername=$MYSQL_USER
    MySqlPassword=$MYSQL_PASSWORD
fi

if [[ $MySqlSchema == "" || $MySqlUsername == "" || $MySqlHost == "" ]]; then
    usage
    exit 1;
fi

if [[ $MySqlPassword == "" ]]; then
    read -r -s -p "MariaDB password: " MySqlPassword
fi

# -- Write credentials to a temporary file
defaultsFile=$(mktemp)
chmod go= "$defaultsFile"

# shellcheck disable=SC2064
trap "rm -f $defaultsFile" EXIT

{
    echo "[client]"
    echo "user = ${MySqlUsername}"
    echo "password = ${MySqlPassword}"
    echo "host = ${MySqlHost}"

    echo "[mysqldump]"
    echo "column-statistics=0"
} >> "$defaultsFile"

function log() {
    printf "[%s] %s\n" "$(date --iso-8601=s)" "$1"
}

function testStringContains() {
    haystack=$1
    needle=$2

    if [[ "$haystack" == *"$needle"* ]]; then
        log "Check for $needle: SUCCESS"
    else
        log "Check for $needle: FAILED"
    fi
}

set -e

if [[ $CreateOnly -eq 0 ]]; then
    log "Testing database build."
else
    log "Creating database."
fi

log "Check a few configuration flags"
log "Checking SQL_MODE"
sqlMode=$(mysql --defaults-file="$defaultsFile" -e "SELECT @@sql_mode;")

testStringContains "${sqlMode}" "STRICT_ALL_TABLES"
testStringContains "${sqlMode}" "ONLY_FULL_GROUP_BY"
testStringContains "${sqlMode}" "ERROR_FOR_DIVISION_BY_ZERO"
testStringContains "${sqlMode}" "NO_ENGINE_SUBSTITUTION"

mysql --defaults-file="$defaultsFile" -e "SELECT @@version;"

if [[ $MySqlUsername == "root" ]]; then
    log "Forcing SQL mode"
    mysql --defaults-file="$defaultsFile" -e "SET GLOBAL sql_mode = 'NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION,STRICT_ALL_TABLES,ONLY_FULL_GROUP_BY,ERROR_FOR_DIVISION_BY_ZERO';"

    mysql --defaults-file="$defaultsFile" -e "SELECT @@sql_mode;"
fi

log "Dropping old database..."
mysql --defaults-file="$defaultsFile"  -e "DROP DATABASE IF EXISTS ${MySqlSchema};"

log "Creating database..."
mysql --defaults-file="$defaultsFile"  -e "CREATE DATABASE ${MySqlSchema};"

log "Loading initial schema..."
mysql --defaults-file="$defaultsFile" "${MySqlSchema}" < db-structure.sql

log "Loading initial seed data..."
for f in seed/*_data.sql; do
    log "  * $f"
    mysql --defaults-file="$defaultsFile" "${MySqlSchema}" < "$f"
done

log "Applying patches..."
for f in patches/patch*.sql; do
    if [[ "$f" == "patches/patch00-example.sql" ]]; then
        continue
    fi

    log "  * $f"
    mysql --defaults-file="$defaultsFile" "${MySqlSchema}" < "$f"
done

log "Checking schema version..."
mysql --defaults-file="$defaultsFile" "${MySqlSchema}" -e 'select * from schemaversion'

if [[ $CreateOnly -eq 0 ]]; then
    log "Dumping schema to file..."
    mysqldump --defaults-file="$defaultsFile" "${MySqlSchema}" --skip-comments > schema.sql

    log "Dropping old database..."
    mysql --defaults-file="$defaultsFile"  -e "DROP DATABASE IF EXISTS ${MySqlSchema};"

    log "Creating database..."
    mysql --defaults-file="$defaultsFile"  -e "CREATE DATABASE ${MySqlSchema};"

    log "Reloading database from file..."
    mysql --defaults-file="$defaultsFile" "${MySqlSchema}" < schema.sql

    log "Dumping schema to file..."
    mysqldump --defaults-file="$defaultsFile" "${MySqlSchema}" --skip-comments > schema2.sql

    log "Comparing dumps..."
    if ! diff -q schema.sql schema2.sql; then
        log "Difference detected!"
        exit 1
    fi

    log "Removing unneeded files..."
    rm schema.sql schema2.sql
fi

log "Done."
