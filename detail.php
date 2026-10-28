<?php

declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

$current = require_login();
$ticket = trim((string) ($_GET['ticket'] ?? ''));

// Ambil pengaduan milik pengguna aktif
$stmt = db()->prepare('SELECT * FROM complaints WHERE ticket_number = ? AND user_id = ?');
$stmt->execute([$ticket, $current['id']]);
$complaint = $stmt->fetch();

if (!$complaint) {
    http_response_code(404);
    exit('Pengaduan tidak ditemukan.');
}

// Ambil riwayat penanganan status
$stmt = db()->prepare('
    SELECT new_status, note, created_at 
    FROM complaint_status_history 
    WHERE complaint_id = ? 
    ORDER BY created_at DESC, id DESC
');
$stmt->execute([$complaint['id']]);
$history = $stmt->fetchAll();

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

$success = flash('success');
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($ticket) ?> — Lapor Purbalingga</title>
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
            <a href="akun.php">Kembali ke laporan</a>
        </div>
    </nav>
</header>

<main class="detail-page">
    <?php if ($success): ?>
        <p class="success"><?= e($success) ?></p>
    <?php endif; ?>

    <section class="detail-head">
        <p class="kicker blue"><?= e($complaint['ticket_number']) ?></p>
        <h1><?= e($complaint['title']) ?></h1>
        <span class="status-badge"><?= e($labels[$complaint['status']] ?? $complaint['status']) ?></span>
    </section>

    <div class="detail-grid">
        <article>
            <h2>Detail pengaduan</h2>
            <dl>
                <div>
                    <dt>Kategori</dt>
                    <dd><?= e($complaint['category']) ?></dd>
                </div>
                <div>
                    <dt>Tanggal kejadian</dt>
                    <dd><?= date('d M Y', strtotime($complaint['incident_date'])) ?></dd>
                </div>
                <div>
                    <dt>Lokasi</dt>
                    <dd>
                        <?= e($complaint['village']) ?>, <?= e($complaint['district']) ?><br>
                        <?= nl2br(e($complaint['address'])) ?>
                    </dd>
                </div>
                <div>
                    <dt>Uraian</dt>
                    <dd><?= nl2br(e($complaint['description'])) ?></dd>
                </div>
            </dl>
        </article>

        <aside>
            <h2>Riwayat status</h2>
            <ol class="history">
                <?php foreach ($history as $item): ?>
                    <li>
                        <strong><?= e($labels[$item['new_status']] ?? $item['new_status']) ?></strong>
                        <span><?= date('d M Y, H:i', strtotime($item['created_at'])) ?></span>
                        <p><?= e($item['note']) ?></p>
                    </li>
                <?php endforeach; ?>
            </ol>
        </aside>
    </div>
</main>
</body>
</html>
