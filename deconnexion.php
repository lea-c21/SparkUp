<?php
session_start();
require 'csrf.php';

if (isset($_GET['token'])) {
    if (!verifyCsrfToken($_GET['token'])) {
        http_response_code(403);
        die('Erreur de sécurité : token CSRF invalide.');
    }
}

$_SESSION = array();

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

session_destroy();

header('Location: index.php');
exit();
?>