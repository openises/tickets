#!/bin/bash
# TicketsCAD PHP Version Compatibility Test
# Tests the application against PHP 7.4, 8.0, 8.1, 8.2, 8.3, 8.4
# Usage: bash tests/php-matrix-test.sh

set -e
SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
PROJECT_DIR="$(dirname "$SCRIPT_DIR")"
PASS=0
FAIL=0
TOTAL=0

PHP_VERSIONS="7.4-apache-buster 8.0-apache-bullseye 8.1-apache-bullseye 8.2-apache-bookworm 8.3-apache-bookworm 8.4-apache-bookworm"

echo "================================================================="
echo "  TicketsCAD PHP Version Compatibility Matrix"
echo "  Date: $(date '+%Y-%m-%d %H:%M:%S')"
echo "================================================================="
echo ""

for PHP_VER in $PHP_VERSIONS; do
    SHORT=$(echo "$PHP_VER" | cut -d- -f1)
    ((TOTAL++)) || true
    echo "─── PHP $SHORT ───"

    # Cleanup
    docker rm -f phptest_web phptest_db >/dev/null 2>&1 || true
    docker network rm phptest_net >/dev/null 2>&1 || true
    docker network create phptest_net >/dev/null 2>&1

    # Start DB
    docker run -d --name phptest_db --network phptest_net \
        -e MARIADB_ROOT_PASSWORD=root -e MARIADB_DATABASE=tickets \
        -e MARIADB_USER=tickets -e MARIADB_PASSWORD=tickets \
        mariadb:10.11 >/dev/null 2>&1

    # Wait for DB
    echo "  Waiting for database..."
    for i in $(seq 1 30); do
        if docker exec phptest_db mariadb-admin ping -u root -proot 2>/dev/null | grep -q alive; then
            break
        fi
        sleep 2
    done

    # Create Dockerfile for this PHP version
    cat > /tmp/Dockerfile.phptest << DEOF
FROM php:${PHP_VER}
RUN apt-get update && apt-get install -y --no-install-recommends \
    libpng-dev libjpeg62-turbo-dev libfreetype6-dev libzip-dev \
    libxml2-dev libonig-dev libcurl4-openssl-dev libssl-dev zlib1g-dev \
    2>/dev/null && \
    (docker-php-ext-configure gd --with-freetype --with-jpeg 2>/dev/null || \
     docker-php-ext-configure gd --with-freetype-dir=/usr --with-jpeg-dir=/usr 2>/dev/null || true) && \
    docker-php-ext-install -j\$(nproc) mysqli pdo pdo_mysql gd zip xml mbstring 2>&1 | tail -1 && \
    apt-get clean && rm -rf /var/lib/apt/lists/*
RUN a2enmod rewrite headers 2>/dev/null || true
COPY . /var/www/html/
RUN chown -R www-data:www-data /var/www/html && chmod -R 755 /var/www/html
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh
EXPOSE 80
ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["apache2-foreground"]
DEOF

    echo "  Building..."
    if ! docker build -t phptest_img -f /tmp/Dockerfile.phptest "$PROJECT_DIR" >/dev/null 2>&1; then
        echo "  BUILD FAILED"
        docker build -t phptest_img -f /tmp/Dockerfile.phptest "$PROJECT_DIR" 2>&1 | grep -i "error\|fail" | tail -3 | sed 's/^/  /'
        ((FAIL++)) || true
        docker rm -f phptest_web phptest_db >/dev/null 2>&1 || true
        docker network rm phptest_net >/dev/null 2>&1 || true
        echo ""
        continue
    fi

    # Run web container
    docker run -d --name phptest_web --network phptest_net -p 9200:80 \
        -e DB_HOST=phptest_db -e DB_USER=tickets -e DB_PASS=tickets -e DB_NAME=tickets \
        -e ADMIN_USER=admin -e ADMIN_PASS=admin -e AUTO_INSTALL=true \
        phptest_img >/dev/null 2>&1

    # Wait for install + Apache
    echo "  Waiting for auto-install..."
    STARTED=false
    for i in $(seq 1 60); do
        if docker logs phptest_web 2>&1 | grep -q "Starting Apache"; then
            STARTED=true
            break
        fi
        sleep 2
    done
    sleep 3

    if [ "$STARTED" = false ]; then
        echo "  TIMEOUT — Apache did not start"
        docker logs phptest_web 2>&1 | tail -5 | sed 's/^/    /'
        ((FAIL++)) || true
        docker rm -f phptest_web phptest_db >/dev/null 2>&1 || true
        docker network rm phptest_net >/dev/null 2>&1 || true
        echo ""
        continue
    fi

    # Run tests
    PHP_ACTUAL=$(docker exec phptest_web php -r 'echo PHP_VERSION;' 2>/dev/null)
    HTTP=$(curl -s -o /dev/null -w '%{http_code}' http://localhost:9200/ 2>/dev/null)
    TABLES=$(docker exec phptest_db mariadb -u tickets -ptickets tickets -N -e \
        "SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA='tickets'" 2>/dev/null)
    ADMIN=$(docker exec phptest_db mariadb -u tickets -ptickets tickets -N -e \
        "SELECT user FROM user WHERE level=0 LIMIT 1" 2>/dev/null)

    # Compat layer
    COMPAT=$(docker exec phptest_web php -r \
        'require "/var/www/html/incs/compat.inc.php"; echo function_exists("utf8_encode")?"OK":"FAIL";' 2>/dev/null)

    # Password formats
    PWTEST=$(docker exec phptest_web php -r '
        require "/var/www/html/incs/compat.inc.php";
        require "/var/www/html/incs/security.inc.php";
        $ok=0;
        if(verify_password("t",password_hash("t",PASSWORD_BCRYPT))["valid"])$ok++;
        if(verify_password("t",md5("t"))["valid"])$ok++;
        if(verify_password("t","*".strtoupper(sha1(sha1("t",true))))["valid"])$ok++;
        echo $ok;
    ' 2>/dev/null)

    # XSS protection
    XSS_BODY=$(curl -s 'http://localhost:9200/single_unit.php?id=123%27%3E%3Cscript%3Ealert(1)%3C/script%3E' 2>/dev/null)
    XSS_SAFE="yes"
    if echo "$XSS_BODY" | grep -q '<script>alert(1)</script>'; then
        XSS_SAFE="no"
    fi

    # PHP fatal errors
    FATALS=$(docker exec phptest_web sh -c 'grep -c "Fatal\|Parse error" /var/log/php_errors.log 2>/dev/null || echo 0' 2>/dev/null)

    # Report
    echo "  PHP:       $PHP_ACTUAL"
    echo "  HTTP:      $HTTP"
    echo "  Tables:    $TABLES"
    echo "  Admin:     $ADMIN"
    echo "  Compat:    $COMPAT"
    echo "  PwFormats: $PWTEST/3"
    echo "  XSS Safe:  $XSS_SAFE"
    echo "  Fatals:    $FATALS"

    ALL_OK=true
    [ "$HTTP" != "200" ] && ALL_OK=false
    [ -z "$TABLES" ] && ALL_OK=false
    [ "${TABLES:-0}" -lt 50 ] 2>/dev/null && ALL_OK=false
    [ "$ADMIN" != "admin" ] && ALL_OK=false
    [ "$COMPAT" != "OK" ] && ALL_OK=false
    [ "$PWTEST" != "3" ] && ALL_OK=false
    [ "$XSS_SAFE" != "yes" ] && ALL_OK=false

    if [ "$ALL_OK" = true ]; then
        echo "  >>> PASS <<<"
        ((PASS++)) || true
    else
        echo "  >>> FAIL <<<"
        ((FAIL++)) || true
        echo "  Entrypoint logs:"
        docker logs phptest_web 2>&1 | grep -i "error\|fail\|fatal\|warning" | tail -5 | sed 's/^/    /'
    fi

    # Cleanup
    docker rm -f phptest_web phptest_db >/dev/null 2>&1 || true
    docker network rm phptest_net >/dev/null 2>&1 || true
    echo ""
done

echo "================================================================="
echo "  RESULTS: $PASS passed, $FAIL failed out of $TOTAL"
echo "================================================================="

exit $FAIL
