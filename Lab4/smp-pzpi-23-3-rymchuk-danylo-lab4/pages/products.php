<?php
?>

<h2>Наші товари</h2>
<div class="product-list">
    <?php foreach ($products as $id => $product):?>
        <div class="product-item">
            <h3><?php echo htmlspecialchars($product['name']); ?></h3>
            <p>Ціна: <?php echo htmlspecialchars($product['price']); ?> грн</p>
            <form action="index.php" method="POST"> <input type="hidden" name="product_id" value="<?php echo $id; ?>">
                <label for="qty_<?php echo $id; ?>">Кількість:</label>
                <input type="number" id="qty_<?php echo $id; ?>" name="quantity" value="1" min="1" max="99">
                <button type="submit" name="add_to_cart">Купити</button>
            </form>
        </div>
    <?php endforeach; ?>
</div>