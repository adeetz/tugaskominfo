<?php
$pageTitle = 'Log Aktivitas';
require_once __DIR__ . '/../includes/header.php';
requirePermission('view_logs');

$actionFilter = $_GET['action'] ?? '';
$userFilter = isset($_GET['user']) ? (int)$_GET['user'] : 0;
$dateFrom = $_GET['date_from'] ?? '';
$dateTo = $_GET['date_to'] ?? '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 50;
$offset = ($page - 1) * $perPage;

$where = [];
$params = [];

if (!empty($actionFilter)) {
    $where[] = "action = ?";
    $params[] = $actionFilter;
}

if ($userFilter > 0) {
    $where[] = "activity_logs.user_id = ?";
    $params[] = $userFilter;
}

if (!empty($dateFrom)) {
    $where[] = "DATE(activity_logs.created_at) >= ?";
    $params[] = $dateFrom;
}

if (!empty($dateTo)) {
    $where[] = "DATE(activity_logs.created_at) <= ?";
    $params[] = $dateTo;
}

$whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

$countStmt = $pdo->prepare("
    SELECT COUNT(*) 
    FROM activity_logs 
    LEFT JOIN users ON activity_logs.user_id = users.user_id 
    $whereClause
");
$countStmt->execute($params);
$totalLogs = $countStmt->fetchColumn();
$totalPages = ceil($totalLogs / $perPage);

$stmt = $pdo->prepare("
    SELECT activity_logs.*, users.email 
    FROM activity_logs 
    LEFT JOIN users ON activity_logs.user_id = users.user_id 
    $whereClause 
    ORDER BY activity_logs.created_at DESC 
    LIMIT ? OFFSET ?
");
$stmt->execute([...$params, $perPage, $offset]);
$logs = $stmt->fetchAll();

$actionsStmt = $pdo->query("SELECT DISTINCT action FROM activity_logs ORDER BY action");
$actions = $actionsStmt->fetchAll(PDO::FETCH_COLUMN);

$usersStmt = $pdo->query("SELECT user_id, email FROM users ORDER BY email");
$users = $usersStmt->fetchAll();
?>

<div class="row">
    <div class="col-12">
        <h1 class="mb-4">
            <i class="bi bi-clock-history"></i> Log Aktivitas
        </h1>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form method="GET" class="row g-3 mb-4">
            <div class="col-12 col-md-3">
                <label for="action" class="form-label">Aksi</label>
                <select class="form-select" name="action" id="action">
                    <option value="">Semua Aksi</option>
                    <?php foreach ($actions as $action): ?>
                    <option value="<?php echo sanitize($action); ?>" <?php echo $actionFilter === $action ? 'selected' : ''; ?>>
                        <?php echo sanitize($action); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="col-12 col-md-3">
                <label for="user" class="form-label">Pengguna</label>
                <select class="form-select" name="user" id="user">
                    <option value="">Semua Pengguna</option>
                    <?php foreach ($users as $user): ?>
                    <option value="<?php echo $user['user_id']; ?>" <?php echo $userFilter === $user['user_id'] ? 'selected' : ''; ?>>
                        <?php echo sanitize($user['email']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="col-12 col-md-2">
                <label for="date_from" class="form-label">Dari Tanggal</label>
                <input type="date" class="form-control" name="date_from" id="date_from" value="<?php echo sanitize($dateFrom); ?>">
            </div>
            
            <div class="col-12 col-md-2">
                <label for="date_to" class="form-label">Sampai Tanggal</label>
                <input type="date" class="form-control" name="date_to" id="date_to" value="<?php echo sanitize($dateTo); ?>">
            </div>
            
            <div class="col-12 col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-funnel"></i> Filter
                </button>
            </div>
        </form>
        
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Pengguna</th>
                        <th>Aksi</th>
                        <th>Deskripsi</th>
                        <th>Alamat IP</th>
                        <th>Tanggal & Waktu</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($logs)): ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted">Tidak ada log ditemukan.</td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($logs as $log): ?>
                    <tr>
                        <td><?php echo $log['log_id']; ?></td>
                        <td>
                            <?php if ($log['email']): ?>
                            <?php echo sanitize($log['email']); ?>
                            <?php else: ?>
                            <span class="text-muted">Pengguna Terhapus</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge bg-primary">
                                <?php echo sanitize($log['action']); ?>
                            </span>
                        </td>
                        <td><?php echo sanitize($log['description']); ?></td>
                        <td>
                            <code><?php echo sanitize($log['ip_address']); ?></code>
                        </td>
                        <td><?php echo formatDate($log['created_at']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <?php if ($totalPages > 1): ?>
        <nav aria-label="Page navigation">
            <ul class="pagination justify-content-center">
                <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $page - 1; ?>&action=<?php echo urlencode($actionFilter); ?>&user=<?php echo $userFilter; ?>&date_from=<?php echo urlencode($dateFrom); ?>&date_to=<?php echo urlencode($dateTo); ?>">Sebelumnya</a>
                </li>
                
                <?php
                $startPage = max(1, $page - 2);
                $endPage = min($totalPages, $page + 2);
                for ($i = $startPage; $i <= $endPage; $i++):
                ?>
                <li class="page-item <?php echo $page === $i ? 'active' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $i; ?>&action=<?php echo urlencode($actionFilter); ?>&user=<?php echo $userFilter; ?>&date_from=<?php echo urlencode($dateFrom); ?>&date_to=<?php echo urlencode($dateTo); ?>"><?php echo $i; ?></a>
                </li>
                <?php endfor; ?>
                
                <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $page + 1; ?>&action=<?php echo urlencode($actionFilter); ?>&user=<?php echo $userFilter; ?>&date_from=<?php echo urlencode($dateFrom); ?>&date_to=<?php echo urlencode($dateTo); ?>">Selanjutnya</a>
                </li>
            </ul>
        </nav>
        <?php endif; ?>
        
        <div class="alert alert-info mt-4">
            <i class="bi bi-info-circle"></i> Menampilkan <?php echo count($logs); ?> dari <?php echo $totalLogs; ?> total log
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
