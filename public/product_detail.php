<?php
// Load the configuration file
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $filteredData = filter();
    $id = intval($filteredData['id'] ?? 0);
    if ($id > 0) {
        // Fetch product details from the database
        $sql = "SELECT 
                    p.product_id,
                    p.product_name,
                    p.description,
                    p.price,
                    p.sale_price,
                    p.stock,
                    c.category_id,
                    c.category_name,
                    pi.image_id,
                    pi.image_path,
                    pi.is_primary
                FROM 
                    products p
                    JOIN categories c ON p.category_id = c.category_id
                    LEFT JOIN product_images pi ON p.product_id = pi.product_id
                WHERE 
                    p.product_id = ?";
        $results = getRecords($sql, [$id]);

        if ($results) {
            $product = [
                'product_id' => $results[0]['product_id'],
                'product_name' => $results[0]['product_name'],
                'description' => $results[0]['description'],
                'price' => $results[0]['price'],
                'sale_price' => $results[0]['sale_price'],
                'stock' => $results[0]['stock'],
                'category_id' => $results[0]['category_id'],
                'category_name' => $results[0]['category_name'],
                'images' => []
            ];

            $product['primary_image'] = null;

            foreach ($results as $row) {
                $image = [
                    'image_id' => $row['image_id'],
                    'image_path' => $row['image_path'],
                    'is_primary' => $row['is_primary']
                ];

                $product['images'][] = $image;

                if ($row['is_primary'] == 1) {
                    $product['primary_image'] = $image;
                }
            }
        }
    } else {
        // Notify the user that the product ID is invalid
        setFlashData('msg', 'Invalid product ID');
        setFlashData('msg_type', 'danger');
        // Redirect to the products page if no product ID is provided
        header("Location: " . BASE_URL . "/public/products.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->

    <title>Electro - Product</title>

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

    <!-- Custom cart CSS -->
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
        }

        .cart-notification .close {
            margin-top: -2px;
        }

        .cart-notification .alert-link {
            font-weight: bold;
            color: #D10024;
            margin-left: 5px;
        }

        .cart-notification .alert-link:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>
    <!-- HEADER -->
    <?php include_once '../includes/header.php'; ?>

    <!-- BREADCRUMB -->
    <div id="breadcrumb" class="section">
        <!-- container -->
        <div class="container">
            <!-- row -->
            <div class="row">
                <div class="col-md-12">
                    <h3 class="breadcrumb-header">Product Detail</h3>
                    <ul class="breadcrumb-tree">
                        <li><a href="<?php echo BASE_URL ?>/public/index.php">Home</a></li>
                        <li class="active">Product Detail</li>
                    </ul>
                </div>
            </div>
            <!-- /row -->
        </div>
        <!-- /container -->
    </div>
    <!-- /BREADCRUMB -->

    <!-- Thêm div này vào ngay sau phần breadcrumb -->
    <div id="cart-notification" class="cart-notification" style="display:none;">
        <div class="container">
            <div class="alert alert-success">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <i class="fa fa-check-circle"></i>
                <span id="notification-message"></span>
            </div>
        </div>
    </div>

    <!-- SECTION -->
    <div class="section">
        <!-- container -->
        <div class="container">
            <!-- row -->
            <div class="row">
                <?php if (!empty($product)): ?>
                    <!-- Product main img -->
                    <?php if (!empty($product['primary_image'])): ?>
                        <div class="col-md-5 col-md-push-2">
                            <div id="product-main-img">
                                <div class="product-preview">
                                    <img src="<?php echo BASE_URL . $product['primary_image']['image_path'] ?>" alt="" />
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="col-md-5 col-md-push-2">
                            <div id="product-main-img">
                                <div class="product-preview">
                                    <img src="<?php echo IMAGE_DIR ?>product05.png" alt="" />
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                    <!-- /Product main img -->

                    <!-- Product thumb imgs -->
                    <?php if (!empty($product['images'])): ?>
                        <div class="col-md-2 col-md-pull-5">
                            <div id="product-imgs">
                                <?php foreach ($product['images'] as $image): ?>
                                    <?php if ($image['is_primary'] != 1): ?>
                                        <div class="product-preview">
                                            <img src="<?php echo BASE_URL . $image['image_path'] ?>" alt="" />
                                        </div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="col-md-2 col-md-pull-5">
                            <div id="product-imgs">
                                <div class="product-preview">
                                    <img src="<?php echo IMAGE_DIR ?>product05.png" alt="" />
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                    <!-- /Product thumb imgs -->

                    <!-- Product details -->
                    <div class="col-md-5">
                        <div class="product-details">
                            <h2 class="product-name"><?php echo htmlspecialchars($product['product_name']) ?></h2>
                            <div>
                                <div class="product-rating">
                                    <i class="fa fa-star"></i>
                                    <i class="fa fa-star"></i>
                                    <i class="fa fa-star"></i>
                                    <i class="fa fa-star"></i>
                                    <i class="fa fa-star"></i>
                                </div>
                            </div>
                            <div>
                                <?php if (!empty($product['sale_price'])): ?>
                                    <h3 class="product-price">
                                        $<?php echo htmlspecialchars($product['sale_price']) ?>
                                        <del class="product-old-price">$<?php echo htmlspecialchars($product['price']) ?></del>
                                    </h3>
                                <?php else: ?>
                                    <h3 class="product-price">
                                        $<?php echo htmlspecialchars($product['price']) ?>
                                    </h3>
                                <?php endif; ?>
                                <?php if ($product['stock'] > 0): ?>
                                    <span class="product-available">In Stock</span>
                                <?php else: ?>
                                    <span class="product-available">Out of Stock</span>
                                <?php endif; ?>
                            </div>
                            <?php if (!empty($product['description'])): ?>
                                <p><?php echo htmlspecialchars($product['description']) ?></p>
                            <?php else: ?>
                                <p>No description available.</p>
                            <?php endif; ?>

                            <!-- Add to cart -->
                            <div class="add-to-cart">
                                <div class="qty-label">
                                    Qty
                                    <div class="input-number">
                                        <input type="number" id="product-quantity" value="1" min="1"
                                            max="<?php echo $product['stock']; ?>" />
                                        <span class="qty-up">+</span>
                                        <span class="qty-down">-</span>
                                    </div>
                                </div>
                                <button id="add-to-cart-btn" class="add-to-cart-btn">
                                    <i class="fa fa-shopping-cart"></i> add to cart
                                </button>
                            </div>
                            <!-- /Add to cart -->

                            <?php if (!empty($product['category_name'])): ?>
                                <ul class="product-links">
                                    <li>Category:</li>
                                    <li><a
                                            href="<?php echo BASE_URL . '/public/products.php?category[]=' . $product['category_id'] ?>"><?php echo htmlspecialchars($product['category_name']) ?></a>
                                    </li>
                                </ul>
                            <?php endif; ?>

                            <ul class="product-links">
                                <li>Share:</li>
                                <li>
                                    <a href="#"><i class="fa fa-facebook"></i></a>
                                </li>
                                <li>
                                    <a href="#"><i class="fa fa-twitter"></i></a>
                                </li>
                                <li>
                                    <a href="#"><i class="fa fa-google-plus"></i></a>
                                </li>
                                <li>
                                    <a href="#"><i class="fa fa-envelope"></i></a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <!-- /Product details -->

                    <!-- Product tab -->
                    <div class="col-md-12">
                        <div id="product-tab">
                            <!-- product tab nav -->
                            <ul class="tab-nav">
                                <li class="active">
                                    <a data-toggle="tab" href="#tab1">Description</a>
                                </li>
                                <li>
                                    <a data-toggle="tab" href="#tab2">Details</a>
                                </li>
                            </ul>
                            <!-- /product tab nav -->

                            <!-- product tab content -->
                            <div class="tab-content">
                                <!-- tab1  -->
                                <div id="tab1" class="tab-pane fade in active">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <?php if (!empty($product['description'])): ?>
                                                <p><?php echo htmlspecialchars($product['description']) ?></p>
                                            <?php else: ?>
                                                <p>No description available.</p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <!-- /tab1  -->

                                <!-- tab2  -->
                                <div id="tab2" class="tab-pane fade in">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <?php if (!empty($product['description'])): ?>
                                                <p><?php echo htmlspecialchars($product['description']) ?></p>
                                            <?php else: ?>
                                                <p>No description available.</p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <!-- /tab2  -->

                                <!-- tab3  -->
                                <div id="tab3" class="tab-pane fade in">
                                    <div class="row">
                                        <!-- Rating -->
                                        <div class="col-md-3">
                                            <div id="rating">
                                                <div class="rating-avg">
                                                    <span>4.5</span>
                                                    <div class="rating-stars">
                                                        <i class="fa fa-star"></i>
                                                        <i class="fa fa-star"></i>
                                                        <i class="fa fa-star"></i>
                                                        <i class="fa fa-star"></i>
                                                        <i class="fa fa-star-o"></i>
                                                    </div>
                                                </div>
                                                <ul class="rating">
                                                    <li>
                                                        <div class="rating-stars">
                                                            <i class="fa fa-star"></i>
                                                            <i class="fa fa-star"></i>
                                                            <i class="fa fa-star"></i>
                                                            <i class="fa fa-star"></i>
                                                            <i class="fa fa-star"></i>
                                                        </div>
                                                        <div class="rating-progress">
                                                            <div style="
                                                                    width: 80%;
                                                                "></div>
                                                        </div>
                                                        <span class="sum">3</span>
                                                    </li>
                                                    <li>
                                                        <div class="rating-stars">
                                                            <i class="fa fa-star"></i>
                                                            <i class="fa fa-star"></i>
                                                            <i class="fa fa-star"></i>
                                                            <i class="fa fa-star"></i>
                                                            <i class="fa fa-star-o"></i>
                                                        </div>
                                                        <div class="rating-progress">
                                                            <div style="
                                                                    width: 60%;
                                                                "></div>
                                                        </div>
                                                        <span class="sum">2</span>
                                                    </li>
                                                    <li>
                                                        <div class="rating-stars">
                                                            <i class="fa fa-star"></i>
                                                            <i class="fa fa-star"></i>
                                                            <i class="fa fa-star"></i>
                                                            <i class="fa fa-star-o"></i>
                                                            <i class="fa fa-star-o"></i>
                                                        </div>
                                                        <div class="rating-progress">
                                                            <div></div>
                                                        </div>
                                                        <span class="sum">0</span>
                                                    </li>
                                                    <li>
                                                        <div class="rating-stars">
                                                            <i class="fa fa-star"></i>
                                                            <i class="fa fa-star"></i>
                                                            <i class="fa fa-star-o"></i>
                                                            <i class="fa fa-star-o"></i>
                                                            <i class="fa fa-star-o"></i>
                                                        </div>
                                                        <div class="rating-progress">
                                                            <div></div>
                                                        </div>
                                                        <span class="sum">0</span>
                                                    </li>
                                                    <li>
                                                        <div class="rating-stars">
                                                            <i class="fa fa-star"></i>
                                                            <i class="fa fa-star-o"></i>
                                                            <i class="fa fa-star-o"></i>
                                                            <i class="fa fa-star-o"></i>
                                                            <i class="fa fa-star-o"></i>
                                                        </div>
                                                        <div class="rating-progress">
                                                            <div></div>
                                                        </div>
                                                        <span class="sum">0</span>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                        <!-- /Rating -->

                                        <!-- Reviews -->
                                        <div class="col-md-6">
                                            <div id="reviews">
                                                <ul class="reviews">
                                                    <li>
                                                        <div class="review-heading">
                                                            <h5 class="name">
                                                                John
                                                            </h5>
                                                            <p class="date">
                                                                27 DEC 2018, 8:0
                                                                PM
                                                            </p>
                                                            <div class="review-rating">
                                                                <i class="fa fa-star"></i>
                                                                <i class="fa fa-star"></i>
                                                                <i class="fa fa-star"></i>
                                                                <i class="fa fa-star"></i>
                                                                <i class="fa fa-star-o empty"></i>
                                                            </div>
                                                        </div>
                                                        <div class="review-body">
                                                            <p>
                                                                Lorem ipsum
                                                                dolor sit amet,
                                                                consectetur
                                                                adipisicing
                                                                elit, sed do
                                                                eiusmod tempor
                                                                incididunt ut
                                                                labore et dolore
                                                                magna aliqua
                                                            </p>
                                                        </div>
                                                    </li>
                                                    <li>
                                                        <div class="review-heading">
                                                            <h5 class="name">
                                                                John
                                                            </h5>
                                                            <p class="date">
                                                                27 DEC 2018,
                                                                8:00 PM
                                                            </p>
                                                            <div class="review-rating">
                                                                <i class="fa fa-star"></i>
                                                                <i class="fa fa-star"></i>
                                                                <i class="fa fa-star"></i>
                                                                <i class="fa fa-star"></i>
                                                                <i class="fa fa-star-o empty"></i>
                                                            </div>
                                                        </div>
                                                        <div class="review-body">
                                                            <p>
                                                                Lorem ipsum
                                                                dolor sit amet,
                                                                consectetur
                                                                adipisicing
                                                                elit, sed do
                                                                eiusmod tempor
                                                                incididunt ut
                                                                labore et dolore
                                                                magna aliqua
                                                            </p>
                                                        </div>
                                                    </li>
                                                    <li>
                                                        <div class="review-heading">
                                                            <h5 class="name">
                                                                John
                                                            </h5>
                                                            <p class="date">
                                                                27 DEC 2018, 8:0
                                                                PM
                                                            </p>
                                                            <div class="review-rating">
                                                                <i class="fa fa-star"></i>
                                                                <i class="fa fa-star"></i>
                                                                <i class="fa fa-star"></i>
                                                                <i class="fa fa-star"></i>
                                                                <i class="fa fa-star-o empty"></i>
                                                            </div>
                                                        </div>
                                                        <div class="review-body">
                                                            <p>
                                                                Lorem ipsum
                                                                dolor sit amet,
                                                                consectetur
                                                                adipisicing
                                                                elit, sed do
                                                                eiusmod tempor
                                                                incididunt ut
                                                                labore et dolore
                                                                magna aliqua
                                                            </p>
                                                        </div>
                                                    </li>
                                                </ul>
                                                <ul class="reviews-pagination">
                                                    <li class="active">1</li>
                                                    <li><a href="#">2</a></li>
                                                    <li><a href="#">3</a></li>
                                                    <li><a href="#">4</a></li>
                                                    <li>
                                                        <a href="#"><i class="fa fa-angle-right"></i></a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                        <!-- /Reviews -->

                                        <!-- Review Form -->
                                        <div class="col-md-3">
                                            <div id="review-form">
                                                <form class="review-form">
                                                    <input class="input" type="text" placeholder="Your Name" />
                                                    <input class="input" type="email" placeholder="Your Email" />
                                                    <textarea class="input" placeholder="Your Review"></textarea>
                                                    <div class="input-rating">
                                                        <span>Your Rating:
                                                        </span>
                                                        <div class="stars">
                                                            <input id="star5" name="rating" value="5" type="radio" /><label
                                                                for="star5"></label>
                                                            <input id="star4" name="rating" value="4" type="radio" /><label
                                                                for="star4"></label>
                                                            <input id="star3" name="rating" value="3" type="radio" /><label
                                                                for="star3"></label>
                                                            <input id="star2" name="rating" value="2" type="radio" /><label
                                                                for="star2"></label>
                                                            <input id="star1" name="rating" value="1" type="radio" /><label
                                                                for="star1"></label>
                                                        </div>
                                                    </div>
                                                    <button class="primary-btn">
                                                        Submit
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                        <!-- /Review Form -->
                                    </div>
                                </div>
                                <!-- /tab3  -->
                            </div>
                            <!-- /product tab content  -->
                        </div>
                    </div>
                    <!-- /product tab -->
                </div>
                <!-- /row -->
            </div>
        <?php else: ?>
            <div class="col-md-12">
                <h3 class="text-center">Product not found</h3>
            </div>
        <?php endif; ?>
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
    <script>
        $(document).ready(function () {
            // Khi người dùng nhấn vào nút close
            $(document).on('click', '.cart-notification .close', function () {
                $('#cart-notification').slideUp();
            });
        });
    </script>
    <script>
        var BASE_URL = '<?php echo BASE_URL ?>';
    </script>

    <!-- Import các file JS -->
    <script src="<?php echo JS_DIR ?>notifications.js"></script>
    <script src="<?php echo JS_DIR ?>cart-api.js"></script>
    <script src="<?php echo JS_DIR ?>global-cart.js"></script>
    <script>
        $(document).ready(function () {
            // Xử lý thêm sản phẩm vào giỏ hàng
            $('#add-to-cart-btn').click(function (e) {
                e.preventDefault();

                const productId = <?php echo $product['product_id']; ?>;
                const productName = "<?php echo addslashes($product['product_name']); ?>";
                const quantity = parseInt($('#product-quantity').val());

                if (isNaN(quantity) || quantity <= 0) {
                    Notifications.show('Please select a valid quantity', 'error');
                    return;
                }

                $(this).prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Adding...');

                CartAPI.addToCart(productId, quantity)
                    .done(function (response) {
                        // Cập nhật giỏ hàng
                        if (typeof window.updateHeaderCart === 'function') {
                            window.updateHeaderCart();
                        }

                        // Hiển thị thông báo
                        Notifications.show(productName + ' added to cart!', 'success');
                    })
                    .fail(function (xhr) {
                        Notifications.show('Failed to add product: ' + (xhr.responseJSON?.message || 'Unknown error'), 'error');
                    })
                    .always(function () {
                        $('#add-to-cart-btn').prop('disabled', false).html('<i class="fa fa-shopping-cart"></i> Add to Cart');
                    });
            });
        });
    </script>
</body>

</html>