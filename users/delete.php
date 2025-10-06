<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

requirePermission('delete');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(APP_URL . '/users/index.php');
}

if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    setFlashMessage('danger', 'Token keamanan tidak valid.');
    redirect(APP_URL . '/users/index.php');
}

$userId = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
$currentUser = getCurrentUser($pdo);

if ($userId <= 0) {
    setFlashMessage('danger', 'ID pengguna tidak valid.');
    redirect(APP_URL . '/users/index.php');
}

if ($userId === $currentUser['user_id']) {
    setFlashMessage('danger', 'Anda tidak dapat menghapus akun Anda sendiri.');
    redirect(APP_URL . '/users/index.php');
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user) {
    setFlashMessage('danger', 'Pengguna tidak ditemukan.');
    redirect(APP_URL . '/users/index.php');
}

if ($user['role'] === 'superadmin' && !hasPermission($currentUser['role'], 'manage_superadmin')) {
    setFlashMessage('danger', 'Anda tidak memiliki izin untuk menghapus pengguna superadmin.');
    redirect(APP_URL . '/users/index.php');
}

$stmt = $pdo->prepare("DELETE FROM users WHERE user_id = ?");

if ($stmt->execute([$userId])) {
    logActivity($pdo, $currentUser['user_id'], 'delete_user', "Menghapus pengguna: {$user['email']} (ID: $userId)");
    setFlashMessage('success', 'Pengguna berhasil dihapus.');
} else {
    setFlashMessage('danger', 'Gagal menghapus pengguna.');
}

redirect(APP_URL . '/users/index.php');
