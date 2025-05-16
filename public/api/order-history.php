<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

session_start();

header("Content-Type: application/json");

$response = [
    'success' => false,
    'message' => '',
    'data' => null,
];

// Check if user is logged in and is an admin
if (!check_login() || !isAdmin()) {
    $response['message'] = 'Unauthorized access';
    http_response_code(403);
    echo json_encode($response);
    exit;
}

try {
    // Query to get order history
    $query = "SELECT 
                 o.order_id,
                 o.user_id,
                 o.created_at as order_date,
                 o.first_name,
                 o.last_name,
                 o.email,
                 o.phone,
                 o.address,
                 o.city,
                 o.country,
                 o.zip_code,
                 o.total_amount,
                 o.status,
                 u.username,
                 u.full_name,  /* Lấy trực tiếp từ users */
                 (SELECT COUNT(*) FROM order_details WHERE order_id = o.order_id) as item_count
              FROM 
                 orders o
              JOIN 
                 users u ON o.user_id = u.user_id
              ORDER BY 
                 o.created_at DESC";

    $orders = getRecords($query, []);

    foreach ($orders as &$order) {
        // Format date
        $order['order_date_formatted'] = date('d/m/Y H:i', strtotime($order['order_date']));

        // Format amount
        $order['total_amount_formatted'] = number_format($order['total_amount'], 2, '.', ',');

        // Sử dụng full_name từ bảng users, nếu không có thì ghép first_name và last_name từ orders
        $order['customer_name'] = !empty($order['full_name']) ?
            $order['full_name'] :
            $order['first_name'] . ' ' . $order['last_name'];

        // Format status
        $order['status_text'] = ucfirst($order['status']);

        // Add status class for Bootstrap label
        switch ($order['status']) {
            case 'completed':
                $order['status_class'] = 'label-success';
                break;
            case 'processing':
                $order['status_class'] = 'label-info';
                break;
            case 'shipped':
                $order['status_class'] = 'label-primary';
                break;
            case 'cancelled':
                $order['status_class'] = 'label-danger';
                break;
            default:
                $order['status_class'] = 'label-warning'; // pending
                break;
        }

        // Get order items
        $itemQuery = "SELECT 
                         od.order_detail_id,
                         od.product_id,
                         od.quantity,
                         od.price,
                         (od.quantity * od.price) as total_price,
                         p.product_name,
                         pi.image_path
                      FROM 
                         order_details od
                      JOIN 
                         products p ON od.product_id = p.product_id
                      LEFT JOIN 
                         (SELECT product_id, image_path FROM product_images WHERE is_primary = 1) pi 
                         ON p.product_id = pi.product_id
                      WHERE 
                         od.order_id = ?";
        $items = getRecords($itemQuery, [$order['order_id']]);

        // Format order items
        foreach ($items as &$item) {
            // Format price and total price
            $item['price_formatted'] = number_format($item['price'], 2, '.', ',');
            $item['total_price_formatted'] = number_format($item['total_price'], 2, '.', ',');

            // Path image
            if (!empty($item['image_path'])) {
                $item['image_url'] = BASE_URL . $item['image_path'];
            } else {
                $item['image_url'] = BASE_URL . '/assets/images/local/product05.png';
            }
        }

        $order['items'] = $items;
    }

    // Success response
    $response = [
        'success' => true,
        'message' => 'Order history retrieved successfully',
        'data' => $orders,
        'count' => count($orders)
    ];

    echo json_encode($response);

} catch (PDOException $e) {
    $response['message'] = 'An Error occurred. Please try again later.';
    http_response_code(500);
    echo json_encode($response);

} catch (Exception $e) {
    $response['message'] = 'An error occurred. Please try again later.';
    http_response_code(500);
    echo json_encode($response);
}
?>