<?php
// Check if setup is needed
define('DB_HOST', 'localhost');
define('DB_NAME', 'tugaskominfo');
define('DB_USER', 'root');
define('DB_PASS', '');

$needsSetup = true;

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = 'admin@example.com' AND is_active = 1");
    $stmt->execute();
    $user = $stmt->fetch();
    
    if ($user && password_verify('Admin@123', $user['password_hash'])) {
        $needsSetup = false;
    }
} catch (PDOException $e) {
    $needsSetup = true;
}

if ($needsSetup) {
    header('Location: setup.php');
} else {
    header('Location: login.php');
}
exit;
