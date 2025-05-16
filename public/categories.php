<?php
// Load the configuration file
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

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
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Categories - Electro</title>

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
</head>
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
                <h3 class="breadcrumb-header">Categories</h3>
                <ul class="breadcrumb-tree">
                    <li><a href="<?php echo BASE_URL ?>/public/index.php">Home</a></li>
                    <li class="active">Categories</li>
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
            <!-- Featured Categories -->
            <div class="col-md-12">
                <div class="section-title">
                    <h3 class="title">Featured Categories</h3>
                </div>
            </div>

            <?php if (!empty($categories)): ?>
                <?php foreach ($categories as $category): ?>
                    <!-- Category -->
                    <div class="col-md-4 col-sm-6">
                        <div class="category-card">
                            <div class="category-img">
                                <img src="<?php echo BASE_URL . htmlspecialchars($category['image_path']) ?>" alt="" />
                                <div class="category-overlay"></div>
                            </div>
                            <div class="category-body">
                                <h3 class="category-name"><?= htmlspecialchars($category['category_name']) ?></h3>
                                <p class="category-count">
                                    <?= htmlspecialchars($category['total_stock']) ?> Products Available
                                </p>
                                <div class="category-description">
                                    <p><?= htmlspecialchars($category['description']) ?></p>
                                </div>
                                <a href="<?php echo BASE_URL . '/public/products.php?category%5B%5D=' . htmlspecialchars($category['category_id']) ?>"
                                    class="category-btn">Shop Now</a>
                            </div>
                        </div>
                    </div>
                    <!-- /Category -->
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-md-12">
                    <h3>No categories available.</h3>
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
<!-- /FOOTER -->

<!-- jQuery Plugins -->
<script src="<?php echo JS_DIR ?>jquery.min.js"></script>
<script src="<?php echo JS_DIR ?>bootstrap.min.js"></script>
<script src="<?php echo JS_DIR ?>slick.min.js"></script>
<script src="<?php echo JS_DIR ?>nouislider.min.js"></script>
<script src="<?php echo JS_DIR ?>jquery.zoom.min.js"></script>
<script src="<?php echo JS_DIR ?>main.js"></script>
</body>

</html>