#!/usr/bin/env bash

if [ $# -lt 3 ]; then
	echo "usage: $0 <db-name> <db-user> <db-pass> [db-host] [wp-version]"
	exit 1
fi

DB_USER=$1
DB_PASS=$2
DB_HOST=${3-localhost}

while ! mysqladmin ping -h ${DB_HOST} -u${DB_USER} -p${DB_PASS} --silent; do
    echo "waiting....${SECONDS}"
    sleep 1
done
