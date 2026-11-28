<?php

// Deteksi otomatis alamat dasar proyek
$docRoot = rtrim(str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT'])), '/');
$projectRoot = rtrim(str_replace('\\', '/', realpath(__DIR__ . '/..')), '/');

if ($docRoot !== '' && stripos($projectRoot, $docRoot) === 0) {
    define('BASE_URL', substr($projectRoot, strlen($docRoot)));
} else {
    define('BASE_URL', '');
}

function redirect($url)
{
    header("Location: " . BASE_URL . $url);
    exit;
}

function e($value)
{
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES,
        'UTF-8'
    );
}

function setFlash($message, $type = "success")
{
    $_SESSION['flash'] = [
        'message' => $message,
        'type' => $type
    ];
}

function getFlash()
{
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);

        return $flash;
    }

    return null;
}

// Format angka rating, contoh: 4,5
function formatRating($nilai)
{
    return number_format((float) $nilai, 1, ',', '.');
}

// Menampilkan bintang rating (1-5) dalam bentuk HTML
function bintangHtml($nilai)
{
    $dibulatkan = (int) round((float) $nilai);
    $html = '<span class="stars" role="img" aria-label="Rating '
        . e(formatRating($nilai)) . ' dari 5">';

    for ($i = 1; $i <= 5; $i++) {
        $html .= '<span class="star' . ($i <= $dibulatkan ? ' on' : '') . '">&#9733;</span>';
    }

    return $html . '</span>';
}

// Format tanggal dan waktu, contoh: 24-11-2026 13:36
function formatWaktu($waktu)
{
    $stempel = strtotime((string) $waktu);

    return $stempel ? date('d-m-Y H:i', $stempel) : '-';
}
