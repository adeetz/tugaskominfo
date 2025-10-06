<?php
if (!defined('APP_NAME')) {
    require_once __DIR__ . '/../config/config.php';
}
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/auth.php';

$currentUser = getCurrentUser($pdo);
$flash = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php if(isset($pageTitle)) { echo sanitize($pageTitle) . ' - '; } echo APP_NAME; ?></title>
    <!-- Bootstrap CSS dari CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <!-- Sweet Alert 2 -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/assets/css/style.css">
</head>
<body>
    <?php if (isLoggedIn()): ?>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?php echo APP_URL; ?>/dashboard.php">
                <i class="bi bi-people-fill"></i> <?php echo APP_NAME; ?>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo APP_URL; ?>/dashboard.php">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                    </li>
                    <?php if (hasPermission($currentUser['role'], 'view')): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo APP_URL; ?>/users/index.php">
                            <i class="bi bi-people"></i> Users
                        </a>
                    </li>
                    <?php endif; ?>
                    <?php if (hasPermission($currentUser['role'], 'view_logs')): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo APP_URL; ?>/logs/index.php">
                            <i class="bi bi-clock-history"></i> Log Aktivitas
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i> <?php echo sanitize($currentUser['email']); ?>
                            <span class="badge bg-secondary"><?php echo sanitize($currentUser['role']); ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="<?php echo APP_URL; ?>/profile.php">
                                <i class="bi bi-person"></i> Profil
                            </a></li>
                            <?php if (hasPermission($currentUser['role'], 'manage_users')): ?>
                            <li><a class="dropdown-item" href="<?php echo APP_URL; ?>/settings.php">
                                <i class="bi bi-gear-fill"></i> Pengaturan
                            </a></li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?php echo APP_URL; ?>/logout.php">
                                <i class="bi bi-box-arrow-right"></i> Keluar
                            </a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <?php endif; ?>
    
    <div class="<?php echo isLoggedIn() ? 'container-fluid mt-4' : 'container'; ?>">
        <?php if ($flash): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: '<?php echo $flash['type'] === 'success' ? 'success' : ($flash['type'] === 'danger' ? 'error' : $flash['type']); ?>',
                    title: '<?php echo $flash['type'] === 'success' ? 'Berhasil!' : 'Perhatian!'; ?>',
                    text: '<?php echo addslashes(sanitize($flash['message'])); ?>',
                    confirmButtonText: 'OK',
                    timer: 3000,
                    timerProgressBar: true
                });
            });
        </script>
        <?php endif; ?>
