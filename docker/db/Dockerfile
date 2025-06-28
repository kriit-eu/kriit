FROM alpine:3.22

RUN apk add --no-cache \
        mariadb mariadb-client tzdata pwgen shadow \
 && mkdir -p /run/mysqld /var/lib/mysql-files \
 && chown -R mysql:mysql /run/mysqld /var/lib/mysql /var/lib/mysql-files \
 && chmod 750 /var/lib/mysql-files


COPY my.cnf /etc/my.cnf
COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 8002
VOLUME /var/lib/mysql

ENV MARIADB_AUTO_UPGRADE=1 \
    MARIADB_DISABLE_UPGRADE_BACKUP=1

ENTRYPOINT ["/entrypoint.sh"]