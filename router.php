<?php
// Get the requested URI
$uri = $_SERVER['REQUEST_URI'];

// Remove query string if present
if (($pos = strpos($uri, '?')) !== false) {
    $uri = substr($uri, 0, $pos);
}

// Remove leading slash
$uri = ltrim($uri, '/');

// If no specific file is requested, serve index.php
if (empty($uri) || $uri === 'index.php') {
    require 'index.php';
    return true;
}

// If the requested file exists, serve it
if (file_exists($uri)) {
    return false; // Let the server handle it
}

// If the file doesn't exist, serve index.php
require 'index.php';
return true;
?> 
