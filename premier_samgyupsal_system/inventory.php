<?php
require_once 'includes/bootstrap.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'save') {
        $id = (int)($_POST['item_id'] ?? 0);
        $name = trim($_POST['item_name'] ?? '');
        $category = trim($_POST['category'] ?? '');
        $quantity = max(0, (int)($_POST['quantity'] ?? 0));
        $unit = trim($_POST['unit'] ?? 'servings');
        $status = $quantity > 0 ? 'Available' : 'Out of Stock';
        if ($name === '' || $category === '' || $unit === '') {
            set_flash('Complete all required inventory fields.', 'error');
        } elseif ($id > 0) {
            $stmt = $pdo->prepare("UPDATE inventory SET item_name=?, category=?, quantity=?, unit=?, status=? WHERE item_id=?");
            $stmt->execute([$name, $category, $quantity, $unit, $status, $id]);
            set_flash('Inventory item updated.');
        } else {
            $stmt = $pdo->prepare("INSERT INTO inventory (item_name, category, quantity, unit, status) VALUES (?,?,?,?,?)");
            $stmt->execute([$name, $category, $quantity, $unit, $status]);
            set_flash('Inventory item added.');
        }
        header('Location: inventory.php'); exit;
    }
    if ($action === 'delete') {
        $id = (int)($_POST['item_id'] ?? 0);
        try {
            $stmt = $pdo->prepare("DELETE FROM inventory WHERE item_id=?");
            $stmt->execute([$id]);
            set_flash('Inventory item deleted.');
        } catch (PDOException $e) {
            set_flash('Cannot delete this item because refill history references it.', 'error');
        }
        header('Location: inventory.php'); exit;
    }
}
$edit = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM inventory WHERE item_id=?");
    $stmt->execute([(int)$_GET['edit']]);
    $edit = $stmt->fetch();
}
$items = $pdo->query("SELECT * FROM inventory ORDER BY category, item_name")->fetchAll();
$pageTitle = 'Inventory';
require 'includes/header.php';
?>
<div class="page-heading"><div><span class="eyebrow">STOCK CONTROL</span><h1>Inventory</h1><p>Add, update, and monitor restaurant supplies.</p></div></div>
<section class="panel form-panel"><div class="panel-heading"><div><h2><?= $edit ? 'Edit inventory item' : 'Add inventory item' ?></h2><p>Current quantity is stored in the inventory table.</p></div></div>
<form method="post" class="form-grid">
<input type="hidden" name="action" value="save"><input type="hidden" name="item_id" value="<?= (int)($edit['item_id'] ?? 0) ?>">
<label>Item name<input name="item_name" required value="<?= e($edit['item_name'] ?? '') ?>" placeholder="Pork Belly"></label>
<label>Category<input name="category" required value="<?= e($edit['category'] ?? '') ?>" placeholder="Meat"></label>
<label>Quantity<input type="number" min="0" name="quantity" required value="<?= e((string)($edit['quantity'] ?? 0)) ?>"></label>
<label>Unit<input name="unit" required value="<?= e($edit['unit'] ?? 'servings') ?>" placeholder="servings"></label>
<div class="form-actions"><button class="button button-primary" type="submit"><?= $edit ? 'Save changes' : 'Add item' ?></button><?php if ($edit): ?><a class="button button-light" href="inventory.php">Cancel</a><?php endif; ?></div>
</form></section>
<section class="panel"><div class="panel-heading"><div><h2>Inventory list</h2><p><?= count($items) ?> registered items</p></div></div>
<div class="table-wrap"><table><thead><tr><th>Item</th><th>Category</th><th>Quantity</th><th>Unit</th><th>Status</th><th>Actions</th></tr></thead><tbody>
<?php foreach ($items as $item): ?><tr><td><strong><?= e($item['item_name']) ?></strong></td><td><?= e($item['category']) ?></td><td><?= (int)$item['quantity'] ?></td><td><?= e($item['unit']) ?></td><td><span class="status <?= (int)$item['quantity'] <= 10 ? 'low' : 'available' ?>"><?= (int)$item['quantity'] <= 10 ? 'Low stock' : e($item['status']) ?></span></td><td class="actions"><a href="inventory.php?edit=<?= (int)$item['item_id'] ?>">Edit</a><form method="post" onsubmit="return confirm('Delete this inventory item?')"><input type="hidden" name="action" value="delete"><input type="hidden" name="item_id" value="<?= (int)$item['item_id'] ?>"><button class="link-danger" type="submit">Delete</button></form></td></tr><?php endforeach; ?>
<?php if (!$items): ?><tr><td colspan="6" class="empty">No inventory items found.</td></tr><?php endif; ?>
</tbody></table></div></section>
<?php require 'includes/footer.php'; ?>
