<?php
// Load the configuration file
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

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

$errors = [];
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['csrf_token'])) {
    // Validate CSRF token
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die("Invalid CSRF token");
    }
    // Validate and sanitize input data
    $filteredData = filter();

    $firstName = trim($filteredData['first-name']);
    $lastName = trim($filteredData['last-name']);
    $username = trim($filteredData['username']);
    $email = trim($filteredData['email']);
    $address = trim($filteredData['address']);
    $phone = trim($filteredData['phone']);
    $password = trim($filteredData['password']);
    $confirmPassword = trim($filteredData['confirm-password']);

    if (!empty($firstName) || !empty($lastName) || !empty($username) || !empty($email) || !empty($address) || !empty($phone) || !empty($password) || !empty($confirmPassword)) {
        if (strlen($firstName) < 2 || strlen($lastName) < 2) {
            $errors['name'] = "First and last name must be at least 2 characters long.";
        }
        if (!validate_email($email)) {
            $errors['email'] = "Invalid email format.";
        }
        if (!validate_phone_number($phone)) {
            $errors['phone'] = "Invalid phone number format.";
        }
        if (!validate_password($password)) {
            $errors['password'] = "Password must be at least 6 characters long and contain at least one uppercase letter, one number, and one special character.";
        }
        if ($password !== $confirmPassword) {
            $errors['confirm-password'] = "Passwords do not match.";
        }
        if (!validate_username($username)) {
            $errors['username'] = "Username can only contain lowercase letters and numbers and must be at least 5 characters long and no more than 20 characters.";
        }
        if (empty($errors)) {
            // Check if username already exists
            $isUserExists = countRecords("SELECT * FROM users WHERE username = ?", [$username]);
            $isEmailExists = countRecords("SELECT * FROM users WHERE email = ?", [$email]);

            if ($isUserExists > 0) {
                $errors['duplicate'] = "Username already exists.";
            } elseif ($isEmailExists > 0) {
                $errors['duplicate'] = "Email already exists.";
            } else {
                $fullName = $firstName . ' ' . $lastName;
                $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
                $currentTimeStamp = date('Y-m-d H:i:s');
                $role = 'user'; // Default role for new users

                // Insert new user into the database
                $sql = "INSERT INTO users (username, email, password, role, full_name, address, phone, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $status = insertRecord($sql, [$username, $email, $hashedPassword, $role, $fullName, $address, $phone, $currentTimeStamp, $currentTimeStamp]);
                if ($status) {
                    // Notify the user of successful registration
                    $successMessage = "Registration successful! You can now log in.";
                    echo "<script>alert('$successMessage');</script>";
                    // Registration successful, redirect to login page
                    $loginUrl = BASE_URL . '/public/login.php';
                    header("Refresh: 0, url=$loginUrl");
                    exit();
                } else {
                    $errors['register_failed'] = "Registration failed. Please try again.";
                }
            }
        }
    } else {
        $errors['empty'] = "All fields are required.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Register - Electro</title>

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
                        ?>
                        <div class="section-title">
                            <h3 class="title">Create Account</h3>
                        </div>
                        <form action="#" method="POST" id="form-register">
                            <div class="form-group">
                                <input class="input" type="text" name="first-name" placeholder="First Name"
                                    value="<?= htmlspecialchars($firstName ?? "") ?>" required />
                            </div>
                            <div class="form-group">
                                <input class="input" type="text" name="last-name" placeholder="Last Name"
                                    value="<?= htmlspecialchars($lastName ?? "") ?>" required />
                            </div>
                            <div class="form-group">
                                <input class="input" type="text" name="username" placeholder="Username"
                                    value="<?= htmlspecialchars($username ?? "") ?>" required />
                            </div>
                            <div class="form-group">
                                <input class="input" type="email" name="email" placeholder="Email"
                                    value="<?= htmlspecialchars($email ?? "") ?>" required />
                            </div>
                            <div class="form-group">
                                <input class="input" type="text" name="address" placeholder="Address"
                                    value="<?= htmlspecialchars(string: $address ?? "") ?>" required />
                            </div>
                            <div class="form-group">
                                <input class="input" type="text" name="phone" placeholder="Phone Number"
                                    value="<?= htmlspecialchars($phone ?? "") ?>" />
                            </div>
                            <div class="form-group">
                                <input class="input" type="password" name="password" placeholder="Password" required />
                            </div>
                            <div class="form-group">
                                <input class="input" type="password" name="confirm-password"
                                    placeholder="Confirm Password" required />
                            </div>
                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>" />
                            <div class="form-group">
                                <div class="input-checkbox">
                                    <input type="checkbox" id="terms" required />
                                    <label for="terms">
                                        <span></span>
                                        I agree to the
                                        <a href="#">terms and conditions</a>
                                    </label>
                                </div>
                            </div>
                            <div class="form-group text-center">
                                <button type="submit" class="primary-btn">
                                    Create Account
                                </button>
                            </div>
                            <div class="text-center">
                                <p>
                                    Already have an account?
                                    <a href="login.php" class="text-primary">Login here</a>
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
    <script src="<?php echo JS_DIR ?>register-validate.js"></script>
</body>

</html>