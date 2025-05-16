<?php
// Load configuration files
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Generate a CSRF token for the form
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = generate_csrf_token();
}

// Check if the user is already logged in
if (isLoggedIn()) {
    setFlashData('msg', 'You are already logged in. Please log out to reset your password.');
    setFlashData('msg_type', 'warning');
    // Redirect to the home page if the user is already logged in
    header('Location: ' . BASE_URL . '/public/index.php');
    exit();
}

$msg = '';
$msg_type = '';
$valid_token = false;
$user = null;
$token = '';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $filteredData = filter();
    $token = trim($filteredData['token']) ?? '';
} else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $filteredData = filter();
    $token = isset($_GET['token']) ? $_GET['token'] : '';

    if (empty($token)) {
        $token = trim($filteredData['token']) ?? '';
    }
}

$valid_token = validateResetToken($token);
var_dump($valid_token);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $valid_token) {
    // Verify the CSRF token
    if (!isset($filteredData['csrf_token']) || $filteredData['csrf_token'] !== $_SESSION['csrf_token']) {
        $msg = 'Invalid CSRF token.';
        $msg_type = 'danger';
    } else {
        // Get form data
        $password = $filteredData['password'] ?? '';
        $confirm_password = $filteredData['confirm_password'] ?? '';

        // Validate password
        if (empty($password)) {
            $msg = 'Password is required.';
            $msg_type = 'danger';
        } elseif (!validate_password($password)) {
            $msg = 'Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character.';
            $msg_type = 'danger';
        } elseif (empty($confirm_password)) {
            $msg = 'Please confirm your password.';
            $msg_type = 'danger';
        } elseif ($password !== $confirm_password) {
            $msg = 'Passwords do not match.';
            $msg_type = 'danger';
        } else {
            // Hash the new password
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);

            // Update the user's password and clear the reset token
            $sql = "UPDATE users SET 
                    password = ?, 
                    reset_token = NULL, 
                    reset_expires = NULL, 
                    updated_at = NOW() 
                    WHERE reset_token = ?";

            try {
                $result = updateRecord($sql, [$hashed_password, $token]);

                if ($result) {
                    $msg = 'Your password has been reset successfully. You can now log in with your new password.';
                    $msg_type = 'success';
                    $valid_token = false; // Disable form after successful password reset
                } else {
                    $msg = 'Failed to update password. Please try again.';
                    $msg_type = 'danger';
                }
            } catch (PDOException $e) {
                $msg = "Error occurred. Please try again later.";
                $msg_type = 'danger';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Reset Password - Electro</title>

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
    <?php include_once("../includes/header.php") ?>
    <!-- /HEADER -->

    <!-- BREADCRUMB -->
    <div id="breadcrumb" class="section">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <h3 class="breadcrumb-header">Reset Password</h3>
                    <ul class="breadcrumb-tree">
                        <li><a href="<?php echo BASE_URL . '/public/index.php' ?>">Home</a></li>
                        <li class="active">Reset Password</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <!-- /BREADCRUMB -->

    <!-- SECTION -->
    <div class="section">
        <div class="container">
            <div class="row">
                <div class="col-md-6 col-md-offset-3">
                    <div class="billing-details">
                        <div class="section-title">
                            <h3 class="title">Reset Password</h3>
                        </div>

                        <?php if (!empty($msg) && !empty($msg_type)): ?>
                            <div class="alert alert-<?php echo $msg_type; ?> mt-3">
                                <?php echo $msg; ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($valid_token): ?>
                            <form
                                action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'] . '?token=' . urlencode($token)); ?>"
                                method="post" id="reset-password-form">
                                <div class="form-group">
                                    <label for="password">New Password</label>
                                    <input class="input" type="password" name="password" id="password"
                                        placeholder="Enter your new password" required />
                                </div>
                                <div class="form-group">
                                    <label for="confirm_password">Confirm Password</label>
                                    <input class="input" type="password" name="confirm_password" id="confirm_password"
                                        placeholder="Confirm your new password" required />
                                </div>
                                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>" />
                                <div class="form-group text-center">
                                    <button type="submit" class="primary-btn">Reset Password</button>
                                </div>
                            </form>
                        <?php else: ?>
                            <?php if ($msg_type === 'success'): ?>
                                <div class="text-center mt-4">
                                    <a href="<?php echo BASE_URL; ?>/public/login.php" class="primary-btn">Go to Login</a>
                                </div>
                            <?php else: ?>
                                <div class="text-center mt-4">
                                    <a href="<?php echo BASE_URL; ?>/public/forgot_password.php" class="primary-btn">Request New
                                        Reset Link</a>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /SECTION -->

    <!-- FOOTER -->
    <?php include_once("../includes/footer.php") ?>
    <!-- /FOOTER -->

    <!-- jQuery Plugins -->
    <script src="<?php echo JS_DIR ?>jquery.min.js"></script>
    <script src="<?php echo JS_DIR ?>bootstrap.min.js"></script>
    <script src="<?php echo JS_DIR ?>main.js"></script>

    <?php if ($valid_token): ?>
        <script>
            $(document).ready(function () {
                // Client-side validation
                $("#reset-password-form").on("submit", function (e) {
                    const password = $("#password").val();
                    const confirmPassword = $("#confirm_password").val();

                    // Validate password length
                    if (password.length < 6) {
                        e.preventDefault();
                        alert("Password must be at least 6 characters");
                        return;
                    }

                    // Validate password match
                    if (password !== confirmPassword) {
                        e.preventDefault();
                        alert("Passwords do not match");
                        return;
                    }
                });

                // Password strength meter
                $("#password").on("keyup", function () {
                    const password = $(this).val();
                    let strength = 0;

                    if (password.length >= 6) strength += 1;
                    if (password.match(/[a-z]+/)) strength += 1;
                    if (password.match(/[A-Z]+/)) strength += 1;
                    if (password.match(/[0-9]+/)) strength += 1;
                    if (password.match(/[!@#$%^&*(),.?":{}|<>]+/)) strength += 1;

                    if (!$(this).next(".password-strength").length) {
                        $(this).after('<div class="password-strength mt-2"></div>');
                    }

                    const strengthMeter = $(this).next(".password-strength");

                    switch (strength) {
                        case 0:
                        case 1:
                            strengthMeter.html('<div class="progress"><div class="progress-bar progress-bar-danger" style="width: 25%">Very Weak</div></div>');
                            break;
                        case 2:
                            strengthMeter.html('<div class="progress"><div class="progress-bar progress-bar-warning" style="width: 50%">Weak</div></div>');
                            break;
                        case 3:
                            strengthMeter.html('<div class="progress"><div class="progress-bar progress-bar-info" style="width: 75%">Good</div></div>');
                            break;
                        case 4:
                        case 5:
                            strengthMeter.html('<div class="progress"><div class="progress-bar progress-bar-success" style="width: 100%">Strong</div></div>');
                            break;
                    }
                });
                // Show/hide password toggle
                $('<div class="password-toggle mt-2"><label><input type="checkbox" id="show-password"> Show password</label></div>')
                    .insertAfter("#confirm_password");

                $("#show-password").on("change", function () {
                    const showPassword = $(this).is(":checked");
                    $("#password, #confirm_password").attr("type", showPassword ? "text" : "password");
                });
            });
        </script>
    <?php endif; ?>