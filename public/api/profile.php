<?php
// Import config
require_once(__DIR__ . '/../../config/config.php');
require_once(__DIR__ . '/../../config/database.php');
require_once(__DIR__ . '/../../includes/auth.php');
require_once(__DIR__ . '/../../includes/functions.php');

session_start();
header("Content-Type: application/json");

// Check if user is logged in
$isLoggedIn = check_login();
if (!$isLoggedIn) {
    http_response_code(401);
    echo json_encode([
        'message' => 'You must be logged in to access profile features',
        'redirect' => BASE_URL . '/public/login.php'
    ]);
    exit;
}

// Get user ID from JWT
$userId = getUserIdFromJwt();
if (!$userId) {
    http_response_code(500);
    echo json_encode(['message' => 'Failed to retrieve user information']);
    exit;
}

// Get action from request
$action = '';

// Handle GET request (View profile, order statistics)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $filteredData = filter();
    $action = trim($filteredData['action'] ?? '');

    switch ($action) {
        case 'view':
            // Get user information
            $sql = "SELECT user_id, username, email, full_name, phone, address, avatar_path, created_at FROM users WHERE user_id = ?";
            $user = getRecord($sql, [$userId]);

            if (!$user) {
                http_response_code(404);
                echo json_encode(['message' => 'User not found']);
                exit;
            }

            // Get order count
            $sqlOrderCount = "SELECT COUNT(*) as total_orders FROM orders WHERE user_id = ?";
            $orderCount = getRecord($sqlOrderCount, [$userId]);

            echo json_encode([
                'success' => true,
                'message' => 'Profile data retrieved successfully',
                'data' => [
                    'user' => $user,
                    'stats' => [
                        'total_orders' => $orderCount ? $orderCount['total_orders'] : 0
                    ]
                ]
            ]);
            break;

        case 'orders':
            // Get order statistics
            $sqlOrders = "SELECT status, COUNT(*) as count FROM orders WHERE user_id = ? GROUP BY status";
            $orderStats = getRecords($sqlOrders, [$userId]);

            $result = [
                'total' => 0,
                'pending' => 0,
                'completed' => 0,
                'cancelled' => 0
            ];

            foreach ($orderStats as $stat) {
                if (isset($stat['status']) && isset($result[$stat['status']])) {
                    $result[$stat['status']] = (int) $stat['count'];
                    $result['total'] += (int) $stat['count'];
                }
            }

            echo json_encode([
                'success' => true,
                'message' => 'Order statistics retrieved successfully',
                'data' => $result
            ]);
            break;

        default:
            http_response_code(400);
            echo json_encode(['message' => 'Invalid action: ' . $action]);
            exit;
    }
}
// Handle POST request (Update profile, change password, upload avatar)
else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if the request is a file upload
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        // Handle file upload
        $action = isset($_POST['action']) ? trim($_POST['action']) : '';
    } else {
        $data = json_decode(file_get_contents('php://input'), true);
        $action = trim($data['action'] ?? '');
    }

    switch ($action) {
        case 'update':
            // Validate data
            $validationErrors = [];

            // Check CSRF token
            if (
                empty($data['csrf_token']) || !isset($_SESSION['csrf_token']) ||
                $data['csrf_token'] !== $_SESSION['csrf_token']
            ) {
                http_response_code(403);
                echo json_encode(['message' => 'Invalid CSRF token']);
                exit;
            }

            // Check email
            if (!empty($data['email'])) {
                if (!validate_email($data['email'])) {
                    $validationErrors[] = 'Invalid email format';
                } else {
                    // Check if email already exists
                    $sqlCheckEmail = "SELECT user_id FROM users WHERE email = ? AND user_id <> ?";
                    $existingUser = getRecord($sqlCheckEmail, [$data['email'], $userId]);

                    if ($existingUser) {
                        $validationErrors[] = 'Email already in use';
                    }
                }
            }

            // Check phone number
            if (!empty($data['phone']) && !validate_phone_number($data['phone'])) {
                $validationErrors[] = 'Invalid phone number format';
            }

            if (!empty($validationErrors)) {
                http_response_code(400);
                echo json_encode([
                    'message' => 'Validation errors',
                    'errors' => $validationErrors
                ]);
                exit;
            }

            $updateFields = [];
            $params = [];

            if (!empty($data['full_name'])) {
                $updateFields[] = "full_name = ?";
                $params[] = trim($data['full_name']);
            }

            if (!empty($data['email'])) {
                $updateFields[] = "email = ?";
                $params[] = trim($data['email']);
            }

            if (!empty($data['phone'])) {
                $updateFields[] = "phone = ?";
                $params[] = trim($data['phone']);
            }

            if (!empty($data['address'])) {
                $updateFields[] = "address = ?";
                $params[] = trim($data['address']);
            }

            if (empty($updateFields)) {
                http_response_code(400);
                echo json_encode(['message' => 'No fields to update']);
                exit;
            }

            $params[] = $userId;

            // Update user information
            $sql = "UPDATE users SET " . implode(", ", $updateFields) . ", updated_at = NOW() WHERE user_id = ?";
            $result = updateRecord($sql, $params);

            if ($result) {
                // Get updated user information
                $sqlUser = "SELECT user_id, username, email, full_name, phone, address, avatar_path FROM users WHERE user_id = ?";
                $updatedUser = getRecord($sqlUser, [$userId]);

                echo json_encode([
                    'success' => true,
                    'message' => 'Profile updated successfully',
                    'data' => $updatedUser
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['message' => 'Failed to update profile']);
            }
            break;

        case 'change-password':
            // Validate data
            if (empty($data['current_password']) || empty($data['new_password']) || empty($data['confirm_password'])) {
                http_response_code(400);
                echo json_encode(['message' => 'All password fields are required']);
                exit;
            }

            // Check CSRF token
            if (
                empty($data['csrf_token']) || !isset($_SESSION['csrf_token']) ||
                $data['csrf_token'] !== $_SESSION['csrf_token']
            ) {
                http_response_code(403);
                echo json_encode(['message' => 'Invalid CSRF token']);
                exit;
            }

            // Check new password and confirm password
            if ($data['new_password'] !== $data['confirm_password']) {
                http_response_code(400);
                echo json_encode(['message' => 'New password and confirm password do not match']);
                exit;
            }

            // Check password length
            if (strlen($data['new_password']) < 6) {
                http_response_code(400);
                echo json_encode(['message' => 'Password must be at least 6 characters long']);
                exit;
            }

            // Get current password from DB
            $sqlGetUser = "SELECT password FROM users WHERE user_id = ?";
            $user = getRecord($sqlGetUser, [$userId]);

            if (!$user) {
                http_response_code(404);
                echo json_encode(['message' => 'User not found']);
                exit;
            }

            // Verify current password
            if (!password_verify($data['current_password'], $user['password'])) {
                http_response_code(401);
                echo json_encode(['message' => 'Current password is incorrect']);
                exit;
            }

            // Hash new password
            $hashedPassword = password_hash($data['new_password'], PASSWORD_BCRYPT);

            // Update password in DB
            $sqlUpdatePassword = "UPDATE users SET password = ?, updated_at = NOW() WHERE user_id = ?";
            $result = updateRecord($sqlUpdatePassword, [$hashedPassword, $userId]);

            if ($result) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Password updated successfully'
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['message' => 'Failed to update password']);
            }
            break;

        case 'upload-avatar':
            // Check file upload
            if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
                $uploadError = $_FILES['avatar']['error'] ?? 'Unknown error';
                http_response_code(400);
                echo json_encode(['message' => 'File upload failed: ' . $uploadError]);
                exit;
            }

            // Check file type
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            $fileType = $_FILES['avatar']['type'];

            if (!in_array($fileType, $allowedTypes)) {
                http_response_code(400);
                echo json_encode(['message' => 'Only JPEG, PNG and GIF files are allowed']);
                exit;
            }

            // Check file size
            if ($_FILES['avatar']['size'] > MAX_FILE_SIZE) {
                http_response_code(400);
                echo json_encode(['message' => 'File size exceeds the limit of 2MB']);
                exit;
            }

            // Create a unique file name
            $extension = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
            $newFileName = 'avatar_' . $userId . '_' . time() . '.' . $extension;

            // Set upload directory
            $uploadDir = __DIR__ . '/../../assets/images/uploads/avatars/';
            $uploadPath = $uploadDir . $newFileName;
            $avatarPath = '/assets/images/uploads/avatars/' . $newFileName;

            // Create directory if it doesn't exist
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            chmod($uploadDir, 0777);

            // Move uploaded file to the upload directory
            if (move_uploaded_file($_FILES['avatar']['tmp_name'], $uploadPath)) {
                // Update avatar path in DB
                $sqlUpdateAvatar = "UPDATE users SET avatar_path = ?, updated_at = NOW() WHERE user_id = ?";
                $result = updateRecord($sqlUpdateAvatar, [$avatarPath, $userId]);

                if ($result) {
                    echo json_encode([
                        'success' => true,
                        'message' => 'Avatar uploaded successfully',
                        'data' => ['avatar_path' => $avatarPath]
                    ]);
                } else {
                    // Delete the uploaded file if DB update fails
                    unlink($uploadPath);
                    http_response_code(500);
                    echo json_encode(['message' => 'Failed to update avatar in database']);
                }
            } else {
                http_response_code(500);
                echo json_encode(['message' => 'Failed to move uploaded file']);
            }
            break;

        default:
            http_response_code(400);
            echo json_encode(['message' => 'Invalid action: ' . $action]);
            exit;
    }
} else {
    http_response_code(405);
    echo json_encode(['message' => 'Method not allowed']);
    exit;
}
?>