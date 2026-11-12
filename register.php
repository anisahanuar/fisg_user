
<?php
session_start();
require __DIR__ . '/db.php';

$errors = [];
$name = '';
$email = '';
$phone = '';
$state = '';
$selectedRole = 'user';

$states = [
    'Johor',
    'Kedah',
    'Kelantan',
    'Melaka',
    'Negeri Sembilan',
    'Pahang',
    'Perak',
    'Perlis',
    'Pulau Pinang',
    'Sabah',
    'Sarawak',
    'Selangor',
    'Terengganu',
    'W.P. Kuala Lumpur',
    'W.P. Putrajaya',
    'W.P. Labuan'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = trim($_POST['full_name'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $phone = trim($_POST['phone'] ?? '');
    $state = trim($_POST['state'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    $terms = isset($_POST['terms']);
    $selectedRole = $_POST['role'] ?? 'user';

    if (!in_array($selectedRole, ['user', 'admin'], true)) {
        $selectedRole = 'user';
        $errors[] = 'Please select a valid account type.';
    }

    if ($name === '' || mb_strlen($name) > 120) {
        $errors[] = 'Please enter your full name (up to 120 characters).';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 190) {
        $errors[] = 'Please enter a valid email address.';
    }

    if ($phone === '' || strlen($phone) > 30) {
        $errors[] = 'Please enter your phone number.';
    }

    if (!in_array($state, $states, true)) {
        $errors[] = 'Please select a valid state.';
    }

    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters long.';
    }

    if ($password !== $confirm) {
        $errors[] = 'The passwords do not match.';
    }

    if (!$terms) {
        $errors[] = 'Please confirm that you have read the terms and risk notice.';
    }

    if (!$errors) {
        try {

            $check = $pdo->prepare(
                'SELECT id FROM users WHERE email = ? LIMIT 1'
            );

            $check->execute([$email]);

            if ($check->fetch()) {

                $errors[] = 'An account with this email already exists. Please log in instead.';

            } else {

                $hash = password_hash($password, PASSWORD_DEFAULT);

                // Admin requests require approval.
                $storedRole = ($selectedRole === 'admin')
                    ? 'pending_admin'
                    : 'user';

                $stmt = $pdo->prepare(
                    'INSERT INTO users
                    (full_name, email, phone, state, password_hash, role)
                    VALUES (?, ?, ?, ?, ?, ?)'
                );

                $stmt->execute([
                    $name,
                    $email,
                    $phone,
                    $state,
                    $hash,
                    $storedRole
                ]);

                $newId = (int)$pdo->lastInsertId();

                if ($storedRole === 'pending_admin') {
                    $_SESSION['pending_admin_name'] = $name;
                    header('Location: admin_pending.php');
                    exit;
                }

                $_SESSION['user_id'] = $newId;
                $_SESSION['user_name'] = $name;

                header('Location: account.php?registered=1');
                exit;
            }

        } catch (PDOException $e) {
            $errors[] = 'Could not create the account. Please check your database setup.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Register | FISG</title>

    <link rel="stylesheet" href="auth.css">

    <link rel="preconnect" href="https://fonts.googleapis.com">

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>

<body>

<main class="auth-wrap">

    <a class="brand" href="index.html">
        <span class="brand-mark">◆</span> FISG
    </a>

    <section class="auth-card">

        <h1>Create your account</h1>

        <p class="intro">
            Register to get started with the FISG platform.
        </p>

        <?php if ($errors): ?>
            <div class="message error">
                <?php foreach ($errors as $error): ?>
                    <div><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="post" action="register.php">

            <!-- Full Name -->
            <div class="field">
                <label for="full_name">Full name</label>

                <input
                    id="full_name"
                    name="full_name"
                    type="text"
                    autocomplete="name"
                    maxlength="120"
                    required
                    value="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>"
                    placeholder="Enter your full name"
                >
            </div>

            <!-- Email -->
            <div class="field">
                <label for="email">Email address</label>

                <input
                    id="email"
                    name="email"
                    type="email"
                    autocomplete="email"
                    maxlength="190"
                    required
                    value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>"
                    placeholder="Enter your email address"
                >
            </div>

            <!-- Phone -->
            <div class="field">
                <label for="phone">Phone number</label>

                <input
                    id="phone"
                    name="phone"
                    type="tel"
                    autocomplete="tel"
                    maxlength="30"
                    required
                    value="<?= htmlspecialchars($phone, ENT_QUOTES, 'UTF-8') ?>"
                    placeholder="Enter your phone number"
                >
            </div>

            <!-- State -->
            <div class="field">
                <label for="state">State</label>

                <select id="state" name="state" required>

                    <option value="">Select your state</option>

                    <?php foreach ($states as $s): ?>

                        <option
                            value="<?= htmlspecialchars($s, ENT_QUOTES, 'UTF-8') ?>"
                            <?= $state === $s ? 'selected' : '' ?>
                        >
                            <?= htmlspecialchars($s, ENT_QUOTES, 'UTF-8') ?>
                        </option>

                    <?php endforeach; ?>

                </select>
            </div>

            <!-- Account Type -->
            <div class="field">
                <label for="role">Account type</label>

                <select id="role" name="role" required>

                    <option value="user"
                        <?= $selectedRole === 'user' ? 'selected' : '' ?>>
                        User
                    </option>

                    <option value="admin"
                        <?= $selectedRole === 'admin' ? 'selected' : '' ?>>
                        Admin (requires approval)
                    </option>

                </select>

                <div class="hint">
                    Admin access is not activated automatically.
                    An existing administrator must approve the request.
                </div>
            </div>

            <!-- Password -->
            <div class="field">
                <label for="password">Password</label>

                <input
                    id="password"
                    name="password"
                    type="password"
                    autocomplete="new-password"
                    minlength="8"
                    required
                    placeholder="Create a password"
                >

                <div class="hint">
                    Use at least 8 characters. Your password is stored as a secure hash, not plain text.
                </div>
            </div>

            <!-- Confirm Password -->
            <div class="field">
                <label for="confirm_password">Confirm password</label>

                <input
                    id="confirm_password"
                    name="confirm_password"
                    type="password"
                    autocomplete="new-password"
                    minlength="8"
                    required
                    placeholder="Re-enter your password"
                >
            </div>

            <!-- Terms -->
            <div class="terms-field">

                <label class="terms-label">
                    <input
                        type="checkbox"
                        name="terms"
                        value="1"
                        required
                        <?= isset($_POST['terms']) ? 'checked' : '' ?>
                    >

                    <span>
                        I acknowledge the terms and trading risk notice.
                    </span>
                </label>

            </div>

            <button class="submit" type="submit">
                Create account
            </button>

        </form>

        <p class="foot">
            Already have an account?
            <a href="login.php">Log in</a>
        </p>

    </section>

    <a class="back" href="index.html">
        ← Back to FISG homepage
    </a>

</main>

</body>
</html>
