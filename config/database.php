<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'nam_builders');

// Create connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to utf8
$conn->set_charset("utf8");

// Define base paths — rtrim prevents double-slash when script is at domain root
$_base_dir = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
// Strip any filename - ensure we only keep the directory portion
if (pathinfo($_base_dir, PATHINFO_EXTENSION) !== '') {
    $_base_dir = dirname($_base_dir);
}
define('BASE_URL',    'http://' . $_SERVER['HTTP_HOST'] . $_base_dir . '/');
define('ROOT_PATH',   dirname(dirname(__FILE__)) . '/');
define('UPLOADS_PATH', ROOT_PATH . 'uploads/');
define('UPLOADS_URL',  BASE_URL . 'uploads/');

// Create uploads directories if they don't exist
if (!is_dir(UPLOADS_PATH)) {
    mkdir(UPLOADS_PATH, 0755, true);
}
if (!is_dir(UPLOADS_PATH . 'clients/')) {
    mkdir(UPLOADS_PATH . 'clients/', 0755, true);
}
if (!is_dir(UPLOADS_PATH . 'services/')) {
    mkdir(UPLOADS_PATH . 'services/', 0755, true);
}

// Session configuration
ini_set('session.gc_maxlifetime', 3600);
session_set_cookie_params(3600);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>