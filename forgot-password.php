<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$message = '';
$isError = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrf($_POST['csrf_token'] ?? null)) {
    $email = trim($_POST['email'] ?? '');
    $db = getDB();
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ? AND status = 'active' LIMIT 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    // Always show the same message whether or not the email exists (avoid account enumeration).
    $message = 'If that email is registered, a password reset link has been generated. Please contact the Admin office to complete the reset, or check your registered email.';

    if ($user) {
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
        $upd = $db->prepare("UPDATE users SET reset_token = ?, reset_expires = ? WHERE id = ?");
        $upd->execute([$token, $expires, $user['id']]);
        // In production: email the link containing $token via PHPMailer.
        // Reset link would be: APP_URL . '/reset-password.php?token=' . $token
        logAudit($user['id'], 'PASSWORD_RESET_REQUEST', 'Reset token generated');
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Forgot Password | <?= sanitize(COLLEGE_NAME) ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
<div class="login-wrapper">
  <div class="login-card">
    <h5 class="fw-bold text-center mb-3">Reset Password</h5>
    <?php if ($message): ?>
      <div class="alert alert-info small"><?= sanitize($message) ?></div>
    <?php endif; ?>
    <form method="POST">
      <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
      <div class="mb-3">
        <label class="form-label">Registered Email</label>
        <input type="email" name="email" class="form-control" required>
      </div>
      <button type="submit" class="btn btn-gold w-100">Send Reset Link</button>
      <div class="text-center mt-3">
        <a href="login.php" class="small text-decoration-none">&larr; Back to Login</a>
      </div>
    </form>
  </div>
</div>
</body>
</html>
