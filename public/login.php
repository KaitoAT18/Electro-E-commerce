<?php
// Load the configuration file
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check flash data 
$msg = getFlashData('msg');
$msgType = getFlashData('msg_type');

// Generate CSRF token
if (!isset($_SESSION['csrf_token'])) {
    generate_csrf_token();
}

// Check if user is already logged in
if (isLoggedIn()) {
    setFlashData('msg', 'You are already logged in.');
    setFlashData('msg_type', 'warning');
    header("Location: " . BASE_URL . "/public/index.php");
    exit();
}

// Check if the user has a remember me cookie
checkRememberMeCookie();

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['csrf_token'])) {
    // Validate CSRF token
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die("Invalid CSRF token");
    }
    // Validate and sanitize input data
    $filteredData = filter();

    $username = trim($filteredData['username']);
    $password = trim($filteredData['password']);
    $remember = isset($filteredData['remember']) ? (bool) $filteredData['remember'] : false;

    if (!empty($username) || !empty($password)) {
        if (empty($errors)) {
            // Check if username exists
            $rows = countRecords("SELECT * FROM users WHERE username = ?", [$username]);
            if ($rows == 0) {
                $errors['login'] = "Invalid username or password.";
            } else {
                // Fetch user data
                $user = getRecord("SELECT user_id, username, password, role FROM users WHERE username = ? AND is_active = 1", [$username]);
                if ($user && password_verify($password, $user['password'])) {
                    // Generate JWT token
                    $jwt = generate_jwt($user['user_id'], $user['username'], $user['role']);
                    setcookie('jwt', $jwt, time() + (60 * 60 * 24), '/', '', false, true);

                    // Handle remember me functionality
                    if ($remember) {
                        // Create a unique token
                        $rememberToken = bin2hex(random_bytes(32));
                        $expires = date('Y-m-d H:i:s', strtotime('+30 days'));

                        // Save the token and expiration date in the database
                        $sql = "UPDATE users SET remember_token = ?, remember_expires = ? WHERE user_id = ?";
                        updateRecord($sql, [$rememberToken, $expires, $user['user_id']]);

                        // Set the remember me cookie (30 days)
                        setcookie('remember_token', $rememberToken, time() + (86400 * 30), '/', '', false, true);
                    }

                    // Redirect to home page
                    header("Location: " . BASE_URL . "/public/index.php");
                    exit();
                } else if ($user && !password_verify($password, $user['password'])) {
                    $errors['login'] = "Invalid username or password.";
                } else {
                    $errors['lock_account'] = "Account is locked. Please contact support.";
                }
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
    <title>Login - Electro</title>

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
    <?php include_once '../includes/header.php'; ?>

    <!-- MAIN -->
    <div class="section">
        <div class="container">
            <div class="row">
                <div class="col-md-6 col-md-offset-3">
                    <div class="billing-details">
                        <?php
                        // Display error messages
                        if (!empty($errors)) {
                            echo '<div class="alert alert-danger">';
                            foreach ($errors as $error) {
                                echo '<p>' . htmlspecialchars($error) . '</p>';
                            }
                            echo '</div>';
                        }

                        // Display flash messages
                        if (!empty($msg) && !empty($msgType)) {
                            echo '<div class="alert alert-' . htmlspecialchars($msgType) . '">';
                            echo '<p>' . htmlspecialchars($msg) . '</p>';
                            echo '</div>';
                        }
                        ?>
                        <div class="section-title">
                            <h3 class="title">Login</h3>
                        </div>
                        <form action="<?php echo BASE_URL ?>/public/login.php" method="POST" id="form-login">
                            <input type="hidden" name="csrf_token"
                                value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>" />
                            <div class="form-group">
                                <input class="input" type="text" name="username" placeholder="Username" required />
                            </div>
                            <div class="form-group">
                                <input class="input" type="password" name="password" placeholder="Password" required />
                            </div>
                            <div class="form-group">
                                <div class="input-checkbox">
                                    <input type="checkbox" id="remember" name="remember" value="1" />
                                    <label for="remember">
                                        <span></span>
                                        Remember me
                                    </label>
                                    <small class="text-muted">(Keeps you logged in for 30 days)</small>
                                </div>
                            </div>
                            <div class="form-group text-center">
                                <button type="submit" class="primary-btn">Login</button>
                            </div>
                            <div class="text-center">
                                <a href="<?php echo BASE_URL . '/public/forgot_password.php' ?>"
                                    class="text-primary">Forgot Password?</a>
                            </div>
                            <hr />
                            <div class="text-center">
                                <p>
                                    Don't have an account?
                                    <a href="register.php" class="text-primary">Register here</a>
                                </p>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- FOOTER -->
    <?php include_once '../includes/footer.php'; ?>

    <!-- jQuery Plugins -->
    <script src="<?php echo JS_DIR ?>jquery.min.js"></script>
    <script src="<?php echo JS_DIR ?>bootstrap.min.js"></script>
    <script src="<?php echo JS_DIR ?>main.js"></script>
</body>

</html>