<?php
session_start();
require __DIR__ . '/db.php';

$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
        $error = 'Please enter your email address and password.';
    } else {
        try {
            $stmt = $pdo->prepare('SELECT id, full_name, email, password_hash, role FROM users WHERE email = ? LIMIT 1');
            $stmt->execute([$email]);
            $account = $stmt->fetch();

            if (!$account || !password_verify($password, $account['password_hash'])) {
                $error = 'Email or password is incorrect.';
            } elseif ($account['role'] !== 'user') {
                $error = 'This login page is for user accounts only. Please use the appropriate account access page.';
            } else {
                session_regenerate_id(true);
                $_SESSION['user_id'] = (int)$account['id'];
                $_SESSION['user_name'] = $account['full_name'];
                $_SESSION['user_role'] = 'user';
                header('Location: index.html');
                exit;
            }
        } catch (PDOException $e) {
            $error = 'Unable to sign in. Please check your database setup.';
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>User Login | FISG</title><link rel="stylesheet" href="auth.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
<main class="auth-wrap">
<a class="brand" href="index.html"><span class="brand-mark">◆</span> FISG</a>
<section class="auth-card">
<h1>Welcome back</h1><p class="intro">Log in to your FISG account.</p>
<?php if ($error !== ''): ?><div class="message error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
<form method="post" action="login.php">
<div class="field"><label for="email">Email address</label><input id="email" name="email" type="email" autocomplete="username" maxlength="190" required value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>"></div>
<div class="field"><label for="password">Password</label><input id="password" name="password" type="password" autocomplete="current-password" required></div>
<button class="submit" type="submit">Log In</button>
</form>
<p class="foot">Don't have an account? <a href="register.php">Register now</a></p>
</section><a class="back" href="index.html">← Back to FISG homepage</a>
</main>
</body></html>
