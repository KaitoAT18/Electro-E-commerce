<?php
// Load the configuration file
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
// Get flash data
$msg = getFlashData('msg');
$msgType = getFlashData('msg_type');

// Get new products
$sql_new_products = "SELECT 
        p.product_id,
        p.product_name,
        p.price,
        c.category_name,
        c.category_id,
        p.sale_price,
        pi.image_path AS primary_image
    FROM 
        products p
        JOIN categories c ON p.category_id = c.category_id
        LEFT JOIN product_images pi ON p.product_id = pi.product_id AND pi.is_primary = 1
    ORDER BY 
        p.product_id ASC
    LIMIT 5;
    ";

$new_products = getRecords($sql_new_products, []);
// Get top selling products
$sql_top_selling_products = "SELECT 
        p.product_id,
        p.product_name,
        p.price,
        c.category_name,
        p.sale_price,
        pi.image_path AS primary_image
    FROM 
        products p
        JOIN categories c ON p.category_id = c.category_id
        LEFT JOIN product_images pi ON p.product_id = pi.product_id AND pi.is_primary = 1
    ORDER BY 
        p.product_id DESC
    LIMIT 5;
    ";
$top_selling_products = getRecords($sql_top_selling_products, []);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->

    <title>Electro - Home</title>

    <!-- Google font -->
    <link href="https://fonts.googleapis.com/css?family=Montserrat:400,500,700" rel="stylesheet" />

    <!-- Bootstrap -->
    <link type="text/css" rel="stylesheet" href="<?php echo CSS_DIR ?>bootstrap.min.css" />

    <!-- Slick -->
    <link type="text/css" rel="stylesheet" href="<?php echo CSS_DIR ?>slick.css" />
    <link type="text/css" rel="stylesheet" href="<?php echo CSS_DIR ?>slick-theme.css" />

    <!-- nouislider -->
    <link type="text/css" rel="stylesheet" href="<?php echo CSS_DIR ?>nouislider.min.css" />

    <!-- Font Awesome Icon -->
    <link rel="stylesheet" href="<?php echo CSS_DIR ?>font-awesome.min.css" />

    <!-- Custom stylesheet -->
    <link type="text/css" rel="stylesheet" href="<?php echo CSS_DIR ?>styles.css" />

    <style>
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

        #product-notifications {
            position: relative;
            z-index: 999;
        }

        #product-notifications .alert {
            border-radius: 0;
            margin-bottom: 10px;
        }

        #product-notifications .btn-primary {
            background-color: #D10024;
            border-color: #D10024;
            color: #fff;
        }

        #product-notifications .btn-primary:hover {
            background-color: #A70018;
            border-color: #A70018;
        }

        .alert-link {
            font-weight: bold;
            color: #D10024 !important;
        }

        .alert-link:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>
    <!-- HEADER -->
    <?php include_once '../includes/header.php'; ?>

    <div id="product-notifications" style="display: none;" class="container mt-2"></div>

    <!-- Cart Notification -->
    <div id="cart-notification" class="cart-notification" style="display:none;">
        <div class="container">
            <div class="alert alert-success">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <i class="fa fa-check-circle"></i>
                <span id="notification-message"></span>
            </div>
        </div>
    </div>

    <!-- Notification -->
    <?php if (!empty($msg) && !empty($msgType)): ?>
        <div class="container mt-2">
            <div class="alert alert-<?php echo htmlspecialchars($msgType); ?>">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <i class="fa fa-check-circle"></i>
                <?php echo htmlspecialchars($msg); ?>
                <script>
                    setTimeout(function () {
                        document.querySelector('.alert').style.display = 'none';
                    }, 3000);
                </script>
            </div>
        </div>
    <?php endif; ?>

    <!-- SECTION -->
    <div class="section">
        <!-- container -->
        <div class="container">
            <!-- row -->
            <div class="row">
                <!-- shop -->
                <div class="col-md-4 col-xs-6">
                    <div class="shop">
                        <div class="shop-img">
                            <img src="<?php echo IMAGE_DIR ?>shop01.png" alt="" />
                        </div>
                        <div class="shop-body">
                            <h3>Laptop<br />Collection</h3>
                            <a href="<?php echo BASE_URL . '/public/products.php?category[]=2' ?>" class="cta-btn">Shop
                                now
                                <i class="fa fa-arrow-circle-right"></i></a>
                        </div>
                    </div>
                </div>
                <!-- /shop -->

                <!-- shop -->
                <div class="col-md-4 col-xs-6">
                    <div class="shop">
                        <div class="shop-img">
                            <img src="<?php echo IMAGE_DIR ?>shop03.png" alt="" />
                        </div>
                        <div class="shop-body">
                            <h3>Accessories<br />Collection</h3>
                            <a href="<?php echo BASE_URL . '/public/products.php?category[]=1' ?>" class="cta-btn">Shop
                                now
                                <i class="fa fa-arrow-circle-right"></i></a>
                        </div>
                    </div>
                </div>
                <!-- /shop -->

                <!-- shop -->
                <div class="col-md-4 col-xs-6">
                    <div class="shop">
                        <div class="shop-img">
                            <img src="<?php echo IMAGE_DIR ?>shop02.png" alt="" />
                        </div>
                        <div class="shop-body">
                            <h3>Cameras<br />Collection</h3>
                            <a href="<?php echo BASE_URL . '/public/products.php?category[]=4' ?>" class="cta-btn">Shop
                                now
                                <i class="fa fa-arrow-circle-right"></i></a>
                        </div>
                    </div>
                </div>
                <!-- /shop -->
            </div>
            <!-- /row -->
        </div>
        <!-- /container -->
    </div>
    <!-- /SECTION -->

    <!-- SECTION -->
    <div class="section">
        <!-- container -->
        <div class="container">
            <!-- row -->
            <div class="row">
                <!-- section title -->
                <div class="col-md-12">
                    <div class="section-title">
                        <h3 class="title">New Products</h3>
                    </div>
                </div>
                <!-- /section title -->

                <!-- Products tab & slick -->
                <?php if (!empty($new_products)): ?>
                    <div class="col-md-12">
                        <div class="row">
                            <div class="products-tabs">
                                <!-- tab -->
                                <div id="tab1" class="tab-pane active">
                                    <div class="products-slick" data-nav="#slick-nav-1">
                                        <?php foreach ($new_products as $product): ?>
                                            <!-- product -->
                                            <div class="product">
                                                <div class="product-img">
                                                    <img src="<?php echo BASE_URL . htmlspecialchars($product['primary_image']) ?>"
                                                        alt="" />
                                                    <?php if (!empty($product['sale_price'])): ?>
                                                        <div class="product-label">
                                                            <span class="sale">
                                                                <?php echo round((($product['price'] - $product['sale_price']) / $product['price']) * 100) ?>%
                                                            </span>
                                                            <span class="new">NEW</span>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="product-body">
                                                    <p class="product-category">
                                                        <?php echo htmlspecialchars($product['category_name']) ?>
                                                    </p>
                                                    <h3 class="product-name">
                                                        <a
                                                            href="<?php echo BASE_URL ?>/public/product_detail.php?id=<?php echo $product['product_id']; ?>">
                                                            <?php echo htmlspecialchars($product['product_name']) ?>
                                                        </a>
                                                    </h3>
                                                    <h4 class="product-price">
                                                        <?php if (!empty($product['sale_price'])): ?>
                                                            $<?php echo htmlspecialchars($product['sale_price']) ?>
                                                            <del class="product-old-price">
                                                                $<?php echo htmlspecialchars($product['price']) ?>
                                                            </del>
                                                        <?php else: ?>
                                                            $<?php echo htmlspecialchars($product['price']) ?>
                                                        <?php endif; ?>
                                                    </h4>
                                                    <div class="product-rating">
                                                        <i class="fa fa-star"></i>
                                                        <i class="fa fa-star"></i>
                                                        <i class="fa fa-star"></i>
                                                        <i class="fa fa-star"></i>
                                                        <i class="fa fa-star"></i>
                                                    </div>
                                                </div>
                                                <div class="add-to-cart">
                                                    <button class="add-to-cart-btn"
                                                        data-product-id="<?php echo $product['product_id']; ?>"
                                                        data-product-name="<?php echo htmlspecialchars($product['product_name']); ?>">
                                                        <i class="fa fa-shopping-cart"></i> add to cart
                                                    </button>
                                                </div>
                                            </div>
                                            <!-- /product -->
                                        <?php endforeach; ?>
                                    </div>
                                    <div id="slick-nav-1" class="products-slick-nav"></div>
                                </div>
                                <!-- /tab -->
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="col-md-12">
                        <div class="row">
                            <div class="products-tabs">
                                <div id="tab2" class="tab-pane fade in active">
                                    <div class="products-slick" data-nav="#slick-nav-2">
                                        <h3>No products found</h3>
                                    </div>
                                    <div id="slick-nav-2" class="products-slick-nav"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                <!-- Products tab & slick -->
            </div>
            <!-- /row -->
        </div>
        <!-- /container -->
    </div>
    <!-- /SECTION -->

    <!-- HOT DEAL SECTION -->
    <div id="hot-deal" class="section">
        <!-- container -->
        <div class="container">
            <!-- row -->
            <div class="row">
                <div class="col-md-12">
                    <div class="hot-deal">
                        <ul class="hot-deal-countdown">
                            <li>
                                <div>
                                    <h3 id="countdown-days">00</h3>
                                    <span>Days</span>
                                </div>
                            </li>
                            <li>
                                <div>
                                    <h3 id="countdown-hours">00</h3>
                                    <span>Hours</span>
                                </div>
                            </li>
                            <li>
                                <div>
                                    <h3 id="countdown-minutes">00</h3>
                                    <span>Mins</span>
                                </div>
                            </li>
                            <li>
                                <div>
                                    <h3 id="countdown-seconds">00</h3>
                                    <span>Secs</span>
                                </div>
                            </li>
                        </ul>
                        <h2 class="text-uppercase">hot deal this week</h2>
                        <p>New Collection Up to 50% OFF</p>
                        <a class="primary-btn cta-btn" href="<?php echo BASE_URL . '/public/products.php' ?>">Shop
                            now</a>
                    </div>
                </div>
            </div>
            <!-- /row -->
        </div>
        <!-- /container -->
    </div>
    <!-- /HOT DEAL SECTION -->

    <!-- SECTION -->
    <div class="section">
        <!-- container -->
        <div class="container">
            <!-- row -->
            <div class="row">
                <!-- section title -->
                <div class="col-md-12">
                    <div class="section-title">
                        <h3 class="title">Top selling</h3>
                    </div>
                </div>
                <!-- /section title -->

                <?php if (!empty($new_products)): ?>
                    <!-- Products tab & slick -->
                    <div class="col-md-12">
                        <div class="row">
                            <div class="products-tabs">
                                <!-- tab -->
                                <div id="tab2" class="tab-pane fade in active">
                                    <div class="products-slick" data-nav="#slick-nav-2">
                                        <?php foreach ($top_selling_products as $product): ?>
                                            <!-- product -->
                                            <div class="product">
                                                <div class="product-img">
                                                    <img src="<?php echo BASE_URL . htmlspecialchars($product['primary_image']) ?>"
                                                        alt="" />
                                                    <?php if (!empty($product['sale_price'])): ?>
                                                        <div class="product-label">
                                                            <span class="sale">
                                                                <?php echo round((($product['price'] - $product['sale_price']) / $product['price']) * 100) ?>%
                                                            </span>
                                                        </div>
                                                    <?php else: ?>
                                                        <div class="product-label">
                                                            <span class="new">NEW</span>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="product-body">
                                                    <p class="product-category">
                                                        <?php echo htmlspecialchars($product['category_name']) ?>
                                                    </p>
                                                    <h3 class="product-name">
                                                        <a
                                                            href="<?php echo BASE_URL ?>/public/product_detail.php?id=<?php echo $product['product_id']; ?>">
                                                            <?php echo htmlspecialchars($product['product_name']) ?>
                                                        </a>
                                                    </h3>
                                                    <h4 class="product-price">
                                                        <?php if (!empty($product['sale_price'])): ?>
                                                            $<?php echo htmlspecialchars($product['sale_price']) ?>
                                                            <del class="product-old-price">
                                                                $<?php echo htmlspecialchars($product['price']) ?>
                                                            </del>
                                                        <?php else: ?>
                                                            $<?php echo htmlspecialchars($product['price']) ?>
                                                        <?php endif; ?>
                                                    </h4>
                                                    <div class="product-rating">
                                                        <i class="fa fa-star"></i>
                                                        <i class="fa fa-star"></i>
                                                        <i class="fa fa-star"></i>
                                                        <i class="fa fa-star"></i>
                                                        <i class="fa fa-star"></i>
                                                    </div>
                                                </div>
                                                <div class="add-to-cart">
                                                    <button class="add-to-cart-btn"
                                                        data-product-id="<?php echo $product['product_id']; ?>"
                                                        data-product-name="<?php echo htmlspecialchars($product['product_name']); ?>">
                                                        <i class="fa fa-shopping-cart"></i> add to cart
                                                    </button>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                        <!-- /product -->
                                    </div>
                                    <div id="slick-nav-2" class="products-slick-nav"></div>
                                </div>
                                <!-- /tab -->
                            </div>
                        </div>
                    </div>
                    <!-- /Products tab & slick -->
                <?php else: ?>
                    <div class="col-md-12">
                        <div class="row">
                            <div class="products-tabs">
                                <div id="tab2" class="tab-pane fade in active">
                                    <div class="products-slick" data-nav="#slick-nav-2">
                                        <h3>No products found</h3>
                                    </div>
                                    <div id="slick-nav-2" class="products-slick-nav"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            <!-- /row -->
        </div>
        <!-- /container -->
    </div>
    <!-- /SECTION -->

    <!-- FOOTER -->
    <?php include_once '../includes/footer.php'; ?>

    <!-- jQuery Plugins -->
    <script src="<?php echo JS_DIR ?>jquery.min.js"></script>
    <script src="<?php echo JS_DIR ?>bootstrap.min.js"></script>
    <script src="<?php echo JS_DIR ?>slick.min.js"></script>
    <script src="<?php echo JS_DIR ?>nouislider.min.js"></script>
    <script src="<?php echo JS_DIR ?>jquery.zoom.min.js"></script>
    <script src="<?php echo JS_DIR ?>main.js"></script>
    <script src="<?php echo JS_DIR ?>countdown.js"></script>

    <!-- BASE_URL -->
    <script>
        var BASE_URL = '<?php echo BASE_URL ?>';
    </script>

    <!-- Import JS -->
    <script src="<?php echo JS_DIR ?>notifications.js"></script>
    <script src="<?php echo JS_DIR ?>cart-api.js"></script>
    <script src="<?php echo JS_DIR ?>global-cart.js"></script>
</body>

</html>