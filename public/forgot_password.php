<?php
// Load the configuration file
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_once '../includes/mail_helper.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Generate a CSRF token for the form
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = generate_csrf_token();
}

// Check if the user is already logged in
if (isLoggedIn()) {
    setFlashData('msg', 'You are already logged in.');
    setFlashData('msg_type', 'warning');
    // Redirect to the home page if the user is already logged in
    header('Location: ' . BASE_URL . '/public/index.php');
    exit();
}

$msg = '';
$msg_type = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $filteredData = filter();
    // Verify the CSRF token
    if (!isset($filteredData['csrf_token']) || $filteredData['csrf_token'] !== $_SESSION['csrf_token']) {
        $msg = 'Invalid CSRF token.';
        $msg_type = 'danger';
    }

    $email = trim($filteredData['email']) ?? '';

    if (!empty($email)) {
        // Validate email format
        if (!validate_email($email)) {
            $msg = 'Invalid email format.';
            $msg_type = 'danger';
        } else {
            // Check if the email exists in the database
            $sql = "SELECT * FROM users WHERE email = ? AND is_active = 1";
            $user = getRecord($sql, [$email]);
            if ($user) {
                // Generate a unique token for password reset
                $token = bin2hex(random_bytes(32));

                // Set token expiration time
                $expires = date('Y-m-d H:i:s', strtotime('+15 minutes'));

                // Update user record with token
                $updateTokenSql = "UPDATE `users` SET `reset_token`=?,`reset_expires`=? WHERE email = ?;
";
                $updateTokenStatus = updateRecord($updateTokenSql, [$token, $expires, $email]);

                if ($updateTokenStatus) {
                    // Create reset password link
                    $resetLink = BASE_URL . '/public/reset_password.php?token=' . $token;

                    $html_message = '
                    <!DOCTYPE html>
                    <html>
                    <head>
                        <style>
                            body {
                                font-family: Arial, sans-serif;
                                line-height: 1.6;
                                color: #333333;
                            }
                            .container {
                                max-width: 600px;
                                margin: 0 auto;
                                padding: 20px;
                                border: 1px solid #dddddd;
                                border-radius: 5px;
                            }
                            .header {
                                background-color: #D10024;
                                color: white;
                                padding: 15px;
                                text-align: center;
                                border-radius: 5px 5px 0 0;
                            }
                            .content {
                                padding: 20px;
                                background-color: #ffffff;
                            }
                            .button {
                                display: inline-block;
                                padding: 10px 20px;
                                background-color: #D10024;
                                color: white;
                                text-decoration: none;
                                border-radius: 5px;
                                margin: 20px 0;
                            }
                            .footer {
                                text-align: center;
                                margin-top: 20px;
                                font-size: 12px;
                                color: #777777;
                            }
                            a {
                                color: #000000;
                            }
                        </style>
                    </head>
                    <body>
                    <div class="container">
                        <div class="header">
                            <h2>Password Reset Request</h2>
                        </div>
                        <div class="content">
                            <p>Hi ' . htmlspecialchars($user['username']) . ',</p>
                            <p>You recently requested to reset your password for your Electro Shop account.</p>
                            <p>Please click the button below to reset it:</p>
                            <p style="text-align: center;">
                                <a href="' . $resetLink . '" class="button">Reset Password</a>
                            </p>
                            <p>Or copy and paste this link into your browser:</p>
                            <p>' . $resetLink . '</p>
                            <p>This link will expire in 15 minutes.</p>
                            <p>If you did not request a password reset, please ignore this email.</p>
                            <p>Best regards,<br>Electro Shop Team</p>
                        </div>
                        <div class="footer">
                            <p>&copy; ' . date('Y') . ' Electro Shop. All rights reserved.</p>
                        </div>
                    </div>
                </body>
                </html>
                ';
                    // Plain text version for email not support HTML
                    $text_message = "Hi {$user['username']},\n\n";
                    $text_message .= "You recently requested to reset your password for your Electro Shop account.\n\n";
                    $text_message .= "Please click the link below to reset it:\n";
                    $text_message .= $resetLink . "\n\n";
                    $text_message .= "This link will expire in 15 minutes.\n\n";
                    $text_message .= "If you did not request a password reset, please ignore this email.\n\n";
                    $text_message .= "Best regards,\nElectro Shop Team";

                    // Send email
                    $result = send_email($email, 'Password Reset Request - Electro Shop', $html_message, $text_message);
                    if ($result['success']) {
                        $msg = 'Password reset instructions have been sent to your email.';
                        $msg_type = 'success';
                    } else {
                        $msg = 'Error occurred. Please try again.';
                        $msg_type = 'danger';
                    }
                }
            } else {
                $msg = 'Email address not found or account is inactive.';
                $msg_type = 'danger';
            }
        }
    } else {
        $msg = 'Please enter your email address.';
        $msg_type = 'danger';
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Forgot Password - Electro</title>

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
    <!-- \HEADER -->

    <!-- MAIN -->
    <div class="section">
        <div class="container">
            <div class="row">
                <div class="col-md-6 col-md-offset-3">
                    <div class="billing-details">
                        <div class="section-title">
                            <h3 class="title">Reset Password</h3>
                            <?php
                            // Display error messages
                            if (!empty($msg) && !empty($msg_type)) {
                                echo '<div class="alert alert-' . htmlspecialchars($msg_type) . '">';
                                echo '<p>' . htmlspecialchars($msg) . '</p>';
                                echo '</div>';
                            }
                            ?>
                            <p class="text-muted">
                                Enter your email address and we'll send you
                                instructions to reset your password.
                            </p>
                        </div>
                        <form action="forgot_password.php" method="POST" id="forgot-password-form">
                            <div class="form-group">
                                <input class="input" type="email" name="email" placeholder="Enter your email"
                                    required />
                            </div>
                            <div class="form-group text-center">
                                <button type="submit" class="primary-btn">
                                    Send Reset Link
                                </button>
                            </div>
                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?>" />
                            <div class="text-center">
                                <p>
                                    Remember your password?
                                    <a href="<?php echo BASE_URL ?>/public/login.php" class="text-primary">Back to
                                        Login</a>
                                </p>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- FOOTER -->
    <?php include_once("../includes/footer.php") ?>
    <!-- /FOOTER -->

    <!-- jQuery Plugins -->
    <script src="<?php echo JS_DIR ?>jquery.min.js"></script>
    <script src="<?php echo JS_DIR ?>bootstrap.min.js"></script>
    <script src="<?php echo JS_DIR ?>main.js"></script>
</body>

</html>