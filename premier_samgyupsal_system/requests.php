<?php
require_once 'includes/bootstrap.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['request_id'] ?? 0);
    $newStatus = $_POST['request_status'] ?? '';
    $allowed = ['Pending', 'Approved', 'Completed', 'Rejected'];
    if (!in_array($newStatus, $allowed, true)) {
        set_flash('Invalid request status.', 'error');
        header('Location: requests.php'); exit;
    }
    try {
        $pdo->beginTransaction();
        $stmt = $pdo->prepare("
            SELECT rr.request_id, rr.item_id, rr.quantity, rr.request_status,
                   i.quantity AS stock_quantity
            FROM refill_requests rr
            JOIN inventory i ON i.item_id = rr.item_id
            WHERE rr.request_id = ? FOR UPDATE
        ");
        $stmt->execute([$id]);
        $request = $stmt->fetch();
        if (!$request) {
            throw new RuntimeException('Refill request not found.');
        }
        if ($request['request_status'] !== 'Completed' && $newStatus === 'Completed') {
            if ((int)$request['stock_quantity'] < (int)$request['quantity']) {
                throw new RuntimeException('Not enough inventory to complete this request.');
            }
            $deduct = $pdo->prepare("UPDATE inventory SET quantity = quantity - ?, status = CASE WHEN quantity - ? <= 0 THEN 'Out of Stock' ELSE 'Available' END WHERE item_id = ?");
            $deduct->execute([(int)$request['quantity'], (int)$request['quantity'], (int)$request['item_id']]);
        }
        if ($request['request_status'] === 'Completed' && $newStatus !== 'Completed') {
            throw new RuntimeException('Completed requests cannot be reopened because stock was already deducted.');
        }
        $update = $pdo->prepare("UPDATE refill_requests SET request_status=? WHERE request_id=?");
        $update->execute([$newStatus, $id]);
        $pdo->commit();
        set_flash('Refill request status updated.');
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        set_flash($e->getMessage(), 'error');
    }
    header('Location: requests.php'); exit;
}
$requests = $pdo->query("
    SELECT rr.*, c.customer_name, c.table_number, i.item_name, i.unit
    FROM refill_requests rr
    JOIN customers c ON c.customer_id = rr.customer_id
    JOIN inventory i ON i.item_id = rr.item_id
    ORDER BY FIELD(rr.request_status,'Pending','Approved','Completed','Rejected'), rr.request_date DESC
")->fetchAll();
$pageTitle = 'Refill Requests';
require 'includes/header.php';
?>
<div class="page-heading"><div><span class="eyebrow">CUSTOMER SERVICE</span><h1>Refill requests</h1><p>Review requests and update their processing status.</p></div></div>
<section class="panel"><div class="panel-heading"><div><h2>All requests</h2><p>Stock is deducted once when a request is marked Completed.</p></div></div>
<div class="table-wrap"><table><thead><tr><th>Request</th><th>Customer / Table</th><th>Requested item</th><th>Quantity</th><th>Date</th><th>Status</th><th>Update</th></tr></thead><tbody>
<?php foreach ($requests as $r): ?><tr><td>#<?= (int)$r['request_id'] ?></td><td><?= e($r['customer_name']) ?><small class="cell-sub">Table <?= (int)$r['table_number'] ?></small></td><td><?= e($r['item_name']) ?></td><td><?= (int)$r['quantity'] ?> <?= e($r['unit']) ?></td><td><?= e($r['request_date']) ?></td><td><span class="status <?= strtolower(e($r['request_status'])) ?>"><?= e($r['request_status']) ?></span></td><td><?php if ($r['request_status'] !== 'Completed' && $r['request_status'] !== 'Rejected'): ?><form method="post" class="inline-form"><input type="hidden" name="request_id" value="<?= (int)$r['request_id'] ?>"><select name="request_status"><option <?= $r['request_status']==='Pending'?'selected':'' ?>>Pending</option><option <?= $r['request_status']==='Approved'?'selected':'' ?>>Approved</option><option>Completed</option><option>Rejected</option></select><button class="button button-small button-dark" type="submit">Update</button></form><?php else: ?><span class="muted">Closed</span><?php endif; ?></td></tr><?php endforeach; ?>
<?php if (!$requests): ?><tr><td colspan="7" class="empty">No refill requests yet.</td></tr><?php endif; ?>
</tbody></table></div></section>
<?php require 'includes/footer.php'; ?>
