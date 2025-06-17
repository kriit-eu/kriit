FROM alpine:3.22

RUN apk add --no-cache mariadb mariadb-client tzdata \
    && mkdir -p /run/mysqld \
    && chown -R mysql:mysql /var/lib/mysql /run/mysqld

COPY my.cnf /etc/my.cnf
COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 8006
VOLUME /var/lib/mysql
ENTRYPOINT ["/entrypoint.sh"]