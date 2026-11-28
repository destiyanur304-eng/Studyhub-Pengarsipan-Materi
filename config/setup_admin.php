<?php

require_once __DIR__ . '/database.php';

$nama = "Administrator";
$username = "admin";
$password = password_hash("admin123", PASSWORD_DEFAULT);
$role = "admin";

try {
    $cek = $pdo->prepare(
        "SELECT id FROM users WHERE username = ?"
    );
    $cek->execute([$username]);

    if ($cek->fetch()) {
        die("Akun admin sudah tersedia.");
    }

    $query = $pdo->prepare(
        "INSERT INTO users (nama, username, password, role)
         VALUES (?, ?, ?, ?)"
    );

    $query->execute([
        $nama,
        $username,
        $password,
        $role
    ]);
    echo "Akun admin berhasil dibuat.";

} catch (PDOException $e) {
    die("Gagal membuat akun admin: " . $e->getMessage());
}
