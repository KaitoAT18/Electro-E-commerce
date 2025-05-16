<?php
// Load the configuration file
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Generate CSRF token
if (!isset($_SESSION['csrf_token'])) {
    generate_csrf_token();
}

// Check if the user is logged in
if (!check_login()) {
    // Not logged in, redirect to login page
    setFlashData('msg', 'You need to login to checkout');
    setFlashData('msg_type', 'danger');
    // Redirect to login page
    header('Location: ' . BASE_URL . '/public/login.php');
    exit;
}

// Get user information
$userId = getUserIdFromJwt();

$sql_user = "SELECT email, full_name, phone FROM users WHERE user_id = ?";
$user = getRecord($sql_user, [$userId]);
if ($user) {
    $email = $user['email'];
    $firstName = explode(' ', $user['full_name'])[0];
    $lastName = explode(' ', $user['full_name'])[1];
    $phone = $user['phone'];
} else {
    // User not found, redirect to login page
    setFlashData('msg', 'User not found. Please login again.');
    setFlashData('msg_type', 'danger');
    header('Location: ' . BASE_URL . '/public/login.php');
    exit;
}

// Check if the cart is empty
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    // Cart is empty, redirect to cart page
    setFlashData('msg', 'Your cart is empty. Please add products to your cart before checking out.');
    setFlashData('msg_type', 'warning');
    // Redirect to products page
    header('Location: ' . BASE_URL . '/public/products.php');
    exit;
}

// Get the cart items
$cartTotal = 0;
$cartItems = [];

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
            'subtotal' => $subtotal
        ];
    }
}


$errors = [];
// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['csrf_token'])) {
    // Check CSRF token
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die("Invalid CSRF token");
    }

    $filteredData = filter();

    $firstName = trim($filteredData['first-name']);
    $lastName = trim($filteredData['last-name']);
    $email = trim($filteredData['email']);
    $address = trim($filteredData['address']);
    $city = trim($filteredData['city']);
    $country = trim($filteredData['country']);
    $zipCode = trim($filteredData['zip-code']) ?? '';
    $phone = trim($filteredData['tel']);
    $termsAccepted = isset($filteredData['terms']) ? 1 : 0;

    // Validate input data
    if (!empty($firstName) || !empty($lastName) || !empty($email) || !empty($address) || !empty($city) || !empty($country) || !empty($phone) || !empty($termsAccepted)) {
        if (strlen($firstName) < 2) {
            $errors['first_name'] = "First name must be at least 2 characters long.";
        }
        if (strlen($lastName) < 2) {
            $errors['last_name'] = "Last name must be at least 2 characters long.";
        }
        if (strlen($address) < 5) {
            $errors['address'] = "Address must be at least 5 characters long.";
        }
        if (strlen($city) < 2) {
            $errors['city'] = "City must be at least 2 characters long.";
        }
        if (strlen($country) < 2) {
            $errors['country'] = "Country must be at least 2 characters long.";
        }
        if (!empty($zipCode) && strlen($zipCode) < 5) {
            $errors['zip_code'] = "ZIP Code must be at least 5 characters long.";
        }
        if (!validate_email($email)) {
            $errors['email'] = "Invalid email address.";
        }
        if (!validate_phone_number($phone)) {
            $errors['phone'] = "Invalid phone number.";
        }
        if ($termsAccepted != 1) {
            $errors['terms'] = "You must accept the terms and conditions.";
        }

        // If there are no errors, proceed with the order
        if (empty($errors)) {
            // Prepare order data
            $orderData = [
                'user_id' => $userId,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $email,
                'address' => $address,
                'city' => $city,
                'country' => $country,
                'zip_code' => $zipCode ?? '',
                'phone' => $phone,
                'total_amount' => $cartTotal,
                'status' => 'pending',
                'created_at' => date('Y-m-d H:i:s')
            ];

            // Insert order into the database
            $results = createOrder($userId, $orderData, $cartItems, $cartTotal);
            if ($results['success']) {
                // Order created successfully
                $orderId = $results['order_id'];

                // Clear the cart
                unset($_SESSION['cart']);

                // Redirect to order confirmation page
                setFlashData('msg', 'Order placed successfully. Thank you for your purchase!');
                setFlashData('msg_type', 'success');

                header('Location: ' . BASE_URL . '/public/orders.php');
                exit;
            } else {
                $errors['order'] = "Failed to create order. Please try again.";
            }

            // Redirect to order page
            header('Location: ' . BASE_URL . '/public/orders.php?order_id=' . $orderId);
            exit;
        } else {
            $errors['order'] = "Failed to create order. Please try again.";
        }
    } else {
        $errors['empty_fields'] = "Please fill in all required fields.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->

    <title>Electro - Checkout</title>

    <!-- Google font -->
    <link href="https://fonts.googleapis.com/css?family=Montserrat:400,500,700" rel="stylesheet">

    <!-- Bootstrap -->
    <link type="text/css" rel="stylesheet" href="<?php echo CSS_DIR ?>bootstrap.min.css" />

    <!-- Slick -->
    <link type="text/css" rel="stylesheet" href="<?php echo CSS_DIR ?>slick.css" />
    <link type="text/css" rel="stylesheet" href="<?php echo CSS_DIR ?>slick-theme.css" />

    <!-- nouislider -->
    <link type="text/css" rel="stylesheet" href="<?php echo CSS_DIR ?>nouislider.min.css" />

    <!-- Font Awesome Icon -->
    <link rel="stylesheet" href="<?php echo CSS_DIR ?>font-awesome.min.css">

    <!-- Custom stylesheet -->
    <link type="text/css" rel="stylesheet" href="<?php echo CSS_DIR ?>styles.css" />

    <style>
        .error-message {
            color: #D10024;
            font-size: 14px;
            margin-top: 5px;
        }

        .required-field::after {
            content: " *";
            color: #D10024;
        }

        .order-summary {
            margin-bottom: 30px;
        }

        .order-products {
            max-height: 300px;
            overflow-y: auto;
        }

        .payment-method .input-radio .caption {
            display: block;
        }
    </style>
</head>

<body>
    <!-- HEADER -->
    <?php require_once("../includes/header.php") ?>

    <!-- BREADCRUMB -->
    <div id="breadcrumb" class="section">
        <!-- container -->
        <div class="container">
            <!-- row -->
            <div class="row">
                <div class="col-md-12">
                    <h3 class="breadcrumb-header">Checkout</h3>
                    <ul class="breadcrumb-tree">
                        <li><a href="<?php echo BASE_URL ?>/public/index.php">Home</a></li>
                        <li><a href="<?php echo BASE_URL ?>/public/cart.php">Cart</a></li>
                        <li class="active">Checkout</li>
                    </ul>
                </div>
            </div>
            <!-- /row -->
        </div>
        <!-- /container -->
    </div>
    <!-- /BREADCRUMB -->

    <!-- SECTION -->
    <div class="section">
        <!-- container -->
        <div class="container">
            <!-- row -->
            <div class="row">
                <?php if (isset($errors) && !empty($errors)): ?>
                    <div class="col-md-12">
                        <div class="alert alert-danger">
                            <ul>
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo htmlspecialchars($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                <?php endif; ?>

                <form id="checkout-form" method="POST" action="">
                    <div class="col-md-7">
                        <!-- Billing Details -->
                        <div class="billing-details">
                            <div class="section-title">
                                <h3 class="title">Billing and Delivery Address</h3>
                            </div>
                            <div class="form-group">
                                <label class="required-field">First Name</label>
                                <input class="input" type="text" name="first-name" placeholder="First Name"
                                    value="<?php echo isset($firstName) ? htmlspecialchars($firstName) : ''; ?>"
                                    required>
                            </div>
                            <div class="form-group">
                                <label class="required-field">Last Name</label>
                                <input class="input" type="text" name="last-name" placeholder="Last Name"
                                    value="<?php echo isset($lastName) ? htmlspecialchars($lastName) : ''; ?>" required>
                            </div>
                            <div class="form-group">
                                <label class="required-field">Email</label>
                                <input class="input" type="email" name="email" placeholder="Email"
                                    value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" required>
                            </div>
                            <div class="form-group">
                                <label class="required-field">Address</label>
                                <input class="input" type="text" name="address" placeholder="Address"
                                    value="<?php echo isset($address) ? htmlspecialchars($address) : ''; ?>" required>
                            </div>
                            <div class="form-group">
                                <label class="required-field">City</label>
                                <input class="input" type="text" name="city" placeholder="City"
                                    value="<?php echo isset($city) ? htmlspecialchars($city) : ''; ?>" required>
                            </div>
                            <div class="form-group">
                                <label class="required-field">Country</label>
                                <input class="input" type="text" name="country" placeholder="Country"
                                    value="<?php echo isset($country) ? htmlspecialchars($country) : ''; ?>" required>
                            </div>
                            <div class="form-group">
                                <label>ZIP Code</label>
                                <input class="input" type="text" name="zip-code" placeholder="ZIP Code"
                                    value="<?php echo isset($zipCode) ? htmlspecialchars($zipCode) : ''; ?>">
                            </div>
                            <div class="form-group">
                                <label class="required-field">Telephone</label>
                                <input class="input" type="tel" name="tel" placeholder="Telephone"
                                    value="<?php echo isset($phone) ? htmlspecialchars($phone) : ''; ?>" required>
                            </div>
                            <input type="hidden" name="csrf_token"
                                value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>" />
                        </div>
                        <!-- /Billing Details -->
                    </div>

                    <!-- Order Details -->
                    <div class="col-md-5 order-details">
                        <div class="section-title text-center">
                            <h3 class="title">Your Order</h3>
                        </div>
                        <div class="order-summary">
                            <div class="order-col">
                                <div><strong>PRODUCT</strong></div>
                                <div><strong>PRICE</strong></div>
                            </div>
                            <div class="order-products">
                                <?php foreach ($cartItems as $item): ?>
                                    <div class="order-col">
                                        <div><?php echo $item['quantity']; ?>x
                                            <?php echo htmlspecialchars($item['product_name']); ?>
                                        </div>
                                        <div>$<?php echo number_format($item['price'], 2); ?></div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="order-col">
                                <div>Shipping</div>
                                <div><strong>FREE</strong></div>
                            </div>
                            <div class="order-col">
                                <div><strong>TOTAL</strong></div>
                                <div><strong class="order-total">$<?php echo number_format($cartTotal, 2); ?></strong>
                                </div>
                            </div>
                        </div>
                        <div class="payment-method">
                            <div class="section-title">
                                <h4 class="title">Payment Method</h4>
                            </div>
                            <div class="input-radio">
                                <input type="radio" name="payment" id="payment-1" value="cod" checked>
                                <label for="payment-1">
                                    <span></span>
                                    Cash on Delivery (COD)
                                </label>
                                <div class="caption">
                                    <p>Pay with cash upon delivery. Your order will be shipped to your address and you
                                        will pay when you receive the products.</p>
                                </div>
                            </div>
                        </div>
                        <div class="input-checkbox">
                            <input type="checkbox" name="terms" id="terms" <?php echo isset($termsAccepted) && $termsAccepted ? 'checked' : ''; ?> required>
                            <label for="terms">
                                <span></span>
                                I've read and accept the <a href="#">terms & conditions</a>
                            </label>
                        </div>
                        <button type="submit" class="primary-btn order-submit">Place order</button>
                    </div>
                    <!-- /Order Details -->
                </form>
            </div>
            <!-- /row -->
        </div>
        <!-- /container -->
    </div>
    <!-- /SECTION -->

    <!-- FOOTER -->
    <?php require_once("../includes/footer.php") ?>

    <!-- jQuery Plugins -->
    <script src="<?php echo JS_DIR ?>jquery.min.js"></script>
    <script src="<?php echo JS_DIR ?>bootstrap.min.js"></script>
    <script src="<?php echo JS_DIR ?>slick.min.js"></script>
    <script src="<?php echo JS_DIR ?>nouislider.min.js"></script>
    <script src="<?php echo JS_DIR ?>jquery.zoom.min.js"></script>
    <script src="<?php echo JS_DIR ?>main.js"></script>
    <script src="<?php echo JS_DIR ?>checkout-validate.js"></script>
</body>

</html>