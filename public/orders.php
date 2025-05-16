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
    // Set flash message
    setFlashData('msg', 'Please log in to view your orders.');
    setFlashData('msg_type', 'danger');
    // Redirect to login page if not logged in
    header('Location: ' . BASE_URL . '/public/login.php');
    exit();
}

$msg = getFlashData('msg');
$msg_type = getFlashData('msg_type');
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>My Orders - Electro</title>

    <!-- Google font -->
    <link href="https://fonts.googleapis.com/css?family=Montserrat:400,500,700" rel="stylesheet" />

    <!-- Bootstrap -->
    <link type="text/css" rel="stylesheet" href="<?php echo CSS_DIR ?>bootstrap.min.css" />

    <!-- Font Awesome Icon -->
    <link rel="stylesheet" href="<?php echo CSS_DIR ?>font-awesome.min.css" />

    <!-- Custom stylesheet -->
    <link type="text/css" rel="stylesheet" href="<?php echo CSS_DIR ?>styles.css" />
</head>

<body>
    <!-- HEADER -->
    <?php include_once '../includes/header.php'; ?>
    <!-- /HEADER -->

    <!-- BREADCRUMB -->
    <div id="breadcrumb" class="section">
        <!-- container -->
        <div class="container">
            <!-- row -->
            <div class="row">
                <div class="col-md-12">
                    <h3 class="breadcrumb-header">My Orders</h3>
                    <ul class="breadcrumb-tree">
                        <li><a href="<?php echo BASE_URL ?>/public/index.php">Home</a></li>
                        <li class="active">My Orders</li>
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
        <div class="container">
            <!-- Notification -->
            <?php if ($msg): ?>
                <div class="row">
                    <div class="col-md-12">
                        <div class="alert alert-<?php echo $msg_type; ?>">
                            <button type="button" class="close" data-dismiss="alert">×</button>
                            <i class="fa fa-check-circle"></i>
                            <span id="notification-message"><?php echo $msg; ?></span>
                            <script>
                                setTimeout(function () {
                                    document.querySelector('.alert').style.display = 'none';
                                }, 3000);
                            </script>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <div class="row">
                <!-- Order Listing -->
                <div class="col-md-12">
                    <!-- Order List -->
                    <div class="orders-list">
                        <!-- Order Item -->
                        <div class="order-item">
                            <div class="order-header">
                                <div class="row">
                                    <div class="col-md-3 col-sm-3">
                                        <h4 class="order-id">
                                            Order #12345
                                        </h4>
                                    </div>
                                    <div class="col-md-3 col-sm-3">
                                        <span class="order-date">March 25, 2025</span>
                                    </div>
                                    <div class="col-md-3 col-sm-3">
                                        <span class="order-total">Total: $980.00</span>
                                    </div>
                                    <div class="col-md-3 col-sm-3">
                                        <span class="order-status delivered">Delivered</span>
                                    </div>
                                </div>
                            </div>
                            <div class="order-body">
                                <div class="row">
                                    <div class="col-md-2 col-sm-2">
                                        <div class="order-product-image">
                                            <img src="./img/product01.png" alt="" />
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-sm-5">
                                        <div class="order-product-info">
                                            <h5 class="product-name">
                                                Laptop XPS 15
                                            </h5>
                                            <p class="product-detail">
                                                Color: Silver | Size: 15.6"
                                            </p>
                                            <p class="product-qty">
                                                Qty: 1
                                            </p>
                                        </div>
                                    </div>
                                    <div class="col-md-4 col-sm-5">
                                        <div class="order-actions">
                                            <button class="order-action-btn view-details">
                                                View Details
                                            </button>
                                            <button class="order-action-btn buy-again">
                                                Buy Again
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- /Order Item -->

                        <!-- Order Item -->
                        <div class="order-item">
                            <div class="order-header">
                                <div class="row">
                                    <div class="col-md-3 col-sm-3">
                                        <h4 class="order-id">
                                            Order #12346
                                        </h4>
                                    </div>
                                    <div class="col-md-3 col-sm-3">
                                        <span class="order-date">March 20, 2025</span>
                                    </div>
                                    <div class="col-md-3 col-sm-3">
                                        <span class="order-total">Total: $2,940.00</span>
                                    </div>
                                    <div class="col-md-3 col-sm-3">
                                        <span class="order-status shipped">Shipped</span>
                                    </div>
                                </div>
                            </div>
                            <div class="order-body">
                                <div class="row">
                                    <div class="col-md-2 col-sm-2">
                                        <div class="order-product-image">
                                            <img src="./img/product02.png" alt="" />
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-sm-5">
                                        <div class="order-product-info">
                                            <h5 class="product-name">
                                                Smartphone Galaxy S22
                                            </h5>
                                            <p class="product-detail">
                                                Color: Black | Storage:
                                                256GB
                                            </p>
                                            <p class="product-qty">
                                                Qty: 3
                                            </p>
                                        </div>
                                    </div>
                                    <div class="col-md-4 col-sm-5">
                                        <div class="order-actions">
                                            <button class="order-action-btn view-details">
                                                View Details
                                            </button>
                                            <button class="order-action-btn track-order">
                                                Track Order
                                            </button>
                                            <button class="order-action-btn buy-again">
                                                Buy Again
                                            </button>
                                            <button class="order-action-btn cancel-order" data-toggle="modal"
                                                data-target="#cancelOrderModal" data-orderid="12346">
                                                Cancel Order
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- /Order Item -->

                        <!-- Order Item -->
                        <div class="order-item">
                            <div class="order-header">
                                <div class="row">
                                    <div class="col-md-3 col-sm-3">
                                        <h4 class="order-id">
                                            Order #12347
                                        </h4>
                                    </div>
                                    <div class="col-md-3 col-sm-3">
                                        <span class="order-date">March 15, 2025</span>
                                    </div>
                                    <div class="col-md-3 col-sm-3">
                                        <span class="order-total">Total: $1,200.00</span>
                                    </div>
                                    <div class="col-md-3 col-sm-3">
                                        <span class="order-status pending">Pending</span>
                                    </div>
                                </div>
                            </div>
                            <div class="order-body">
                                <div class="row">
                                    <div class="col-md-2 col-sm-2">
                                        <div class="order-product-image">
                                            <img src="./img/product03.png" alt="" />
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-sm-5">
                                        <div class="order-product-info">
                                            <h5 class="product-name">
                                                Wireless Headphones
                                            </h5>
                                            <p class="product-detail">
                                                Color: White | Type:
                                                Over-ear
                                            </p>
                                            <p class="product-qty">
                                                Qty: 1
                                            </p>
                                        </div>
                                    </div>
                                    <div class="col-md-4 col-sm-5">
                                        <div class="order-actions">
                                            <button class="order-action-btn view-details">
                                                View Details
                                            </button>
                                            <button class="order-action-btn track-order">
                                                Track Order
                                            </button>
                                            <button class="order-action-btn cancel-order" data-toggle="modal"
                                                data-target="#cancelOrderModal" data-orderid="12347">
                                                Cancel Order
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- /Order Item -->
                    </div>

                    <!-- Pagination -->
                    <div class="store-filter clearfix">
                        <span class="store-qty">Showing 1-4 of 8 orders</span>
                        <ul class="store-pagination">
                            <li class="active">1</li>
                            <li><a href="#">2</a></li>
                            <li><a href="#">3</a></li>
                            <li>
                                <a href="#"><i class="fa fa-angle-right"></i></a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /SECTION -->

    <!-- Order Details Modal -->
    <div class="modal fade" id="orderDetailsModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">
                        &times;
                    </button>
                    <h4 class="modal-title">Order #12345 Details</h4>
                </div>
                <div class="modal-body">
                    <div class="order-detail-header">
                        <div class="row">
                            <div class="col-md-6">
                                <p>
                                    <strong>Order Date:</strong> March 25,
                                    2025
                                </p>
                                <p>
                                    <strong>Order Status:</strong>
                                    <span class="order-status delivered">Delivered</span>
                                </p>
                                <p>
                                    <strong>Payment Method:</strong> Credit
                                    Card (**** 1234)
                                </p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Shipping Address:</strong></p>
                                <p>
                                    John Doe<br />
                                    1734 Stonecoal Road<br />
                                    New York, NY 10001<br />
                                    United States
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="order-detail-items">
                        <h5>Order Items</h5>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Price</th>
                                    <th>Quantity</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <div class="product-widget">
                                            <div class="product-img">
                                                <img src="./img/product01.png" alt="" />
                                            </div>
                                            <div class="product-body">
                                                <h5 class="product-name">
                                                    Laptop XPS 15
                                                </h5>
                                                <p>
                                                    Color: Silver | Size:
                                                    15.6"
                                                </p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>$980.00</td>
                                    <td>1</td>
                                    <td>$980.00</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="order-detail-summary">
                        <div class="row">
                            <div class="col-md-4 col-md-offset-8">
                                <table class="table">
                                    <tbody>
                                        <tr>
                                            <td>Subtotal</td>
                                            <td>$980.00</td>
                                        </tr>
                                        <tr>
                                            <td>Shipping</td>
                                            <td>FREE</td>
                                        </tr>
                                        <tr>
                                            <td>Tax</td>
                                            <td>$0.00</td>
                                        </tr>
                                        <tr class="total-row">
                                            <td><strong>Total</strong></td>
                                            <td>
                                                <strong>$980.00</strong>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="order-detail-tracking">
                        <h5>Order Tracking</h5>
                        <div class="tracking-timeline">
                            <div class="tracking-step completed">
                                <div class="step-icon">
                                    <i class="fa fa-check"></i>
                                </div>
                                <div class="step-info">
                                    <h6>Order Placed</h6>
                                    <p>March 25, 2025 at 10:30 AM</p>
                                </div>
                            </div>
                            <div class="tracking-step completed">
                                <div class="step-icon">
                                    <i class="fa fa-check"></i>
                                </div>
                                <div class="step-info">
                                    <h6>Processing</h6>
                                    <p>March 25, 2025 at 2:15 PM</p>
                                </div>
                            </div>
                            <div class="tracking-step completed">
                                <div class="step-icon">
                                    <i class="fa fa-check"></i>
                                </div>
                                <div class="step-info">
                                    <h6>Shipped</h6>
                                    <p>March 26, 2025 at 9:45 AM</p>
                                </div>
                            </div>
                            <div class="tracking-step completed">
                                <div class="step-icon">
                                    <i class="fa fa-check"></i>
                                </div>
                                <div class="step-info">
                                    <h6>Delivered</h6>
                                    <p>March 28, 2025 at 3:20 PM</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="primary-btn" data-dismiss="modal">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Cancel Order Modal -->
    <div class="modal fade" id="cancelOrderModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">
                        &times;
                    </button>
                    <h4 class="modal-title">Cancel Order</h4>
                </div>
                <div class="modal-body">
                    <p>
                        Are you sure you want to cancel order #<span id="cancelOrderId"></span>?
                    </p>
                    <div class="form-group">
                        <label>Reason for cancellation:</label>
                        <select class="input-select">
                            <option value="">Select a reason</option>
                            <option value="changed_mind">
                                Changed my mind
                            </option>
                            <option value="found_better_price">
                                Found a better price elsewhere
                            </option>
                            <option value="delivery_too_long">
                                Delivery time is too long
                            </option>
                            <option value="ordered_wrong_item">
                                Ordered wrong item
                            </option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="form-group" id="otherReasonGroup" style="display: none">
                        <label>Please specify:</label>
                        <textarea class="input" rows="3" placeholder="Enter your reason"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="primary-btn" data-dismiss="modal">
                        Close
                    </button>
                    <button type="button" class="primary-btn" id="confirmCancelBtn">
                        Confirm Cancellation
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- FOOTER -->
    <?php include_once '../includes/footer.php'; ?>
    <!-- /FOOTER -->

    <!-- jQuery Plugins -->
    <script src="<?php echo JS_DIR ?>jquery.min.js"></script>
    <script src="<?php echo JS_DIR ?>bootstrap.min.js"></script>
    <script src="<?php echo JS_DIR ?>slick.min.js"></script>
    <script src="<?php echo JS_DIR ?>jquery.zoom.min.js"></script>
    <script src="<?php echo JS_DIR ?>main.js"></script>
    <script>
        var BASE_URL = '<?php echo BASE_URL ?>';
    </script>
    <script src="<?php echo JS_DIR ?>orders.js"></script>
</body>

</html>