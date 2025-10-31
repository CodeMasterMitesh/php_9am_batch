<?php
// Centralized auth + security helpers

// Basic security headers (idempotent across includes)
if (!headers_sent()) {
    header('X-Frame-Options: SAMEORIGIN');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: same-origin');
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
}

function is_logged_in(): bool {
    return isset($_SESSION['user']) && isset($_SESSION['user']['id']);
}

function current_user_type(): string {
    return isset($_SESSION['user']['type']) ? strtolower((string)$_SESSION['user']['type']) : '';
}

function require_login(string $redirect = 'login.php') : void {
    if (is_logged_in()) {
        return;
    }
    // If expecting JSON, respond with JSON error; otherwise redirect
    if (expects_json()) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }
    header("Location: {$redirect}");
    exit;
}

function require_roles(array $allowedRoles, string $redirect = '404.php') : void {
    $uType = current_user_type();
    $allowed = array_map('strtolower', $allowedRoles);
    if (in_array($uType, $allowed, true)) {
        return;
    }
    if (expects_json()) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Forbidden']);
        exit;
    }
    header("Location: {$redirect}");
    exit;
}

function is_ajax_request(): bool {
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        return true;
    }
    // If the client explicitly prefers JSON, treat as ajax/API
    if (!empty($_SERVER['HTTP_ACCEPT']) && stripos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
        return true;
    }
    // Many fetch() calls send */*; allow it for API endpoints if content-type is json
    if (!empty($_SERVER['CONTENT_TYPE']) && stripos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
        return true;
    }
    return false;
}

function expects_json(): bool { return is_ajax_request(); }

// For JSON endpoints: if user tries to open in a browser (Accept: text/html), bounce
function deny_direct_browser_access(string $redirect = '../404.php'): void {
    if (!empty($_SERVER['HTTP_ACCEPT']) && stripos($_SERVER['HTTP_ACCEPT'], 'text/html') !== false && !is_ajax_request()) {
        header("Location: {$redirect}");
        exit;
    }
}

?>
