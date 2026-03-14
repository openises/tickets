<?php
/**
 * PHPUnit test bootstrap for Tickets CAD
 *
 * Sets up a minimal environment for running unit tests without
 * requiring a database connection or web server.
 */

// Prevent any output buffering issues
error_reporting(E_ALL);

// Define test mode flag - used by application code to skip database connections
define('TICKETS_TEST_MODE', true);

// Set a predictable timezone for tests
date_default_timezone_set('America/New_York');

// Autoload PHPUnit and project files
require_once __DIR__ . '/../vendor/autoload.php';

// Load the new security and database abstraction layers (when they don't need DB)
if (file_exists(__DIR__ . '/../incs/security.inc.php')) {
    require_once __DIR__ . '/../incs/security.inc.php';
}
