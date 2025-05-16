<?php
// Load the configuration file
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>About Us - Electro</title>

    <!-- Google font -->
    <link href="https://fonts.googleapis.com/css?family=Montserrat:400,500,700" rel="stylesheet" />

    <!-- Bootstrap -->
    <link type="text/css" rel="stylesheet" href="<?php echo CSS_DIR ?>bootstrap.min.css" />

    <!-- Font Awesome Icon -->
    <link rel="stylesheet" href="<?php echo CSS_DIR ?>font-awesome.min.css" />

    <!-- Custom stlylesheet -->
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
                    <h3 class="breadcrumb-header">About Us</h3>
                    <ul class="breadcrumb-tree">
                        <li><a href="<?php echo BASE_URL ?>/public/index.php">Home</a></li>
                        <li class="active">About Us</li>
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
            <!-- Store Information Section -->
            <div class="row">
                <div class="col-md-6">
                    <div class="about-content">
                        <h2 class="about-title">
                            Welcome to Electro Store
                        </h2>
                        <p>
                            Electro is an e-commerce platform
                            specializing in high-quality electronics, including laptops, smartphones, tablets,
                            and tech accessories like headphones.
                        </p>

                        <div class="store-info">
                            <div class="info-item">
                                <i class="fa fa-map-marker"></i>
                                <div class="info-content">
                                    <h4>Store Location</h4>
                                    <p>1734 Stonecoal Road, New York</p>
                                </div>
                            </div>

                            <div class="info-item">
                                <i class="fa fa-clock-o"></i>
                                <div class="info-content">
                                    <h4>Working Hours</h4>
                                    <p>
                                        Monday - Friday: 9:00 AM - 9:00 PM
                                    </p>
                                    <p>
                                        Saturday - Sunday: 9:00 AM - 5:00 PM
                                    </p>
                                </div>
                            </div>

                            <div class="info-item">
                                <i class="fa fa-phone"></i>
                                <div class="info-content">
                                    <h4>Contact Us</h4>
                                    <p>Hotline: +021-95-51-84</p>
                                    <p>Email: support@electro.com</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <h3 class="sub-title">Video</h3>
                    <div class="about-video">
                        <div class="video-wrapper">
                            <iframe width="100%" height="315" src="https://www.youtube.com/embed/your-video-id"
                                title="Store Introduction" frameborder="0"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                allowfullscreen>
                            </iframe>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Google Map Section -->
            <div class="row mt-50">
                <div class="col-md-12">
                    <div class="map-container">
                        <h3 class="sub-title">Find Us on Map</h3>
                        <div class="google-map">
                            <iframe src="https://www.google.com/maps/embed?pb=" width="100%" height="450"
                                style="border: 0" allowfullscreen="" loading="lazy"
                                referrerpolicy="no-referrer-when-downgrade">
                            </iframe>
                        </div>
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
</body>

</html>