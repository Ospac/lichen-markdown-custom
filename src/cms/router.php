<?php
require_once __DIR__ . "/utils.php";

$requestUri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$requestUri = '/' . ltrim(urldecode($requestUri), '/');

if (str_contains($requestUri, '..')) {
    http_response_code(400);
    exit('Invalid path');
}

// Source and application files are not public URLs.
if (preg_match('#^/(src|page|dist)(/|$)#', $requestUri)) {
    http_response_code(404);
    exit('404 Not Found');
}

// CMS scripts remain under src/cms, while their public URLs stay /cms/....
if (preg_match('#^/cms/([^/]+\.php)(/.*)?$#', $requestUri, $matches)) {
    $script = CMS_DIR . '/' . $matches[1];
    if (!is_file($script)) {
        http_response_code(404);
        exit('404 Not Found');
    }
    $_SERVER['PATH_INFO'] = $matches[2] ?? '';
    if ($matches[1] === 'render.php') {
        unset($_SERVER['REDIRECT_URL']);
    }
    include $script;
    return true;
}

// Serve files outside the page source directly (assets, favicon, etc.).
$staticPath = PROJECT_DIR . $requestUri;
if (is_file($staticPath) && !str_ends_with(strtolower($staticPath), '.md')) {
    return false;
}

$publicPath = $requestUri;
$markdownPath = PAGE_DIR . $publicPath . '.md';
$indexMarkdownPath = PAGE_DIR . rtrim($publicPath, '/') . '/index.md';

if ($requestUri === '/' || str_ends_with($requestUri, '/')) {
    if (is_file($indexMarkdownPath)) {
        $_SERVER['PATH_INFO'] = content_relative_path(rtrim($publicPath, '/') . '/index.md');
        unset($_SERVER['REDIRECT_URL']);
        include CMS_DIR . '/render.php';
        return true;
    }
} elseif (is_file($markdownPath)) {
    $_SERVER['PATH_INFO'] = content_relative_path($publicPath . '.md');
    unset($_SERVER['REDIRECT_URL']);
    include CMS_DIR . '/render.php';
    return true;
}

// Explicit .html URLs render the corresponding Markdown source in development.
if (str_ends_with($requestUri, '.html')) {
    $markdownPath = PAGE_DIR . substr($requestUri, 0, -5) . '.md';
    if (is_file($markdownPath)) {
        $_SERVER['PATH_INFO'] = content_relative_path(substr($requestUri, 0, -5) . '.md');
        unset($_SERVER['REDIRECT_URL']);
        include CMS_DIR . '/render.php';
        return true;
    }
}

// Fall back to the static build when no source page is available.
$distPath = DIST_DIR . $requestUri;
if ($requestUri === '/' || str_ends_with($requestUri, '/')) {
    $distPath = DIST_DIR . rtrim($requestUri, '/') . '/index.html';
} elseif (!pathinfo($requestUri, PATHINFO_EXTENSION)) {
    $distPath = DIST_DIR . $requestUri . '.html';
}
if (is_file($distPath)) {
    readfile($distPath);
    return true;
}

http_response_code(404);
echo "404 Not Found";
