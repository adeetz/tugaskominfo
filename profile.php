<?php
$pageTitle = 'Profil Saya';
require_once __DIR__ . '/includes/header.php';
requireLogin();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Token keamanan tidak valid.';
    } else {
        $email = trim($_POST['email'] ?? '');
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        if (empty($email)) {
            $errors[] = 'Email wajib diisi.';
        } elseif (!validateEmail($email)) {
            $errors[] = 'Format email tidak valid.';
        } else {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ? AND user_id != ?");
            $stmt->execute([$email, $currentUser['user_id']]);
            if ($stmt->fetchColumn() > 0) {
                $errors[] = 'Email sudah digunakan.';
            }
        }
        
        if (!empty($newPassword)) {
            if (empty($currentPassword)) {
                $errors[] = 'Kata sandi saat ini diperlukan untuk mengubah kata sandi.';
            } elseif (!password_verify($currentPassword, $currentUser['password_hash'])) {
                $errors[] = 'Kata sandi saat ini salah.';
            } elseif (!validatePassword($newPassword)) {
                $errors[] = 'Kata sandi baru harus minimal 8 karakter dan mengandung huruf besar, huruf kecil, dan angka.';
            } elseif ($newPassword !== $confirmPassword) {
                $errors[] = 'Kata sandi baru tidak cocok.';
            }
        }
        
        if (empty($errors)) {
            if (!empty($newPassword)) {
                $passwordHash = password_hash($newPassword, PASSWORD_BCRYPT);
                $stmt = $pdo->prepare("UPDATE users SET email = ?, password_hash = ?, updated_at = NOW() WHERE user_id = ?");
                $stmt->execute([$email, $passwordHash, $currentUser['user_id']]);
                logActivity($pdo, $currentUser['user_id'], 'update_profile', 'Memperbarui profil dengan perubahan kata sandi');
            } else {
                $stmt = $pdo->prepare("UPDATE users SET email = ?, updated_at = NOW() WHERE user_id = ?");
                $stmt->execute([$email, $currentUser['user_id']]);
                logActivity($pdo, $currentUser['user_id'], 'update_profile', 'Memperbarui profil');
            }
            
            $_SESSION['user_email'] = $email;
            
            setFlashMessage('success', 'Profil berhasil diperbarui.');
            redirect(APP_URL . '/profile.php');
        }
    }
}

$stmt = $pdo->prepare("SELECT * FROM activity_logs WHERE user_id = ? ORDER BY created_at DESC LIMIT 20");
$stmt->execute([$currentUser['user_id']]);
$myActivities = $stmt->fetchAll();
?>

<div class="row">
    <div class="col-12">
        <h1 class="mb-4"><i class="bi bi-person-circle"></i> Profil Saya</h1>
    </div>
</div>

<div class="row">
    <div class="col-12 col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-pencil"></i> Edit Profil</h5>
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
                               value="<?php echo sanitize($currentUser['email']); ?>">
                    </div>
                    
                    <hr class="my-4">
                    
                    <h6>Ubah Kata Sandi</h6>
                    <p class="text-muted small">Biarkan kosong jika Anda tidak ingin mengubah kata sandi.</p>
                    
                    <div class="mb-3">
                        <label for="current_password" class="form-label">Kata Sandi Saat Ini</label>
                        <input type="password" class="form-control" id="current_password" name="current_password">
                    </div>
                    
                    <div class="mb-3">
                        <label for="new_password" class="form-label">Kata Sandi Baru</label>
                        <input type="password" class="form-control" id="new_password" name="new_password">
                        <div class="form-text">Minimal 8 karakter, harus mengandung huruf besar, huruf kecil, dan angka.</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Konfirmasi Kata Sandi Baru</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle"></i> Perbarui Profil
                    </button>
                </form>
            </div>
        </div>
        
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-clock-history"></i> Aktivitas Terkini Saya</h5>
            </div>
            <div class="card-body">
                <?php if (empty($myActivities)): ?>
                <p class="text-muted">Belum ada aktivitas tercatat.</p>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Aksi</th>
                                <th>Deskripsi</th>
                                <th>Tanggal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($myActivities as $activity): ?>
                            <tr>
                                <td><span class="badge bg-primary"><?php echo sanitize($activity['action']); ?></span></td>
                                <td><?php echo sanitize($activity['description']); ?></td>
                                <td><?php echo formatDate($activity['created_at']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-12 col-lg-4 mt-4 mt-lg-0">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="bi bi-info-circle"></i> Informasi Akun</h6>
            </div>
            <div class="card-body">
                <dl>
                    <dt>User ID</dt>
                    <dd><?php echo $currentUser['user_id']; ?></dd>
                    
                    <dt>Email</dt>
                    <dd><?php echo sanitize($currentUser['email']); ?></dd>
                    
                    <dt>Role</dt>
                    <dd><span class="badge bg-secondary text-capitalize"><?php echo sanitize($currentUser['role']); ?></span></dd>
                    
                    <dt>Status</dt>
                    <dd>
                        <?php if ($currentUser['is_active']): ?>
                        <span class="badge bg-success">Aktif</span>
                        <?php else: ?>
                        <span class="badge bg-danger">Nonaktif</span>
                        <?php endif; ?>
                    </dd>
                    
                    <dt>Terdaftar Sejak</dt>
                    <dd><?php echo formatDate($currentUser['created_at']); ?></dd>
                    
                    <dt>Terakhir Diperbarui</dt>
                    <dd><?php echo formatDate($currentUser['updated_at']); ?></dd>
                </dl>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
