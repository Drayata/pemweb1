<?php

declare(strict_types=1);

/**
 * Self-check mandiri tanpa framework untuk menguji logika inti dan sanitasi.
 * Jalankan: php tests/check.php
 */

require_once __DIR__ . '/../bootstrap.php';

echo "Menjalankan uji mandiri...\n";

// 1. Uji fungsi escape HTML (XSS prevention)
assert(e('<script>alert("xss")</script>') === '&lt;script&gt;alert(&quot;xss&quot;)&lt;/script&gt;', 'Fungsi e() gagal sanitasi script');
assert(e(null) === '', 'Fungsi e(null) harus mengembalikan string kosong');
assert(e('Halo "Dunia"') === 'Halo &quot;Dunia&quot;', 'Fungsi e() gagal escape quotes');

// 2. Uji base_url helper
$testUrl = base_url('login.php');
assert(str_ends_with($testUrl, '/login.php'), 'base_url() harus menyertakan path');

// 3. Uji CSRF token generation
$token = csrf_token();
assert(strlen($token) === 64, 'Token CSRF harus 64 karakter hex');
assert(ctype_xdigit($token), 'Token CSRF harus berupa heksadesimal valid');

// 4. Uji Flash message helper
flash('info', 'Pesan sukses sementara');
assert(flash('info') === 'Pesan sukses sementara', 'Flash message gagal disimpan');
assert(flash('info') === null, 'Flash message harus hilang setelah dibaca');

// 5. Uji format generator tiket pengaduan
$sqlite = new PDO('sqlite::memory:');
$sqlite->exec('CREATE TABLE complaints (ticket_number TEXT UNIQUE)');
$ticket = ticket_number($sqlite);
assert((bool) preg_match('/^PBG-\d{4}-[A-F0-9]{6}$/', $ticket), "Format tiket tidak sesuai: {$ticket}");

echo "Semua pemeriksaan logika inti (5/5) lolos!\n";
