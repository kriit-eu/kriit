#!/bin/sh
set -e

# Initialize database if not already done
if [ ! -d "/var/lib/mysql/mysql" ]; then
    echo "Initializing database..."
    mysql_install_db --user=mysql --datadir=/var/lib/mysql

    # Start temporary server with port 8006
    mysqld --user=mysql --port=8006 --bind-address=0.0.0.0 --socket=/run/mysqld/mysqld.sock &
    pid="$!"

    # Wait for server to start
    for i in {30..0}; do
        if mysqladmin --socket=/run/mysqld/mysqld.sock ping &>/dev/null; then
            break
        fi
        echo "Waiting for server..."
        sleep 1
    done

    if [ "$i" = 0 ]; then
        echo "Unable to start server."
        exit 1
    fi

    # Set root password and create database
    mysql --socket=/run/mysqld/mysqld.sock <<-EOSQL
        SET @@SESSION.SQL_LOG_BIN=0;
        SET PASSWORD FOR 'root'@'localhost' = PASSWORD('${MYSQL_ROOT_PASSWORD}');
        CREATE DATABASE IF NOT EXISTS ${MYSQL_DATABASE};
        GRANT ALL ON *.* TO 'root'@'%' IDENTIFIED BY '${MYSQL_ROOT_PASSWORD}' WITH GRANT OPTION;
        FLUSH PRIVILEGES;
EOSQL

    # Import initial data if exists
    if [ -f /docker-entrypoint-initdb.d/init.sql ]; then
        echo "Importing initial data..."
        mysql --socket=/run/mysqld/mysqld.sock -uroot -p${MYSQL_ROOT_PASSWORD} ${MYSQL_DATABASE} < /docker-entrypoint-initdb.d/init.sql
    fi

    # Stop temporary server
    if ! mysqladmin --socket=/run/mysqld/mysqld.sock -uroot -p${MYSQL_ROOT_PASSWORD} shutdown; then
        echo "Unable to shut down server."
        exit 1
    fi

    # Wait for server to stop
    wait "$pid"
    echo "Database initialized."
fi

# Start MariaDB with port 8006 and bind to all interfaces
exec mysqld --user=mysql --console --port=8006 --bind-address=0.0.0.0