Database is in docker with config
// Local configuration for Docker
const DATABASE_HOSTNAME = 'db'; // Docker service name
const DATABASE_USERNAME = 'root';
const DATABASE_PASSWORD = 'kriitkriit'; // From docker-compose.yml
const DATABASE_DATABASE = 'kriit';

docker containers running:
CONTAINER ID IMAGE COMMAND CREATED STATUS PORTS NAMES
507ee37257fb kriit/nginx "nginx -g 'daemon of…" About a minute ago Up About a minute 127.0.0.1:8080->80/tcp kriit_nginx
2b888800cd29 kriit/app "php-fpm -F" About a minute ago Up About a minute 9000/tcp kriit_app
1e9587555d43 kriit/phpmyadmin "php -S 0.0.0.0:80 -…" About a minute ago Up About a minute 127.0.0.1:8081->80/tcp kriit_phpmyadmin
cc239d3d4d92 kriit/db "/entrypoint.sh" About a minute ago Up About a minute (healthy) 127.0.0.1:8006->8006/tcp kriit_db
15d41e105fa9 kriit/mailhog "mailhog" About a minute ago Up About a minute 127.0.0.1:8025->8025/tcp kriit_mailhog
