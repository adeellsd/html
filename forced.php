<?php
session_start();
$__connected = array(
    "username" => $_SESSION["username"] ?? null,
    "ADMIN" => $_SESSION["admin"] ?? 0
);

if (! $__connected["username"]) {
    if (! isset($LOGIN_PAGE)) {
        header("Location: /login.php");
        die();
    }
}

?>