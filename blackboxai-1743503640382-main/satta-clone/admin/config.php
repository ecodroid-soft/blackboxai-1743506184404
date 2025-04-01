<?php
// Database configuration (if needed in future)
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'satta_king');

// Google Sheets configuration
define('GOOGLE_SHEETS_ID', 'YOUR_SPREADSHEET_ID');
define('GOOGLE_SHEETS_NAME', 'Results');

// Admin credentials (in production, use proper password hashing)
define('ADMIN_USERNAME', 'admin');
define('ADMIN_PASSWORD', 'admin123');

// Site configuration
define('SITE_NAME', 'Satta King');
define('SITE_URL', 'http://localhost:8000');

// Time settings
date_default_timezone_set('Asia/Kolkata');

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Session configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Set to 1 in production with HTTPS

// Create data directory if it doesn't exist
$dataDir = __DIR__ . '/../data';
if (!file_exists($dataDir)) {
    mkdir($dataDir, 0755, true);
}

// Function to sanitize input
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Function to validate date format
function validate_date($date, $format = 'Y-m-d') {
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}

// Function to validate time format
function validate_time($time, $format = 'H:i') {
    $d = DateTime::createFromFormat($format, $time);
    return $d && $d->format($format) === $time;
}

// Function to get client IP
function get_client_ip() {
    $ipaddress = '';
    if (isset($_SERVER['HTTP_CLIENT_IP']))
        $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
    else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
    else if(isset($_SERVER['HTTP_X_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
    else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
    else if(isset($_SERVER['HTTP_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_FORWARDED'];
    else if(isset($_SERVER['REMOTE_ADDR']))
        $ipaddress = $_SERVER['REMOTE_ADDR'];
    else
        $ipaddress = 'UNKNOWN';
    return $ipaddress;
}

// Function to log admin actions
function log_admin_action($action, $details = '') {
    $logFile = __DIR__ . '/../data/admin_log.txt';
    $timestamp = date('Y-m-d H:i:s');
    $ip = get_client_ip();
    $username = isset($_SESSION['admin_username']) ? $_SESSION['admin_username'] : 'unknown';
    
    $logEntry = sprintf(
        "[%s] User: %s, IP: %s, Action: %s, Details: %s\n",
        $timestamp,
        $username,
        $ip,
        $action,
        $details
    );
    
    file_put_contents($logFile, $logEntry, FILE_APPEND);
}

// Initialize session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>