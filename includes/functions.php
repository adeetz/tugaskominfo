<?php
// File berisi helper functions

// Generate CSRF token untuk keamanan form
function generateCsrfToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Verifikasi CSRF token
function verifyCsrfToken($token) {
    if(isset($_SESSION['csrf_token']) && $_SESSION['csrf_token'] == $token) {
        return true;
    }
    return false;
}

// Sanitasi data untuk mencegah XSS
function sanitize($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

function redirect($url) {
    header("Location: " . $url);
    exit;
}

function getClientIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        return $_SERVER['REMOTE_ADDR'];
    }
}

function logActivity($pdo, $user_id, $action, $description) {
    $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, description, ip_address, created_at) VALUES (?, ?, ?, ?, NOW())");
    $stmt->execute([$user_id, $action, $description, getClientIP()]);
}

function logLoginAttempt($pdo, $email, $success) {
    $stmt = $pdo->prepare("INSERT INTO login_attempts (email, ip_address, success, attempted_at) VALUES (?, ?, ?, NOW())");
    $stmt->execute([$email, getClientIP(), $success]);
}

function checkLoginAttempts($pdo, $email) {
    $stmt = $pdo->prepare("SELECT COUNT(*) as attempts FROM login_attempts WHERE email = ? AND success = 0 AND attempted_at > DATE_SUB(NOW(), INTERVAL 15 MINUTE)");
    $stmt->execute([$email]);
    $result = $stmt->fetch();
    return $result['attempts'] >= 5;
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function validatePassword($password) {
    if (strlen($password) < 8) return false;
    if (!preg_match('/[A-Z]/', $password)) return false;
    if (!preg_match('/[a-z]/', $password)) return false;
    if (!preg_match('/[0-9]/', $password)) return false;
    return true;
}

function hasPermission($userRole, $action) {
    $permissions = [
        'superadmin' => ['view', 'create', 'edit', 'delete', 'view_logs', 'manage_superadmin', 'manage_users'],
        'admin' => ['view', 'create', 'edit', 'delete', 'view_logs', 'manage_users'],
        'operator' => ['view', 'edit'],
        'validator' => ['view']
    ];
    
    return in_array($action, $permissions[$userRole] ?? []);
}

function formatDate($datetime) {
    return date('d M Y H:i', strtotime($datetime));
}

function setFlashMessage($type, $message) {
    $_SESSION['flash_message'] = ['type' => $type, 'message' => $message];
}

function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $flash = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $flash;
    }
    return null;
}

function getRoleBadgeClass($role) {
    $colors = [
        'superadmin' => 'bg-danger',
        'admin' => 'bg-primary',
        'operator' => 'bg-info',
        'validator' => 'bg-warning text-dark'
    ];
    return $colors[$role] ?? 'bg-secondary';
}
