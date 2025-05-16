<?php
include_once(__DIR__ . '/../config/database.php');

// Hàm filter lọc dữ liệu
function filter()
{
    $filteredArr = [];
    if ($_SERVER['REQUEST_METHOD'] == 'GET') {
        // Xử lý dữ liệu trước khi hiển thị ra
        if (!empty($_GET)) {
            foreach ($_GET as $key => $value) {
                $key = strip_tags($key);
                if (is_array($key)) {
                    $filteredArr[$key] = filter_input(INPUT_GET, $key, FILTER_SANITIZE_SPECIAL_CHARS, FILTER_REQUIRE_ARRAY);
                } else {
                    $filteredArr[$key] = filter_input(INPUT_GET, $key, FILTER_SANITIZE_SPECIAL_CHARS);
                }
            }
        }
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Xử lý dữ liệu trước khi hiển thị ra
        if (!empty($_POST)) {
            foreach ($_POST as $key => $value) {
                $key = strip_tags($key);
                if (is_array($key)) {
                    $filteredArr[$key] = filter_input(INPUT_POST, $key, FILTER_SANITIZE_SPECIAL_CHARS, FILTER_REQUIRE_ARRAY);
                } else {
                    $filteredArr[$key] = filter_input(INPUT_POST, $key, FILTER_SANITIZE_SPECIAL_CHARS);
                }
            }
        }
    }
    return $filteredArr;
}

// Hàm generate CSRF token
function generate_csrf_token()
{
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Hàm kiểm tra mật khẩu
function validate_password($password)
{
    if (strlen($password) < 6) {
        return false;
    }
    if (!preg_match('/[A-Z]/', $password)) {
        return false;
    }
    if (!preg_match('/[0-9]/', $password)) {
        return false;
    }
    if (!preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password)) {
        return false;
    }
    return true;
}

// Hàm kiểm tra số điện thoại
function validate_phone_number($phone_number)
{
    // Biểu thức chính quy kiểm tra số điện thoại hợp lệ
    $pattern = '/^0\d{9}$/';
    // Kiểm tra chuỗi với biểu thức chính quy
    if (preg_match($pattern, $phone_number)) {
        return true;
    }
    return false;
}

// Hàm kiểm tra email
function validate_email($email)
{
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return true;
    }
    return false;
}

// Hàm kiểm tra tên người dùng
function validate_username($username)
{
    // Kiểm tra độ dài tên người dùng
    if (strlen($username) < 5 || strlen($username) > 20) {
        return false;
    }
    // Kiểm tra ký tự hợp lệ
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        return false;
    }
    return true;
}

// Hàm check login bằng JWT
function check_login()
{
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    if (isset($_COOKIE['jwt'])) {
        require_once dirname(__FILE__) . '/auth.php';
        $user = verify_jwt($_COOKIE['jwt']);
        if ($user !== false) {
            return $user;
        }
    }
    return false;
}

// Hàm kiểm tra xem một trang có phải là trang hiện tại không
function isCurrentPage($pageName)
{
    global $currentUrl;
    return (strpos($currentUrl, $pageName) !== false);
}

// Hàm tạo URL phân trang với các tham số hiện tại
function buildPaginationUrl($page)
{
    $params = $_GET;
    $params['page'] = $page;
    return '?' . http_build_query($params);
}

function getProduct($id)
{
    $sql = "SELECT 
            p.product_id,
            p.product_name,
            p.price,
            p.sale_price,
            p.stock,
            c.category_name,
            pi.image_path AS primary_image
        FROM 
            products p
            JOIN categories c ON p.category_id = c.category_id
            LEFT JOIN product_images pi ON p.product_id = pi.product_id AND pi.is_primary = 1
        WHERE 
            p.product_id = ?";

    $result = getRecords($sql, [$id]);
    return $result ? $result[0] : null;
}

/**
 * Tạo đơn hàng mới với cơ chế transaction
 * 
 * @param int $userId ID của người dùng đặt hàng
 * @param array $orderData Thông tin đơn hàng (tên, email, địa chỉ...)
 * @param array $cartItems Các sản phẩm trong giỏ hàng
 * @param float $cartTotal Tổng tiền đơn hàng
 * 
 * @return array ['success' => bool, 'message' => string, 'order_id' => int|null]
 */
function createOrder($userId, $orderData, $cartItems, $cartTotal)
{
    $result = [
        'success' => false,
        'message' => '',
        'order_id' => null
    ];

    try {
        // Lấy database connection
        $db = getDB();

        // Bắt đầu transaction
        $db->beginTransaction();

        // Chuẩn bị dữ liệu đơn hàng
        $sql = "INSERT INTO orders (
                    user_id, first_name, last_name, email, 
                    phone, address, city, country, zip_code, 
                    total_amount, status, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $orderId = insertRecord($sql, [
            $userId,
            $orderData['first_name'],
            $orderData['last_name'],
            $orderData['email'],
            $orderData['phone'],
            $orderData['address'],
            $orderData['city'],
            $orderData['country'],
            $orderData['zip_code'],
            $cartTotal,
            'pending',
            date('Y-m-d H:i:s')
        ]);

        if (!$orderId) {
            throw new Exception("Can't create order");
        }

        // Thêm chi tiết đơn hàng
        foreach ($cartItems as $item) {
            // Kiểm tra số lượng tồn kho
            $checkStockSql = "SELECT product_name, stock FROM products WHERE product_id = ?";
            $product = getRecord($checkStockSql, [$item['product_id']]);

            if (!$product) {
                throw new Exception("Product not found: " . $item['product_id']);
            }

            if ($product['stock'] < $item['quantity']) {
                throw new Exception("Not enough stock for product: " . $product['product_name']);
            }

            // Thêm chi tiết đơn hàng
            $itemSql = "INSERT INTO order_details (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)";
            $itemId = insertRecord($itemSql, [
                $orderId,
                $item['product_id'],
                $item['quantity'],
                $item['price']
            ]);

            if (!$itemId) {
                throw new Exception("Can't add order details for product: " . $item['product_name']);
            }

            // Cập nhật số lượng tồn kho
            $stock = $product['stock'] - $item['quantity'];
            if ($stock < 0) {
                throw new Exception("Not enough stock for product: " . $product['product_name']);
            }

            $updateStockSql = "UPDATE products SET stock = ? WHERE product_id = ?";
            $stockResult = updateRecord($updateStockSql, [
                $stock,
                $item['product_id']
            ]);

            if (!$stockResult) {
                throw new Exception("Can't update stock for product: " . $product['product_name']);
            }
        }

        // Commit transaction khi mọi thứ thành công
        $db->commit();

        $result['success'] = true;
        $result['message'] = "Order created successfully";
        $result['order_id'] = $orderId;

        return $result;

    } catch (Exception $e) {
        // Rollback transaction nếu có lỗi
        if (isset($db) && $db->inTransaction()) {
            $db->rollBack();
        }

        $result['success'] = false;
        $result['message'] = $e->getMessage();

        // Log lỗi
        error_log("Order Error: " . $e->getMessage());

        return $result;
    }
}

// Get user ID from JWT
function getUserIdFromJwt()
{
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    if (isset($_COOKIE['jwt'])) {
        require_once dirname(__FILE__) . '/auth.php';
        $user = verify_jwt($_COOKIE['jwt']);
        if ($user !== false) {
            $username = $user['username'];
            $sql = "SELECT user_id FROM users WHERE username = ?";
            $result = getRecord($sql, [$username]);
            if ($result) {
                return $result['user_id'];
            }
        }
    }
    return false;
}

// Hàm kiểm tra xem có phải là admin hay không
function isAdmin()
{
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    if (isset($_COOKIE['jwt'])) {
        require_once dirname(__FILE__) . '/auth.php';
        $user = verify_jwt($_COOKIE['jwt']);
        if ($user !== false) {
            $role = $user['role'];
            return $role === 'admin';
        }
    }
    return false;
}

// Hàm kiểm tra xem người dùng đã đăng nhập hay chưa
function isLoggedIn()
{
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    if (isset($_COOKIE['jwt'])) {
        require_once dirname(__FILE__) . '/auth.php';
        $user = verify_jwt($_COOKIE['jwt']);
        if ($user !== false) {
            return true;
        }
    }
    return false;
}

// Kiểm tra quyền admin
function checkAdminAccess()
{
    if (!check_login() || !isAdmin()) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Access denied. Admin privileges required.']);
        exit;
    }
}

// Hàm xác thực token reset mật khẩu
function validateResetToken($token)
{
    global $msg, $msg_type, $user;

    $is_valid = false;

    if (empty($token)) {
        $msg = 'No reset token provided. Please request a password reset.';
        $msg_type = 'danger';
        return false;
    }

    // Verify if the token exists and has not expired
    $sql = "SELECT user_id, username, email, reset_expires FROM users 
            WHERE reset_token = ?";

    try {
        $tokenData = getRecord($sql, [$token]);

        if ($tokenData) {
            // Compare time with the expiration time
            $expiryTime = strtotime($tokenData['reset_expires']);
            $currentTime = time();

            if ($currentTime <= $expiryTime) {
                // Token is valid
                $user = $tokenData;
                $is_valid = true;
            } else {
                // Token has expired
                $msg = 'This password reset link has expired. Please request a new one.';
                $msg_type = 'danger';
            }
        } else {
            // Token does not exist
            $msg = 'Invalid password reset link. Please request a new password reset.';
            $msg_type = 'danger';
        }
    } catch (PDOException $e) {
        $msg = 'Error occurred. Please try again later.';
        $msg_type = 'danger';
    }
    return $is_valid;
}

// Hàm kiểm tra remember me cookie
function checkRememberMeCookie()
{
    if (isset($_COOKIE['remember_token']) && !empty($_COOKIE['remember_token'])) {
        $rememberToken = $_COOKIE['remember_token'];

        // Kiểm tra token trong database
        $sql = "SELECT user_id, username, role, remember_expires FROM users WHERE remember_token = ?";
        $user = getRecord($sql, [$rememberToken]);

        if ($user && strtotime($user['remember_expires']) > time()) {
            // Token hợp lệ, tạo JWT và đăng nhập người dùng
            $jwt = generate_jwt($user['user_id'], $user['username'], $user['role']);
            setcookie('jwt', $jwt, time() + (60 * 60 * 24), '/', '', false, true);

            // Làm mới thời gian hết hạn của token
            $expires = date('Y-m-d H:i:s', strtotime('+30 days'));
            $sql = "UPDATE users SET remember_expires = ? WHERE user_id = ?";
            updateRecord($sql, [$expires, $user['user_id']]);

            // Làm mới cookie
            setcookie('remember_token', $rememberToken, time() + (86400 * 30), '/', '', false, true);

            // Chuyển hướng đến trang chính
            header("Location: " . BASE_URL . "/public/index.php");
            exit();
        } else if ($user) {
            // Token hết hạn, xóa khỏi database và cookie
            $sql = "UPDATE users SET remember_token = NULL, remember_expires = NULL WHERE remember_token = ?";
            updateRecord($sql, [$rememberToken]);
            setcookie('remember_token', '', time() - 3600, '/', '', false, true);
        }
    }
}

/*==========================
    SESSION FUNCTIONS
==========================*/

// Hàm gán session
function setSession($key, $value)
{
    return $_SESSION[$key] = $value;
}

// Hàm đọc session
function getSession($key = '')
{
    if (empty($key)) {
        return $_SESSION;
    } else {
        if (isset($_SESSION[$key])) {
            return $_SESSION[$key];
        }
    }
}

// Hàm xoá session
function removeSession($key = '')
{
    if (empty($key)) {
        session_destroy();
        return true;
    } else {
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
            return true;
        }
    }
}

// Hàm gán flash data
function setFlashData($key, $value)
{
    $key = 'flash_' . $key;
    return setSession($key, $value);
}

// Hàm đọc flash data
function getFlashData($key)
{
    $key = 'flash_' . $key;
    $data = getSession($key);
    removeSession($key);
    return $data;
}