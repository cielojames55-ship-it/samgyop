<?php
require_once 'includes/bootstrap.php';
require_login();
$tables = $pdo->query("SELECT table_number, customer_name FROM customers ORDER BY table_number")->fetchAll();
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$folder = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
$pageTitle = 'Table QR Links';
require 'includes/header.php';
?>
<div class="page-heading"><div><span class="eyebrow">TABLE ACCESS</span><h1>Table QR links</h1><p>Each link opens the refill page assigned to a table.</p></div></div>
<section class="panel"><div class="panel-heading"><div><h2>Table access URLs</h2><p>Copy a URL into a QR-code generator or print it as a table link.</p></div></div>
<div class="table-wrap"><table><thead><tr><th>Table</th><th>Customer record</th><th>Local URL</th><th>Open</th></tr></thead><tbody>
<?php foreach ($tables as $t): $url = 'http://' . $host . $folder . '/customer_refill.php?table=' . (int)$t['table_number']; ?><tr><td>Table <?= (int)$t['table_number'] ?></td><td><?= e($t['customer_name']) ?></td><td><code><?= e($url) ?></code></td><td><a href="customer_refill.php?table=<?= (int)$t['table_number'] ?>" target="_blank">Open</a></td></tr><?php endforeach; ?>
<?php if (!$tables): ?><tr><td colspan="4" class="empty">No table records found.</td></tr><?php endif; ?>
</tbody></table></div><p class="notice">The generated links are local-network links only. To scan from a phone, replace localhost with the computer's LAN IP address and ensure both devices are on the same network.</p></section>
<?php require 'includes/footer.php'; ?>
