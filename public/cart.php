<?php
// Load the configuration file
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is logged in
$isLoggedIn = check_login();
if (!$isLoggedIn) {
    setFlashData('msg', 'Please login to view your cart.');
    setFlashData('msg_type', 'danger');
    header('Location: ' . BASE_URL . '/public/login.php');
    exit;
}

// Cart session
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Calculate cart total
$cartTotal = 0;
$cartItems = [];

// Get product details from the database
foreach ($_SESSION['cart'] as $productId => $item) {
    $product = getProduct($item['product_id']);
    if ($product) {
        $price = $product['sale_price'] ?? $product['price'];
        $subtotal = $price * $item['quantity'];
        $cartTotal += $subtotal;

        $cartItems[] = [
            'product_id' => $item['product_id'],
            'product_name' => $item['product_name'],
            'price' => $price,
            'quantity' => $item['quantity'],
            'primary_image' => $item['primary_image'],
            'category_name' => $item['category_name'],
            'subtotal' => $subtotal
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Shopping Cart - Electro</title>

    <!-- Google font -->
    <link href="https://fonts.googleapis.com/css?family=Montserrat:400,500,700" rel="stylesheet" />

    <!-- Bootstrap -->
    <link type="text/css" rel="stylesheet" href="<?php echo CSS_DIR ?>bootstrap.min.css" />

    <!-- Font Awesome Icon -->
    <link rel="stylesheet" href="<?php echo CSS_DIR ?>font-awesome.min.css" />

    <!-- Custom stylesheet -->
    <link type="text/css" rel="stylesheet" href="<?php echo CSS_DIR ?>styles.css" />

    <style>
        .loading {
            pointer-events: none;
            opacity: 0.6;
        }

        .cart-item {
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #E4E7ED;
        }

        .product-actions {
            display: flex;
            align-items: center;
        }

        .qty-label {
            margin-right: 15px;
        }

        .input-number {
            position: relative;
            width: 100px;
        }

        .delete {
            background: none;
            border: none;
            color: #D10024;
            font-size: 18px;
            cursor: pointer;
        }

        .empty-cart {
            text-align: center;
            padding: 50px 0;
        }

        .disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        /* Thêm style cho cart-notification */
        .cart-notification {
            position: sticky;
            top: 0;
            width: 100%;
            z-index: 999;
            padding: 10px 0;
            background-color: rgba(255, 255, 255, 0.95);
            border-bottom: 1px solid #e4e7ed;
            animation: slideDown 0.3s ease-out;
        }

        @keyframes slideDown {
            from {
                transform: translateY(-100%);
            }

            to {
                transform: translateY(0);
            }
        }

        .cart-notification .alert {
            margin-bottom: 0;
            border-radius: 0;
            padding: 15px;
        }

        .cart-notification .alert.alert-success {
            background-color: #d4edda;
            border-color: #c3e6cb;
            color: #155724;
        }

        .cart-notification .alert.alert-danger {
            background-color: #f8d7da;
            border-color: #f5c6cb;
            color: #721c24;
        }

        .cart-notification .close {
            margin-top: -2px;
            float: right;
            font-size: 20px;
            font-weight: bold;
            border: none;
            background: transparent;
            color: inherit;
        }
    </style>
</head>

<body>
    <!-- HEADER -->
    <?php require_once("../includes/header.php") ?>

    <!-- BREADCRUMB -->
    <div id="breadcrumb" class="section">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <h3 class="breadcrumb-header">Shopping Cart</h3>
                    <ul class="breadcrumb-tree">
                        <li><a href="<?php echo BASE_URL ?>/public/index.php">Home</a></li>
                        <li class="active">Cart</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Cart Notification -->
    <div id="cart-notification" class="cart-notification" style="display:none;">
        <div class="container">
            <div class="alert">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <i class="fa fa-check-circle"></i>
                <span id="notification-message"></span>
            </div>
        </div>
    </div>

    <!-- SECTION -->
    <div class="section">
        <div class="container">
            <div class="row">
                <!-- Cart Details -->
                <div class="col-md-8">
                    <div class="cart-details" id="cart-container">
                        <?php if (count($cartItems) > 0): ?>
                            <div class="cart-list">
                                <?php foreach ($cartItems as $item): ?>
                                    <!-- Cart Item -->
                                    <div class="cart-item" data-product-id="<?php echo $item['product_id']; ?>">
                                        <div class="product-widget">
                                            <div class="product-img">
                                                <img src="<?php echo BASE_URL . $item['primary_image']; ?>"
                                                    alt="<?php echo htmlspecialchars($item['product_name']); ?>" />
                                            </div>
                                            <div class="product-body">
                                                <h3 class="product-name">
                                                    <a
                                                        href="product_detail.php?id=<?php echo $item['product_id']; ?>"><?php echo htmlspecialchars($item['product_name']); ?></a>
                                                </h3>
                                                <h4 class="product-price">
                                                    <span
                                                        class="qty"><?php echo $item['quantity']; ?>x</span>$<?php echo number_format($item['price'], 2); ?>
                                                </h4>
                                            </div>
                                            <div class="product-actions">
                                                <div class="qty-label">
                                                    Qty
                                                    <div class="input-number">
                                                        <input type="number" class="item-quantity"
                                                            value="<?php echo $item['quantity']; ?>" min="1" />
                                                        <span class="qty-up">+</span>
                                                        <span class="qty-down">-</span>
                                                    </div>
                                                </div>
                                                <button class="delete remove-from-cart">
                                                    <i class="fa fa-close"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- /Cart Item -->
                                <?php endforeach; ?>
                            </div>

                            <div class="cart-btns">
                                <a href="products.php" class="primary-btn">Continue Shopping</a>
                                <button id="update-cart" class="primary-btn">Update Cart</button>
                            </div>
                        <?php else: ?>
                            <div class="empty-cart">
                                <h3>Your cart is empty</h3>
                                <p>Looks like you haven't added any products to your cart yet.</p>
                                <a href="products.php" class="primary-btn">Continue Shopping</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Cart Summary -->
                <div class="col-md-4">
                    <div class="cart-summary">
                        <h3 class="title">Order Summary</h3>
                        <div class="cart-total">
                            <div class="order-col">
                                <div><strong>SUBTOTAL</strong></div>
                                <div><strong id="subtotal">$<?php echo number_format($cartTotal, 2); ?></strong></div>
                            </div>
                            <div class="order-col">
                                <div>Shipping</div>
                                <div><strong>FREE</strong></div>
                            </div>
                            <div class="order-col">
                                <div><strong>TOTAL</strong></div>
                                <div>
                                    <strong class="order-total"
                                        id="total">$<?php echo number_format($cartTotal, 2); ?></strong>
                                </div>
                            </div>
                        </div>
                        <a href="checkout.php"
                            class="primary-btn order-submit <?php echo count($cartItems) == 0 ? 'disabled' : ''; ?>">Proceed
                            to Checkout</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- FOOTER -->
    <?php require_once("../includes/footer.php") ?>

    <!-- jQuery Plugins -->
    <script src="<?php echo JS_DIR ?>jquery.min.js"></script>
    <script src="<?php echo JS_DIR ?>bootstrap.min.js"></script>
    <script src="<?php echo JS_DIR ?>slick.min.js"></script>
    <script src="<?php echo JS_DIR ?>main.js"></script>

    <!-- URL -->
    <script>
        var BASE_URL = '<?php echo BASE_URL ?>';
    </script>

    <!-- Import JS -->
    <script src="<?php echo JS_DIR ?>notifications.js"></script>
    <script src="<?php echo JS_DIR ?>cart-api.js"></script>
    <script src="<?php echo JS_DIR ?>global-cart.js"></script>
    <script src="<?php echo JS_DIR ?>cart.js"></script>
</body>

</html>