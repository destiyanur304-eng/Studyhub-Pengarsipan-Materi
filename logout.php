<?php

session_start();

require_once __DIR__ . '/includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/index.php');
}

if (
    !isset($_POST['csrf_token']) ||
    !hash_equals(
        $_SESSION['csrf_token'] ?? '',
        $_POST['csrf_token']
    )
) {
    die("Permintaan tidak valid.");
}

$_SESSION = [];

session_destroy();

redirect('/login.php');