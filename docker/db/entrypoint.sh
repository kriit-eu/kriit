#!/bin/sh
set -Eeuo pipefail

# Initialize database if not already present
if [ ! -d "/var/lib/mysql/mysql" ]; then
    echo "Initializing database..."
    mariadb-install-db --user=mysql --datadir=/var/lib/mysql --skip-test-db

    # Start temporary server
    mysqld --skip-name-resolve --skip-host-cache \
           --user=mysql --port=8002 --bind-address=0.0.0.0 \
           --socket=/run/mysqld/mysqld.sock &
    pid="$!"

    # Wait for server to start (30s timeout)
    for i in $(seq 30 -1 0); do
       if mysqladmin --socket=/run/mysqld/mysqld.sock ping &>/dev/null; then
           break
       fi
       echo "Waiting for server..."
       sleep 1
    done
    [ "$i" -gt 0 ] || { echo "Unable to start server"; exit 1; }

    # Configure root user and database
    mysql --socket=/run/mysqld/mysqld.sock <<-EOSQL
        SET @@SESSION.SQL_LOG_BIN=0;
        SET PASSWORD FOR 'root'@'localhost' = PASSWORD('${MYSQL_ROOT_PASSWORD}');
        ${MYSQL_DATABASE:+CREATE DATABASE IF NOT EXISTS \`${MYSQL_DATABASE}\`;}
        GRANT ALL ON *.* TO 'root'@'%' IDENTIFIED BY '${MYSQL_ROOT_PASSWORD}' WITH GRANT OPTION;
        FLUSH PRIVILEGES;
EOSQL

    # Import initial data if present
    if [ -f /docker-entrypoint-initdb.d/init.sql ]; then
        echo "Importing initial data..."
        mysql --socket=/run/mysqld/mysqld.sock -uroot -p${MYSQL_ROOT_PASSWORD} ${MYSQL_DATABASE} < /docker-entrypoint-initdb.d/init.sql
    fi

    # Stop temporary server
    mysqladmin --socket=/run/mysqld/mysqld.sock -uroot -p${MYSQL_ROOT_PASSWORD} shutdown || \
        { echo "Unable to shut down server"; exit 1; }
    wait "$pid"
    echo "Database initialized."
fi

# Start production server
exec mysqld \
     --user=mysql --console \
     --port=8002 --bind-address=0.0.0.0 \
     --skip-name-resolve --skip-host-cache