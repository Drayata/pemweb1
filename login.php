<?php

declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

// Jika sudah masuk, arahkan langsung ke halaman akun
if (user()) {
    redirect('akun.php');
}

$error = null;

// Proses formulir saat metode POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $email = mb_strtolower(trim((string) ($_POST['email'] ?? '')));
    $password = (string) ($_POST['password'] ?? '');

    $stmt = db()->prepare('SELECT id, name, email, password_hash, role FROM users WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    $account = $stmt->fetch();

    if ($account && password_verify($password, $account['password_hash'])) {
        session_regenerate_id(true);
        unset($account['password_hash']);
        $_SESSION['user'] = $account;

        // Tentukan halaman tujuan berdasarkan peran pengguna
        $defaultPage = $account['role'] === 'central_admin' ? 'admin.php' : 'akun.php';
        $target = $_SESSION['after_login'] ?? base_url($defaultPage);
        unset($_SESSION['after_login']);

        header('Location: ' . $target);
        exit;
    }

    // Delay singkat untuk mitigasi brute force
    usleep(300000);
    $error = 'Email atau kata sandi salah.';
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk — Lapor Purbalingga</title>
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
        <p class="kicker blue">Selamat datang kembali</p>
        <h1>Pantau laporan Anda.</h1>
        <p class="lead">Masuk untuk melihat perkembangan dan tanggapan petugas.</p>
    </section>

    <form class="auth-card" method="post">
        <h2>Masuk</h2>

        <?php if ($error): ?>
            <p class="alert"><?= e($error) ?></p>
        <?php endif; ?>

        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">

        <label>
            Email
            <input name="email" type="email" required autocomplete="email">
        </label>

        <label>
            Kata sandi
            <input name="password" type="password" required autocomplete="current-password">
        </label>

        <button class="pill" type="submit">Masuk</button>
        <p>Belum punya akun? <a href="register.php">Daftar</a></p>
    </form>
</main>
</body>
</html>
