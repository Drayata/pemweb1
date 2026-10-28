<?php

declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

// Jika sudah masuk, arahkan langsung ke akun
if (user()) {
    redirect('akun.php');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $name = trim((string) ($_POST['name'] ?? ''));
    $email = mb_strtolower(trim((string) ($_POST['email'] ?? '')));
    $password = (string) ($_POST['password'] ?? '');

    // Validasi input
    if (mb_strlen($name) < 3 || mb_strlen($name) > 100) {
        $errors[] = 'Nama harus terdiri dari 3–100 karakter.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 190) {
        $errors[] = 'Alamat email tidak valid.';
    }
    if (strlen($password) < 8) {
        $errors[] = 'Kata sandi minimal 8 karakter.';
    }

    if (!$errors) {
        try {
            $stmt = db()->prepare('INSERT INTO users (name, email, password_hash) VALUES (?, ?, ?)');
            $stmt->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT)]);

            // Sesi baru setelah pendaftaran berhasil
            session_regenerate_id(true);
            $_SESSION['user'] = [
                'id' => (int) db()->lastInsertId(),
                'name' => $name,
                'email' => $email,
                'role' => 'citizen',
            ];

            redirect('akun.php');
        } catch (PDOException $exception) {
            // Kode 23000 = integritas unik violated (email duplikat)
            if ($exception->getCode() === '23000') {
                $errors[] = 'Email sudah terdaftar.';
            } else {
                throw $exception;
            }
        }
    }
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Daftar — Lapor Purbalingga</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="assets/css/auth.css">
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
            <a href="index.php">Kembali ke beranda</a>
        </div>
    </nav>
</header>

<main class="auth-page">
    <section class="auth-copy">
        <p class="kicker blue">Akun warga</p>
        <h1>Mulai melapor.</h1>
        <p class="lead">Satu akun untuk membuat dan memantau seluruh pengaduan Anda.</p>
    </section>

    <form class="auth-card" method="post">
        <h2>Buat akun</h2>

        <?php foreach ($errors as $error): ?>
            <p class="alert"><?= e($error) ?></p>
        <?php endforeach; ?>

        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">

        <label>
            Nama lengkap
            <input name="name" required maxlength="100" autocomplete="name" value="<?= e($_POST['name'] ?? '') ?>">
        </label>

        <label>
            Email
            <input name="email" type="email" required maxlength="190" autocomplete="email" value="<?= e($_POST['email'] ?? '') ?>">
        </label>

        <label>
            Kata sandi
            <input name="password" type="password" required minlength="8" autocomplete="new-password">
            <small>Minimal 8 karakter.</small>
        </label>

        <button class="pill" type="submit">Daftar</button>
        <p>Sudah punya akun? <a href="login.php">Masuk</a></p>
    </form>
</main>
</body>
</html>
