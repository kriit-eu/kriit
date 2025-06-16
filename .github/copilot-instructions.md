Database is in docker with config
// Local configuration for Docker
const DATABASE_HOSTNAME = 'db'; // Docker service name
const DATABASE_USERNAME = 'root';
const DATABASE_PASSWORD = 'kriitkriit'; // From docker-compose.yml
const DATABASE_DATABASE = 'kriit';

docker containers running:
CONTAINER ID IMAGE COMMAND CREATED STATUS PORTS NAMES
d31ebb8ad96a phpmyadmin:latest "/docker-entrypoint.…" 21 hours ago Up 5 hours 0.0.0.0:8081->80/tcp, [::]:8081->80/tcp kriit-phpmyadmin-1
49eb6ffad13b kriit-app "docker-php-entrypoi…" 21 hours ago Up 5 hours 0.0.0.0:8080->80/tcp, [::]:8080->80/tcp kriit-app-1
b415c75c8a92 mariadb:10.5 "docker-entrypoint.s…" 21 hours ago Up 5 hours 0.0.0.0:3306->3306/tcp, [::]:3306->3306/tcp kriit-db-1
