<?php
require_once 'includes/bootstrap.php';
if (!empty($_SESSION['staff_id'])) { header('Location: index.php'); exit; }
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $stmt = $pdo->prepare("SELECT staff_id, staff_name, username, password_hash, role FROM staff WHERE username = ? AND is_active = 1 LIMIT 1");
    $stmt->execute([$username]);
    $staff = $stmt->fetch();
    if ($staff && password_verify($password, $staff['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['staff_id'] = (int)$staff['staff_id'];
        $_SESSION['staff_name'] = $staff['staff_name'];
        $_SESSION['staff_role'] = $staff['role'];
        header('Location: index.php');
        exit;
    }
    $error = 'Invalid username or password.';
}
$pageTitle = 'Staff Login';
require 'includes/header.php';
?>
<div class="login-wrap"><section class="login-card">
    <span class="eyebrow">STAFF PORTAL</span><h1>Welcome back.</h1><p>Sign in to manage restaurant operations.</p>
    <?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>
    <form method="post">
        <label>Username<input name="username" required autocomplete="username"></label>
        <label>Password<input type="password" name="password" required autocomplete="current-password"></label>
        <button class="button button-primary full" type="submit">Sign in</button>
    </form>
    <small class="muted">Initial account: admin / password</small>
</section></div>
<?php require 'includes/footer.php'; ?>
