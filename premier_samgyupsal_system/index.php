<?php
require_once 'includes/bootstrap.php';
require_login();

$pageTitle = 'Dashboard';
$counts = [
    'items' => (int)$pdo->query("SELECT COUNT(*) FROM inventory")->fetchColumn(),
    'requests' => (int)$pdo->query("SELECT COUNT(*) FROM refill_requests WHERE request_status IN ('Pending','Approved')")->fetchColumn(),
    'low' => (int)$pdo->query("SELECT COUNT(*) FROM inventory WHERE quantity <= 10")->fetchColumn(),
    'customers' => (int)$pdo->query("SELECT COUNT(*) FROM customers")->fetchColumn(),
];
$latest = $pdo->query("
    SELECT rr.request_id, c.customer_name, c.table_number, i.item_name,
           rr.quantity, rr.request_status, rr.request_date
    FROM refill_requests rr
    JOIN customers c ON c.customer_id = rr.customer_id
    JOIN inventory i ON i.item_id = rr.item_id
    ORDER BY rr.request_date DESC LIMIT 8
")->fetchAll();
require 'includes/header.php';
?>
<div class="page-heading"><div><span class="eyebrow">OVERVIEW</span><h1>Dashboard</h1><p>Restaurant operations at a glance.</p></div></div>
<section class="stats-grid">
    <article class="stat-card"><span>Inventory items</span><strong><?= $counts['items'] ?></strong><small>Registered food supplies</small></article>
    <article class="stat-card"><span>Open refill requests</span><strong><?= $counts['requests'] ?></strong><small>Pending or approved</small></article>
    <article class="stat-card warning"><span>Low-stock items</span><strong><?= $counts['low'] ?></strong><small>Quantity at or below 10</small></article>
    <article class="stat-card"><span>Customer records</span><strong><?= $counts['customers'] ?></strong><small>Recorded table customers</small></article>
</section>
<section class="panel">
    <div class="panel-heading"><div><h2>Recent refill requests</h2><p>Latest customer food requests</p></div><a class="button button-dark" href="requests.php">Manage requests</a></div>
    <div class="table-wrap"><table><thead><tr><th>Request</th><th>Customer</th><th>Table</th><th>Item</th><th>Qty</th><th>Status</th><th>Date</th></tr></thead><tbody>
    <?php foreach ($latest as $r): ?>
    <tr><td>#<?= (int)$r['request_id'] ?></td><td><?= e($r['customer_name']) ?></td><td><?= (int)$r['table_number'] ?></td><td><?= e($r['item_name']) ?></td><td><?= (int)$r['quantity'] ?></td><td><span class="status <?= strtolower(e($r['request_status'])) ?>"><?= e($r['request_status']) ?></span></td><td><?= e($r['request_date']) ?></td></tr>
    <?php endforeach; ?>
    <?php if (!$latest): ?><tr><td colspan="7" class="empty">No refill requests yet.</td></tr><?php endif; ?>
    </tbody></table></div>
</section>
<?php require 'includes/footer.php'; ?>
