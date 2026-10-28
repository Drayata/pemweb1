<?php

declare(strict_types=1);

/**
 * Berkas inisialisasi aplikasi (bootstrap).
 * Memuat konfigurasi, mengatur sesi, koneksi basis data PDO, dan fungsi bantuan umum.
 * ponytail: static PDO singleton dan session sederhana cukup untuk skala monolit kecil, pisah ke DI container jika modularitas bertambah.
 */

$configFile = __DIR__ . '/config.php';
if (!is_file($configFile)) {
    http_response_code(500);
    exit('Konfigurasi belum tersedia. Salin config.example.php menjadi config.php.');
}

$config = require $configFile;

// Pengaturan keamanan PHP & sesi
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');

session_name('lapor_session');
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    'httponly' => true,
    'samesite' => 'Lax',
]);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

/**
 * Koneksi ke database MySQL menggunakan PDO.
 */
function db(): PDO
{
    static $pdo;
    global $config;

    if (!$pdo) {
        $pdo = new PDO($config['db']['dsn'], $config['db']['user'], $config['db']['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    }

    return $pdo;
}

/**
 * Mencegah XSS dengan membersihkan output HTML.
 */
function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Menghasilkan URL absolut berdasarkan konfigurasi base_url.
 */
function base_url(string $path = ''): string
{
    global $config;
    return rtrim($config['app']['base_url'], '/') . '/' . ltrim($path, '/');
}

/**
 * Mengalihkan halaman ke URL tujuan.
 */
function redirect(string $path): never
{
    header('Location: ' . base_url($path));
    exit;
}

/**
 * Mengambil atau membuat token CSRF baru.
 */
function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

/**
 * Memvalidasi token CSRF dari permintaan POST.
 */
function verify_csrf(): void
{
    if (!isset($_POST['csrf']) || !hash_equals($_SESSION['csrf'] ?? '', (string) $_POST['csrf'])) {
        http_response_code(419);
        exit('Sesi formulir tidak valid. Muat ulang halaman dan coba kembali.');
    }
}

/**
 * Mengambil data pengguna yang sedang login dari sesi.
 */
function user(): ?array
{
    return $_SESSION['user'] ?? null;
}

/**
 * Memastikan pengguna sudah login sebelum mengakses halaman.
 */
function require_login(): array
{
    $current = user();
    if (!$current) {
        $_SESSION['after_login'] = $_SERVER['REQUEST_URI'] ?? base_url();
        redirect('login.php');
    }
    return $current;
}

/**
 * Menyimpan atau membaca pesan kilat (flash message) satu kali tampil.
 */
function flash(string $key, ?string $value = null): ?string
{
    if ($value !== null) {
        $_SESSION['flash'][$key] = $value;
        return null;
    }

    $message = $_SESSION['flash'][$key] ?? null;
    unset($_SESSION['flash'][$key]);
    return $message;
}

/**
 * Mengambil nilai input formulir sebelumnya saat terjadi validasi gagal.
 */
function old(string $key): string
{
    return e($_SESSION['old'][$key] ?? '');
}

/**
 * Membersihkan data input formulir lama dari sesi.
 */
function consume_old(): void
{
    unset($_SESSION['old']);
}

/**
 * Menghasilkan nomor tiket pengaduan unik (contoh: PBG-2609-A1B2C3).
 */
function ticket_number(PDO $pdo): string
{
    do {
        $ticket = 'PBG-' . date('ym') . '-' . strtoupper(bin2hex(random_bytes(3)));
        $query = $pdo->prepare('SELECT 1 FROM complaints WHERE ticket_number = ?');
        $query->execute([$ticket]);
    } while ($query->fetchColumn());

    return $ticket;
}
