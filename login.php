<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

if (isset($_SESSION['user_id'])) {
    header('Location: ' . APP_URL . '/' . (currentRole() === 'admin' ? 'admin/dashboard.php' : 'teacher/dashboard.php'));
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? null)) {
        $error = 'Session expired, please try again.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($username === '' || $password === '') {
            $error = 'Please enter both username and password.';
        } elseif (attemptLogin($username, $password)) {
            header('Location: ' . APP_URL . '/' . (currentRole() === 'admin' ? 'admin/dashboard.php' : 'teacher/dashboard.php'));
            exit;
        } else {
            $error = 'Invalid username or password.';
        }
    }
}

if (isset($_GET['timeout'])) $error = 'Your session expired. Please log in again.';
if (isset($_GET['denied']))  $error = 'You do not have permission to access that page.';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Login | <?= sanitize(COLLEGE_NAME) ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
<div class="login-wrapper">
  <div class="login-card">
    <div class="text-center">
      <div class="brand-badge"><i class="bi bi-mortarboard-fill"></i></div>
      <h5 class="fw-bold mb-0"><?= sanitize(COLLEGE_NAME) ?></h5>
      <p class="text-muted small">Attendance Management System</p>
    </div>

    <?php if ($error): ?>
      <div class="alert alert-danger py-2"><?= sanitize($error) ?></div>
    <?php endif; ?>

    <form method="POST" autocomplete="off">
      <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
      <div class="mb-3">
        <label class="form-label">Username</label>
        <input type="text" name="username" class="form-control" required autofocus>
      </div>
      <div class="mb-3">
        <label class="form-label">Password</label>
        <input type="password" name="password" class="form-control" required>
      </div>
      <button type="submit" class="btn btn-gold w-100 py-2">Login</button>
      <div class="text-center mt-3">
        <a href="forgot-password.php" class="small text-decoration-none">Forgot Password?</a>
      </div>
    </form>
  </div>
</div>
</body>
</html>
