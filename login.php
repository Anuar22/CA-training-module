<?php
// login.php
require_once 'app/config/config.php';
require_once 'app/config/database.php';

$errors = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // TEMP BYPASS: Checking strings directly to test if session routing works
    if ($username === 'admin' && $password === 'password123') {
        $_SESSION['user_id'] = 999;
        $_SESSION['username'] = 'admin';
        $_SESSION['full_name'] = 'St. Jude Corp Apps Team';

        header("Location: index.php");
        exit;
    } else {
        $errors = "Invalid username or account password credentials.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - St. Jude Training Portal</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body { background: #f4f6f9; height: 100vh; display: flex; align-items: center; justify-content: center; }
        .login-card { width: 100%; max-width: 400px; padding: 20px; border-radius: 8px; }
    </style>
</head>
<body>

<div class="card login-card shadow-sm border">
    <div class="card-body">
        <div class="text-center mb-4">
            <h4 class="fw-bold text-dark m-0">The School of St. Jude</h4>
            <small class="text-muted text-uppercase tracking-wider">Corporate Applications Training</small>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger py-2 small"><?php echo $errors; ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="mb-3">
                <label class="form-label small font-weight-bold">Username</label>
                <input type="text" name="username" class="form-control form-control-sm" required autofocus>
            </div>
            <div class="mb-3">
                <label class="form-label small font-weight-bold">Password</label>
                <input type="password" name="password" class="form-control form-control-sm" required>
            </div>
            <button type="submit" class="btn btn-primary btn-sm w-100 mt-2">Sign In to Dashboard</button>
        </form>
    </div>
</div>

</body>
</html>