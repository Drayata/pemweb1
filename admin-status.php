<?php

declare(strict_types=1);

/**
 * Pemrosesan perubahan status pengaduan oleh administrator.
 * Menggunakan transaksi DB dan lock FOR UPDATE untuk konsistensi data.
 */

require __DIR__ . '/bootstrap.php';

$current = require_login();

if ($current['role'] !== 'central_admin') {
    http_response_code(403);
    exit('Akses ditolak.');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Metode tidak diizinkan.');
}

verify_csrf();

$ticket = trim((string) ($_POST['ticket'] ?? ''));
$status = (string) ($_POST['status'] ?? '');
$note   = trim((string) ($_POST['note'] ?? ''));

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

if (!in_array($status, $allowed, true) || mb_strlen($note) < 3 || mb_strlen($note) > 1000) {
    http_response_code(422);
    exit('Status atau catatan tidak valid.');
}

$pdo = db();

try {
    $pdo->beginTransaction();

    // Kunci baris dengan FOR UPDATE agar perubahan status tidak saling tumpang tindih
    $stmt = $pdo->prepare('SELECT id, status FROM complaints WHERE ticket_number = ? FOR UPDATE');
    $stmt->execute([$ticket]);
    $complaint = $stmt->fetch();

    if (!$complaint) {
        $pdo->rollBack();
        http_response_code(404);
        exit('Pengaduan tidak ditemukan.');
    }

    // Perbarui status terkini pada tabel complaints
    $stmt = $pdo->prepare('UPDATE complaints SET status = ? WHERE id = ?');
    $stmt->execute([$status, $complaint['id']]);

    // Tambah riwayat audit perubahan status
    $stmt = $pdo->prepare('
        INSERT INTO complaint_status_history (complaint_id, actor_id, old_status, new_status, note) 
        VALUES (?, ?, ?, ?, ?)
    ');
    $stmt->execute([
        $complaint['id'],
        $current['id'],
        $complaint['status'],
        $status,
        $note,
    ]);

    $pdo->commit();

    flash('success', 'Status berhasil diperbarui.');
    redirect('admin-detail.php?ticket=' . rawurlencode($ticket));

} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log($e->__toString());
    http_response_code(500);
    exit('Status gagal diperbarui.');
}
