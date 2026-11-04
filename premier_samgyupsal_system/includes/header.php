<?php
$pageTitle = $pageTitle ?? 'Premier The Samgyupsal';
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($pageTitle) ?> | Premier The Samgyupsal</title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<header class="topbar">
    <div class="brand"><span class="brand-mark">P</span><div><strong>PREMIER</strong><small>THE SAMGYUPSAL</small></div></div>
    <?php if (!empty($_SESSION['staff_id'])): ?>
    <div class="top-user"><?= e($_SESSION['staff_name'] ?? 'Staff') ?> <a href="logout.php">Log out</a></div>
    <?php endif; ?>
</header>
<div class="app-shell">
<?php if (!empty($_SESSION['staff_id'])): ?>
<aside class="sidebar">
    <div class="nav-label">WORKSPACE</div>
    <a href="index.php">Dashboard</a>
    <a href="inventory.php">Inventory</a>
    <a href="requests.php">Refill requests</a>
    <a href="customer.php">Customer refill page</a>
    <a href="tables.php">Table QR links</a>
</aside>
<main class="main-content">
<?php else: ?>
<main class="main-content public-content">
<?php endif; ?>
<?php show_flash(); ?>
