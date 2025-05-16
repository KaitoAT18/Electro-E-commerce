<?php
// Load the configuration file
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$items_per_page = 6;
$current_page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$current_page = max(1, $current_page);
$offset = ($current_page - 1) * $items_per_page;

$searchResults = [];
$total_products = 0;
$total_pages = 1;
$searchQuery = '';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $filteredData = filter();
    $searchQuery = trim($filteredData['q'] ?? '');

    if (!empty($searchQuery)) {
        // Query to count total products matching the search query
        $count_sql = "SELECT COUNT(*) as total 
                     FROM products p
                     JOIN categories c ON p.category_id = c.category_id
                     WHERE p.product_name LIKE ? OR c.category_name LIKE ?";
        $searchTerm = '%' . $searchQuery . '%';
        $count_result = getRecords($count_sql, [$searchTerm, $searchTerm]);
        $total_products = $count_result[0]['total'];
        $total_pages = ceil($total_products / $items_per_page);

        // Pagination 
        $sql = "SELECT 
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
        WHERE 
            p.product_name LIKE ? 
            OR
            c.category_name LIKE ?
        ORDER BY p.product_id ASC
        LIMIT $items_per_page OFFSET $offset";

        $searchResults = getRecords($sql, [$searchTerm, $searchTerm]);
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

    <title>Electro - Search Results for "<?php echo htmlspecialchars($searchQuery); ?>"</title>

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
                    <h3 class="breadcrumb-header">Search</h3>
                    <ul class="breadcrumb-tree">
                        <li><a href="<?php echo BASE_URL ?>/public/index.php">Home</a></li>
                        <li class="active">Search</li>
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
                <!-- STORE -->
                <div id="store" class="col-md-12">
                    <!-- store products -->
                    <div class="row">
                        <?php if (!empty($searchResults)): ?>
                            <?php foreach ($searchResults as $product): ?>
                                <!-- product -->
                                <div class="col-md-4 col-xs-6">
                                    <div class="product">
                                        <div class="product-img">
                                            <img src="<?php echo BASE_URL . htmlspecialchars($product['primary_image']) ?>"
                                                alt="">
                                            <div class="product-label">
                                                <?php if (!empty($product['sale_price'])): ?>
                                                    <span
                                                        class="sale">-<?= round((($product['price'] - $product['sale_price']) / $product['price']) * 100) ?>%</span>
                                                <?php else: ?>
                                                    <span class="new">NEW</span>
                                                <?php endif; ?>
                                            </div>
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
                                                    <del
                                                        class="product-old-price">$<?php echo htmlspecialchars($product['price']) ?></del>
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
                                            <button class="add-to-cart-btn"><i class="fa fa-shopping-cart"></i> add to
                                                cart</button>
                                        </div>
                                    </div>
                                </div>
                                <!-- /product -->
                            <?php endforeach; ?>
                        <?php elseif (!empty($searchQuery)): ?>
                            <div class="col-md-12 text-center">
                                <h3>No products found matching "<?php echo htmlspecialchars($searchQuery); ?>"</h3>
                                <p>Please try a different search term or browse our categories.</p>
                            </div>
                        <?php else: ?>
                            <div class="col-md-12 text-center">
                                <h3>Please enter a search term</h3>
                            </div>
                        <?php endif; ?>
                    </div>
                    <!-- /store products -->

                    <!-- store bottom filter -->
                    <div class="store-filter clearfix">
                        <?php if (!empty($searchResults)): ?>
                            <span class="store-qty">
                                Showing
                                <?php echo $offset + 1; ?>-<?php echo min($offset + count($searchResults), $total_products); ?>
                                of <?php echo $total_products; ?> products
                            </span>

                            <?php if ($total_pages > 1): ?>
                                <ul class="store-pagination">
                                    <?php if ($current_page > 1): ?>
                                        <li><a href="<?php echo buildPaginationUrl($current_page - 1); ?>"><i
                                                    class="fa fa-angle-left"></i></a></li>
                                    <?php endif; ?>

                                    <?php
                                    // Display page numbers with a maximum of 5 pages
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
    <?php require_once("../includes/footer.php") ?>

    <!-- jQuery Plugins -->
    <script src="<?php echo JS_DIR ?>jquery.min.js"></script>
    <script src="<?php echo JS_DIR ?>bootstrap.min.js"></script>
    <script src="<?php echo JS_DIR ?>slick.min.js"></script>
    <script src="<?php echo JS_DIR ?>nouislider.min.js"></script>
    <script src="<?php echo JS_DIR ?>jquery.zoom.min.js"></script>
    <script src="<?php echo JS_DIR ?>main.js"></script>
</body>

</html>