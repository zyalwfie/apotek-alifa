<?php
require_once 'auth_functions.php';

if (!isLoggedIn()) {
    header("Location: /apotek-alifa/layouts/landing/");
    exit();
}

logout();
