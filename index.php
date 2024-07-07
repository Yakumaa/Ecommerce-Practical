<?php
session_start();
require_once 'db_connection.php';

// Initialize cart
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = array();
}

// Products
$products = array(
    1 => array('name' => 'Product 1', 'price' => 10.99),
    2 => array('name' => 'Product 2', 'price' => 19.99)
);

// Add to cart
if (isset($_POST['add_to_cart'])) {
    $product_id = $_POST['product_id'];
    if (isset($products[$product_id])) {
        $_SESSION['cart'][$product_id] = $products[$product_id];
    }
}

// Place order
if (isset($_POST['place_order']) && isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    
    // Insert order into database
    $order_total = 0;
    foreach ($_SESSION['cart'] as $product) {
        $order_total += $product['price'];
    }

    $sql = "INSERT INTO orders (user_id, total) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("id", $user_id, $order_total);
    $stmt->execute();
    $order_id = $stmt->insert_id;

    // Insert order items into database
    $sql = "INSERT INTO order_items (order_id, product_name, price) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    foreach ($_SESSION['cart'] as $product) {
        $stmt->bind_param("isd", $order_id, $product['name'], $product['price']);
        $stmt->execute();
    }

    // Clear cart
    $_SESSION['cart'] = array();
    echo "<p class='success'>Order placed successfully!</p>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Simple Shop</title>
    <link rel="stylesheet" type="text/css" href="styles.css">
</head>
<body>
    <header>
        <h1>Simple Shop</h1>
        <nav>
            <?php if (isset($_SESSION['user_id'])): ?>
                <span>Welcome, <?php echo $_SESSION['user_name']; ?></span>
                <a href="logout.php">Logout</a>
            <?php else: ?>
                <a href="login.php">Login</a>
                <a href="register.php">Register</a>
            <?php endif; ?>
        </nav>
    </header>

    <main>
        <section id="products">
            <h2>Products</h2>
            <?php foreach ($products as $id => $product): ?>
                <form method="post" class="product">
                    <h3><?php echo $product['name']; ?> - $<?php echo $product['price']; ?></h3>
                    <input type="hidden" name="product_id" value="<?php echo $id; ?>">
                    <input type="submit" name="add_to_cart" value="Add to Cart">
                </form>
            <?php endforeach; ?>
        </section>

        <section id="cart">
            <h2>Cart</h2>
            <?php if (empty($_SESSION['cart'])): ?>
                <p>Your cart is empty.</p>
            <?php else: ?>
                <?php foreach ($_SESSION['cart'] as $product): ?>
                    <p><?php echo $product['name']; ?> - $<?php echo $product['price']; ?></p>
                <?php endforeach; ?>

                <?php if (isset($_SESSION['user_id'])): ?>
                    <form method="post">
                        <input type="submit" name="place_order" value="Place Order">
                    </form>
                <?php else: ?>
                    <p>Please <a href="login.php">login</a> to place an order.</p>
                <?php endif; ?>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>