<?php
$pageTitle = 'Edit Pengguna';
require_once __DIR__ . '/../includes/header.php';
requirePermission('edit');

$userId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($userId <= 0) {
    setFlashMessage('danger', 'ID pengguna tidak valid.');
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
    setFlashMessage('danger', 'Anda tidak memiliki izin untuk mengedit pengguna superadmin.');
    redirect(APP_URL . '/users/index.php');
}

if ($userId === $currentUser['user_id'] && isset($_POST['is_active']) && !$_POST['is_active']) {
    setFlashMessage('danger', 'Anda tidak dapat menonaktifkan akun Anda sendiri.');
    redirect(APP_URL . '/users/edit.php?id=' . $userId);
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Token keamanan tidak valid.';
    } else {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        $role = $_POST['role'] ?? '';
        $isActive = isset($_POST['is_active']) ? 1 : 0;
        
        if ($userId === $currentUser['user_id'] && $isActive == 0) {
            $errors[] = 'Anda tidak dapat menonaktifkan akun Anda sendiri.';
        }
        
        if (empty($email)) {
            $errors[] = 'Email wajib diisi.';
        } elseif (!validateEmail($email)) {
            $errors[] = 'Format email tidak valid.';
        } else {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ? AND user_id != ?");
            $stmt->execute([$email, $userId]);
            if ($stmt->fetchColumn() > 0) {
                $errors[] = 'Email sudah digunakan.';
            }
        }
        
        if (!empty($password)) {
            if (!validatePassword($password)) {
                $errors[] = 'Kata sandi harus minimal 8 karakter dan mengandung huruf besar, huruf kecil, dan angka.';
            } elseif ($password !== $confirmPassword) {
                $errors[] = 'Kata sandi tidak cocok.';
            }
        }
        
        if (empty($role)) {
            $errors[] = 'Peran wajib dipilih.';
        } elseif (!in_array($role, ['superadmin', 'admin', 'operator', 'validator'])) {
            $errors[] = 'Peran tidak valid.';
        } elseif ($role === 'superadmin' && !hasPermission($currentUser['role'], 'manage_superadmin')) {
            $errors[] = 'Anda tidak memiliki izin untuk mengatur peran superadmin.';
        }
        
        if (empty($errors)) {
            if (!empty($password)) {
                $passwordHash = password_hash($password, PASSWORD_BCRYPT);
                $stmt = $pdo->prepare("UPDATE users SET email = ?, password_hash = ?, role = ?, is_active = ?, updated_at = NOW() WHERE user_id = ?");
                $stmt->execute([$email, $passwordHash, $role, $isActive, $userId]);
            } else {
                $stmt = $pdo->prepare("UPDATE users SET email = ?, role = ?, is_active = ?, updated_at = NOW() WHERE user_id = ?");
                $stmt->execute([$email, $role, $isActive, $userId]);
            }
            
            logActivity($pdo, $currentUser['user_id'], 'update_user', "Memperbarui pengguna: $email (ID: $userId)");
            
            setFlashMessage('success', 'Pengguna berhasil diperbarui.');
            redirect(APP_URL . '/users/index.php');
        }
    }
} else {
    $_POST = $user;
}
?>

<div class="row">
    <div class="col-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?php echo APP_URL; ?>/users/index.php">Pengguna</a></li>
                <li class="breadcrumb-item active">Edit Pengguna</li>
            </ol>
        </nav>
    </div>
</div>

<div class="row">
    <div class="col-12 col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-pencil"></i> Edit Pengguna</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?>
                        <li><?php echo sanitize($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Alamat Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="email" name="email" required
                               value="<?php echo sanitize($_POST['email']); ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">Kata Sandi Baru</label>
                        <input type="password" class="form-control" id="password" name="password">
                        <div class="form-text">Biarkan kosong untuk mempertahankan kata sandi saat ini.</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Konfirmasi Kata Sandi Baru</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                    </div>
                    
                    <div class="mb-3">
                        <label for="role" class="form-label">Role <span class="text-danger">*</span></label>
                        <select class="form-select" id="role" name="role" required>
                            <?php if (hasPermission($currentUser['role'], 'manage_superadmin')): ?>
                            <option value="superadmin" <?php echo $_POST['role'] === 'superadmin' ? 'selected' : ''; ?>>Superadmin</option>
                            <?php endif; ?>
                            <option value="admin" <?php echo $_POST['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                            <option value="operator" <?php echo $_POST['role'] === 'operator' ? 'selected' : ''; ?>>Operator</option>
                            <option value="validator" <?php echo $_POST['role'] === 'validator' ? 'selected' : ''; ?>>Validator</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" 
                                   <?php echo $_POST['is_active'] ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="is_active">
                                Aktif
                            </label>
                        </div>
                    </div>
                    
                    <div class="alert alert-info">
                        <small>
                            <strong>Dibuat:</strong> <?php echo formatDate($user['created_at']); ?><br>
                            <strong>Terakhir Diperbarui:</strong> <?php echo formatDate($user['updated_at']); ?>
                        </small>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> Perbarui Pengguna
                        </button>
                        <a href="<?php echo APP_URL; ?>/users/index.php" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-12 col-lg-4 mt-4 mt-lg-0">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="bi bi-info-circle"></i> Informasi Pengguna</h6>
            </div>
            <div class="card-body">
                <dl>
                    <dt>User ID</dt>
                    <dd><?php echo $user['user_id']; ?></dd>
                    
                    <dt>Email</dt>
                    <dd><?php echo sanitize($user['email']); ?></dd>
                    
                    <dt>Peran Saat Ini</dt>
                    <dd><span class="badge <?php echo getRoleBadgeClass($user['role']); ?> text-capitalize"><?php echo sanitize($user['role']); ?></span></dd>
                    
                    <dt>Status</dt>
                    <dd>
                        <?php if ($user['is_active']): ?>
                        <span class="badge bg-success">Aktif</span>
                        <?php else: ?>
                        <span class="badge bg-danger">Nonaktif</span>
                        <?php endif; ?>
                    </dd>
                </dl>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
