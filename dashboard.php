<?php
// Dashboard utama
$pageTitle = 'Dashboard';
require_once __DIR__ . '/includes/header.php';
requireLogin();

// Ambil statistik user
$stats = array();

// Total semua user
$query1 = "SELECT COUNT(*) as total FROM users";
$stmt = $pdo->query($query1);
$stats['total_users'] = $stmt->fetchColumn();

// Total user aktif
$query2 = "SELECT COUNT(*) as active FROM users WHERE is_active = 1";
$stmt = $pdo->query($query2);
$stats['active_users'] = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT * FROM activity_logs ORDER BY created_at DESC LIMIT 10");
$recent_activities = $stmt->fetchAll();
?>

<div class="row">
    <div class="col-12">
        <h1 class="mb-4">
            <i class="bi bi-speedometer2"></i> Dashboard
        </h1>
    </div>
</div>

<div class="row g-4">
    <!-- Card untuk total users -->
    <div class="col-12 col-md-6 col-lg-3">
        <div class="card text-white bg-primary" style="min-height: 120px;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title text-uppercase mb-0">Total Pengguna</h6>
                        <h2 class="mb-0"><?php echo $stats['total_users']; ?></h2>
                    </div>
                    <div class="display-4">
                        <i class="bi bi-people"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-12 col-md-6 col-lg-3">
        <div class="card text-white bg-success">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title text-uppercase mb-0">Pengguna Aktif</h6>
                        <h2 class="mb-0"><?php echo $stats['active_users']; ?></h2>
                    </div>
                    <div class="display-4">
                        <i class="bi bi-person-check"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-12 col-md-6 col-lg-3">
        <div class="card text-white bg-warning">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title text-uppercase mb-0">Pengguna Nonaktif</h6>
                        <h2 class="mb-0"><?php echo $stats['total_users'] - $stats['active_users']; ?></h2>
                    </div>
                    <div class="display-4">
                        <i class="bi bi-person-x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-12 col-md-6 col-lg-3">
        <div class="card text-white bg-info">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title text-uppercase mb-0">Peran Anda</h6>
                        <h5 class="mb-0 text-capitalize"><?php echo sanitize($currentUser['role']); ?></h5>
                    </div>
                    <div class="display-4">
                        <i class="bi bi-shield-check"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mt-2">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-clock-history"></i> Aktivitas Terkini</h5>
            </div>
            <div class="card-body">
                <?php if (empty($recent_activities)): ?>
                <p class="text-muted">Belum ada aktivitas tercatat.</p>
                <?php else: ?>
                <div class="activity-feed">
                    <?php foreach ($recent_activities as $activity): ?>
                    <div class="activity-item mb-3">
                        <div class="d-flex">
                            <div class="flex-shrink-0">
                                <i class="bi bi-circle-fill text-primary" style="font-size: 0.5rem;"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <p class="mb-0">
                                    <strong><?php echo sanitize($activity['action']); ?></strong>
                                </p>
                                <p class="text-muted small mb-0">
                                    <?php echo sanitize($activity['description']); ?>
                                </p>
                                <p class="text-muted small mb-0">
                                    <i class="bi bi-clock"></i> <?php echo formatDate($activity['created_at']); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php if (hasPermission($currentUser['role'], 'view_logs')): ?>
                <a href="<?php echo APP_URL; ?>/logs/index.php" class="btn btn-sm btn-outline-primary w-100">
                    Lihat Semua Log
                </a>
                <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
