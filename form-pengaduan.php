<?php

declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

require_login();
$error = flash('error');
$current = user();

$oldCategory = $_SESSION['old']['kategori'] ?? '';
$oldDistrict = $_SESSION['old']['wilayah'] ?? '';

$categories = [
    'Infrastruktur',
    'Kesehatan',
    'Pendidikan',
    'Administrasi kependudukan',
    'Lingkungan',
    'Lainnya',
];

$districts = [
    'Purbalingga',
    'Bobotsari',
    'Bojongsari',
    'Bukateja',
    'Kalimanah',
    'Kemangkon',
    'Kutasari',
    'Mrebet',
];
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Form untuk melaporkan masalah pelayanan publik di Kabupaten Purbalingga.">
    <title>Buat Laporan — Lapor Purbalingga</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="assets/css/public.css">
    <link rel="stylesheet" href="assets/css/form.css">
</head>
<body class="public-home complaint-page">
<a class="skip-link" href="#formulir">Lewati ke formulir</a>

<header class="public-header">
    <div class="official-bar">
        <div class="public-container">
            <span>Website Resmi Kabupaten Purbalingga</span>
            <a href="tel:+62281891012">Bantuan: (0281) 891012</a>
        </div>
    </div>
    <nav class="public-nav" aria-label="Navigasi utama">
        <div class="public-container">
            <a class="public-brand" href="index.php">
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
            <a class="form-account" href="akun.php">
                <svg viewBox="0 0 24 24" aria-hidden="true">
                    <circle cx="12" cy="8" r="4"/>
                    <path d="M4 21a8 8 0 0 1 16 0"/>
                </svg>
                <span><?= e($current['name']) ?></span>
            </a>
        </div>
    </nav>
</header>

<main id="konten">
    <section class="form-intro">
        <div class="form-container">
            <a class="back-link" href="index.php">‹ Kembali</a>
            <div class="intro-icon">
                <svg viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M12 20h9"/>
                    <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L8 18l-4 1 1-4Z"/>
                </svg>
            </div>
            <div>
                <p class="form-kicker">BUAT LAPORAN</p>
                <h1>Apa masalah yang Anda alami?</h1>
                <p>Isi data di bawah. Kolom bertanda <b>*</b> wajib diisi.</p>
            </div>
        </div>
    </section>

    <div class="form-progress" aria-label="Tahapan membuat laporan">
        <div class="form-container">
            <div class="progress-item active">
                <span>1</span>
                <strong>Ceritakan</strong>
            </div>
            <i></i>
            <div class="progress-item">
                <span>2</span>
                <strong>Lokasi</strong>
            </div>
            <i></i>
            <div class="progress-item">
                <span>3</span>
                <strong>Kirim</strong>
            </div>
        </div>
    </div>

    <section class="form-area" id="formulir">
        <div class="form-container form-layout-new">
            <aside class="form-help-card">
                <span>
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <circle cx="12" cy="12" r="10"/>
                        <path d="M9.5 9a2.7 2.7 0 1 1 4.6 2c-1.1 1-2.1 1.5-2.1 3"/>
                        <path d="M12 18h.01"/>
                    </svg>
                </span>
                <h2>Butuh bantuan?</h2>
                <p>Hubungi petugas. Kami bantu Anda mengisi laporan.</p>
                <a href="tel:+62281891012">
                    <small>Telepon bantuan</small>
                    <strong>(0281) 891012</strong>
                </a>
                <div class="safe-note">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/>
                        <path d="m9 12 2 2 4-4"/>
                    </svg>
                    <span>
                        <strong>Data Anda aman</strong>
                        <small>Tidak ditampilkan ke publik</small>
                    </span>
                </div>
            </aside>

            <form class="friendly-form" action="submit-complaint.php" method="post" enctype="multipart/form-data">
                <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">

                <?php if ($error): ?>
                    <div class="form-error" role="alert">
                        <strong>Ada yang perlu diperbaiki</strong>
                        <p><?= e($error) ?></p>
                    </div>
                <?php endif; ?>

                <fieldset class="friendly-section">
                    <legend>
                        <span>1</span>
                        <div>
                            <strong>Ceritakan masalah</strong>
                            <small>Apa yang terjadi?</small>
                        </div>
                    </legend>

                    <div class="friendly-field">
                        <label for="judul">Masalah apa yang terjadi? <b>*</b></label>
                        <p class="field-hint">Tulis dengan singkat.</p>
                        <input id="judul" name="judul" required minlength="5" maxlength="150" value="<?= old('judul') ?>" placeholder="Contoh: Lampu jalan mati">
                    </div>

                    <div class="friendly-field">
                        <label for="kategori">Masalah ini tentang apa? <b>*</b></label>
                        <select id="kategori" name="kategori" required>
                            <option value="">Pilih jenis masalah</option>
                            <?php foreach ($categories as $category): ?>
                                <option <?= $oldCategory === $category ? 'selected' : '' ?>><?= e($category) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="friendly-field">
                        <label for="tanggal">Kapan masalah terjadi? <b>*</b></label>
                        <input id="tanggal" name="tanggal" type="date" max="<?= date('Y-m-d') ?>" required value="<?= old('tanggal') ?>">
                    </div>

                    <div class="friendly-field">
                        <label for="uraian">Ceritakan lebih lengkap <b>*</b></label>
                        <p class="field-hint">Apa yang terjadi? Siapa yang terdampak?</p>
                        <textarea id="uraian" name="uraian" required minlength="20" maxlength="3000" rows="6" placeholder="Contoh: Lampu jalan sudah mati selama tiga malam. Jalan menjadi gelap dan sulit dilalui."><?= old('uraian') ?></textarea>
                    </div>
                </fieldset>

                <fieldset class="friendly-section section-blue">
                    <legend>
                        <span>2</span>
                        <div>
                            <strong>Lokasi masalah</strong>
                            <small>Di mana kejadiannya?</small>
                        </div>
                    </legend>

                    <div class="two-fields">
                        <div class="friendly-field">
                            <label for="wilayah">Kecamatan <b>*</b></label>
                            <select id="wilayah" name="wilayah" required>
                                <option value="">Pilih kecamatan</option>
                                <?php foreach ($districts as $district): ?>
                                    <option <?= $oldDistrict === $district ? 'selected' : '' ?>><?= e($district) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="friendly-field">
                            <label for="desa">Desa atau kelurahan <b>*</b></label>
                            <input id="desa" name="desa" required maxlength="100" value="<?= old('desa') ?>" placeholder="Contoh: Bancar">
                        </div>
                    </div>

                    <div class="friendly-field">
                        <label for="alamat">Alamat kejadian <b>*</b></label>
                        <p class="field-hint">Sebutkan jalan atau tempat yang mudah dikenali.</p>
                        <textarea id="alamat" name="alamat" required maxlength="500" rows="3" placeholder="Contoh: Jalan Jenderal Sudirman, dekat Pasar Segamas"><?= old('alamat') ?></textarea>
                    </div>

                    <div class="friendly-field map-picker-field">
                        <label>Titik di peta <em>Boleh dikosongkan</em></label>
                        <p class="field-hint">Tekan peta pada tempat kejadian, atau gunakan lokasi perangkat.</p>
                        <button class="location-button" id="use-location" type="button">
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <circle cx="12" cy="12" r="3"/>
                                <circle cx="12" cy="12" r="8"/>
                                <path d="M12 2V5M12 19v3M2 12h3M19 12h3"/>
                            </svg>
                            Gunakan Lokasi Saya
                        </button>
                        <div id="complaint-map" class="complaint-map" aria-label="Peta untuk memilih lokasi kejadian"></div>
                        <p id="map-message" class="map-message">Belum ada titik yang dipilih.</p>
                        <input type="hidden" id="latitude" name="latitude" value="<?= old('latitude') ?>">
                        <input type="hidden" id="longitude" name="longitude" value="<?= old('longitude') ?>">
                    </div>

                    <div class="friendly-field">
                        <label>Foto masalah <em>Boleh dikosongkan</em></label>
                        <p class="field-hint">Foto membantu petugas memahami masalah.</p>
                        <label class="friendly-upload" for="foto">
                            <span>
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M4 4h16v16H4z"/>
                                    <circle cx="9" cy="9" r="2"/>
                                    <path d="m4 17 5-5 4 4 3-3 4 4"/>
                                </svg>
                            </span>
                            <div>
                                <strong>Pilih Foto</strong>
                                <small>Maksimal 5 foto · JPEG atau PNG</small>
                            </div>
                        </label>
                        <input class="file-input" id="foto" name="foto[]" type="file" accept="image/jpeg,image/png" multiple>
                    </div>
                </fieldset>

                <section class="send-section">
                    <div class="send-heading">
                        <span>3</span>
                        <div>
                            <h2>Siap dikirim?</h2>
                            <p>Periksa kembali informasi Anda.</p>
                        </div>
                    </div>

                    <label class="friendly-consent">
                        <input type="checkbox" name="setuju" required>
                        <span>Informasi yang saya tulis sudah benar. <b>*</b></span>
                    </label>

                    <div class="send-actions">
                        <a href="index.php">Batal</a>
                        <button type="submit">
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M22 2 11 13"/>
                                <path d="m22 2-7 20-4-9-9-4Z"/>
                            </svg>
                            <span>
                                <strong>Kirim Laporan</strong>
                                <small>Anda akan mendapat nomor laporan</small>
                            </span>
                        </button>
                    </div>
                </section>
            </form>
        </div>
    </section>
</main>

<footer class="simple-public-footer">
    <div class="public-container">
        <div>
            <strong>Lapor Purbalingga</strong>
            <span>Layanan Pengaduan Pelayanan Publik</span>
        </div>
        <p>© 2026 Pemerintah Kabupaten Purbalingga</p>
    </div>
</footer>

<script src="assets/js/maps.js"></script>
<script async src="https://maps.googleapis.com/maps/api/js?key=<?= e($config['app']['google_maps_api_key']) ?>&callback=initComplaintMap"></script>
<?php consume_old(); ?>
</body>
</html>
