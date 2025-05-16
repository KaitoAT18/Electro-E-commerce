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
        'message' => 'You must be logged in to access order features',
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

// Process GET requests (view orders)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $filteredData = filter();
    $action = trim($filteredData['action'] ?? '');

    // Handle different GET actions
    switch ($action) {
        case 'list':
            // Get all orders for the current user
            $page = isset($filteredData['page']) ? (int) $filteredData['page'] : 1;
            $limit = isset($filteredData['limit']) ? (int) $filteredData['limit'] : 10;
            $offset = ($page - 1) * $limit;

            // Query to get orders
            $sqlOrders = "SELECT o.*, 
                COUNT(od.order_detail_id) as total_items 
                FROM orders o 
                LEFT JOIN order_details od ON o.order_id = od.order_id 
                WHERE o.user_id = ? 
                GROUP BY o.order_id 
                ORDER BY o.created_at DESC 
                LIMIT ?, ?";

            $orders = getRecords($sqlOrders, [$userId, $offset, $limit]);

            // Count total orders for pagination
            $sqlCount = "SELECT COUNT(*) as count FROM orders WHERE user_id = ?";
            $countResult = getRecord($sqlCount, [$userId]);
            $totalOrders = $countResult ? $countResult['count'] : 0;
            $totalPages = ceil($totalOrders / $limit);

            echo json_encode([
                'success' => true,
                'message' => empty($orders) ? 'No orders found' : 'Orders retrieved successfully',
                'data' => $orders,
                'pagination' => [
                    'current_page' => $page,
                    'total_pages' => $totalPages,
                    'total_orders' => $totalOrders,
                    'limit' => $limit
                ]
            ]);
            break;

        case 'detail':
            // Get details of a specific order
            $orderId = isset($filteredData['order_id']) ? (int) $filteredData['order_id'] : 0;

            if ($orderId <= 0) {
                http_response_code(400);
                echo json_encode(['message' => 'Invalid order ID']);
                exit;
            }

            // First verify this order belongs to the current user
            $sqlOrder = "SELECT * FROM orders WHERE order_id = ? AND user_id = ?";
            $order = getRecord($sqlOrder, [$orderId, $userId]);

            if (!$order) {
                http_response_code(404);
                echo json_encode(['message' => 'Order not found or access denied']);
                exit;
            }

            // Get order items
            $sqlItems = "SELECT od.*, p.product_name, p.description, pi.image_path as primary_image
                FROM order_details od 
                LEFT JOIN products p ON od.product_id = p.product_id 
                LEFT JOIN product_images pi ON p.product_id = pi.product_id AND pi.is_primary = 1
                WHERE od.order_id = ?";
            $orderItems = getRecords($sqlItems, [$orderId]);

            // Combine order and items
            $orderDetails = [
                'order' => $order,
                'items' => $orderItems
            ];

            echo json_encode([
                'success' => true,
                'message' => 'Order details retrieved successfully',
                'data' => $orderDetails
            ]);
            break;

        case 'count':
            // Get count of orders by status
            $sqlCount = "SELECT status, COUNT(*) as count 
                FROM orders 
                WHERE user_id = ? 
                GROUP BY status";
            $statusCounts = getRecords($sqlCount, [$userId]);

            // Format the result
            $result = [
                'total' => 0,
                'pending' => 0,
                'completed' => 0,
                'cancelled' => 0
            ];

            foreach ($statusCounts as $status) {
                if (isset($status['status']) && isset($result[$status['status']])) {
                    $result[$status['status']] = (int) $status['count'];
                    $result['total'] += (int) $status['count'];
                }
            }

            echo json_encode([
                'success' => true,
                'message' => 'Order counts retrieved successfully',
                'data' => $result
            ]);
            break;

        default:
            http_response_code(400);
            echo json_encode(['message' => 'Invalid action: ' . $action]);
            exit;
    }
}
// Process POST requests (create/update orders)
else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get JSON data from request body
    $data = json_decode(file_get_contents('php://input'), true);
    $action = trim($data['action'] ?? '');

    switch ($action) {
        case 'create':
            // Check if cart is empty
            if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
                http_response_code(400);
                echo json_encode(['message' => 'Cannot create order with empty cart']);
                exit;
            }

            // Validate required fields
            $requiredFields = ['first_name', 'last_name', 'email', 'address', 'city', 'country', 'phone'];
            $errors = [];

            foreach ($requiredFields as $field) {
                if (!isset($data[$field]) || empty(trim($data[$field]))) {
                    $errors[] = ucfirst(str_replace('_', ' ', $field)) . ' is required';
                }
            }

            if (!empty($errors)) {
                http_response_code(400);
                echo json_encode([
                    'message' => 'Validation errors',
                    'errors' => $errors
                ]);
                exit;
            }

            // Calculate cart total
            $cartTotal = 0;
            $cartItems = [];

            foreach ($_SESSION['cart'] as $productId => $item) {
                $product = getProduct($item['product_id']);
                if ($product) {
                    $price = $product['sale_price'] ?? $product['price'];
                    $subtotal = $price * $item['quantity'];
                    $cartTotal += $subtotal;

                    $cartItems[] = [
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'price' => $price
                    ];
                }
            }

            // Begin transaction
            $conn = getDB();
            $conn->beginTransaction();

            try {
                // Insert into orders table
                $sqlOrder = "INSERT INTO orders (user_id, first_name, last_name, email, phone, address, city, country, zip_code, total_amount, status, created_at) 
                             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())";

                $stmtOrder = $conn->prepare($sqlOrder);
                $stmtOrder->execute([
                    $userId,
                    trim($data['first_name']),
                    trim($data['last_name']),
                    trim($data['email']),
                    trim($data['phone']),
                    trim($data['address']),
                    trim($data['city']),
                    trim($data['country']),
                    trim($data['zip_code'] ?? ''),
                    $cartTotal
                ]);

                $orderId = $conn->lastInsertId();

                // Insert order details
                $sqlDetail = "INSERT INTO order_details (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)";
                $stmtDetail = $conn->prepare($sqlDetail);

                foreach ($cartItems as $item) {
                    $stmtDetail->execute([
                        $orderId,
                        $item['product_id'],
                        $item['quantity'],
                        $item['price']
                    ]);

                    // Update product stock (optional)
                    $sqlUpdateStock = "UPDATE products SET stock = stock - ? WHERE product_id = ?";
                    $stmtStock = $conn->prepare($sqlUpdateStock);
                    $stmtStock->execute([$item['quantity'], $item['product_id']]);
                }

                // Commit transaction
                $conn->commit();

                // Clear the cart after successful order
                unset($_SESSION['cart']);

                echo json_encode([
                    'success' => true,
                    'message' => 'Order created successfully',
                    'data' => [
                        'order_id' => $orderId,
                        'total' => $cartTotal
                    ]
                ]);

            } catch (Exception $e) {
                // Rollback transaction on error
                $conn->rollBack();

                http_response_code(500);
                echo json_encode([
                    'message' => 'Failed to create order',
                    'error' => $e->getMessage()
                ]);
            }
            break;

        case 'cancel':
            // Cancel an existing order
            $orderId = isset($data['order_id']) ? (int) $data['order_id'] : 0;

            if ($orderId <= 0) {
                http_response_code(400);
                echo json_encode(['message' => 'Invalid order ID']);
                exit;
            }

            // Verify the order belongs to the current user
            $sqlVerify = "SELECT order_id, status FROM orders WHERE order_id = ? AND user_id = ?";
            $orderInfo = getRecord($sqlVerify, [$orderId, $userId]);

            if (!$orderInfo) {
                http_response_code(404);
                echo json_encode(['message' => 'Order not found or access denied']);
                exit;
            }

            // Check if the order can be cancelled
            if ($orderInfo['status'] === 'completed') {
                http_response_code(400);
                echo json_encode([
                    'message' => 'Cannot cancel this order. Order is already completed.'
                ]);
                exit;
            }

            // If order is already cancelled
            if ($orderInfo['status'] === 'cancelled') {
                echo json_encode([
                    'success' => true,
                    'message' => 'Order was already cancelled',
                    'data' => [
                        'order_id' => $orderId,
                        'status' => 'cancelled'
                    ]
                ]);
                exit;
            }

            // Begin transaction
            $conn = getDB();
            $conn->beginTransaction();

            try {
                // First get order details to restore product stock
                $sqlGetDetails = "SELECT product_id, quantity FROM order_details WHERE order_id = ?";
                $orderDetails = getRecords($sqlGetDetails, [$orderId]);

                // Update order status to cancelled
                $sqlUpdate = "UPDATE orders SET status = 'cancelled', updated_at = NOW() WHERE order_id = ?";
                $stmtUpdate = $conn->prepare($sqlUpdate);
                $stmtUpdate->execute([$orderId]);

                // Restore product stock for each item
                $sqlRestoreStock = "UPDATE products SET stock = stock + ? WHERE product_id = ?";
                $stmtRestore = $conn->prepare($sqlRestoreStock);

                foreach ($orderDetails as $item) {
                    $stmtRestore->execute([$item['quantity'], $item['product_id']]);
                }

                // Commit the transaction
                $conn->commit();

                echo json_encode([
                    'success' => true,
                    'message' => 'Order cancelled successfully',
                    'data' => [
                        'order_id' => $orderId,
                        'status' => 'cancelled'
                    ]
                ]);
            } catch (Exception $e) {
                // Rollback on error
                $conn->rollBack();

                http_response_code(500);
                echo json_encode([
                    'message' => 'Failed to cancel order',
                    'error' => $e->getMessage()
                ]);
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