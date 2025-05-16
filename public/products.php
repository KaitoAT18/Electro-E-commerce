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

// Configuration for pagination
$items_per_page = 6; 

// When the page is loaded for the first time, reset the current page to 1
if (isset($_GET['category']) && !isset($_POST['keep_page'])) {
    $current_page = 1;
} else {
    $current_page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
}

$current_page = max(1, $current_page);
$offset = ($current_page - 1) * $items_per_page;

// Get total number of products for pagination
$sql_count = "SELECT COUNT(*) as total FROM products";
$count_result = getRecords($sql_count, []);
$total_products = $count_result[0]['total'];
$total_pages = ceil($total_products / $items_per_page);

// Get categories with total stock
$sql_categories =
    "SELECT 
        c.category_id,
        c.category_name,
        c.description,
        c.image_path,
        COALESCE(SUM(p.stock), 0) AS total_stock
    FROM 
        categories c
        LEFT JOIN products p ON c.category_id = p.category_id
    GROUP BY 
        c.category_id, c.category_name
    ORDER BY 
        c.category_name ASC;
    ";
$categories = getRecords($sql_categories, []);

$category_filter = '';
$params = [];

if (isset($_GET['category']) && !empty($_GET['category'])) {
    $category_ids = is_array($_GET['category']) ? $_GET['category'] : [$_GET['category']];

    // Validate input
    $category_ids = array_map('intval', $category_ids);
    $placeholders = implode(',', array_fill(0, count($category_ids), '?'));
    $category_filter = " WHERE p.category_id IN ($placeholders) ";
    $params = $category_ids;
    
    // Update SQL to count total products based on filter
    $sql_count = "SELECT COUNT(*) as total FROM products p $category_filter";
    $count_result = getRecords($sql_count, $params);
    $total_products = $count_result[0]['total'];
    $total_pages = ceil($total_products / $items_per_page);
}

// Get products with pagination
$sql_products =
    "SELECT 
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
    $category_filter
    ORDER BY p.product_id DESC
    LIMIT $items_per_page OFFSET $offset
    ";
$products = getRecords($sql_products, $params);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->

    <title>Electro - Products</title>

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
        .loading-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.7);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 100;
        }

        .spinner {
            font-size: 30px;
            color: #D10024;
        }

        .input-checkbox label small {
            color: #888;
            font-weight: normal;
        }
    </style>
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
                    <h3 class="breadcrumb-header">Products</h3>
                    <ul class="breadcrumb-tree">
                        <li><a href="<?php echo BASE_URL ?>/public/index.php">Home</a></li>
                        <li class="active">Products</li>
                    </ul>
                </div>
            </div>
            <!-- /row -->
        </div>
        <!-- /container -->
    </div>
    <!-- /BREADCRUMB -->

    <!-- Cart Notification -->
    <div id="cart-notification" class="cart-notification" style="display:none;">
        <div class="container">
            <div class="alert alert-success">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <i class="fa fa-check-circle"></i> 
                <span id="notification-message"></span>
                <a href="<?php echo BASE_URL ?>/public/cart.php" class="alert-link">View Cart</a>
            </div>
        </div>
    </div>

    <!-- SECTION -->
    <div class="section">
        <!-- container -->
        <div class="container">
            <div class="row">
                <?php if (!empty($msg) && !empty($msgType)) {
                    echo '<div class="alert alert-' . htmlspecialchars($msgType) . '">';
                    echo '<p>' . htmlspecialchars($msg) . '</p>';
                    echo '</div>';
                }
                ?>
            </div>
            <!-- row -->
            <div class="row">
                <!-- ASIDE -->
                <div id="aside" class="col-md-3">
                    <!-- aside Widget -->
                    <div class="aside">
                        <h3 class="aside-title">Categories</h3>
                        <?php if (!empty($categories)): ?>
                            <div class="checkbox-filter">
                                <form id="category-filter-form" method="GET" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                                    <input type="hidden" name="page" value="1">
                                    
                                    <?php 
                                    // Lấy danh sách các category được chọn
                                    $selected_categories = [];
                                    if (isset($_GET['category'])) {
                                        $selected_categories = is_array($_GET['category']) ? 
                                            array_map('intval', $_GET['category']) : 
                                            [intval($_GET['category'])];
                                    }
                                    
                                    foreach ($categories as $category): 
                                        // Chuyển đổi category_id sang integer để so sánh an toàn
                                        $category_id = (int)$category['category_id'];
                                    ?>
                                        <div class="input-checkbox">
                                            <input type="checkbox" 
                                                name="category[]"
                                                value="<?php echo $category_id; ?>"
                                                id="category-<?php echo $category_id; ?>"
                                                <?php echo in_array($category_id, $selected_categories) ? 'checked' : ''; ?>>
                                            <label for="category-<?php echo $category_id; ?>">
                                                <span></span>
                                                <?php echo htmlspecialchars($category['category_name']); ?>
                                                <small>(<?php echo (int)$category['total_stock']; ?> items in stock)</small>
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                    
                                    <?php if (!empty($selected_categories)): ?>
                                        <div class="text-center" style="margin-top: 15px;">
                                            <a href="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="btn btn-sm btn-default">
                                                <i class="fa fa-times"></i> Clear Filters
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </form>
                            </div>
                        <?php else: ?>
                            <p class="text-muted">No categories available.</p>
                        <?php endif; ?>
                    </div>
                    <!-- /aside Widget -->
                </div>
                <!-- /ASIDE -->

                <!-- STORE -->
                <div id="store" class="col-md-9">
                    <!-- store products -->
                    <div class="row">
                        <?php if (!empty($products)): ?>
                            <?php foreach ($products as $product): ?>
                                <!-- product -->
                                <div class="col-md-4 col-xs-6">
                                    <div class="product">
                                        <div class="product-img">
                                            <img src="<?php echo BASE_URL . htmlspecialchars($product['primary_image']) ?>"
                                                alt="">
                                            <?php if (!empty($product['sale_price'])): ?>
                                                <div class="product-label">
                                                    <span
                                                        class="sale"><?php echo round((($product['price'] - $product['sale_price']) / $product['price']) * 100) ?>%
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
                                                    <?php echo htmlspecialchars($product['product_name']); ?>
                                                </a>
                                            </h3>
                                            <?php if (!empty($product['sale_price'])): ?>
                                                <h4 class="product-price">
                                                    $<?php echo htmlspecialchars($product['sale_price']) ?>
                                                    <del
                                                        class="product-old-price">$<?php echo htmlspecialchars($product['price']) ?></del>
                                                </h4>
                                            <?php else: ?>
                                                <h4 class="product-price">
                                                    $<?php echo htmlspecialchars($product['price']) ?>
                                                </h4>
                                            <?php endif; ?>
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
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="col-md-12">
                                <h3>No products available.</h3>
                            </div>
                        <?php endif; ?>
                        <!-- /product -->
                    </div>
                    <!-- /store products -->

                    <!-- store bottom filter -->
                    <div class="store-filter clearfix">
                        <span class="store-qty">Showing
                            <?php echo $offset + 1; ?>-<?php echo min($offset + count($products), $total_products); ?>
                            of <?php echo $total_products; ?> products</span>
                        <?php if ($total_pages > 1): ?>
                            <ul class="store-pagination">
                                <?php if ($current_page > 1): ?>
                                    <li><a href="<?php echo buildPaginationUrl($current_page - 1); ?>"><i
                                                class="fa fa-angle-left"></i></a></li>
                                <?php endif; ?>

                                <?php
                                // Hiển thị tối đa 5 trang
                                $start_page = max(1, min($current_page - 2, $total_pages - 4));
                                $end_page = min($start_page + 4, $total_pages);

                                for ($i = $start_page; $i <= $end_page; $i++): ?>
                                    <li <?php echo ($i == $current_page) ? 'class="active"' : ''; ?>>
                                        <a href="<?php echo buildPaginationUrl($i); ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>

                                <?php if ($current_page < $total_pages): ?>
                                    <li><a href="<?php echo buildPaginationUrl($current_page + 1); ?>"><i
                                                class="fa fa-angle-right"></i></a></li>
                                <?php endif; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                    <!-- /store bottom filter -->
                </div>
                <!-- /STORE -->
            </div>
            <!-- /row -->
        </div>
        <!-- /container -->
    </div>
    <!-- /SECTION -->

    <!-- FOOTER -->
    <?php include_once '../includes/footer.php'; ?>
    <!-- /FOOTER -->

    <!-- jQuery Plugins -->
    <script src="<?php echo JS_DIR ?>jquery.min.js"></script>
    <script src="<?php echo JS_DIR ?>bootstrap.min.js"></script>
    <script src="<?php echo JS_DIR ?>slick.min.js"></script>
    <script src="<?php echo JS_DIR ?>nouislider.min.js"></script>
    <script src="<?php echo JS_DIR ?>jquery.zoom.min.js"></script>
    <script src="<?php echo JS_DIR ?>main.js"></script>
    <script>
    $(document).ready(function() {
        // Submit form khi checkbox thay đổi
        $('input[name="category[]"]').change(function() {
            // Hiển thị loading indicator
            $('#store').append('<div class="loading-overlay"><div class="spinner"><i class="fa fa-spinner fa-spin"></i></div></div>');
            
            // Reset về trang 1 khi thay đổi filter
            $('input[name="page"]').val(1);
            
            // Submit form
            $('#category-filter-form').submit();
        });
    });
    </script> 

    <!-- Add-to-cart JS -->
    <script>
    $(document).ready(function() {
        // Xử lý thêm sản phẩm vào giỏ hàng từ danh sách
        $('.add-to-cart-btn').click(function() {
            const productId = $(this).data('product-id');
            const productName = $(this).data('product-name');
            const button = $(this);
            
            // Thay đổi giao diện nút
            button.html('<i class="fa fa-spinner fa-spin"></i> Adding...');
            button.prop('disabled', true);
            
            $.ajax({
                url: '<?php echo BASE_URL ?>/public/api/cart.php',
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({
                    action: 'add',
                    product_id: productId,
                    quantity: 1
                }),
                success: function(response) {
                    // Hiển thị thông báo thành công
                    $('#notification-message').text(`${productName} added to cart!`);
                    $('#cart-notification').slideDown();
                    
                    // Tự động ẩn thông báo sau 5 giây
                    setTimeout(function() {
                        $('#cart-notification').slideUp();
                    }, 5000);
                    
                    // Cập nhật giỏ hàng trên header
                    if (typeof window.updateHeaderCart === 'function') {
                        window.updateHeaderCart();
                    }
                    
                    // Khôi phục nút
                    button.html('<i class="fa fa-shopping-cart"></i> add to cart');
                    button.prop('disabled', false);
                },
                error: function(xhr) {
                    // Hiển thị thông báo lỗi
                    alert('Failed to add product: ' + (xhr.responseJSON?.message || 'Unknown error'));
                    
                    // Khôi phục nút
                    button.html('<i class="fa fa-shopping-cart"></i> add to cart');
                    button.prop('disabled', false);
                }
            });
        });
        
        // Khi người dùng nhấn vào nút close thông báo
        $(document).on('click', '.cart-notification .close', function() {
            $('#cart-notification').slideUp();
        });
    });
    </script>

    <div id="cart-notification" class="cart-notification" style="display:none;">
        <div class="container">
            <div class="alert">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <i class="fa fa-check-circle"></i>
                <span id="notification-message"></span>
            </div>
        </div>
    </div>

    <script>
        var BASE_URL = '<?php echo BASE_URL ?>';
    </script>

    <!-- Import JS -->
    <script src="<?php echo JS_DIR ?>notifications.js"></script>
    <script src="<?php echo JS_DIR ?>cart-api.js"></script>
    <script src="<?php echo JS_DIR ?>global-cart.js"></script>

</body>

</html>