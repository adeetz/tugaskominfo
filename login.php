<?php
// Load konfigurasi dan functions
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

// Cek kalau udah login, redirect ke dashboard
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$timeout = isset($_GET['timeout']) ? true : false;

// Ambil logo dari settings
$stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = 'login_logo'");
$stmt->execute();
$loginLogo = $stmt->fetchColumn() ?: '';

// Proses login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    // Validasi input
    if (empty($email) || empty($password)) {
        $error = 'Email dan kata sandi harus diisi!';
    } else {
        $result = login($pdo, $email, $password);
        
        if ($result['success']) {
            header('Location: dashboard.php');
            exit;
        } else {
            $error = $result['message'];
        }
    }
}

$pageTitle = 'Login';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle . ' - ' . APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/assets/css/style.css">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-md-6 col-lg-4">
                <div class="card shadow">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <?php if (!empty($loginLogo)): ?>
                            <img src="<?php echo APP_URL; ?>/assets/images/<?php echo sanitize($loginLogo); ?>" alt="Logo" style="max-width: 400px; max-height: 200px; margin-bottom: 1rem;">
                            <?php else: ?>
                            <i class="bi bi-people-fill text-primary" style="font-size: 3rem;"></i>
                            <?php endif; ?>
                            <h3 class="mt-3"><?php echo APP_NAME; ?></h3>
                            
                        </div>
                        
                        <?php if ($timeout): ?>
                        <div class="alert alert-warning" role="alert">
                            <i class="bi bi-exclamation-triangle"></i> Sesi Anda telah berakhir. Silakan masuk kembali.
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($error): ?>
                        <div class="alert alert-danger" role="alert">
                            <i class="bi bi-x-circle"></i> <?php echo sanitize($error); ?>
                        </div>
                        <?php endif; ?>
                        
                        <form method="POST" action="">
                            <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">Alamat Email</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                    <input type="email" class="form-control" id="email" name="email" required 
                                           value="<?php echo isset($_POST['email']) ? sanitize($_POST['email']) : ''; ?>"
                                           placeholder="Masukkan email Anda">
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="password" class="form-label">Kata Sandi</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                    <input type="password" class="form-control" id="password" name="password" required
                                           placeholder="Masukkan kata sandi Anda">
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-box-arrow-in-right"></i> Masuk
                            </button>
                        </form>
                        
                        <hr class="my-4">
                        
                        <div class="text-center text-muted small">
                            <p class="mb-0">Silahkan dicoba bang:</p>
                            <p class="mb-0"><strong>Email:</strong> admin@example.com</p>
                            <p class="mb-0"><strong>Kata Sandi:</strong> Admin@123</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
