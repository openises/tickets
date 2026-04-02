#!/bin/bash
#
# TicketsCAD Version Compatibility Test Matrix
#
# Tests the application against multiple PHP and MySQL/MariaDB combinations.
# Runs the deployment test suite against each combination.
#
# Usage: bash tests/docker-matrix-test.sh
#
# Requires: Docker, Docker Compose, curl, php-cli

set -e

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
PROJECT_DIR="$(dirname "$SCRIPT_DIR")"
RESULTS_FILE="$SCRIPT_DIR/matrix-results.txt"
PASS_COUNT=0
FAIL_COUNT=0
TOTAL_COMBOS=0

# Define test matrix
# Format: "PHP_IMAGE DB_IMAGE LABEL"
MATRIX=(
    "php:8.2-apache-bookworm mariadb:10.11 PHP8.2+MariaDB10.11"
    "php:8.4-apache-bookworm mariadb:11.7 PHP8.4+MariaDB11.7"
    "php:8.0-apache-bullseye mariadb:10.6 PHP8.0+MariaDB10.6"
    "php:8.3-apache-bookworm mysql:8.0 PHP8.3+MySQL8.0"
    "php:7.4-apache-buster mariadb:10.4 PHP7.4+MariaDB10.4"
)

echo "================================================================="
echo "  TicketsCAD Version Compatibility Test Matrix"
echo "  Date: $(date '+%Y-%m-%d %H:%M:%S')"
echo "  Combinations: ${#MATRIX[@]}"
echo "================================================================="
echo ""

# Clean up any previous test containers
cleanup() {
    local label=$1
    local prefix="tctest_${label}"
    docker rm -f "${prefix}_web" "${prefix}_db" 2>/dev/null || true
    docker network rm "${prefix}_net" 2>/dev/null || true
}

# Run a single test combination
run_test() {
    local php_image=$1
    local db_image=$2
    local label=$3
    local prefix="tctest_${label}"
    local web_port=$((9100 + TOTAL_COMBOS))

    echo "─── Testing: $label ───"
    echo "  PHP: $php_image"
    echo "  DB:  $db_image"
    echo "  Port: $web_port"

    cleanup "$label"

    # Create network
    docker network create "${prefix}_net" >/dev/null 2>&1

    # Determine DB env vars based on image type
    local db_env=""
    if echo "$db_image" | grep -q "mysql"; then
        db_env="-e MYSQL_ROOT_PASSWORD=root -e MYSQL_DATABASE=tickets -e MYSQL_USER=tickets -e MYSQL_PASSWORD=tickets"
    else
        db_env="-e MARIADB_ROOT_PASSWORD=root -e MARIADB_DATABASE=tickets -e MARIADB_USER=tickets -e MARIADB_PASSWORD=tickets"
    fi

    # Start database
    docker run -d --name "${prefix}_db" --network "${prefix}_net" \
        $db_env \
        "$db_image" >/dev/null 2>&1

    # Wait for DB to be ready
    echo "  Waiting for database..."
    for i in $(seq 1 40); do
        if docker exec "${prefix}_db" sh -c 'mysqladmin ping -u root -proot 2>/dev/null | grep -q alive' 2>/dev/null; then
            echo "  Database ready after ${i}s"
            break
        fi
        sleep 2
    done

    # Build the web image with the specific PHP version
    # Create a temporary Dockerfile
    local tmpdir=$(mktemp -d)
    cat > "$tmpdir/Dockerfile" << DEOF
FROM $php_image

RUN apt-get update && apt-get install -y --no-install-recommends \\
    libpng-dev libjpeg62-turbo-dev libfreetype6-dev libzip-dev \\
    libxml2-dev libonig-dev libcurl4-openssl-dev libssl-dev zlib1g-dev \\
    && docker-php-ext-configure gd --with-freetype --with-jpeg 2>/dev/null || \\
      docker-php-ext-configure gd --with-freetype-dir=/usr --with-jpeg-dir=/usr 2>/dev/null || true \\
    && docker-php-ext-install -j\$(nproc) mysqli pdo pdo_mysql gd zip xml mbstring \\
    && apt-get clean && rm -rf /var/lib/apt/lists/*

RUN a2enmod rewrite headers

COPY . /var/www/html/
RUN chown -R www-data:www-data /var/www/html && chmod -R 755 /var/www/html
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 80
ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["apache2-foreground"]
DEOF

    # Copy application code to temp dir
    cp -r "$PROJECT_DIR"/* "$tmpdir/" 2>/dev/null
    cp "$PROJECT_DIR/.dockerignore" "$tmpdir/" 2>/dev/null || true

    # Build
    echo "  Building image..."
    if ! docker build -t "${prefix}_img" "$tmpdir" >/dev/null 2>&1; then
        echo "  BUILD FAILED"
        echo "FAIL $label BUILD_FAILED" >> "$RESULTS_FILE"
        ((FAIL_COUNT++)) || true
        cleanup "$label"
        rm -rf "$tmpdir"
        return
    fi
    rm -rf "$tmpdir"

    # Start web container
    docker run -d --name "${prefix}_web" --network "${prefix}_net" \
        -p "${web_port}:80" \
        -e DB_HOST="${prefix}_db" \
        -e DB_USER=tickets \
        -e DB_PASS=tickets \
        -e DB_NAME=tickets \
        -e ADMIN_USER=admin \
        -e ADMIN_PASS=admin \
        -e AUTO_INSTALL=true \
        "${prefix}_img" >/dev/null 2>&1

    # Wait for web server and auto-install
    echo "  Waiting for auto-install..."
    local ready=false
    for i in $(seq 1 60); do
        if docker logs "${prefix}_web" 2>&1 | grep -q "Starting Apache"; then
            ready=true
            break
        fi
        sleep 2
    done

    if [ "$ready" = false ]; then
        echo "  TIMEOUT — auto-install did not complete"
        echo "  Last logs:"
        docker logs "${prefix}_web" 2>&1 | tail -5 | sed 's/^/    /'
        echo "FAIL $label INSTALL_TIMEOUT" >> "$RESULTS_FILE"
        ((FAIL_COUNT++)) || true
        cleanup "$label"
        return
    fi

    sleep 5  # Give Apache a moment

    # Run quick tests
    local http_code=$(curl -s -o /dev/null -w '%{http_code}' "http://localhost:${web_port}/" 2>/dev/null)
    local php_ver=$(docker exec "${prefix}_web" php -r 'echo PHP_VERSION;' 2>/dev/null)
    local table_count=$(docker exec "${prefix}_db" mysql -u tickets -ptickets tickets -N -e 'SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA="tickets"' 2>/dev/null)
    local admin_exists=$(docker exec "${prefix}_db" mysql -u tickets -ptickets tickets -N -e 'SELECT user FROM user WHERE level=0 LIMIT 1' 2>/dev/null)
    local version=$(docker exec "${prefix}_db" mysql -u tickets -ptickets tickets -N -e 'SELECT value FROM settings WHERE name="_version" LIMIT 1' 2>/dev/null)
    local tile_mode=$(docker exec "${prefix}_db" mysql -u tickets -ptickets tickets -N -e 'SELECT value FROM settings WHERE name="tile_mode" LIMIT 1' 2>/dev/null)

    # Test XSS protection
    local xss_payload=$(printf '123%%27%%3E%%3Cscript%%3Ealert(1)%%3C/script%%3E')
    local xss_body=$(curl -s "http://localhost:${web_port}/single_unit.php?id=${xss_payload}" 2>/dev/null)
    local xss_safe="yes"
    if echo "$xss_body" | grep -q '<script>alert(1)</script>'; then
        xss_safe="no"
    fi

    # Test PHP compat layer
    local compat=$(docker exec "${prefix}_web" php -r 'require "/var/www/html/incs/compat.inc.php"; echo function_exists("utf8_encode") ? "OK" : "FAIL";' 2>/dev/null)

    # Test password verification
    local pw_test=$(docker exec "${prefix}_web" php -r '
        require "/var/www/html/incs/compat.inc.php";
        require "/var/www/html/incs/security.inc.php";
        $ok = 0;
        if (verify_password("test", password_hash("test", PASSWORD_BCRYPT))["valid"]) $ok++;
        if (verify_password("test", md5("test"))["valid"]) $ok++;
        if (verify_password("test", "*" . strtoupper(sha1(sha1("test", true))))["valid"]) $ok++;
        echo $ok;
    ' 2>/dev/null)

    # Report
    local all_pass=true
    echo "  Results:"
    echo "    PHP version:    $php_ver"
    echo "    HTTP status:    $http_code $([ "$http_code" = "200" ] && echo "OK" || echo "FAIL")"
    [ "$http_code" != "200" ] && all_pass=false
    echo "    Tables:         $table_count $([ "$table_count" -gt 50 ] 2>/dev/null && echo "OK" || echo "FAIL")"
    [ "$table_count" -lt 50 ] 2>/dev/null && all_pass=false
    echo "    Admin user:     $admin_exists $([ "$admin_exists" = "admin" ] && echo "OK" || echo "FAIL")"
    [ "$admin_exists" != "admin" ] && all_pass=false
    echo "    Version:        $version $([ "$version" = "3.44.1" ] && echo "OK" || echo "FAIL")"
    echo "    Tile mode:      $tile_mode $([ "$tile_mode" = "proxy" ] && echo "OK" || echo "FAIL")"
    echo "    XSS protected:  $xss_safe $([ "$xss_safe" = "yes" ] && echo "OK" || echo "FAIL")"
    [ "$xss_safe" != "yes" ] && all_pass=false
    echo "    Compat layer:   $compat"
    [ "$compat" != "OK" ] && all_pass=false
    echo "    Password fmts:  $pw_test/3 $([ "$pw_test" = "3" ] && echo "OK" || echo "FAIL")"
    [ "$pw_test" != "3" ] && all_pass=false

    if [ "$all_pass" = true ]; then
        echo "  >>> PASS <<<"
        echo "PASS $label PHP=$php_ver tables=$table_count" >> "$RESULTS_FILE"
        ((PASS_COUNT++)) || true
    else
        echo "  >>> FAIL <<<"
        echo "FAIL $label PHP=$php_ver tables=$table_count http=$http_code" >> "$RESULTS_FILE"
        ((FAIL_COUNT++)) || true
    fi

    # Cleanup
    cleanup "$label"
    echo ""
}

# Clear results
> "$RESULTS_FILE"

# Run all combinations
for combo in "${MATRIX[@]}"; do
    read -r php_img db_img label <<< "$combo"
    ((TOTAL_COMBOS++)) || true
    run_test "$php_img" "$db_img" "$label"
done

# Summary
echo "================================================================="
echo "  MATRIX TEST RESULTS"
echo "================================================================="
echo ""
cat "$RESULTS_FILE"
echo ""
echo "  Passed: $PASS_COUNT / $TOTAL_COMBOS"
echo "  Failed: $FAIL_COUNT / $TOTAL_COMBOS"
echo ""
if [ "$FAIL_COUNT" -eq 0 ]; then
    echo "  ALL COMBINATIONS PASS"
else
    echo "  SOME COMBINATIONS FAILED — review output above"
fi
echo "================================================================="

exit $FAIL_COUNT
