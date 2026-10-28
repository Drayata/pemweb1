<?php

declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

$current = require_login();

// Akses terbatas untuk admin pusat
if ($current['role'] !== 'central_admin') {
    http_response_code(403);
    exit('Akses ditolak.');
}

$ticket = trim((string) ($_GET['ticket'] ?? ''));

// Ambil detail pengaduan beserta data pemohon
$stmt = db()->prepare('
    SELECT c.*, u.name AS citizen_name, u.email AS citizen_email 
    FROM complaints c 
    JOIN users u ON u.id = c.user_id 
    WHERE c.ticket_number = ?
');
$stmt->execute([$ticket]);
$complaint = $stmt->fetch();

if (!$complaint) {
    http_response_code(404);
    exit('Pengaduan tidak ditemukan.');
}

// Ambil riwayat penanganan beserta nama petugas
$stmt = db()->prepare('
    SELECT h.*, u.name AS actor_name 
    FROM complaint_status_history h 
    JOIN users u ON u.id = h.actor_id 
    WHERE h.complaint_id = ? 
    ORDER BY h.created_at DESC, h.id DESC
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
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($ticket) ?> — Admin</title>
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
            <a href="admin.php">Kembali ke antrean</a>
        </div>
    </nav>
</header>

<main class="admin-detail">
    <div class="admin-detail-head">
        <p class="kicker blue"><?= e($ticket) ?></p>
        <h1><?= e($complaint['title']) ?></h1>
        <span class="badge"><?= e($labels[$complaint['status']]) ?></span>
    </div>

    <?php if ($message = flash('success')): ?>
        <p class="success"><?= e($message) ?></p>
    <?php endif; ?>

    <div class="admin-grid">
        <article class="panel">
            <h2>Detail laporan</h2>
            <dl>
                <div>
                    <dt>Pelapor</dt>
                    <dd>
                        <?= e($complaint['citizen_name']) ?><br>
                        <?= e($complaint['citizen_email']) ?>
                    </dd>
                </div>
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
            <form class="status-form panel" method="post" action="admin-status.php">
                <h2>Perbarui status</h2>
                <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                <input type="hidden" name="ticket" value="<?= e($ticket) ?>">

                <label>
                    Status
                    <select name="status" required>
                        <?php foreach ($labels as $key => $label): ?>
                            <option value="<?= e($key) ?>" <?= $complaint['status'] === $key ? 'selected' : '' ?>>
                                <?= e($label) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>

                <label>
                    Catatan
                    <textarea name="note" rows="4" maxlength="1000" required placeholder="Jelaskan alasan perubahan status"></textarea>
                </label>

                <button class="pill" type="submit">Simpan Status</button>
            </form>

            <section class="panel history-panel">
                <h2>Riwayat</h2>
                <ol>
                    <?php foreach ($history as $row): ?>
                        <li>
                            <strong><?= e($labels[$row['new_status']] ?? $row['new_status']) ?></strong>
                            <small><?= e($row['actor_name']) ?> · <?= date('d M Y, H:i', strtotime($row['created_at'])) ?></small>
                            <p><?= e($row['note']) ?></p>
                        </li>
                    <?php endforeach; ?>
                </ol>
            </section>
        </aside>
    </div>
</main>
</body>
</html>
