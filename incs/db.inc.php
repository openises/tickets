<?php
/**
 * Tickets CAD - Database Abstraction Layer
 *
 * Provides a secure database interface using mysqli prepared statements.
 * This layer coexists with the legacy mysql2i shim during migration.
 * New code should use these functions; old code continues to work via the shim.
 *
 * Usage:
 *   // Simple query with no parameters
 *   $result = db_query("SELECT * FROM `{$GLOBALS['mysql_prefix']}settings`");
 *
 *   // Prepared statement with parameters (auto-detects types)
 *   $row = db_fetch_one(
 *       "SELECT * FROM `{$GLOBALS['mysql_prefix']}user` WHERE `id` = ?",
 *       [$userId]
 *   );
 *
 *   // Explicit type string
 *   $rows = db_fetch_all(
 *       "SELECT * FROM `{$GLOBALS['mysql_prefix']}ticket` WHERE `status` = ? AND `severity` >= ?",
 *       [$status, $severity],
 *       'si'
 *   );
 *
 *   // INSERT/UPDATE/DELETE
 *   db_query(
 *       "UPDATE `{$GLOBALS['mysql_prefix']}user` SET `name_f` = ? WHERE `id` = ?",
 *       [$firstName, $userId]
 *   );
 *   $affected = db_affected_rows();
 *
 * @since v3.44.0
 */

/**
 * Get or create the singleton mysqli connection.
 *
 * Uses the same credentials as the legacy mysql2i connection from mysql.inc.php.
 * The connection is created once and reused for all subsequent calls.
 *
 * @return mysqli The database connection object
 * @throws RuntimeException if connection fails
 */
function db(): mysqli
{
    static $conn = null;

    // Check if connection exists and is alive.
    // IMPORTANT: Do NOT use $conn->ping() here — ping() sends COM_PING to MySQL
    // which returns an OK packet that resets $conn->insert_id and $conn->affected_rows
    // to 0, breaking db_insert_id() and db_affected_rows() for callers.
    if ($conn !== null && $conn->errno === 0) {
        return $conn;
    }

    // In test mode, throw so tests can mock as needed
    if (defined('TICKETS_TEST_MODE') && TICKETS_TEST_MODE) {
        throw new RuntimeException('Database not available in test mode. Use integration tests.');
    }

    // Load credentials from the same source as the legacy code
    $host = $GLOBALS['mysql_host'] ?? 'localhost';
    $user = $GLOBALS['mysql_user'] ?? '';
    $pass = $GLOBALS['mysql_passwd'] ?? '';
    $dbname = $GLOBALS['mysql_db'] ?? '';

    $conn = new mysqli($host, $user, $pass, $dbname);

    if ($conn->connect_errno) {
        $error = $conn->connect_error;
        $conn = null;
        error_log("Tickets CAD DB connection failed: {$error}");
        throw new RuntimeException("Database connection failed: {$error}");
    }

    $conn->set_charset('utf8mb4');

    // Share connection with the mysql2i shim so legacy mysql_*() calls
    // (mysql_num_fields, mysql_field_name, etc.) still work during migration
    if (class_exists('mysql2i', false)) {
        mysql2i::$currObj = $conn;
    }

    return $conn;
}

/**
 * Execute a SQL query with optional prepared statement parameters.
 *
 * @param string      $sql    SQL query with ? placeholders
 * @param array       $params Values to bind to placeholders (optional)
 * @param string|null $types  mysqli type string ('s'=string, 'i'=int, 'd'=double, 'b'=blob)
 *                            If null, types are auto-detected from $params values
 * @return mysqli_result|bool  Result object for SELECT, true/false for INSERT/UPDATE/DELETE
 */
function db_query(string $sql, array $params = [], ?string $types = null)
{
    $conn = db();

    // No parameters — simple query (safe only for queries without user input)
    if (empty($params)) {
        $result = $conn->query($sql);
        if ($result === false) {
            error_log("Tickets CAD query error: {$conn->error} | SQL: " . substr($sql, 0, 200));
        }
        return $result;
    }

    // Prepared statement with parameters
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        error_log("Tickets CAD prepare error: {$conn->error} | SQL: " . substr($sql, 0, 200));
        return false;
    }

    // Auto-detect types if not provided
    if ($types === null) {
        $types = db_build_types($params);
    }

    // Bind parameters
    $stmt->bind_param($types, ...$params);

    // Execute
    if (!$stmt->execute()) {
        error_log("Tickets CAD execute error: {$stmt->error} | SQL: " . substr($sql, 0, 200));
        $stmt->close();
        return false;
    }

    // For SELECT queries, return the result set
    $result = $stmt->get_result();
    if ($result !== false) {
        // Store the result so we can close the statement
        // Return the result object (caller can use mysql_fetch_assoc patterns)
        return $result;
    }

    // For INSERT/UPDATE/DELETE, capture insert_id before closing
    // Store it so db_insert_id() can retrieve it reliably
    $GLOBALS['_db_last_insert_id'] = (int) $stmt->insert_id;
    $GLOBALS['_db_last_affected_rows'] = (int) $stmt->affected_rows;
    $stmt->close();
    return true;
}

/**
 * Fetch all rows from a SELECT query as an array of associative arrays.
 *
 * @param string      $sql    SQL query with ? placeholders
 * @param array       $params Values to bind (optional)
 * @param string|null $types  Type string (optional, auto-detected)
 * @return array Array of associative arrays, empty array on failure
 */
function db_fetch_all(string $sql, array $params = [], ?string $types = null): array
{
    $result = db_query($sql, $params, $types);

    if ($result === false || $result === true) {
        return [];
    }

    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
    $result->free();

    return $rows;
}

/**
 * Fetch a single row from a SELECT query as an associative array.
 *
 * @param string      $sql    SQL query with ? placeholders
 * @param array       $params Values to bind (optional)
 * @param string|null $types  Type string (optional, auto-detected)
 * @return array|null Array (both numeric and associative keys) for the row, or null if no results.
 *                    Uses fetch_array() for backward compatibility with legacy code that accesses
 *                    columns by numeric index (e.g. $row[0] for the first column).
 */
function db_fetch_one(string $sql, array $params = [], ?string $types = null): ?array
{
    $result = db_query($sql, $params, $types);

    if ($result === false || $result === true) {
        return null;
    }

    $row = $result->fetch_array();
    $result->free();

    return $row ?: null;
}

/**
 * Get the last auto-increment ID from an INSERT query.
 *
 * @return int The last insert ID
 */
function db_insert_id(): int
{
    // Use the value captured by db_query() before $stmt->close(),
    // falling back to the connection's value for non-prepared queries
    return (int) ($GLOBALS['_db_last_insert_id'] ?? db()->insert_id);
}

/**
 * Get the number of affected rows from the last INSERT/UPDATE/DELETE.
 *
 * @return int Number of affected rows
 */
function db_affected_rows(): int
{
    // Use the value captured by db_query() before $stmt->close(),
    // falling back to the connection's value for non-prepared queries
    return (int) ($GLOBALS['_db_last_affected_rows'] ?? db()->affected_rows);
}

/**
 * Escape a string for use in SQL where prepared statements cannot be used.
 *
 * This should ONLY be used for dynamic table/column names that come from
 * trusted configuration (like $GLOBALS['mysql_prefix']), never for user input.
 *
 * @param string $value The value to escape
 * @return string The escaped value
 */
function db_escape(string $value): string
{
    return db()->real_escape_string($value);
}

/**
 * Auto-detect mysqli type string from parameter values.
 *
 * @param array $params Array of parameter values
 * @return string Type string (e.g., 'ssi' for string, string, integer)
 */
function db_build_types(array $params): string
{
    $types = '';
    foreach ($params as $param) {
        if (is_int($param)) {
            $types .= 'i';
        } elseif (is_float($param)) {
            $types .= 'd';
        } else {
            $types .= 's';
        }
    }
    return $types;
}

/**
 * Sanitize a table name to prevent SQL injection in dynamic table references.
 *
 * Only allows alphanumeric characters and underscores.
 * This is for the table PREFIX and table NAMES from configuration — never user input.
 *
 * @param string $name Table name or prefix
 * @return string Sanitized table name
 */
function db_sanitize_table_name(string $name): string
{
    return preg_replace('/[^a-zA-Z0-9_]/', '', $name);
}

/**
 * Get the table name with prefix applied.
 *
 * Convenience function to consistently build prefixed table names.
 *
 * @param string $table Base table name (e.g., 'ticket', 'user', 'settings')
 * @return string Full table name with prefix and backtick quoting
 */
function db_table(string $table): string
{
    $prefix = $GLOBALS['mysql_prefix'] ?? '';
    return '`' . db_sanitize_table_name($prefix . $table) . '`';
}
