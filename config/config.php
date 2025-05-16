<?php
// Import env.php to load environment variables
require_once 'env.php';

if (!defined('BASEPATH')) {
    define('BASEPATH', dirname(__DIR__));
}

// Base URL
define('BASE_URL', getenv('BASE_URL'));

// JWT Secret Key
define('JWT_SECRET_KEY', getenv('JWT_SECRET_KEY'));

// Limit upload file size
define('MAX_FILE_SIZE', 2 * 1024 * 1024);   // 2MB

// Path to local image directory
define('IMAGE_DIR', BASE_URL . '/assets/images/local/');

// Path to javascript files
define('JS_DIR', BASE_URL . '/assets/js/');

// Path to css files
define('CSS_DIR', BASE_URL . '/assets/css/');

