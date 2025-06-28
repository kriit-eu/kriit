# Enable AVIF Support Commands

## For Docker Setup (Current Project)

### 1. Rebuild Docker Container with AVIF Support

```bash
# Stop the current containers
docker-compose down

# Rebuild only the app service
docker-compose build app --no-cache

# Start the containers again
docker-compose up -d

# Verify AVIF support
curl http://localhost:8000/api/images/diagnostics
```

### 2. Alternative: Force rebuild everything

```bash
# Stop and remove containers
docker-compose down

# Remove the app image to force complete rebuild
docker rmi kriit-app

# Rebuild and start
docker-compose up -d --build
```

## For Ubuntu/Debian Servers (Native PHP)

### 1. Install AVIF Development Libraries

```bash
# Update package list
sudo apt-get update

# Install required libraries
sudo apt-get install -y libavif-dev libwebp-dev libjpeg-dev libpng-dev libfreetype6-dev

# Install PHP development tools
sudo apt-get install -y php-dev php-pear build-essential
```

### 2. Recompile PHP GD Extension with AVIF Support

```bash
# Remove existing GD extension
sudo apt-get remove php-gd

# Download PHP source (adjust version as needed)
cd /tmp
wget https://www.php.net/distributions/php-8.3.0.tar.gz
tar -xzf php-8.3.0.tar.gz
cd php-8.3.0/ext/gd

# Configure and compile GD with AVIF
phpize
./configure \
    --with-gd \
    --with-freetype \
    --with-jpeg \
    --with-webp \
    --with-avif
make
sudo make install

# Enable the extension
echo "extension=gd.so" | sudo tee /etc/php/8.3/mods-available/gd.ini
sudo phpenmod gd

# Restart web server
sudo systemctl restart apache2
# OR for Nginx with PHP-FPM
sudo systemctl restart php8.3-fpm
sudo systemctl restart nginx
```

## For CentOS/RHEL/Rocky Linux

### 1. Install AVIF Libraries

```bash
# Enable EPEL repository
sudo dnf install epel-release -y

# Install development tools
sudo dnf groupinstall "Development Tools" -y

# Install required libraries
sudo dnf install -y libavif-devel libwebp-devel libjpeg-devel libpng-devel freetype-devel

# Install PHP development
sudo dnf install -y php-devel
```

### 2. Recompile GD Extension

```bash
# Similar to Ubuntu process but using dnf/yum
# Follow the PHP source compilation steps above
```

## For Alpine Linux (Docker)

### Dockerfile modifications:

```dockerfile
FROM php:8.3-apache-alpine

# Install AVIF support
RUN apk add --no-cache \
    libavif-dev \
    libwebp-dev \
    libjpeg-turbo-dev \
    libpng-dev \
    freetype-dev

# Configure and install GD with AVIF
RUN docker-php-ext-configure gd \
    --with-freetype \
    --with-jpeg \
    --with-webp \
    --with-avif && \
    docker-php-ext-install gd
```

## Verification Commands

### 1. Check PHP Configuration

```bash
# Check if AVIF functions exist
php -r "echo function_exists('imageavif') ? 'AVIF supported' : 'AVIF not supported';"

# Check GD info
php -r "print_r(gd_info());"

# Check supported image types
php -r "echo 'Supported types: ' . imagetypes();"
```

### 2. Test AVIF Creation

```bash
# Create test script
cat > test_avif.php << 'EOF'
<?php
$image = imagecreatetruecolor(100, 100);
$red = imagecolorallocate($image, 255, 0, 0);
imagefill($image, 0, 0, $red);

if (function_exists('imageavif')) {
    if (imageavif($image, 'test.avif')) {
        echo "AVIF file created successfully!\n";
        echo "File size: " . filesize('test.avif') . " bytes\n";
    } else {
        echo "Failed to create AVIF file\n";
    }
} else {
    echo "imageavif function not available\n";
}

imagedestroy($image);
?>
EOF

php test_avif.php
```

### 3. Check Web Server Logs

```bash
# Apache logs
sudo tail -f /var/log/apache2/error.log

# Nginx logs
sudo tail -f /var/log/nginx/error.log

# PHP-FPM logs
sudo tail -f /var/log/php8.3-fpm.log
```

## Troubleshooting Commands

### 1. Check Loaded Extensions

```bash
php -m | grep gd
```

### 2. Check PHP Extension Directory

```bash
php -i | grep extension_dir
ls -la $(php -i | grep extension_dir | cut -d' ' -f3)
```

### 3. Check Compilation Options

```bash
php -i | grep -A 20 gd
```

### 4. Rebuild PHP from Source (if needed)

```bash
# Download PHP source
wget https://www.php.net/distributions/php-8.3.0.tar.gz
tar -xzf php-8.3.0.tar.gz
cd php-8.3.0

# Configure with AVIF support
./configure \
    --enable-gd \
    --with-freetype \
    --with-jpeg \
    --with-webp \
    --with-avif \
    --with-apxs2=/usr/bin/apxs2

make
sudo make install
```

## Post-Installation Verification

### Test your application:

```bash
# Test the diagnostics endpoint
curl http://your-domain.com/api/images/diagnostics

# Or locally
curl http://localhost:8000/api/images/diagnostics
```

### Expected successful output should include:

```json
{
  "status": 200,
  "data": {
    "avif_functions": {
      "imageavif": true,
      "imagecreatefromavif": true
    },
    "supported_formats": {
      "AVIF": true
    }
  }
}
```
