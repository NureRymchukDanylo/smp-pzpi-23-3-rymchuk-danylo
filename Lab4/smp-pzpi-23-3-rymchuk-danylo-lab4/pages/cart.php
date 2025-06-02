<?php
$total_bill = 0;
?>

<h2>Ваш кошик</h2>
<?php if (empty($_SESSION['cart'])): ?>
    <p>Ваш кошик порожній.</p>
    <p><a href="index.php?page=products">Перейти до покупок</a></p>
<?php else: ?>
    <table class="cart-table">
        <thead>
            <tr>
                <th>№</th>
                <th>Назва</th>
                <th>Ціна</th>
                <th>Кількість</th>
                <th>Вартість</th>
                <th>Дія</th> </tr>
        </thead>
        <tbody>
            <?php
            $item_num = 1;
            foreach ($_SESSION['cart'] as $productId => $quantity):
                if (isset($products[$productId])):
                    $product_name = $products[$productId]['name'];
                    $product_price = $products[$productId]['price'];
                    $item_cost = $product_price * $quantity;
                    $total_bill += $item_cost;
            ?>
                <tr>
                    <td><?php echo $item_num++; ?></td>
                    <td><?php echo htmlspecialchars($product_name); ?></td>
                    <td><?php echo htmlspecialchars($product_price); ?> грн</td>
                    <td><?php echo htmlspecialchars($quantity); ?></td>
                    <td><?php echo htmlspecialchars($item_cost); ?> грн</td>
                    <td>
                        <form action="index.php" method="POST" class="remove-item-form">
                            <input type="hidden" name="remove_item_id" value="<?php echo $productId; ?>">
                            <button type="submit" name="remove_item" class="remove-btn">Видалити</button>
                        </form>
                    </td>
                </tr>
            <?php
                endif;
            endforeach;
            ?>
        </tbody>
    </table>
    <div class="cart-summary">
        <div class="cart-total">
            РАЗОМ ДО СПЛАТИ: <?php echo htmlspecialchars($total_bill); ?> грн
        </div>
        <form action="index.php" method="POST" class="clear-cart-form">
            <button type="submit" name="clear_cart" class="clear-cart-btn">Очистити кошик</button>
        </form>
    </div>
<?php endif; ?>