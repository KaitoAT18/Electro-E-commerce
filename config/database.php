<?php
require_once 'env.php';

// Ngăn truy cập trực tiếp vào file
if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}

// Cấu hình cơ sở dữ liệu
define('DB_HOST', getenv('DB_HOST'));
define('DB_NAME', getenv('DB_NAME'));
define('DB_USER', getenv('DB_USER'));
define('DB_PASS', getenv('DB_PASS'));
define('DB_CHARSET', getenv('DB_CHARSET'));
define('DB_PORT', getenv('DB_PORT'));

// Hàm tạo kết nối PDO
function getDB()
{
    static $pdo = null;
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $pdo = new PDO($dsn, DB_USER, DB_PASS);
            // Thiết lập chế độ lỗi
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            // Tắt mô phỏng prepared statements để tăng bảo mật
            $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            // Thiết lập fetch mode mặc định
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }
    return $pdo;
}

// Hàm đếm số bản ghi
function countRecords($query, $params = [])
{
    $pdo = getDB();
    try {
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        return $stmt->rowCount();
    } catch (PDOException $e) {
        return false;
    }
}

// Hàm insert bản ghi
function insertRecord($query, $params = [])
{
    $pdo = getDB();
    try {
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        return $pdo->lastInsertId();
    } catch (PDOException $e) {
        return false;
    }
}

// Hàm lấy một bản ghi
function getRecord($query, $params = [])
{
    $pdo = getDB();
    try {
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        return $stmt->fetch();
    } catch (PDOException $e) {
        return false;
    }
}

// Hàm lấy nhiều bản ghi
function getRecords($query, $params = [])
{
    $pdo = getDB();
    try {
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return false;
    }
}

// Hàm cập nhật bản ghi
function updateRecord($query, $params = [])
{
    $pdo = getDB();
    try {
        $stmt = $pdo->prepare($query);
        return $stmt->execute($params);
    } catch (PDOException $e) {
        return false;
    }
}

// Update or Delete bản ghi
function updateOrDeleteRecord($query, $params = [])
{
    $pdo = getDB();
    try {
        $stmt = $pdo->prepare($query);
        return $stmt->execute($params);
    } catch (PDOException $e) {
        return false;
    }
}
?>