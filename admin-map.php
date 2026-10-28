<?php

declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

$current = require_login();

// Akses terbatas untuk admin pusat
if ($current['role'] !== 'central_admin') {
    http_response_code(403);
    exit('Akses ditolak.');
}

// Ambil laporan yang memiliki koordinat lokasi
$stmt = db()->query('
    SELECT ticket_number, title, category, status, latitude, longitude 
    FROM complaints 
    WHERE latitude IS NOT NULL AND longitude IS NOT NULL 
    ORDER BY created_at DESC
');
$reports = $stmt->fetchAll();
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Peta Pengaduan — Admin</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body>
<header>
    <nav class="global-nav">
        <div class="nav-inner">
            <a class="crest" href="admin.php" aria-label="Dashboard admin">
                <svg viewBox="0 0 36 36">
                    <path d="M18 3 31 8v9c0 8-5 13-13 16C10 30 5 25 5 17V8l13-5Z"/>
                    <path d="m11 18 4 4 10-11"/>
                </svg>
            </a>
            <div class="nav-links">
                <a href="admin.php">Antrean</a>
                <a href="admin-map.php">Peta</a>
                <a href="index.php">Situs publik</a>
            </div>
            <form method="post" action="logout.php">
                <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                <button class="logout" type="submit">Keluar</button>
            </form>
        </div>
    </nav>
    <div class="sub-nav">
        <div class="sub-inner">
            <span class="wordmark">Peta Pengaduan</span>
            <span><?= count($reports) ?> titik lokasi</span>
        </div>
    </div>
</header>

<main class="map-admin-page">
    <div class="map-admin-head">
        <div>
            <p class="kicker blue">SEBARAN LAPORAN</p>
            <h1>Peta pengaduan warga</h1>
            <p>Klik titik untuk membuka laporan.</p>
        </div>
        <div class="map-legend">
            <span><i class="dot blue"></i> Diterima</span>
            <span><i class="dot yellow"></i> Diproses</span>
            <span><i class="dot green"></i> Selesai</span>
            <span><i class="dot red"></i> Ditolak</span>
        </div>
    </div>

    <?php if (!$reports): ?>
        <p class="map-empty">Belum ada laporan yang memiliki titik lokasi.</p>
    <?php endif; ?>

    <div id="admin-map"
         class="admin-map"
         data-reports="<?= e(json_encode($reports, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) ?>"
         aria-label="Peta sebaran lokasi pengaduan"></div>
</main>

<script src="assets/js/maps.js"></script>
<script async src="https://maps.googleapis.com/maps/api/js?key=<?= e($config['app']['google_maps_api_key']) ?>&callback=initAdminMap"></script>
</body>
</html>
