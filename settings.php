<?php
$pageTitle = 'Pengaturan Sistem';
require_once __DIR__ . '/includes/header.php';
requireLogin();

if (!hasPermission($currentUser['role'], 'manage_users')) {
    setFlashMessage('danger', 'Anda tidak memiliki akses ke halaman ini.');
    redirect(APP_URL . '/dashboard.php');
}

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Token keamanan tidak valid.';
    } else {
        $logoFileName = '';
        
        // Cek apakah ada file yang diupload
        if (isset($_FILES['login_logo']) && $_FILES['login_logo']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['login_logo'];
            $fileName = $file['name'];
            $fileTmpName = $file['tmp_name'];
            $fileSize = $file['size'];
            $fileError = $file['error'];
            
            // Validasi extension
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'svg', 'gif', 'webp'];
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            
            if (!in_array($fileExtension, $allowedExtensions)) {
                $errors[] = 'Format file tidak didukung. Gunakan: JPG, PNG, SVG, GIF, atau WebP.';
            } elseif ($fileSize > 2 * 1024 * 1024) { // 2MB
                $errors[] = 'Ukuran file terlalu besar. Maksimal 2MB.';
            } else {
                // Generate nama file unik
                $newFileName = 'logo_' . time() . '.' . $fileExtension;
                $uploadDir = __DIR__ . '/assets/images/';
                $uploadPath = $uploadDir . $newFileName;
                
                // Pastikan folder ada
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                
                // Hapus logo lama jika ada
                $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = 'login_logo'");
                $stmt->execute();
                $oldLogo = $stmt->fetchColumn();
                if (!empty($oldLogo) && file_exists($uploadDir . $oldLogo)) {
                    unlink($uploadDir . $oldLogo);
                }
                
                // Upload file baru
                if (move_uploaded_file($fileTmpName, $uploadPath)) {
                    $logoFileName = $newFileName;
                } else {
                    $errors[] = 'Gagal mengupload file.';
                }
            }
        } elseif (isset($_POST['remove_logo'])) {
            // Hapus logo
            $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = 'login_logo'");
            $stmt->execute();
            $oldLogo = $stmt->fetchColumn();
            if (!empty($oldLogo) && file_exists(__DIR__ . '/assets/images/' . $oldLogo)) {
                unlink(__DIR__ . '/assets/images/' . $oldLogo);
            }
            $logoFileName = '';
            
            // Update database langsung untuk hapus
            $stmt = $pdo->prepare("UPDATE settings SET setting_value = '', updated_at = NOW() WHERE setting_key = 'login_logo'");
            $stmt->execute();
            
            logActivity($pdo, $currentUser['user_id'], 'delete_logo', 'Menghapus logo login');
            setFlashMessage('success', 'Logo berhasil dihapus.');
            redirect(APP_URL . '/settings.php');
        } else {
            // Tidak ada perubahan, gunakan logo yang ada
            $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = 'login_logo'");
            $stmt->execute();
            $logoFileName = $stmt->fetchColumn() ?: '';
        }
        
        if (empty($errors)) {
            try {
                $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value, updated_at) 
                                       VALUES ('login_logo', ?, NOW()) 
                                       ON DUPLICATE KEY UPDATE setting_value = ?, updated_at = NOW()");
                $stmt->execute([$logoFileName, $logoFileName]);
                
                logActivity($pdo, $currentUser['user_id'], 'update_settings', 'Memperbarui pengaturan logo login');
                setFlashMessage('success', 'Pengaturan berhasil disimpan.');
                redirect(APP_URL . '/settings.php');
            } catch (PDOException $e) {
                $errors[] = 'Gagal menyimpan pengaturan: ' . $e->getMessage();
            }
        }
    }
}

$stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = 'login_logo'");
$stmt->execute();
$currentLogo = $stmt->fetchColumn() ?: '';
?>

<div class="row">
    <div class="col-12">
        <h1 class="mb-4"><i class="bi bi-gear-fill"></i> Pengaturan Sistem</h1>
    </div>
</div>

<div class="row">
    <div class="col-12 col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-image"></i> Pengaturan Logo Login</h5>
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
                
                <form method="POST" action="" enctype="multipart/form-data" id="uploadForm">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                    
                    <?php if (!empty($currentLogo)): ?>
                    <div class="mb-3">
                        <label class="form-label">Logo Saat Ini</label>
                        <div class="border p-3 text-center bg-light mb-2">
                            <img src="<?php echo APP_URL; ?>/assets/images/<?php echo sanitize($currentLogo); ?>" alt="Logo" style="max-width: 200px; max-height: 100px;">
                        </div>
                        <button type="button" class="btn btn-sm btn-danger" onclick="confirmDelete()">
                            <i class="bi bi-trash"></i> Hapus Logo
                        </button>
                    </div>
                    <?php endif; ?>
                    
                    <div class="mb-3">
                        <label for="login_logo" class="form-label">Upload Logo Baru</label>
                        <input type="file" class="form-control" id="login_logo" name="login_logo" accept="image/png,image/jpeg,image/jpg,image/svg+xml,image/gif,image/webp" onchange="previewLogo(this)">
                        <div class="form-text">
                            Format: JPG, PNG, SVG, GIF, WebP | Maksimal: 2MB | Rekomendasi ukuran: 200x100 piksel
                        </div>
                    </div>
                    
                    <!-- Preview logo sebelum upload -->
                    <div id="previewContainer" class="mb-3" style="display: none;">
                        <label class="form-label">Preview Logo Baru</label>
                        <div class="border p-3 text-center bg-light">
                            <img id="previewImage" alt="Preview" style="max-width: 200px; max-height: 100px;">
                        </div>
                    </div>
                    
                    <button type="button" class="btn btn-primary" onclick="confirmUpload()">
                        <i class="bi bi-upload"></i> Upload Logo
                    </button>
                    <a href="<?php echo APP_URL; ?>/dashboard.php" class="btn btn-secondary">
                        <i class="bi bi-x-circle"></i> Batal
                    </a>
                </form>
            </div>
        </div>
        
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-info-circle"></i> Rekomendasi Logo</h5>
            </div>
            <div class="card-body">
                <ul>
                    <li><strong>Format:</strong> PNG (background transparan), JPG, SVG, GIF, atau WebP</li>
                    <li><strong>Ukuran File:</strong> Maksimal 2MB</li>
                    <li><strong>Dimensi:</strong> Rekomendasi 200px × 100px (otomatis disesuaikan)</li>
                    <li><strong>Rasio:</strong> Landscape (horizontal) lebih baik untuk logo</li>
                </ul>
                
                <div class="alert alert-info mt-3 mb-0">
                    <small>
                        <i class="bi bi-lightbulb"></i> <strong>Tips:</strong> 
                        Gunakan logo dengan background transparan (PNG) agar terlihat lebih profesional di halaman login.
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<form id="deleteLogoForm" method="POST" action="" style="display: none;">
    <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
    <input type="hidden" name="remove_logo" value="1">
</form>

<script>
// Preview logo sebelum upload
function previewLogo(input) {
    const file = input.files[0];
    
    if (file) {
        // Validasi ukuran file (2MB = 2 * 1024 * 1024 bytes)
        const maxSize = 2 * 1024 * 1024;
        if (file.size > maxSize) {
            Swal.fire({
                icon: 'error',
                title: 'File Terlalu Besar!',
                text: 'Ukuran file maksimal 2MB. File Anda: ' + (file.size / 1024 / 1024).toFixed(2) + ' MB',
                confirmButtonText: 'OK'
            });
            input.value = '';
            document.getElementById('previewContainer').style.display = 'none';
            return;
        }
        
        // Validasi format file
        const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/svg+xml', 'image/gif', 'image/webp'];
        if (!allowedTypes.includes(file.type)) {
            Swal.fire({
                icon: 'error',
                title: 'Format Tidak Didukung!',
                text: 'Gunakan format: JPG, PNG, SVG, GIF, atau WebP',
                confirmButtonText: 'OK'
            });
            input.value = '';
            document.getElementById('previewContainer').style.display = 'none';
            return;
        }
        
        // Preview gambar
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('previewImage').src = e.target.result;
            document.getElementById('previewContainer').style.display = 'block';
        };
        reader.readAsDataURL(file);
    } else {
        document.getElementById('previewContainer').style.display = 'none';
    }
}

// Konfirmasi upload dengan Sweet Alert
function confirmUpload() {
    const fileInput = document.getElementById('login_logo');
    
    if (!fileInput.files || fileInput.files.length === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Pilih File!',
            text: 'Silakan pilih file logo terlebih dahulu',
            confirmButtonText: 'OK'
        });
        return;
    }
    
    const file = fileInput.files[0];
    const fileSize = (file.size / 1024 / 1024).toFixed(2);
    
    Swal.fire({
        title: 'Upload Logo?',
        html: `
            <div class="text-start">
                <p><strong>Nama File:</strong> ${file.name}</p>
                <p><strong>Ukuran:</strong> ${fileSize} MB</p>
                <p><strong>Type:</strong> ${file.type}</p>
            </div>
            <p class="text-muted mt-2">Logo lama akan diganti dengan logo baru ini.</p>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#0d6efd',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="bi bi-upload"></i> Ya, Upload!',
        cancelButtonText: 'Batal',
        showLoaderOnConfirm: true,
        preConfirm: () => {
            // Submit form
            return new Promise((resolve) => {
                document.getElementById('uploadForm').submit();
                resolve();
            });
        },
        allowOutsideClick: () => !Swal.isLoading()
    });
}

// Konfirmasi hapus logo
function confirmDelete() {
    Swal.fire({
        title: 'Hapus Logo?',
        text: "Logo akan dihapus dan halaman login akan menggunakan ikon default.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="bi bi-trash"></i> Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            // Show loading
            Swal.fire({
                title: 'Menghapus...',
                text: 'Mohon tunggu sebentar',
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            document.getElementById('deleteLogoForm').submit();
        }
    });
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
