<?php
// Set session security
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0);

session_start();

// Konfigurasi aplikasi
define('APP_NAME', 'Sistem Manajemen Pengguna');
define('APP_URL', 'http://localhost/tugaskominfo');
define('SESSION_TIMEOUT', 1800); // 30 menit

// Set timezone Indonesia
date_default_timezone_set('Asia/Jakarta');

if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT)) {
    session_unset();
    session_destroy();
    header('Location: ' . APP_URL . '/login.php?timeout=1');
    exit;
}

$_SESSION['last_activity'] = time();

require_once __DIR__ . '/database.php';
