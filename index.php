<?php

declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

$currentUser = user();
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Website resmi untuk melaporkan masalah pelayanan publik di Kabupaten Purbalingga.">
    <title>Lapor Purbalingga — Laporkan Masalah Pelayanan</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="assets/css/public.css">
</head>
<body class="public-home">
<a class="skip-link" href="#konten">Lewati ke isi utama</a>

<header class="public-header">
    <div class="official-bar">
        <div class="public-container">
            <span>Website Resmi Kabupaten Purbalingga</span>
            <a href="tel:+62281891012" aria-label="Telepon bantuan">Bantuan: (0281) 891012</a>
        </div>
    </div>
    <nav class="public-nav" aria-label="Navigasi utama">
        <div class="public-container">
            <a class="public-brand" href="index.php" aria-label="Lapor Purbalingga, beranda">
                <span class="brand-shield">
                    <svg viewBox="0 0 48 48" aria-hidden="true">
                        <path d="M24 4 42 11v12c0 11-7 18-18 21C13 41 6 34 6 23V11l18-7Z"/>
                        <path d="m14 25 6 6 14-15"/>
                    </svg>
                </span>
                <span>
                    <strong>Lapor Purbalingga</strong>
                    <small>Pengaduan Pelayanan Publik</small>
                </span>
            </a>
            <div class="public-links">
                <a href="#cara">Cara Melapor</a>
                <a href="#status">Status Laporan</a>
                <a href="#bantuan">Bantuan</a>
            </div>
            <?php if ($currentUser): ?>
                <?php $accountUrl = $currentUser['role'] === 'central_admin' ? 'admin.php' : 'akun.php'; ?>
                <a class="account-button" href="<?= e($accountUrl) ?>">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <circle cx="12" cy="8" r="4"/>
                        <path d="M4 21a8 8 0 0 1 16 0"/>
                    </svg>
                    <?= e($currentUser['name']) ?>
                </a>
            <?php else: ?>
                <a class="account-button" href="login.php">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <circle cx="12" cy="8" r="4"/>
                        <path d="M4 21a8 8 0 0 1 16 0"/>
                    </svg>
                    Masuk
                </a>
            <?php endif; ?>
        </div>
    </nav>
</header>

<main id="konten">
    <section class="clear-hero">
        <div class="public-container hero-layout">
            <div class="clear-hero-copy">
                <span class="official-label">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m5 12 4 4L19 6"/></svg>
                    Layanan resmi pemerintah
                </span>
                <h1>Ada masalah pelayanan?<br><span>Laporkan di sini.</span></h1>
                <p>Ceritakan masalah Anda. Kami teruskan ke petugas yang tepat.</p>
                <div class="main-actions">
                    <a class="cta-report" href="form-pengaduan.php">
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M12 20h9"/>
                            <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L8 18l-4 1 1-4Z"/>
                        </svg>
                        <span>
                            <strong>Buat Laporan</strong>
                            <small>Laporkan masalah baru</small>
                        </span>
                        <b>›</b>
                    </a>
                    <a class="cta-track" href="#lacak">
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <circle cx="11" cy="11" r="7"/>
                            <path d="m20 20-4-4"/>
                        </svg>
                        <span>
                            <strong>Cek Laporan</strong>
                            <small>Lihat perkembangan</small>
                        </span>
                        <b>›</b>
                    </a>
                </div>
                <div class="trust-row">
                    <span>
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/>
                            <path d="m9 12 2 2 4-4"/>
                        </svg>
                        Data aman
                    </span>
                    <span>
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <circle cx="12" cy="12" r="9"/>
                            <path d="M12 7v5l3 2"/>
                        </svg>
                        Bisa dipantau
                    </span>
                    <span>
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M4 13h4l3 8 4-18 3 10h2"/>
                        </svg>
                        Gratis
                    </span>
                </div>
            </div>
            <div class="report-illustration" aria-hidden="true">
                <div class="illustration-circle">
                    <svg viewBox="0 0 180 180">
                        <path class="megaphone" d="M38 88v-24c0-7 5-12 12-12h20l59-27v102l-59-27H50c-7 0-12-5-12-12Z"/>
                        <path class="handle" d="m61 101 13 48h28l-18-43"/>
                        <path class="sound" d="M142 55c12 10 12 32 0 42M153 42c21 18 21 50 0 68"/>
                        <circle cx="46" cy="139" r="18"/>
                        <path class="check" d="m37 139 6 6 12-14"/>
                    </svg>
                </div>
                <div class="illustration-note">
                    <span>✓</span>
                    <div>
                        <strong>Laporan diterima</strong>
                        <small>Petugas segera memeriksa</small>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="quick-check" id="lacak" aria-labelledby="check-title">
        <div class="public-container check-box">
            <div class="check-title">
                <span class="round-icon">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M9 11l3 3L22 4"/>
                        <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
                    </svg>
                </span>
                <div>
                    <h2 id="check-title">Sudah pernah melapor?</h2>
                    <p>Masukkan nomor laporan Anda.</p>
                </div>
            </div>
            <form action="detail.php" method="get">
                <label for="ticket">Nomor laporan</label>
                <div>
                    <input id="ticket" name="ticket" placeholder="Contoh: PBG-2609-A1B2C3" required>
                    <button type="submit">Cek Sekarang</button>
                </div>
            </form>
        </div>
    </section>

    <section class="simple-steps" id="cara">
        <div class="public-container">
            <div class="simple-heading">
                <span>CARA MELAPOR</span>
                <h2>Hanya 3 langkah</h2>
                <p>Mudah dilakukan dari ponsel.</p>
            </div>
            <ol>
                <li>
                    <span class="step-icon">
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M12 20h9"/>
                            <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L8 18l-4 1 1-4Z"/>
                        </svg>
                    </span>
                    <b>1</b>
                    <h3>Tulis masalah</h3>
                    <p>Jelaskan apa yang terjadi.</p>
                </li>
                <li>
                    <span class="step-icon">
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M4 4h16v16H4z"/>
                            <circle cx="9" cy="9" r="2"/>
                            <path d="m4 17 5-5 4 4 3-3 4 4"/>
                        </svg>
                    </span>
                    <b>2</b>
                    <h3>Tambah bukti</h3>
                    <p>Foto boleh ditambahkan.</p>
                </li>
                <li>
                    <span class="step-icon">
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M22 2 11 13"/>
                            <path d="m22 2-7 20-4-9-9-4Z"/>
                        </svg>
                    </span>
                    <b>3</b>
                    <h3>Kirim laporan</h3>
                    <p>Dapatkan nomor laporan.</p>
                </li>
            </ol>
            <a class="large-report-button" href="form-pengaduan.php">Buat Laporan Sekarang <span>›</span></a>
        </div>
    </section>

    <section class="status-guide" id="status">
        <div class="public-container status-layout">
            <div>
                <span class="section-label">MUDAH DIPANTAU</span>
                <h2>Arti warna laporan</h2>
                <p>Warna membantu Anda mengenali perkembangan laporan.</p>
            </div>
            <div class="status-cards">
                <div class="status-card status-new">
                    <span>
                        <svg viewBox="0 0 24 24">
                            <path d="M12 8v5l3 2"/>
                            <circle cx="12" cy="12" r="9"/>
                        </svg>
                    </span>
                    <div>
                        <strong>Diterima</strong>
                        <small>Laporan sedang diperiksa</small>
                    </div>
                </div>
                <div class="status-card status-process">
                    <span>
                        <svg viewBox="0 0 24 24">
                            <path d="M4 12a8 8 0 0 1 14-5l2 2"/>
                            <path d="M20 4v5h-5M20 12a8 8 0 0 1-14 5l-2-2"/>
                            <path d="M4 20v-5h5"/>
                        </svg>
                    </span>
                    <div>
                        <strong>Diproses</strong>
                        <small>Petugas sedang menangani</small>
                    </div>
                </div>
                <div class="status-card status-done">
                    <span>
                        <svg viewBox="0 0 24 24">
                            <path d="m5 12 4 4L19 6"/>
                            <circle cx="12" cy="12" r="10"/>
                        </svg>
                    </span>
                    <div>
                        <strong>Selesai</strong>
                        <small>Masalah sudah ditangani</small>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="human-help" id="bantuan">
        <div class="public-container">
            <span class="help-person">
                <svg viewBox="0 0 24 24" aria-hidden="true">
                    <circle cx="12" cy="8" r="4"/>
                    <path d="M4 21a8 8 0 0 1 16 0"/>
                    <path d="M19 8h3v7h-3M5 8H2v7h3"/>
                </svg>
            </span>
            <div>
                <h2>Bingung cara melapor?</h2>
                <p>Petugas kami siap membantu.</p>
            </div>
            <a href="tel:+62281891012">
                <span>Telepon Bantuan</span>
                <strong>(0281) 891012</strong>
            </a>
        </div>
    </section>
</main>

<footer class="simple-public-footer">
    <div class="public-container">
        <div>
            <strong>Lapor Purbalingga</strong>
            <span>Layanan Pengaduan Pelayanan Publik</span>
        </div>
        <nav aria-label="Tautan bawah">
            <a href="#cara">Cara Melapor</a>
            <a href="#bantuan">Bantuan</a>
            <a href="#">Privasi</a>
        </nav>
        <p>© 2026 Pemerintah Kabupaten Purbalingga</p>
    </div>
</footer>
</body>
</html>
