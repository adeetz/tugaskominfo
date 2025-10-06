<?php
// Halaman untuk manage user
$pageTitle = 'Kelola Pengguna';
require_once __DIR__ . '/../includes/header.php';
requirePermission('view');

// ambil parameter dari URL
$search = '';
if(isset($_GET['search'])) {
    $search = $_GET['search'];
}

$roleFilter = '';
if(isset($_GET['role'])) {
    $roleFilter = $_GET['role'];
}

$statusFilter = '';
if(isset($_GET['status'])) {
    $statusFilter = $_GET['status'];
}

$page = 1;
if(isset($_GET['page'])) {
    $page = (int)$_GET['page'];
}

$perPage = 20; // tampilkan 20 data per halaman
$offset = ($page - 1) * $perPage;

$where = [];
$params = [];

if (!empty($search)) {
    $where[] = "email LIKE ?";
    $params[] = "%$search%";
}

if (!empty($roleFilter)) {
    $where[] = "role = ?";
    $params[] = $roleFilter;
}

if ($statusFilter !== '') {
    $where[] = "is_active = ?";
    $params[] = $statusFilter;
}

$whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

$countStmt = $pdo->prepare("SELECT COUNT(*) FROM users $whereClause");
$countStmt->execute($params);
$totalUsers = $countStmt->fetchColumn();
$totalPages = ceil($totalUsers / $perPage);

$stmt = $pdo->prepare("SELECT * FROM users $whereClause ORDER BY created_at DESC LIMIT ? OFFSET ?");
$stmt->execute([...$params, $perPage, $offset]);
$users = $stmt->fetchAll();
?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1><i class="bi bi-people"></i> Kelola Pengguna</h1>
            <?php if (hasPermission($currentUser['role'], 'create')): ?>
            <a href="<?php echo APP_URL; ?>/users/create.php" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Tambah Pengguna Baru
            </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form method="GET" class="row g-3 mb-4">
            <div class="col-12 col-md-4">
                <input type="text" class="form-control" name="search" placeholder="Cari berdasarkan email..." 
                       value="<?php echo sanitize($search); ?>">
            </div>
            <div class="col-12 col-md-3">
                <select class="form-select" name="role">
                    <option value="">Semua Peran</option>
                    <option value="superadmin" <?php echo $roleFilter === 'superadmin' ? 'selected' : ''; ?>>Superadmin</option>
                    <option value="admin" <?php echo $roleFilter === 'admin' ? 'selected' : ''; ?>>Admin</option>
                    <option value="operator" <?php echo $roleFilter === 'operator' ? 'selected' : ''; ?>>Operator</option>
                    <option value="validator" <?php echo $roleFilter === 'validator' ? 'selected' : ''; ?>>Validator</option>
                </select>
            </div>
            <div class="col-12 col-md-3">
                <select class="form-select" name="status">
                    <option value="">Semua Status</option>
                    <option value="1" <?php echo $statusFilter === '1' ? 'selected' : ''; ?>>Aktif</option>
                    <option value="0" <?php echo $statusFilter === '0' ? 'selected' : ''; ?>>Nonaktif</option>
                </select>
            </div>
            <div class="col-12 col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search"></i> Filter
                </button>
            </div>
        </form>
        
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Created At</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted">Tidak ada pengguna ditemukan.</td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo $user['user_id']; ?></td>
                        <td><?php echo sanitize($user['email']); ?></td>
                        <td>
                            <span class="badge <?php echo getRoleBadgeClass($user['role']); ?> text-capitalize">
                                <?php echo sanitize($user['role']); ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($user['is_active']): ?>
                            <span class="badge bg-success">Aktif</span>
                            <?php else: ?>
                            <span class="badge bg-danger">Nonaktif</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo formatDate($user['created_at']); ?></td>
                        <td>
                            <div class="btn-group btn-group-sm" role="group">
                                <?php if (hasPermission($currentUser['role'], 'edit')): ?>
                                <?php if ($user['role'] !== 'superadmin' || hasPermission($currentUser['role'], 'manage_superadmin')): ?>
                                <a href="<?php echo APP_URL; ?>/users/edit.php?id=<?php echo $user['user_id']; ?>" 
                                   class="btn btn-outline-primary">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <?php endif; ?>
                                <?php endif; ?>
                                
                                <?php if (hasPermission($currentUser['role'], 'delete') && $user['user_id'] != $currentUser['user_id']): ?>
                                <?php if ($user['role'] !== 'superadmin' || hasPermission($currentUser['role'], 'manage_superadmin')): ?>
                                <button type="button" class="btn btn-outline-danger" 
                                        onclick="confirmDelete(<?php echo $user['user_id']; ?>, '<?php echo sanitize($user['email']); ?>')">
                                    <i class="bi bi-trash"></i>
                                </button>
                                <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </td>
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
                    <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&role=<?php echo urlencode($roleFilter); ?>&status=<?php echo urlencode($statusFilter); ?>">Sebelumnya</a>
                </li>
                
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li class="page-item <?php echo $page === $i ? 'active' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&role=<?php echo urlencode($roleFilter); ?>&status=<?php echo urlencode($statusFilter); ?>"><?php echo $i; ?></a>
                </li>
                <?php endfor; ?>
                
                <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&role=<?php echo urlencode($roleFilter); ?>&status=<?php echo urlencode($statusFilter); ?>">Selanjutnya</a>
                </li>
            </ul>
        </nav>
        <?php endif; ?>
    </div>
</div>

<form id="deleteForm" method="POST" action="<?php echo APP_URL; ?>/users/delete.php" style="display: none;">
    <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
    <input type="hidden" name="user_id" id="deleteUserId">
</form>

<script>
function confirmDelete(userId, email) {
    if (confirm(`Apakah Anda yakin ingin menghapus pengguna: ${email}?`)) {
        document.getElementById('deleteUserId').value = userId;
        document.getElementById('deleteForm').submit();
    }
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
