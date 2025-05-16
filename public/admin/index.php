<?php

// Load the configuration file
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

session_start();

// Check if the user is logged in and is an admin
if (!check_login() || !isAdmin()) {
    setFlashData('msg', 'You need to login as an admin to access this page.');
    setFlashData('msg_type', 'danger');
    // Redirect to login page
    header('Location: ' . BASE_URL . '/public/login.php');
    exit;
}

// Generate CSRF token if it doesn't exist
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Admin Dashboard - Electro</title>

    <!-- Google font -->
    <link href="https://fonts.googleapis.com/css?family=Montserrat:400,500,700" rel="stylesheet" />

    <!-- Bootstrap -->
    <link type="text/css" rel="stylesheet" href="<?php echo CSS_DIR ?>bootstrap.min.css" />

    <!-- Font Awesome Icon -->
    <link rel="stylesheet" href="<?php echo CSS_DIR ?>font-awesome.min.css" />

    <!-- Custom stylesheet -->
    <link type="text/css" rel="stylesheet" href="<?php echo CSS_DIR ?>styles.css" />

    <!-- Admin stylesheet -->
    <link type="text/css" rel="stylesheet" href="<?php echo CSS_DIR ?>admin-styles.css" />

    <!-- Styles Orders History -->
    <style>
        /* Order history styles */
        .product-info {
            display: flex;
            align-items: center;
        }

        .product-info img {
            margin-right: 10px;
            max-width: 50px;
            max-height: 50px;
            border: 1px solid #eee;
        }

        #viewOrderModal .modal-lg {
            width: 90%;
            max-width: 900px;
        }

        .order-details h4 {
            margin-top: 20px;
            margin-bottom: 15px;
            padding-bottom: 8px;
            border-bottom: 1px solid #eee;
        }

        .table-bordered {
            border: 1px solid #ddd;
        }

        .table-bordered>thead>tr>th,
        .table-bordered>tbody>tr>th,
        .table-bordered>tfoot>tr>th,
        .table-bordered>thead>tr>td,
        .table-bordered>tbody>tr>td,
        .table-bordered>tfoot>tr>td {
            border: 1px solid #ddd;
        }
    </style>
</head>

<body>
    <!-- HEADER -->
    <?php require_once("../../includes/header.php") ?>

    <!-- BREADCRUMB -->
    <div id="breadcrumb" class="section">
        <!-- container -->
        <div class="container">
            <!-- row -->
            <div class="row">
                <div class="col-md-12">
                    <h3 class="breadcrumb-header">Admin Dashboard</h3>
                    <ul class="breadcrumb-tree">
                        <li><a href="<?php echo BASE_URL ?>/public/index.php">Home</a></li>
                        <li class="active">Admin Dashboard</li>
                    </ul>
                </div>
            </div>
            <!-- /row -->
        </div>
        <!-- /container -->
    </div>
    <!-- /BREADCRUMB -->

    <!-- MAIN -->
    <div class="section">
        <div class="container">
            <div class="row">
                <!-- Admin Sidebar -->
                <div class="col-md-3">
                    <div class="admin-sidebar">
                        <ul class="admin-menu">
                            <li class="active">
                                <a href="#users" data-toggle="tab">
                                    <i class="fa fa-users"></i> User
                                    Management
                                </a>
                            </li>
                            <li>
                                <a href="#orders" data-toggle="tab">
                                    <i class="fa fa-shopping-cart"></i>
                                    Order History
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Admin Content -->
                <div class="col-md-9">
                    <div class="tab-content">
                        <!-- Users Tab -->
                        <div class="tab-pane fade in active" id="users">
                            <div class="admin-panel">
                                <div class="panel-heading">
                                    <h3 class="panel-title">User Management</h3>
                                    <div class="panel-actions">
                                        <button class="primary-btn" data-toggle="modal" data-target="#addUserModal">
                                            <i class="fa fa-plus"></i> Add New User
                                        </button>
                                    </div>
                                </div>
                                <div class="panel-body">
                                    <!-- User alerts container -->
                                    <div id="user-alerts"></div>

                                    <!-- User data container -->
                                    <div class="user-data-container">
                                        <div class="text-center">
                                            <i class="fa fa-spinner fa-spin fa-2x"></i>
                                            <p>Loading users...</p>
                                        </div>
                                    </div>

                                    <!-- Pagination -->
                                    <div id="user-pagination"></div>
                                </div>
                            </div>
                        </div>


                        <!-- Orders Tab -->
                        <div class="tab-pane fade" id="orders">
                            <div class="admin-panel">
                                <div class="panel-heading">
                                    <h3 class="panel-title">Order History</h3>
                                </div>
                                <div class="panel-body">
                                    <div class="text-center">
                                        <i class="fa fa-spinner fa-spin fa-2x"></i>
                                        <p>Loading orders...</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add User Modal -->
    <div class="modal fade" id="addUserModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">
                        &times;
                    </button>
                    <h4 class="modal-title">Add New User</h4>
                </div>
                <div class="modal-body">
                    <div id="add-user-errors" class="alert alert-danger" style="display:none;"></div>
                    <form id="add-user-form">
                        <div class="form-group">
                            <label for="username">Username <span class="text-danger">*</span></label>
                            <input class="input" type="text" id="username" name="username" placeholder="Username"
                                required />
                            <small class="error-msg" id="username-error"></small>
                        </div>
                        <div class="form-group">
                            <label for="full_name">First Name<span class="text-danger">*</span></label>
                            <input class="input" type="text" id="first_name" name="first_name" placeholder="First Name"
                                required />
                            <small class="error-msg" id="first_name-error"></small>
                        </div>
                        <div class="form-group">
                            <label for="full_name">Last Name <span class="text-danger">*</span></label>
                            <input class="input" type="text" id="last_name" name="last_name" placeholder="Last Name"
                                required />
                            <small class="error-msg" id="last_name-error"></small>
                        </div>
                        <div class="form-group">
                            <label for="email">Email <span class="text-danger">*</span></label>
                            <input class="input" type="email" id="email" name="email" placeholder="Email" required />
                            <small class="error-msg" id="email-error"></small>
                        </div>

                        <div class="form-group">
                            <label for="password">Password <span class="text-danger">*</span></label>
                            <input class="input" type="password" id="password" name="password" placeholder="Password"
                                required />
                            <small class="error-msg" id="password-error"></small>
                        </div>
                        <div class="form-group">
                            <label for="role">Role <span class="text-danger">*</span></label>
                            <select class="input-select" id="role" name="role" required>
                                <option value="user">Regular User</option>
                                <option value="admin">Administrator</option>
                            </select>
                            <small class="error-msg" id="role-error"></small>
                        </div>
                        <div class="form-group">
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" id="is_active" name="is_active" checked> Active Account
                                </label>
                            </div>
                        </div>
                        <!-- CSRF Token -->
                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

                        <div class="form-group">
                            <button type="submit" id="add-user-btn" class="primary-btn">Add User</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div class="modal fade" id="editUserModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">
                        &times;
                    </button>
                    <h4 class="modal-title">Edit User</h4>
                </div>
                <div class="modal-body">
                    <div id="edit-user-errors" class="alert alert-danger" style="display:none;"></div>
                    <form id="edit-user-form">
                        <div id="edit-user-form-fields">
                            <div class="text-center">
                                <i class="fa fa-spinner fa-spin"></i> Loading...
                            </div>
                        </div>

                        <!-- CSRF Token -->
                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                        <input type="hidden" id="edit_user_id" name="user_id">

                        <div class="form-group">
                            <button type="submit" id="edit-user-btn" class="primary-btn">Update User</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- View Order Modal -->
    <div class="modal fade" id="viewOrderModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Order Details</h4>
                </div>
                <div class="modal-body">
                    <div class="text-center">
                        <i class="fa fa-spinner fa-spin"></i> Loading order details...
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- FOOTER -->
    <?php require_once("../../includes/footer.php") ?>

    <!-- jQuery Plugins -->
    <script src="<?php echo JS_DIR ?>jquery.min.js"></script>
    <script src="<?php echo JS_DIR ?>bootstrap.min.js"></script>
    <script src="<?php echo JS_DIR ?>main.js"></script>

    <!-- Admin Script -->
    <script>
        var BASE_URL = '<?php echo BASE_URL; ?>';
        var CSRF_TOKEN = '<?php echo $csrf_token; ?>';
    </script>
    <script src="<?php echo JS_DIR ?>admin-users.js"></script>
    <script src="<?php echo JS_DIR ?>admin-orders.js"></script>
</body>

</html>