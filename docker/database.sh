#!/bin/bash -x

#*******************************************************************************
# Wikipedia Account Creation Assistance tool                                   *
# ACC Development Team. Please see team.json for a list of contributors.       *
#                                                                              *
# This is free and unencumbered software released into the public domain.      *
# Please see LICENSE.md for the full licencing statement.                      *
#*******************************************************************************

cd /wacadb || { echo "Failed to cd to /wacadb"; exit 1; }

# Set env vars needed by the test_db.sh script. Note that the MYSQL_USER and MYSQL_PASSWORD env vars are set by the
# docker-compose.yml file and passed through to test_db.sh.
export MYSQL_HOST="localhost"
export MYSQL_SCHEMA="${MYSQL_DATABASE}"  # Compatibility shim

./test_db.sh --ci --create
