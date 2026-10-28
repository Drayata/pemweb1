<?php

declare(strict_types=1);

/**
 * Pemrosesan penyimpanan pengaduan baru dari formulir warga.
 * Memvalidasi input teks, file foto, menyimpan ke MySQL dengan transaksi, dan memindahkan berkas.
 * ponytail: penyimpanan lokal di storage/private, bila sistem berskala multi-server ganti ke S3/object storage.
 */

require __DIR__ . '/bootstrap.php';

$current = require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Metode tidak diizinkan.');
}

verify_csrf();

// Ambil dan bersihkan data formulir
$fields = [
    'judul'     => trim((string) ($_POST['judul'] ?? '')),
    'kategori'  => trim((string) ($_POST['kategori'] ?? '')),
    'tanggal'   => trim((string) ($_POST['tanggal'] ?? '')),
    'uraian'    => trim((string) ($_POST['uraian'] ?? '')),
    'wilayah'   => trim((string) ($_POST['wilayah'] ?? '')),
    'desa'      => trim((string) ($_POST['desa'] ?? '')),
    'alamat'    => trim((string) ($_POST['alamat'] ?? '')),
    'latitude'  => trim((string) ($_POST['latitude'] ?? '')),
    'longitude' => trim((string) ($_POST['longitude'] ?? '')),
];

$_SESSION['old'] = $fields;
$allowedCategories = [
    'Infrastruktur',
    'Kesehatan',
    'Pendidikan',
    'Administrasi kependudukan',
    'Lingkungan',
    'Lainnya',
];

$errors = [];

// Validasi isian teks
if (mb_strlen($fields['judul']) < 5 || mb_strlen($fields['judul']) > 150) {
    $errors[] = 'Judul harus terdiri dari 5–150 karakter.';
}

if (!in_array($fields['kategori'], $allowedCategories, true)) {
    $errors[] = 'Kategori tidak valid.';
}

$date = DateTimeImmutable::createFromFormat('!Y-m-d', $fields['tanggal']);
if (!$date || $date->format('Y-m-d') !== $fields['tanggal'] || $date > new DateTimeImmutable('today')) {
    $errors[] = 'Tanggal kejadian tidak valid.';
}

if (mb_strlen($fields['uraian']) < 20 || mb_strlen($fields['uraian']) > 3000) {
    $errors[] = 'Uraian harus terdiri dari 20–3.000 karakter.';
}

$locationFields = [
    'wilayah' => 'Kecamatan',
    'desa'    => 'Desa/kelurahan',
    'alamat'  => 'Alamat',
];

foreach ($locationFields as $key => $label) {
    if ($fields[$key] === '' || mb_strlen($fields[$key]) > 500) {
        $errors[] = "{$label} tidak valid.";
    }
}

// Validasi titik koordinat jika diisi
$latitude = filter_var($fields['latitude'], FILTER_VALIDATE_FLOAT);
$longitude = filter_var($fields['longitude'], FILTER_VALIDATE_FLOAT);

$hasCoordinates = ($fields['latitude'] !== '' || $fields['longitude'] !== '');
if ($hasCoordinates) {
    $validLat = ($latitude !== false && $latitude >= -90 && $latitude <= 90);
    $validLng = ($longitude !== false && $longitude >= -180 && $longitude <= 180);
    if (!$validLat || !$validLng) {
        $errors[] = 'Titik lokasi tidak valid.';
    }
}

if (!isset($_POST['setuju'])) {
    $errors[] = 'Persetujuan data wajib diberikan.';
}

// Validasi berkas lampiran (foto)
$uploads = [];
if (isset($_FILES['foto']) && is_array($_FILES['foto']['name'])) {
    $validNames = array_filter($_FILES['foto']['name'], static fn($name) => $name !== '');
    $count = count($validNames);

    if ($count > (int) $config['app']['max_uploads']) {
        $errors[] = 'Maksimal lima foto.';
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    foreach ($_FILES['foto']['name'] as $i => $name) {
        if ($name === '') {
            continue;
        }

        $uploadError = $_FILES['foto']['error'][$i];
        $size = (int) $_FILES['foto']['size'][$i];
        $tmp = $_FILES['foto']['tmp_name'][$i];

        if ($uploadError !== UPLOAD_ERR_OK || $size < 1 || $size > (int) $config['app']['max_upload_bytes']) {
            $errors[] = 'Salah satu foto gagal diunggah atau melebihi 5 MB.';
            continue;
        }

        $mime = $finfo->file($tmp);
        if (!in_array($mime, ['image/jpeg', 'image/png'], true) || @getimagesize($tmp) === false) {
            $errors[] = 'Foto harus berupa JPEG atau PNG yang valid.';
            continue;
        }

        $uploads[] = [
            'tmp'      => $tmp,
            'original' => basename((string) $name),
            'mime'     => $mime,
            'size'     => $size,
            'ext'      => $mime === 'image/jpeg' ? 'jpg' : 'png',
        ];
    }
}

// Jika ada validasi gagal, kembali ke formulir dengan pesan
if ($errors) {
    flash('error', implode(' ', $errors));
    redirect('form-pengaduan.php');
}

$pdo = db();
$movedFiles = [];

try {
    $pdo->beginTransaction();

    // Buat nomor tiket unik
    $ticket = ticket_number($pdo);

    $stmt = $pdo->prepare('
        INSERT INTO complaints (
            ticket_number, user_id, title, category, incident_date, 
            description, district, village, address, latitude, longitude
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ');

    $stmt->execute([
        $ticket,
        $current['id'],
        $fields['judul'],
        $fields['kategori'],
        $fields['tanggal'],
        $fields['uraian'],
        $fields['wilayah'],
        $fields['desa'],
        $fields['alamat'],
        $fields['latitude'] === '' ? null : $latitude,
        $fields['longitude'] === '' ? null : $longitude,
    ]);

    $complaintId = (int) $pdo->lastInsertId();

    // Simpan foto lampiran jika ada
    if ($uploads) {
        $directory = rtrim($config['app']['upload_dir'], '/') . '/' . $complaintId;
        if (!is_dir($directory) && !mkdir($directory, 0700, true) && !is_dir($directory)) {
            throw new RuntimeException('Penyimpanan foto tidak dapat dibuat.');
        }

        foreach ($uploads as $upload) {
            $storedName = bin2hex(random_bytes(16)) . '.' . $upload['ext'];
            $destination = $directory . '/' . $storedName;

            if (!move_uploaded_file($upload['tmp'], $destination)) {
                throw new RuntimeException('Foto gagal disimpan.');
            }

            chmod($destination, 0600);
            $movedFiles[] = $destination;

            $stmt = $pdo->prepare('
                INSERT INTO complaint_attachments (
                    complaint_id, stored_name, original_name, mime_type, size_bytes
                ) VALUES (?, ?, ?, ?, ?)
            ');
            $stmt->execute([
                $complaintId,
                $storedName,
                $upload['original'],
                $upload['mime'],
                $upload['size'],
            ]);
        }
    }

    // Catat riwayat status awal
    $stmt = $pdo->prepare('
        INSERT INTO complaint_status_history (complaint_id, actor_id, new_status, note) 
        VALUES (?, ?, ?, ?)
    ');
    $stmt->execute([$complaintId, $current['id'], 'submitted', 'Pengaduan diajukan oleh warga.']);

    $pdo->commit();

    unset($_SESSION['old']);
    flash('success', "Pengaduan berhasil dikirim. Nomor tiket: {$ticket}");
    redirect('detail.php?ticket=' . rawurlencode($ticket));

} catch (Throwable $exception) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    // Hapus file yang sudah sempat dipindahkan jika database gagal
    foreach ($movedFiles as $file) {
        @unlink($file);
    }

    error_log($exception->__toString());
    flash('error', 'Pengaduan gagal disimpan. Coba kembali.');
    redirect('form-pengaduan.php');
}
