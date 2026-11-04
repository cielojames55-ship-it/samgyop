<?php
require_once 'includes/bootstrap.php';
require_login();
$tables = $pdo->query("SELECT DISTINCT table_number FROM customers ORDER BY table_number")->fetchAll(PDO::FETCH_COLUMN);
$pageTitle = 'Customer Refill Page';
require 'includes/header.php';
?>
<div class="page-heading"><div><span class="eyebrow">CUSTOMER PORTAL</span><h1>Table refill access</h1><p>Open the customer-facing refill page for a table.</p></div></div>
<section class="panel"><div class="panel-heading"><div><h2>Available table links</h2><p>These local URLs can be encoded into printed QR codes.</p></div></div>
<div class="table-wrap"><table><thead><tr><th>Table</th><th>Customer refill URL</th><th>Action</th></tr></thead><tbody>
<?php foreach ($tables as $table): $url = 'customer_refill.php?table='.(int)$table; ?><tr><td>Table <?= (int)$table ?></td><td><code><?= e($url) ?></code></td><td><a href="<?= e($url) ?>" target="_blank">Open page</a></td></tr><?php endforeach; ?>
<?php if (!$tables): ?><tr><td colspan="3" class="empty">Add a customer record with a table number to create its access link.</td></tr><?php endif; ?>
</tbody></table></div><p class="notice">This project runs on localhost. A phone cannot open your computer's localhost URL directly; use your computer's local network IP and allow local network access when testing on phones.</p></section>
<?php require 'includes/footer.php'; ?>
