<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup - User Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-md-8 col-lg-6">
                <div class="card shadow">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <i class="bi bi-gear-fill text-primary" style="font-size: 3rem;"></i>
                            <h3 class="mt-3">Initial Setup</h3>
                            <p class="text-muted">User Management System</p>
                        </div>

<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'tugaskominfo');
define('DB_USER', 'root');
define('DB_PASS', '');

$setupComplete = false;
$errors = [];
$messages = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['run_setup'])) {
    try {
        // Connect to database
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]
        );
        $messages[] = "✓ Database connection successful";

        // Check if admin user already exists
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = 'admin@example.com'");
        $stmt->execute();
        $userExists = $stmt->fetchColumn() > 0;

        // Default admin credentials
        $adminEmail = 'admin@example.com';
        $adminPassword = 'Admin@123';
        $passwordHash = password_hash($adminPassword, PASSWORD_BCRYPT);

        if ($userExists) {
            // Update existing admin user
            $stmt = $pdo->prepare("UPDATE users SET password_hash = ?, role = 'superadmin', is_active = 1, updated_at = NOW() WHERE email = ?");
            $stmt->execute([$passwordHash, $adminEmail]);
            $messages[] = "✓ Admin user password updated";
        } else {
            // Create new admin user
            $stmt = $pdo->prepare("INSERT INTO users (email, password_hash, role, is_active, created_at, updated_at) VALUES (?, ?, 'superadmin', 1, NOW(), NOW())");
            $stmt->execute([$adminEmail, $passwordHash]);
            $messages[] = "✓ Admin user created successfully";
        }

        // Verify password works
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$adminEmail]);
        $user = $stmt->fetch();

        if ($user && password_verify($adminPassword, $user['password_hash'])) {
            $messages[] = "✓ Password verification successful";
            $setupComplete = true;
        } else {
            $errors[] = "Password verification failed";
        }

    } catch (PDOException $e) {
        $errors[] = "Database error: " . $e->getMessage();
    }
}

// Auto-check if setup is needed
$needsSetup = true;
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = 'admin@example.com' AND is_active = 1");
    $stmt->execute();
    $user = $stmt->fetch();
    
    if ($user && password_verify('Admin@123', $user['password_hash'])) {
        $needsSetup = false;
    }
} catch (PDOException $e) {
    $errors[] = "Cannot connect to database. Please check config/database.php";
}

if (!empty($errors)) {
    echo '<div class="alert alert-danger">';
    echo '<h5><i class="bi bi-exclamation-triangle"></i> Errors:</h5><ul class="mb-0">';
    foreach ($errors as $error) {
        echo '<li>' . htmlspecialchars($error) . '</li>';
    }
    echo '</ul></div>';
}

if (!empty($messages)) {
    echo '<div class="alert alert-success">';
    echo '<h5><i class="bi bi-check-circle"></i> Setup Progress:</h5><ul class="mb-0">';
    foreach ($messages as $message) {
        echo '<li>' . htmlspecialchars($message) . '</li>';
    }
    echo '</ul></div>';
}

if ($setupComplete) {
    echo '<div class="alert alert-success">';
    echo '<h4 class="alert-heading"><i class="bi bi-check-circle-fill"></i> Setup Complete!</h4>';
    echo '<hr>';
    echo '<p class="mb-0">You can now login with:</p>';
    echo '<ul>';
    echo '<li><strong>Email:</strong> admin@example.com</li>';
    echo '<li><strong>Password:</strong> Admin@123</li>';
    echo '</ul>';
    echo '<a href="login.php" class="btn btn-primary w-100 mt-3">';
    echo '<i class="bi bi-box-arrow-in-right"></i> Go to Login Page';
    echo '</a>';
    echo '</div>';
    echo '<div class="alert alert-warning mt-3">';
    echo '<i class="bi bi-shield-exclamation"></i> <strong>Security Notice:</strong> ';
    echo 'Please change the default password after first login!';
    echo '</div>';
} elseif ($needsSetup) {
    echo '<div class="alert alert-info">';
    echo '<h5><i class="bi bi-info-circle"></i> Setup Required</h5>';
    echo '<p>Click the button below to create/reset the admin user with correct password hash for this server.</p>';
    echo '</div>';
    
    echo '<form method="POST">';
    echo '<input type="hidden" name="run_setup" value="1">';
    echo '<button type="submit" class="btn btn-primary w-100">';
    echo '<i class="bi bi-play-circle"></i> Run Setup Now';
    echo '</button>';
    echo '</form>';
    
    echo '<div class="card mt-4 bg-light">';
    echo '<div class="card-body">';
    echo '<h6><i class="bi bi-info-circle"></i> What does this do?</h6>';
    echo '<ul class="small mb-0">';
    echo '<li>Checks database connection</li>';
    echo '<li>Creates admin user if not exists</li>';
    echo '<li>Generates password hash compatible with this PHP version</li>';
    echo '<li>Verifies login credentials work</li>';
    echo '</ul>';
    echo '</div></div>';
} else {
    echo '<div class="alert alert-success">';
    echo '<h5><i class="bi bi-check-circle-fill"></i> Setup Already Complete</h5>';
    echo '<p>Admin user is ready. You can login now.</p>';
    echo '<a href="login.php" class="btn btn-primary w-100">';
    echo '<i class="bi bi-box-arrow-in-right"></i> Go to Login Page';
    echo '</a>';
    echo '</div>';
    
    echo '<hr>';
    echo '<p class="text-center text-muted small">Need to reset password?</p>';
    echo '<form method="POST">';
    echo '<input type="hidden" name="run_setup" value="1">';
    echo '<button type="submit" class="btn btn-outline-secondary w-100">';
    echo '<i class="bi bi-arrow-clockwise"></i> Reset Admin Password';
    echo '</button>';
    echo '</form>';
}
?>

                        <hr class="my-4">
                        <div class="text-center">
                            <p class="text-muted small mb-0">
                                <i class="bi bi-server"></i> PHP Version: <?php echo PHP_VERSION; ?>
                            </p>
                            <p class="text-muted small mb-0">
                                <i class="bi bi-database"></i> Database: <?php echo DB_NAME; ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
