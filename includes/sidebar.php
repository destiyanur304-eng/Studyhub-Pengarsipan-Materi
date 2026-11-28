<?php

$role = $_SESSION['user']['role'] ?? '';
$current = basename($_SERVER['PHP_SELF']);

function menuActive($file, $current)
{
    return $file === $current ? 'active' : '';
}

// Ikon SVG sederhana
function icon($name)
{
    $paths = [
        'home'     => '<path d="M3 11l9-8 9 8"/><path d="M5 10v10h14V10"/>',
        'users'    => '<circle cx="9" cy="8" r="3.5"/><path d="M2.5 20c0-3.6 2.9-6 6.5-6s6.5 2.4 6.5 6"/><circle cx="17" cy="9" r="2.5"/><path d="M17 14c2.6 0 4.5 1.8 4.5 4.5"/>',
        'book'     => '<path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20V3H6.5A2.5 2.5 0 0 0 4 5.5z"/><path d="M4 19.5A2.5 2.5 0 0 0 6.5 22H20v-5"/>',
        'file'     => '<path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8z"/><path d="M14 3v5h5"/><path d="M9 13h6M9 17h6"/>',
        'folder'   => '<path d="M3 6a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>',
        'user'     => '<circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 3.6-7 8-7s8 3 8 7"/>',
        'logout'   => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="M16 17l5-5-5-5"/><path d="M21 12H9"/>',
        'calendar' => '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4M8 3v4M3 11h18"/>',
        'history'  => '<path d="M3 12a9 9 0 1 0 2.6-6.4L3 8"/><path d="M3 3v5h5"/><path d="M12 7v5l3 2"/>',
        'lock'     => '<rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/>',
        'search'   => '<circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/>',
        'chat'     => '<path d="M21 15a2 2 0 0 1-2 2H8l-5 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/><path d="M8 9h8M8 13h5"/>',
        'tags'     => '<path d="M20.59 13.41 11 3.83A2 2 0 0 0 9.59 3H4a1 1 0 0 0-1 1v5.59a2 2 0 0 0 .59 1.41l9.58 9.58a2 2 0 0 0 2.83 0l4.59-4.59a2 2 0 0 0 0-2.58z"/><circle cx="7.5" cy="7.5" r="1"/>',
    ];

    return '<svg class="icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ($paths[$name] ?? '') . '</svg>';
}

?>

<aside class="sidebar">

    <div class="sidebar-brand">
        <h2>StudyHub</h2>
        <p>Sistem Pengarsipan Materi</p>
    </div>

    <nav class="sidebar-menu">

        <?php if ($role === 'admin'): ?>

            <p class="menu-label">MENU ADMIN</p>

            <a href="<?= BASE_URL; ?>/admin/dashboard.php"
               class="menu-item <?= menuActive('dashboard.php', $current); ?>">
                <?= icon('home'); ?> Dashboard
            </a>

            <a href="<?= BASE_URL; ?>/admin/mahasiswa.php"
               class="menu-item <?= menuActive('mahasiswa.php', $current); ?>">
                <?= icon('users'); ?> Data Mahasiswa
            </a>

            <a href="<?= BASE_URL; ?>/admin/matakuliah.php"
               class="menu-item <?= menuActive('matakuliah.php', $current); ?>">
                <?= icon('book'); ?> Mata Kuliah
            </a>

            <a href="<?= BASE_URL; ?>/admin/materi.php"
               class="menu-item <?= menuActive('materi.php', $current); ?>">
                <?= icon('file'); ?> Materi
            </a>

            <a href="<?= BASE_URL; ?>/admin/kategori.php"
               class="menu-item <?= menuActive('kategori.php', $current); ?>">
                <?= icon('tags'); ?> Kategori
            </a>

        <?php elseif ($role === 'mahasiswa'): ?>

            <p class="menu-label">MENU MAHASISWA</p>

            <a href="<?= BASE_URL; ?>/mahasiswa/dashboard.php"
               class="menu-item <?= menuActive('dashboard.php', $current); ?>">
                <?= icon('home'); ?> Dashboard
            </a>

            <a href="<?= BASE_URL; ?>/mahasiswa/arsip.php"
               class="menu-item <?= menuActive('arsip.php', $current); ?>">
                <?= icon('folder'); ?> Arsip Materi
            </a>

            <a href="<?= BASE_URL; ?>/mahasiswa/diskusi.php"
               class="menu-item <?= menuActive('diskusi.php', $current); ?>">
                <?= icon('chat'); ?> Forum Diskusi
            </a>

            <a href="<?= BASE_URL; ?>/mahasiswa/riwayat.php"
               class="menu-item <?= menuActive('riwayat.php', $current); ?>">
                <?= icon('history'); ?> Riwayat Akses
            </a>

            <a href="<?= BASE_URL; ?>/mahasiswa/profile.php"
               class="menu-item <?= menuActive('profile.php', $current); ?>">
                <?= icon('user'); ?> Profil Saya
            </a>

        <?php endif; ?>

    </nav>

    <div class="sidebar-bottom">

        <form action="<?= BASE_URL; ?>/logout.php" method="POST">

            <input
                type="hidden"
                name="csrf_token"
                value="<?= e($_SESSION['csrf_token']); ?>"
            >

            <button type="submit" class="logout-button">
                <?= icon('logout'); ?> Keluar
            </button>

        </form>

    </div>

</aside>