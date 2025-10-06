<?php

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        redirect(APP_URL . '/login.php');
    }
}

function requirePermission($action) {
    requireLogin();
    if (!hasPermission($_SESSION['user_role'], $action)) {
        setFlashMessage('danger', 'You do not have permission to perform this action.');
        redirect(APP_URL . '/dashboard.php');
    }
}

function login($pdo, $email, $password) {
    if (checkLoginAttempts($pdo, $email)) {
        return ['success' => false, 'message' => 'Too many failed login attempts. Please try again in 15 minutes.'];
    }
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password_hash'])) {
        if ($user['is_active'] == 0) {
            logLoginAttempt($pdo, $email, 0);
            return ['success' => false, 'message' => 'Your account has been deactivated.'];
        }
        
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        
        logLoginAttempt($pdo, $email, 1);
        logActivity($pdo, $user['user_id'], 'login', 'User logged in successfully');
        
        return ['success' => true, 'message' => 'Login successful'];
    }
    
    logLoginAttempt($pdo, $email, 0);
    return ['success' => false, 'message' => 'Invalid email or password.'];
}

function logout($pdo) {
    if (isLoggedIn()) {
        logActivity($pdo, $_SESSION['user_id'], 'logout', 'User logged out');
    }
    
    session_unset();
    session_destroy();
    redirect(APP_URL . '/login.php');
}

function getCurrentUser($pdo) {
    if (!isLoggedIn()) {
        return null;
    }
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}
