<?php
require_once 'includes/bootstrap.php';
$table = max(1, (int)($_GET['table'] ?? $_POST['table_number'] ?? 0));
$error = '';
$success = '';
$products = $pdo->query("SELECT item_id, item_name, category, quantity, unit FROM inventory WHERE quantity > 0 ORDER BY category, item_name")->fetchAll();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['customer_name'] ?? '');
    $contact = trim($_POST['contact_number'] ?? '');
    $itemId = (int)($_POST['item_id'] ?? 0);
    $qty = (int)($_POST['quantity'] ?? 0);
    try {
        if ($table < 1 || $name === '' || $itemId < 1 || $qty < 1) {
            throw new RuntimeException('Complete the required fields.');
        }
        $pdo->beginTransaction();
        $itemStmt = $pdo->prepare("SELECT quantity FROM inventory WHERE item_id=? FOR UPDATE");
        $itemStmt->execute([$itemId]);
        $stock = $itemStmt->fetchColumn();
        if ($stock === false || (int)$stock < $qty) {
            throw new RuntimeException('The requested quantity is not currently available.');
        }
        $customerStmt = $pdo->prepare("SELECT customer_id FROM customers WHERE table_number=? LIMIT 1");
        $customerStmt->execute([$table]);
        $customerId = $customerStmt->fetchColumn();
        if ($customerId) {
            $updateCustomer = $pdo->prepare("UPDATE customers SET customer_name=?, contact_number=? WHERE customer_id=?");
            $updateCustomer->execute([$name, $contact ?: null, $customerId]);
        } else {
            $insertCustomer = $pdo->prepare("INSERT INTO customers (customer_name, table_number, contact_number) VALUES (?,?,?)");
            $insertCustomer->execute([$name, $table, $contact ?: null]);
            $customerId = $pdo->lastInsertId();
        }
        $insertRequest = $pdo->prepare("INSERT INTO refill_requests (customer_id, item_id, quantity, request_status) VALUES (?,?,?,'Pending')");
        $insertRequest->execute([$customerId, $itemId, $qty]);
        $pdo->commit();
        $success = 'Your refill request has been submitted. The staff will process it shortly.';
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        $error = $e->getMessage();
    }
}
$pageTitle = 'Request a Refill';
require 'includes/header.php';
?>
<div class="customer-page">
    <div class="customer-brand"><span class="brand-mark">P</span><span>PREMIER THE SAMGYUPSAL</span></div>
    <section class="customer-card">
        <span class="eyebrow">TABLE <?= $table ?></span><h1>Request a refill</h1><p>Choose an available item and submit your request.</p>
        <?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>
        <?php if ($success): ?><div class="alert success"><?= e($success) ?></div><?php endif; ?>
        <form method="post" class="customer-form">
            <input type="hidden" name="table_number" value="<?= $table ?>">
            <label>Your name<input name="customer_name" required placeholder="Enter your name" value="<?= e($_POST['customer_name'] ?? '') ?>"></label>
            <label>Contact number <span class="muted">(optional)</span><input name="contact_number" placeholder="09XXXXXXXXX" value="<?= e($_POST['contact_number'] ?? '') ?>"></label>
            <label>Food item<select name="item_id" required><option value="">Select an item</option><?php foreach ($products as $p): ?><option value="<?= (int)$p['item_id'] ?>" <?= (($_POST['item_id'] ?? '') == $p['item_id'])?'selected':'' ?>><?= e($p['item_name']) ?> — <?= (int)$p['quantity'] ?> <?= e($p['unit']) ?> available</option><?php endforeach; ?></select></label>
            <label>Quantity<input type="number" name="quantity" min="1" max="20" required value="<?= e($_POST['quantity'] ?? '1') ?>"></label>
            <button class="button button-primary full" type="submit">Submit refill request</button>
        </form>
    </section>
    <p class="customer-foot">Premier The Samgyupsal · Smart Table System</p>
</div>
<?php require 'includes/footer.php'; ?>
