<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/functions.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function requireLogin()
{
    if (!isset($_SESSION['user'])) {
        redirect('/login.php');
    }
}

function requireRole($role)
{
    requireLogin();

    if ($_SESSION['user']['role'] !== $role) {
        redirect('/index.php');
    }
}