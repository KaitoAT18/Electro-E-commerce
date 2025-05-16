<?php

// Read .env and load environment variables
function loadEnv($path)
{
    if (!file_exists($path)) {
        return false;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Skip comment
        if (strpos(trim($line), '#') === 0) {
            continue;
        }

        if (strpos($line, '=') !== false) {
            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);

            if (strpos($value, '"') === 0 && strrpos($value, '"') === strlen($value) - 1) {
                $value = substr($value, 1, -1);
            }

            if (strpos($value, '${') !== false) {
                $value = preg_replace_callback('/\${([^}]+)}/', function ($matches) {
                    return getenv($matches[1]) ?: $_ENV[$matches[1]] ?? '';
                }, $value);
            }

            putenv("$name=$value");
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }
    }
    return true;
}

$envPath = dirname(__DIR__) . '/.env';
loadEnv($envPath);
?>