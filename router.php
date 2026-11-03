<?php

declare(strict_types=1);

// Router for: php -S localhost:8072 -t websites/goalgus websites/goalgus/router.php
// Static files under the -t document root are served when we return false.
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');
$root = __DIR__;

$file = $root.$uri;
if ($uri !== '/' && is_file($file)) {
    return false; // let the built-in server emit the asset
}

$candidates = [
    $root.$uri.'/index.php',
    $root.$uri.'index.php',
    $root.rtrim($uri, '/').'/index.php',
];

foreach ($candidates as $candidate) {
    if (is_file($candidate)) {
        require $candidate;
        return true;
    }
}

http_response_code(404);
echo 'Not Found';
return true;
