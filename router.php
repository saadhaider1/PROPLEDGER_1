<?php
// Simple router for PHP built-in server
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = ltrim($uri, '/');

// If requesting a PHP file in auth, config, managers, or api directories
if (preg_match('/^(auth|config|managers|api)\/.*\.php$/', $uri)) {
    $file = __DIR__ . '/' . $uri;
    if (file_exists($file)) {
        return false; // Serve the PHP file
    }
}

// If requesting an HTML file
if (preg_match('/\.html$/', $uri)) {
    $file = __DIR__ . '/html/' . basename($uri);
    if (file_exists($file)) {
        include $file;
        return true;
    }
}

// If requesting CSS, JS, or images
if (preg_match('/\.(css|js|png|jpg|jpeg|gif|svg|ico|woff|woff2|ttf|eot)$/', $uri)) {
    // Try html/ directory first
    $file = __DIR__ . '/html/' . $uri;
    if (file_exists($file)) {
        return false; // Let PHP serve the static file
    }
    
    // Try root directory as fallback
    $file = __DIR__ . '/' . $uri;
    if (file_exists($file)) {
        return false; // Let PHP serve the static file
    }
}

// Default to index.html for root
if ($uri === '' || $uri === '/') {
    $file = __DIR__ . '/html/index.html';
    if (file_exists($file)) {
        include $file;
        return true;
    }
}

// 404 for everything else
http_response_code(404);
echo "404 - File not found: " . htmlspecialchars($uri);
return true;
?>
