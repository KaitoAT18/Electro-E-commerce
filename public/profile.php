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
if (!check_login()) {
    setFlashData('msg', 'You need to login to access this page.');
    setFlashData('msg_type', 'danger');
    // Redirect to login page
    header('Location: ' . BASE_URL . '/public/login.php');
    exit;
}

// Get user ID
$userId = getUserIdFromJwt();

// Generate CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = generate_csrf_token();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>My Profile - Electro</title>

    <!-- Google font -->
    <link href="https://fonts.googleapis.com/css?family=Montserrat:400,500,700" rel="stylesheet" />

    <!-- Bootstrap -->
    <link type="text/css" rel="stylesheet" href="<?php echo CSS_DIR ?>bootstrap.min.css" />

    <!-- Font Awesome Icon -->
    <link rel="stylesheet" href="<?php echo CSS_DIR ?>font-awesome.min.css" />

    <!-- Custom stylesheet -->
    <link type="text/css" rel="stylesheet" href="<?php echo CSS_DIR ?>styles.css" />

    <!-- Profile CSS -->
    <link type="text/css" rel="stylesheet" href="<?php echo CSS_DIR ?>profile.css" />
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
                    <h3 class="breadcrumb-header">My Account</h3>
                    <ul class="breadcrumb-tree">
                        <li><a href="<?php echo BASE_URL ?>/public/index.php">Home</a></li>
                        <li class="active">My Account</li>
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
            <div class="row">
                <!-- Profile Sidebar -->
                <div class="col-md-3">
                    <div class="profile-sidebar">
                        <div class="profile-userpic text-center">
                            <img src="<?php echo BASE_URL ?>/assets/images/local/avatar_default.png"
                                class="img-responsive" alt="" />
                            <button class="primary-btn change-photo-btn">
                                Change Photo
                            </button>
                        </div>
                        <div class="profile-usertitle">
                            <div class="profile-usertitle-name">
                                User
                            </div>
                        </div>
                        <div class="profile-usermenu">
                            <ul class="nav">
                                <li class="active">
                                    <a href="#profile" data-toggle="tab">
                                        <i class="fa fa-user"></i> Profile
                                    </a>
                                </li>
                                <li>
                                    <a href="#orders" data-toggle="tab">
                                        <i class="fa fa-shopping-cart"></i>
                                        My Orders
                                    </a>
                                </li>
                                <li>
                                    <a href="#settings" data-toggle="tab">
                                        <i class="fa fa-cog"></i> Settings
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Profile Content -->
                <div class="col-md-9">
                    <div class="tab-content">
                        <!-- Profile Tab -->
                        <div class="tab-pane fade in active" id="profile">
                            <div class="billing-details">
                                <div class="section-title">
                                    <h3 class="title">
                                        Profile Information
                                    </h3>
                                </div>
                                <form id="profile-form">
                                    <div class="form-group">
                                        <input class="input" type="text" name="first-name" placeholder="First Name"
                                            required />
                                    </div>
                                    <div class="form-group">
                                        <input class="input" type="text" name="last-name" placeholder="Last Name"
                                            required />
                                    </div>
                                    <div class="form-group">
                                        <input class="input" type="email" name="email" placeholder="Email" required />
                                    </div>
                                    <div class="form-group">
                                        <input class="input" type="tel" name="phone" placeholder="Phone Number" />
                                    </div>
                                    <div class="form-group">
                                        <input class="input" type="text" name="address" placeholder="Address" />
                                    </div>
                                    <input type="hidden" name="csrf_token"
                                        value="<?php echo $_SESSION['csrf_token'] ?>" />
                                    <div class="form-group">
                                        <button type="submit" class="primary-btn">
                                            Save Changes
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Orders Tab -->
                        <div class="tab-pane fade" id="orders">
                            <div class="order-summary">
                                <div class="section-title">
                                    <h3 class="title">Order History</h3>
                                </div>
                                <div class="order-products">
                                    <!-- Order data will be loaded here -->
                                    <p><i class="fa fa-spinner fa-spin"></i> Loading your orders...</p>
                                </div>
                            </div>
                        </div>

                        <!-- Settings Tab -->
                        <div class="tab-pane fade" id="settings">
                            <div class="billing-details">
                                <div class="section-title">
                                    <h3 class="title">Change Password</h3>
                                </div>
                                <form id="password-form">
                                    <div class="form-group">
                                        <input class="input" type="password" name="current-password"
                                            placeholder="Current Password" required />
                                    </div>
                                    <div class="form-group">
                                        <input class="input" type="password" name="new-password"
                                            placeholder="New Password" required />
                                    </div>
                                    <div class="form-group">
                                        <input class="input" type="password" name="confirm-password"
                                            placeholder="Confirm New Password" required />
                                    </div>
                                    <input type="hidden" name="csrf_token"
                                        value="<?php echo $_SESSION['csrf_token'] ?>" />
                                    <div class="form-group">
                                        <button type="submit" class="primary-btn">
                                            Update Password
                                        </button>
                                    </div>
                                </form>
                            </div>
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
    <script src="<?php echo JS_DIR ?>main.js"></script>

    <!-- Set BASE_URL for JavaScript -->
    <script>
        var BASE_URL = '<?php echo BASE_URL ?>';
    </script>

    <!-- Profile JavaScript -->
    <script src="<?php echo JS_DIR ?>profile.js"></script>
    <!-- Profile Form Validation -->
    <script src="<?php echo JS_DIR ?>profile-validate.js"></script>
</body>

</html>