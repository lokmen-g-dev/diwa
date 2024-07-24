<?php
define('ROOT_PATH', __DIR__);
define('SYSTEM_PATH', ROOT_PATH . '/includes');
define('INSTALLATION_PATH', SYSTEM_PATH . '/installation');
define('CONTENT_PATH', ROOT_PATH . '/content');
define('LAYOUT_PATH', ROOT_PATH . '/layout');

// bootstrap DIWA
require_once SYSTEM_PATH . '/bootstrap.php';

// Set security headers
header('Content-Security-Policy: default-src \'self\'');
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('X-XSS-Protection: 1; mode=block');
header_remove('X-Powered-By');

// Function to generate CSRF token
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Function to validate CSRF token
function validate_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Start session
session_start();

// is HTTP Basic Auth enabled?
if (
    isset($config['auth']['username'])
    && !empty($config['auth']['username'])
    && isset($config['auth']['password'])
    && !empty($config['auth']['password'])
) {
    if (
        !isset($_SERVER['PHP_AUTH_USER'])
        || !isset($_SERVER['PHP_AUTH_PW'])
        || $_SERVER['PHP_AUTH_USER'] !== $config['auth']['username']
        || $_SERVER['PHP_AUTH_PW'] !== $config['auth']['password']
    ) {
        header('WWW-Authenticate: Basic realm="DIWA"');
        header('HTTP/1.0 401 Authorization Required');
        echo 'Please enter correct username and password.';
        exit;
    }
}

// perform a reset?
if (isset($_GET['reset']) && 'diwa' == $_GET['reset']) {
    try {
        if (!include(INSTALLATION_PATH . '/install.php')) {
            die('Error: could not include "install.php".');
        } else {
            die('DIWA\'s Database has been reset!<br/><a href="/">Back to DIWA</a>');
        }
    } catch (Exception $ex) {
        die('Error: could not include "install.php": ' . $ex->getMessage());
    }
}

// start output buffering
ob_start();

// include header
require_once LAYOUT_PATH . '/header.php';

// include content
if (isset($_GET['page'])) {
    $content = basename($_GET['page']);  // Prevent directory traversal
} else {
    $content = 'home';
}

$contentFile = CONTENT_PATH . '/' . $content . '.php';

if (file_exists($contentFile)) {
    require_once $contentFile;
} else {
    require_once CONTENT_PATH . '/404.php';
}

// include footer
require_once LAYOUT_PATH . '/footer.php';

// Send content
ob_end_flush();
?>
