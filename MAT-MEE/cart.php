<?php
require_once 'components/config-page.php';
$page_title = 'Shopping Cart';

$successMessage = '';
$errorMessage = '';

// Verify cart exists
if (empty($cartId)) {
    $errorMessage = 'Session error. Please refresh and try again.';
}

function resolveSizeId($conn, $sizeName) {
    if (trim($sizeName) === '') {
        return null;
    }
    $row = $conn->fetchOne("SELECT id FROM sizes WHERE size_name = ? LIMIT 1", [trim($sizeName)]);
    return $row['id'] ?? null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($errorMessage)) {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $productId = (int)($_POST['product_id'] ?? 0);
        $qty = max(1, min(99, (int)($_POST['qty'] ?? 1)));
        $sizeId = resolveSizeId($conn, $_POST['size'] ?? '');

        if ($productId > 0) {
            $existing = $conn->fetchOne(
                'SELECT * FROM cart_items WHERE cart_id = ? AND product_id = ? AND size_id ' . ($sizeId === null ? 'IS NULL' : '= ?') . ' LIMIT 1',
                $sizeId === null ? [$cartId, $productId] : [$cartId, $productId, $sizeId]
            );

            if ($existing) {
                $updateResult = $conn->execute('UPDATE cart_items SET quantity = quantity + ? WHERE id = ?', [$qty, $existing['id']]);
                if ($updateResult !== false) {
                    $successMessage = '✓ Item quantity updated in cart.';
                } else {
                    $errorMessage = 'Error updating cart: ' . $conn->getError();
                }
            } else {
                $insertResult = $conn->execute('INSERT INTO cart_items (cart_id, product_id, size_id, quantity) VALUES (?, ?, ?, ?)', [$cartId, $productId, $sizeId, $qty]);
                if ($insertResult !== false) {
                    $successMessage = '✓ Item added to cart successfully.';
                } else {
                    $errorMessage = 'Error adding item to cart: ' . $conn->getError();
                }
            }
        }
    }

    if ($action === 'update') {
        $itemId = (int)($_POST['item_id'] ?? 0);
        $qty = max(0, min(99, (int)($_POST['quantity'] ?? 1)));
        if ($itemId > 0) {
            if ($qty === 0) {
                $deleteResult = $conn->execute('DELETE FROM cart_items WHERE id = ? AND cart_id = ?', [$itemId, $cartId]);
                if ($deleteResult !== false) {
                    $successMessage = '✓ Item removed from cart.';
                } else {
                    $errorMessage = 'Error removing item: ' . $conn->getError();
                }
            } else {
                $updateResult = $conn->execute('UPDATE cart_items SET quantity = ? WHERE id = ? AND cart_id = ?', [$qty, $itemId, $cartId]);
                if ($updateResult !== false) {
                    $successMessage = '✓ Cart updated.';
                } else {
                    $errorMessage = 'Error updating cart: ' . $conn->getError();
                }
            }
        }
    }

    if ($action === 'remove') {
        $itemId = (int)($_POST['item_id'] ?? 0);
        if ($itemId > 0) {
            $deleteResult = $conn->execute('DELETE FROM cart_items WHERE id = ? AND cart_id = ?', [$itemId, $cartId]);
            if ($deleteResult !== false) {
                $successMessage = '✓ Item removed from cart.';
            } else {
                $errorMessage = 'Error removing item: ' . $conn->getError();
            }
        }
    }
}

$cartItems = $conn->fetch(
    'SELECT ci.id AS cart_item_id, ci.quantity, p.id AS product_id, p.name, p.slug, p.main_image, p.price, p.discount_price, ci.size_id, s.size_name FROM cart_items ci
     JOIN products p ON p.id = ci.product_id
     LEFT JOIN sizes s ON s.id = ci.size_id
     WHERE ci.cart_id = ?
     ORDER BY ci.id DESC',
    [$cartId]
);

$cartTotal = 0;
foreach ($cartItems as $item) {
    $price = (!empty($item['discount_price']) && (float)$item['discount_price'] < (float)$item['price']) ? (float)$item['discount_price'] : (float)$item['price'];
    $cartTotal += $price * $item['quantity'];
}
?>?>

<section class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <h1 class="mb-4">🛒 Shopping Cart</h1>

            <?php if ($successMessage): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($successMessage) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            <?php if ($errorMessage): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($errorMessage) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (empty($cartItems)): ?>
                <div class="card">
                    <div class="card-body text-center py-5">
                        <p class="fs-5 text-muted">Your cart is empty</p>
                        <a href="shop.php" class="btn btn-primary">Continue Shopping</a>
                    </div>
                </div>
            <?php else: ?>
                <div class="table-responsive mb-4">
                    <table class="table align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Product</th>
                                <th>Size</th>
                                <th class="text-end">Price</th>
                                <th class="text-center">Quantity</th>
                                <th class="text-end">Total</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($cartItems as $item):
                            $unitPrice = (!empty($item['discount_price']) && (float)$item['discount_price'] < (float)$item['price']) ? (float)$item['discount_price'] : (float)$item['price'];
                            $subtotal = $unitPrice * $item['quantity'];
                        ?>
                            <tr>
                                <td class="align-middle">
                                    <div class="d-flex align-items-center gap-3">
                                        <img src="upload/products/<?= htmlspecialchars($item['main_image'] ?: 'image/placeholder.jpg') ?>" alt="<?= htmlspecialchars($item['name']) ?>" width="60" height="60" class="rounded" style="object-fit: cover;">
                                        <div>
                                            <a href="product-view.php?id=<?= $item['product_id'] ?>" class="text-decoration-none fw-semibold text-dark"><?= htmlspecialchars($item['name']) ?></a>
                                        </div>
                                    </div>
                                </td>
                                <td class="align-middle text-center"><?= !empty($item['size_name']) ? htmlspecialchars($item['size_name']) : '—' ?></td>
                                <td class="align-middle text-end fw-semibold">৳<?= number_format($unitPrice, 0) ?></td>
                                <td class="align-middle text-center">
                                    <form method="POST" class="d-inline">
                                        <div class="input-group input-group-sm" style="width: 120px; margin: 0 auto;">
                                            <input type="hidden" name="action" value="update">
                                            <input type="hidden" name="item_id" value="<?= $item['cart_item_id'] ?>">
                                            <input type="number" name="quantity" value="<?= $item['quantity'] ?>" min="0" max="99" class="form-control form-control-sm text-center" onchange="this.form.submit()">
                                        </div>
                                    </form>
                                </td>
                                <td class="align-middle text-end fw-bold">৳<?= number_format($subtotal, 0) ?></td>
                                <td class="align-middle text-center">
                                    <form method="POST" class="d-inline" onsubmit="return confirm('Remove this item?');">
                                        <input type="hidden" name="action" value="remove">
                                        <input type="hidden" name="item_id" value="<?= $item['cart_item_id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Remove</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="card mb-4">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <a href="shop.php" class="btn btn-outline-secondary w-100">← Continue Shopping</a>
                            </div>
                            <div class="col-md-6">
                                <div class="d-grid">
                                    <div class="card" style="background-color: #f9f9f9;">
                                        <div class="card-body">
                                            <p class="mb-2 text-muted">Subtotal:</p>
                                            <p class="fs-4 fw-bold">৳<?= number_format($cartTotal, 0) ?></p>
                                            <small class="text-muted">Shipping will be added at checkout</small>
                                            <a href="checkout.php" class="btn btn-success btn-lg w-100 mt-3">Proceed to Checkout →</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php require_once 'components/page_close.php'; ?>

