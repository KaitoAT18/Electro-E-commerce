<?php
// Import config
require_once(__DIR__ . '/../../config/config.php');
require_once(__DIR__ . '/../../config/database.php');
require_once(__DIR__ . '/../../includes/auth.php');
require_once(__DIR__ . '/../../includes/functions.php');

session_start();

header("Content-Type: application/json");

// Check if the user is logged in
$isLoggedIn = check_login();
if (!$isLoggedIn) {
    http_response_code(401);
    echo json_encode([
        'message' => 'You must be logged in to access cart features',
        'redirect' => BASE_URL . '/public/login.php'
    ]);
    exit;
}

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$action = '';

// Handle GET request
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $filteredData = filter();
    $action = trim($filteredData['action'] ?? '');

    if ($action === 'view') {
        $cart = [];
        foreach ($_SESSION['cart'] as $productId => $item) {
            $product = getProduct($item['product_id']);
            if ($product) {
                $cart[] = [
                    'product_id' => $item['product_id'],
                    'product_name' => $item['product_name'],
                    'price' => $product['sale_price'] ?? $product['price'],
                    'quantity' => $item['quantity'],
                    'primary_image' => $product['primary_image'],
                    'category_name' => $product['category_name'],
                    'subtotal' => ($product['sale_price'] ?? $product['price']) * $item['quantity']
                ];
            }
        }

        echo json_encode([
            'message' => empty($cart) ? 'Cart is empty' : 'Cart retrieved successfully',
            'data' => $cart
        ]);
        exit;
    } else if (!empty($action)) {
        http_response_code(405);
        echo json_encode(['message' => 'Action ' . $action . ' requires POST method']);
        exit;
    }
}
// Handle POST request
else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get JSON data from the request body
    $data = json_decode(file_get_contents('php://input'), true);
    $action = trim($data['action'] ?? '');

    switch ($action) {
        case 'add':
            $productId = isset($data['product_id']) ? (int) $data['product_id'] : 0;
            $quantity = isset($data['quantity']) ? (int) $data['quantity'] : 1;

            if ($productId <= 0 || $quantity <= 0) {
                http_response_code(400);
                echo json_encode(['message' => 'Invalid product ID or quantity']);
                exit;
            }

            $product = getProduct($productId);
            if (!$product) {
                http_response_code(404);
                echo json_encode(['message' => 'Product not found']);
                exit;
            }

            if ($product['stock'] < $quantity) {
                http_response_code(400);
                echo json_encode(['message' => 'Insufficient stock']);
                exit;
            }

            if (isset($_SESSION['cart'][$productId])) {
                $_SESSION['cart'][$productId]['quantity'] += $quantity;
            } else {
                $_SESSION['cart'][$productId] = [
                    'product_id' => $product['product_id'],
                    'product_name' => $product['product_name'],
                    'price' => $product['sale_price'] ?? $product['price'],
                    'quantity' => $quantity,
                    'primary_image' => $product['primary_image'],
                    'category_name' => $product['category_name']
                ];
            }
            echo json_encode([
                'message' => 'Product added to cart',
                'data' => $_SESSION['cart']
            ]);
            break;

        case 'update':
            $productId = intval($data['product_id'] ?? 0);
            $quantity = intval($data['quantity'] ?? 0);

            if ($productId <= 0 || $quantity <= 0) {
                http_response_code(400);
                echo json_encode(['message' => 'Invalid product ID or quantity']);
                exit;
            }

            // Check if the product exists
            $product = getProduct($productId);
            if (!$product) {
                http_response_code(404);
                echo json_encode(['message' => 'Product not found']);
                exit;
            }

            // Check stock
            if ($product['stock'] < $quantity) {
                http_response_code(400);
                echo json_encode([
                    'message' => 'Insufficient stock. Only ' . $product['stock'] . ' ' . $product['product_name'] . ' items for available.',
                    'available_stock' => $product['stock']
                ]);
                exit;
            }

            // Update the quantity in the cart
            if (isset($_SESSION['cart'][$productId])) {
                $_SESSION['cart'][$productId]['quantity'] = $quantity;

                echo json_encode([
                    'message' => 'Product quantity updated successfully',
                    'data' => [
                        'product_id' => $productId,
                        'quantity' => $quantity,
                        'price' => $product['sale_price'] ?? $product['price'],
                        'subtotal' => ($product['sale_price'] ?? $product['price']) * $quantity
                    ]
                ]);
            } else {
                http_response_code(404);
                echo json_encode(['message' => 'Product not found in cart']);
            }
            exit;

        case 'remove':
            $productId = isset($data['product_id']) ? (int) $data['product_id'] : 0;

            if ($productId <= 0) {
                http_response_code(400);
                echo json_encode(['message' => 'Invalid product ID']);
                exit;
            }

            if (!isset($_SESSION['cart'][$productId])) {
                http_response_code(404);
                echo json_encode(['message' => 'Product not in cart']);
                exit;
            }

            unset($_SESSION['cart'][$productId]);

            echo json_encode([
                'message' => 'Product removed from cart',
                'data' => $_SESSION['cart']
            ]);
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