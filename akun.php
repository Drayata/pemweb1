<?php

declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

$current = require_login();

// Ambil riwayat pengaduan milik pengguna yang sedang login
$stmt = db()->prepare('
    SELECT ticket_number, title, category, status, created_at 
    FROM complaints 
    WHERE user_id = ? 
    ORDER BY created_at DESC
');
$stmt->execute([$current['id']]);
$complaints = $stmt->fetchAll();

$labels = [
    'submitted'             => 'Diajukan',
    'verified'              => 'Diverifikasi',
    'assigned'              => 'Ditugaskan',
    'processing'            => 'Diproses',
    'awaiting_confirmation' => 'Menunggu Konfirmasi',
    'completed'             => 'Selesai',
    'needs_information'     => 'Perlu Pelengkapan',
    'rejected'              => 'Ditolak',
    'referred'              => 'Dirujuk',
];
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Akun — Lapor Purbalingga</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="assets/css/account.css">
</head>
<body>
<header>
    <nav class="global-nav">
        <div class="nav-inner">
            <a class="crest" href="index.php" aria-label="Beranda">
                <svg viewBox="0 0 36 36">
                    <path d="M18 3 31 8v9c0 8-5 13-13 16C10 30 5 25 5 17V8l13-5Z"/>
                    <path d="m11 18 4 4 10-11"/>
                </svg>
            </a>
            <div class="nav-links">
                <a href="index.php">Beranda</a>
                <a href="form-pengaduan.php">Buat Pengaduan</a>
            </div>
            <form method="post" action="logout.php">
                <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                <button class="logout" type="submit">Keluar</button>
            </form>
        </div>
    </nav>
    <div class="sub-nav">
        <div class="sub-inner">
            <span class="wordmark">Laporan Saya</span>
            <a class="pill pill-small" href="form-pengaduan.php">Buat Pengaduan</a>
        </div>
    </div>
</header>

<main class="account-page">
    <section class="account-head">
        <p class="kicker blue">Akun warga</p>
        <h1>Halo, <?= e($current['name']) ?>.</h1>
        <p class="lead">Pantau seluruh pengaduan dan perkembangan terbaru di sini.</p>
    </section>

    <section class="complaint-list">
        <?php if (!$complaints): ?>
            <div class="empty">
                <h2>Belum ada pengaduan.</h2>
                <p>Pengaduan pertama Anda akan tampil di halaman ini.</p>
                <a class="pill" href="form-pengaduan.php">Buat Pengaduan</a>
            </div>
        <?php else: ?>
            <h2>Pengaduan terbaru</h2>
            <?php foreach ($complaints as $item): ?>
                <a class="complaint-row" href="detail.php?ticket=<?= urlencode($item['ticket_number']) ?>">
                    <div>
                        <small><?= e($item['ticket_number']) ?> · <?= e($item['category']) ?></small>
                        <strong><?= e($item['title']) ?></strong>
                        <span><?= date('d M Y', strtotime($item['created_at'])) ?></span>
                    </div>
                    <b><?= e($labels[$item['status']] ?? $item['status']) ?></b>
                    <i>›</i>
                </a>
            <?php endforeach; ?>
        <?php endif; ?>
    </section>
</main>
</body>
</html>
