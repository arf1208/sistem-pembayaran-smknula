<?php
require_once 'config/database.php';

// 1. Kosongkan array $_SESSION
$_SESSION = array();

// 2. Hancurkan cookie session di peramban jika ada
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 3. Hancurkan session secara permanen di server
session_destroy();

// 4. Alihkan kembali ke halaman login dengan status sukses
header("Location: login.php?status=logout_success");
exit();
?>