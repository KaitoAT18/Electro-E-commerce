<?php
// Import config
require_once(__DIR__ . '/../../config/config.php');
require_once(__DIR__ . '/../../config/database.php');
require_once(__DIR__ . '/../../includes/auth.php');
require_once(__DIR__ . '/../../includes/functions.php');

session_start();
header("Content-Type: application/json");

// Validate CSRF token
function validate_csrf_token($token)
{
    if (!isset($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

// Regenerate CSRF token
function regenerate_csrf_token()
{
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf_token'];
}

// Handle GET requests (Get user list or user details)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Check if user is logged in and has admin access
    checkAdminAccess();
    $filteredData = filter();
    $action = isset($filteredData['action']) ? trim($filteredData['action']) : 'list';

    switch ($action) {
        case 'list':
            // Pagination
            $page = isset($filteredData['page']) ? intval($filteredData['page']) : 1;
            $limit = isset($filteredData['limit']) ? intval($filteredData['limit']) : 10;
            if ($page < 1)
                $page = 1;
            if ($limit < 1 || $limit > 100)
                $limit = 10;
            $offset = ($page - 1) * $limit;

            // Search
            $search = isset($filteredData['search']) ? trim($filteredData['search']) : '';
            $searchCondition = '';
            $params = [];

            if (!empty($search)) {
                $searchCondition = "WHERE username LIKE ? OR email LIKE ? OR full_name LIKE ?";
                $searchParam = "%$search%";
                $params = [$searchParam, $searchParam, $searchParam];
            }

            // Count total users
            $countSql = "SELECT COUNT(*) as total FROM users $searchCondition";
            $totalResult = getRecords($countSql, $params);
            $total = $totalResult ? $totalResult[0]['total'] : 0;

            // Get user list
            $sql = "SELECT user_id, username, email, full_name, role, is_active, created_at 
                   FROM users $searchCondition 
                   ORDER BY user_id ASC LIMIT ? OFFSET ?";

            // Add limit and offset to params
            $params[] = $limit;
            $params[] = $offset;

            $users = getRecords($sql, $params);

            // Reformat data
            foreach ($users as &$user) {
                $user['created_at'] = date('Y-m-d H:i:s', strtotime($user['created_at']));
                $user['is_active'] = (bool) $user['is_active'];
            }

            $totalPages = ceil($total / $limit);

            echo json_encode([
                'success' => true,
                'data' => $users,
                'pagination' => [
                    'total' => $total,
                    'per_page' => $limit,
                    'current_page' => $page,
                    'last_page' => $totalPages
                ]
            ]);
            break;

        case 'detail':
            if (!isset($filteredData['id'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'User ID is required']);
                exit;
            }

            $userId = intval($filteredData['id']);
            if ($userId < 1) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Invalid User ID']);
                exit;
            }
            $sql = "SELECT user_id, username, email, full_name, phone, address, role, avatar_path, is_active, created_at 
                   FROM users WHERE user_id = ?";
            $user = getRecord($sql, [$userId]);

            if (!$user) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'User not found']);
                exit;
            }

            // Reformat data
            $user['created_at'] = date('Y-m-d H:i:s', strtotime($user['created_at']));
            $user['is_active'] = (bool) $user['is_active'];

            echo json_encode(['success' => true, 'data' => $user]);
            break;

        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            break;
    }
}
// Handle POST requests (Create new user, update user, delete user)
else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get data from request body
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid input data']);
        exit;
    }

    // Get CSRF token from request
    $csrfToken = isset($data['csrf_token']) ? $data['csrf_token'] : null;

    // Check CSRF token
    if (!validate_csrf_token($csrfToken)) {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'message' => 'Invalid security token or session expired. Please refresh the page.'
        ]);
        exit;
    }

    $filteredData = filter();
    $action = isset($data['action']) ? $data['action'] : (isset($filteredData['action']) ? $filteredData['action'] : 'create');

    switch ($action) {
        case 'create':
            // Check if user is logged in and has admin access
            checkAdminAccess();

            // Validate input data
            $errors = [];

            // Validate username
            if (empty($data['username']) || !validate_username($data['username'])) {
                $errors[] = 'Invalid username. Username must be 5-20 characters and can only contain letters, numbers.';
            } else {
                // Check if username already exists
                $checkSql = "SELECT user_id FROM users WHERE username = ?";
                $existingUser = getRecord($checkSql, [$data['username']]);
                if ($existingUser) {
                    $errors[] = 'Username already exists';
                }
            }

            // Validate email
            if (empty($data['email']) || !validate_email($data['email'])) {
                $errors[] = 'Invalid email format';
            } else {
                // Check if email already exists
                $checkSql = "SELECT user_id FROM users WHERE email = ?";
                $existingEmail = getRecord($checkSql, [$data['email']]);
                if ($existingEmail) {
                    $errors[] = 'Email already exists';
                }
            }

            // Validate password
            if (empty($data['password']) || strlen($data['password']) < 6 || !validate_password($data['password'])) {
                $errors[] = 'Password must be at least 6 characters long and contain uppercase, lowercase letters, numbers, and special characters';
            }

            // Validate role
            if (isset($data['role']) && !in_array($data['role'], ['admin', 'user'])) {
                $errors[] = 'Invalid role value';
            }

            if (!empty($errors)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Validation failed', 'errors' => $errors]);
                exit;
            }

            // Create new user
            $username = $data['username'];
            $email = $data['email'];
            $password = password_hash($data['password'], PASSWORD_BCRYPT);
            $role = isset($data['role']) ? $data['role'] : 'user';
            $firstName = isset($data['first_name']) ? $data['first_name'] : '';
            $lastName = isset($data['last_name']) ? $data['last_name'] : '';
            $fullName = trim($firstName . ' ' . $lastName);
            $phone = isset($data['phone']) ? $data['phone'] : '';
            $address = isset($data['address']) ? $data['address'] : '';
            $isActive = isset($data['is_active']) ? ($data['is_active'] ? 1 : 0) : 1;

            $sql = "INSERT INTO users (username, email, password, role, full_name, phone, address, is_active) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $params = [$username, $email, $password, $role, $fullName, $phone, $address, $isActive];

            $result = insertRecord($sql, $params);

            if ($result) {
                $userId = $result;

                // Get user details after creation
                $userSql = "SELECT user_id, username, email, full_name, role, is_active, created_at 
                           FROM users WHERE user_id = ?";
                $user = getRecord($userSql, [$userId]);

                $newCsrfToken = regenerate_csrf_token();
                echo json_encode([
                    'success' => true,
                    'message' => 'User created successfully',
                    'data' => $user,
                    'csrf_token' => $newCsrfToken
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to create user']);
            }
            break;

        case 'update':
            checkAdminAccess();

            // Get user_id from request body or query string
            $userId = isset($data['user_id']) ? intval($data['user_id']) : (isset($filteredData['id']) ? intval($filteredData['id']) : 0);

            if (!$userId) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'User ID is required']);
                exit;
            }

            // Check if user exists
            $checkSql = "SELECT user_id FROM users WHERE user_id = ?";
            $existingUser = getRecord($checkSql, [$userId]);

            if (!$existingUser) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'User not found']);
                exit;
            }

            $updateFields = [];
            $params = [];

            // Email
            if (isset($data['email'])) {
                if (!validate_email($data['email'])) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'message' => 'Invalid email format']);
                    exit;
                }

                // Check if email already exists (excluding current user)
                $checkEmailSql = "SELECT user_id FROM users WHERE email = ? AND user_id != ?";
                $existingEmail = getRecord($checkEmailSql, [$data['email'], $userId]);

                if ($existingEmail) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'message' => 'Email already in use by another user']);
                    exit;
                }

                $updateFields[] = "email = ?";
                $params[] = $data['email'];
            }

            // Full name
            if (isset($data['full_name'])) {
                $updateFields[] = "full_name = ?";
                $params[] = $data['full_name'];
            }

            // Phone
            if (isset($data['phone'])) {
                $updateFields[] = "phone = ?";
                $params[] = $data['phone'];
            }

            // Address
            if (isset($data['address'])) {
                $updateFields[] = "address = ?";
                $params[] = $data['address'];
            }

            // Role
            if (isset($data['role'])) {
                if (!in_array($data['role'], ['admin', 'user'])) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'message' => 'Invalid role value']);
                    exit;
                }
                $updateFields[] = "role = ?";
                $params[] = $data['role'];
            }

            // Active status
            if (isset($data['is_active'])) {
                $isActive = $data['is_active'] ? 1 : 0;
                $updateFields[] = "is_active = ?";
                $params[] = $isActive;
            }

            // Password
            if (!empty($data['password'])) {
                if (strlen($data['password']) < 6) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters long']);
                    exit;
                }

                $updateFields[] = "password = ?";
                $params[] = password_hash($data['password'], PASSWORD_BCRYPT);
            }

            if (empty($updateFields)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'No fields to update']);
                exit;
            }

            $params[] = $userId;

            $sql = "UPDATE users SET " . implode(", ", $updateFields) . " WHERE user_id = ?";
            $result = updateRecord($sql, $params);

            if ($result) {
                // Get user details after update
                $userSql = "SELECT user_id, username, email, full_name, role, is_active, created_at 
                           FROM users WHERE user_id = ?";
                $user = getRecord($userSql, [$userId]);

                $newCsrfToken = regenerate_csrf_token();
                echo json_encode([
                    'success' => true,
                    'message' => 'User updated successfully',
                    'data' => $user,
                    'csrf_token' => $newCsrfToken
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to update user']);
            }
            break;

        // Delete or deactivate user
        case 'delete':
            checkAdminAccess();

            // Get user_id from request body or query string
            $userId = isset($data['user_id']) ? intval($data['user_id']) : (isset($filteredData['id']) ? intval($filteredData['id']) : 0);

            if (!$userId) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'User ID is required']);
                exit;
            }

            // Check if user exists
            $checkSql = "SELECT user_id FROM users WHERE user_id = ?";
            $existingUser = getRecord($checkSql, [$userId]);

            if (!$existingUser) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'User not found']);
                exit;
            }

            // Check if trying to delete self
            $currentUserId = getUserIdFromJwt();
            if ($userId == $currentUserId) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'You cannot delete your own account']);
                exit;
            }

            // Decide whether to hard delete or soft delete
            $mode = isset($data['mode']) ? trim($data['mode']) : (isset($filteredData['mode']) ? trim($filteredData['mode']) : 'soft');

            if ($mode === 'hard') {
                // Hard delete user
                $sql = "DELETE FROM users WHERE user_id = ?";
            } else {
                // Soft delete user (deactivate)
                $sql = "UPDATE users SET is_active = 0 WHERE user_id = ?";
            }

            $result = updateOrDeleteRecord($sql, [$userId]);

            if ($result) {
                $newCsrfToken = regenerate_csrf_token();
                echo json_encode([
                    'success' => true,
                    'message' => ($mode === 'hard') ? 'User deleted successfully' : 'User deactivated successfully',
                    'csrf_token' => $newCsrfToken
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to process user deletion']);
            }
            break;

        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            break;
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}
?>