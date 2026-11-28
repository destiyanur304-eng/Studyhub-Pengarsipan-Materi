<?php

$host = "localhost";
$dbname = "studyhub";
$username = "root";
$password = "";

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password
    );

    $pdo->setAttribute(
        PDO::ATTR_ERRMODE,
        PDO::ERRMODE_EXCEPTION
    );

    $pdo->setAttribute(
        PDO::ATTR_DEFAULT_FETCH_MODE,
        PDO::FETCH_ASSOC
    );

    // Menyimpan ID admin yang login agar trigger log_aktivitas tahu pelakunya
    if (
        session_status() === PHP_SESSION_ACTIVE &&
        ($_SESSION['user']['role'] ?? '') === 'admin'
    ) {
        $setPelaku = $pdo->prepare("SET @studyhub_user = ?");
        $setPelaku->execute([(int) $_SESSION['user']['id']]);
    }

} catch (PDOException $e) {
    die("Koneksi database gagal: " . $e->getMessage());
}