<?php

declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

$current = require_login();

// Batasi akses hanya untuk administrator pusat
if ($current['role'] !== 'central_admin') {
    http_response_code(403);
    exit('Akses ditolak.');
}

$allowed = [
    'submitted',
    'verified',
    'assigned',
    'processing',
    'awaiting_confirmation',
    'completed',
    'needs_information',
    'rejected',
    'referred',
];

$status = (string) ($_GET['status'] ?? '');

// Susun query dengan filter status jika diminta
$sql = '
    SELECT c.ticket_number, c.title, c.category, c.status, c.created_at, u.name AS citizen_name 
    FROM complaints c 
    JOIN users u ON u.id = c.user_id
';
$params = [];

if (in_array($status, $allowed, true)) {
    $sql .= ' WHERE c.status = ?';
    $params[] = $status;
}

$sql .= ' ORDER BY c.created_at DESC';

$stmt = db()->prepare($sql);
$stmt->execute($params);
$items = $stmt->fetchAll();

// Hitung rekap jumlah laporan per status
$counts = array_fill_keys($allowed, 0);
$countQuery = db()->query('SELECT status, COUNT(*) AS total FROM complaints GROUP BY status');
foreach ($countQuery as $row) {
    $counts[$row['status']] = (int) $row['total'];
}

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
    <title>Admin Pusat — Lapor Purbalingga</title>
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
            <span class="wordmark">Admin Pusat</span>
            <span><?= e($current['name']) ?></span>
        </div>
    </div>
</header>

<main class="admin-page">
    <div class="admin-head">
        <p class="kicker blue">Pengelolaan pengaduan</p>
        <h1>Antrean laporan.</h1>
        <p class="lead">Verifikasi dan pantau seluruh laporan warga.</p>
    </div>

    <section class="summary">
        <a href="admin.php">
            <strong><?= array_sum($counts) ?></strong>
            <span>Semua</span>
        </a>
        <a href="?status=submitted">
            <strong><?= $counts['submitted'] ?></strong>
            <span>Diajukan</span>
        </a>
        <a href="?status=processing">
            <strong><?= $counts['processing'] ?></strong>
            <span>Diproses</span>
        </a>
        <a href="?status=completed">
            <strong><?= $counts['completed'] ?></strong>
            <span>Selesai</span>
        </a>
    </section>

    <section class="queue">
        <div class="queue-title">
            <h2><?= $status && isset($labels[$status]) ? e($labels[$status]) : 'Semua laporan' ?></h2>
            <form method="get">
                <label class="sr-only" for="status">Filter status</label>
                <select id="status" name="status" onchange="this.form.submit()">
                    <option value="">Semua status</option>
                    <?php foreach ($labels as $key => $label): ?>
                        <option value="<?= e($key) ?>" <?= $status === $key ? 'selected' : '' ?>>
                            <?= e($label) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>

        <?php if (!$items): ?>
            <p class="empty-row">Belum ada laporan pada antrean ini.</p>
        <?php endif; ?>

        <?php foreach ($items as $item): ?>
            <a class="queue-row" href="admin-detail.php?ticket=<?= urlencode($item['ticket_number']) ?>">
                <div>
                    <small><?= e($item['ticket_number']) ?> · <?= e($item['category']) ?></small>
                    <strong><?= e($item['title']) ?></strong>
                    <span><?= e($item['citizen_name']) ?> · <?= date('d M Y, H:i', strtotime($item['created_at'])) ?></span>
                </div>
                <b><?= e($labels[$item['status']]) ?></b>
                <i>›</i>
            </a>
        <?php endforeach; ?>
    </section>
</main>
</body>
</html>
