<?php

session_start();

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

if (isset($_SESSION['user'])) {

    if ($_SESSION['user']['role'] === 'admin') {
        redirect('/admin/dashboard.php');

    } elseif ($_SESSION['user']['role'] === 'mahasiswa') {
        redirect('/mahasiswa/dashboard.php');
    }

} else {
    redirect('/login.php');
}
