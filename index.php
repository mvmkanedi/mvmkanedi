<?php
require_once __DIR__ . '/includes/auth.php';
if (isset($_SESSION['user_id'])) {
    header('Location: ' . APP_URL . '/' . (currentRole() === 'admin' ? 'admin/dashboard.php' : 'teacher/dashboard.php'));
} else {
    header('Location: ' . APP_URL . '/login.php');
}
exit;
