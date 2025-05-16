<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// Function to create JWT
function generate_jwt($user_id, $username, $role)
{
    $payload = [
        'iss' => BASE_URL,
        'sub' => $user_id,
        'username' => $username,
        'role' => $role,
        'iat' => time(),
        'exp' => time() + (60 * 60 * 24) // Token valid for 1 day
    ];
    return JWT::encode($payload, JWT_SECRET_KEY, 'HS256');
}

// Function to verify JWT
function verify_jwt($jwt)
{
    if (empty($jwt)) {
        return false;
    }

    // Check if the token is blacklisted
    try {
        $sql = "SELECT * FROM jwt_blacklist WHERE token = ?";
        $rows = countRecords($sql, [$jwt]);
        if ($rows > 0) {
            return false; // Token is blacklisted
        }
    } catch (Exception $e) {
        return false; // Token is invalid
    }

    // Verify the token
    try {
        $decoded = JWT::decode($jwt, new Key(JWT_SECRET_KEY, 'HS256'));
        return (array) $decoded;
    } catch (Exception $e) {
        return false; // Token is invalid
    }
}
?>