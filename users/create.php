<?php
$pageTitle = 'Tambah Pengguna';
require_once __DIR__ . '/../includes/header.php';
requirePermission('create');

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
        
        if (empty($email)) {
            $errors[] = 'Email wajib diisi.';
        } elseif (!validateEmail($email)) {
            $errors[] = 'Format email tidak valid.';
        } else {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetchColumn() > 0) {
                $errors[] = 'Email sudah digunakan.';
            }
        }
        
        if (empty($password)) {
            $errors[] = 'Kata sandi wajib diisi.';
        } elseif (!validatePassword($password)) {
            $errors[] = 'Kata sandi harus minimal 8 karakter dan mengandung huruf besar, huruf kecil, dan angka.';
        } elseif ($password !== $confirmPassword) {
            $errors[] = 'Kata sandi tidak cocok.';
        }
        
        if (empty($role)) {
            $errors[] = 'Peran wajib dipilih.';
        } elseif (!in_array($role, ['superadmin', 'admin', 'operator', 'validator'])) {
            $errors[] = 'Peran tidak valid.';
        } elseif ($role === 'superadmin' && !hasPermission($currentUser['role'], 'manage_superadmin')) {
            $errors[] = 'Anda tidak memiliki izin untuk membuat pengguna superadmin.';
        }
        
        // Kalau ga ada error, insert ke database
        if (empty($errors)) {
            // Hash password
            $passwordHash = password_hash($password, PASSWORD_BCRYPT);
            
            // Query insert user baru
            $sql = "INSERT INTO users (email, password_hash, role, is_active, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())";
            $stmt = $pdo->prepare($sql);
            
            if ($stmt->execute([$email, $passwordHash, $role, $isActive])) {
                // Ambil ID user yang baru dibuat
                $newUserId = $pdo->lastInsertId();
                
                // Log aktivitas
                logActivity($pdo, $currentUser['user_id'], 'create_user', "Membuat pengguna: $email (ID: $newUserId)");
                
                // Set pesan sukses
                setFlashMessage('success', 'User berhasil dibuat!');
                
                // Redirect ke halaman list user
                header('Location: index.php');
                exit;
            } else {
                $errors[] = 'Gagal membuat pengguna.';
            }
        }
    }
}
?>

<div class="row">
    <div class="col-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?php echo APP_URL; ?>/users/index.php">Pengguna</a></li>
                <li class="breadcrumb-item active">Tambah Pengguna</li>
            </ol>
        </nav>
    </div>
</div>

<div class="row">
    <div class="col-12 col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-person-plus"></i> Tambah Pengguna Baru</h5>
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
                               value="<?php echo isset($_POST['email']) ? sanitize($_POST['email']) : ''; ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">Kata Sandi <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="password" name="password" required>
                        <div class="form-text">Minimal 8 karakter, harus mengandung huruf besar, huruf kecil, dan angka.</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Konfirmasi Kata Sandi <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="role" class="form-label">Role <span class="text-danger">*</span></label>
                        <select class="form-select" id="role" name="role" required>
                            <option value="">Pilih Peran</option>
                            <?php if (hasPermission($currentUser['role'], 'manage_superadmin')): ?>
                            <option value="superadmin" <?php echo (isset($_POST['role']) && $_POST['role'] === 'superadmin') ? 'selected' : ''; ?>>Superadmin</option>
                            <?php endif; ?>
                            <option value="admin" <?php echo (isset($_POST['role']) && $_POST['role'] === 'admin') ? 'selected' : ''; ?>>Admin</option>
                            <option value="operator" <?php echo (isset($_POST['role']) && $_POST['role'] === 'operator') ? 'selected' : ''; ?>>Operator</option>
                            <option value="validator" <?php echo (isset($_POST['role']) && $_POST['role'] === 'validator') ? 'selected' : ''; ?>>Validator</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" 
                                   <?php echo (isset($_POST['is_active']) || !isset($_POST['email'])) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="is_active">
                                Aktif
                            </label>
                        </div>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> Tambah Pengguna
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
                <h6 class="mb-0"><i class="bi bi-info-circle"></i> Izin Peran</h6>
            </div>
            <div class="card-body">
                <dl>
                    <dt>Superadmin</dt>
                    <dd>Akses penuh ke semua fitur</dd>
                    
                    <dt>Admin</dt>
                    <dd>Kelola pengguna kecuali superadmin, lihat log</dd>
                    
                    <dt>Operator</dt>
                    <dd>Lihat dan edit pengguna</dd>
                    
                    <dt>Validator</dt>
                    <dd>Hanya dapat melihat</dd>
                </dl>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
