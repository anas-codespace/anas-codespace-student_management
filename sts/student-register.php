<?php
require_once 'functions.php';

$pdo = pdo_connect();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = trim($_POST['name'] ?? '');
    $reg = trim($_POST['reg_no'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if ($name === '' || $reg === '' || $password === '') {

        $error = 'Please fill in all required fields.';

    } elseif (strlen($password) < 8) {

        $error = 'Password must contain at least 8 characters.';

    } elseif ($password !== $confirm_password) {

        $error = 'Passwords do not match.';

    } else {

        // Check whether register number already exists
        $stmt = $pdo->prepare(
            'SELECT id FROM students WHERE reg_no = ?'
        );

        $stmt->execute([$reg]);

        if ($stmt->fetch()) {

            $error = 'This register number is already registered.';

        } else {

            // Hash password before storing it
            $hashed_password = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            try {

                $stmt = $pdo->prepare(
                    'INSERT INTO students (name, reg_no, password)
                     VALUES (?, ?, ?)'
                );

                $stmt->execute([
                    $name,
                    $reg,
                    $hashed_password
                ]);

                header('Location: student-login.php?registered=1');
                exit;

            } catch (PDOException $e) {

                // Avoid exposing database details to users
                if ($e->getCode() === '23000') {
                    $error = 'This register number is already registered.';
                } else {
                    $error = 'Registration failed. Please try again.';
                }

            }
        }
    }
}
?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Student Registration</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link href="assets/css/styles.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container py-5">

    <div class="row justify-content-center">

        <div class="col-md-5">

            <div class="card shadow-sm border-0">

                <div class="card-body p-4">

                    <h4 class="text-center mb-2">
                        Student Registration
                    </h4>

                    <p class="text-muted text-center mb-4">
                        Create your student account.
                    </p>

                    <?php if ($error !== ''): ?>

                        <div class="alert alert-danger">
                            <?= h($error) ?>
                        </div>

                    <?php endif; ?>

                    <form method="post">

                        <div class="mb-3">

                            <label for="name" class="form-label">
                                Full Name
                            </label>

                            <input
                                type="text"
                                id="name"
                                name="name"
                                class="form-control"
                                value="<?= h($_POST['name'] ?? '') ?>"
                                required
                                maxlength="150"
                                autocomplete="name"
                            >

                        </div>

                        <div class="mb-3">

                            <label for="reg_no" class="form-label">
                                Register Number
                            </label>

                            <input
                                type="text"
                                id="reg_no"
                                name="reg_no"
                                class="form-control"
                                value="<?= h($_POST['reg_no'] ?? '') ?>"
                                required
                                maxlength="50"
                                autocomplete="username"
                            >

                        </div>

                        <div class="mb-3">

                            <label for="password" class="form-label">
                                Password
                            </label>

                            <input
                                type="password"
                                id="password"
                                name="password"
                                class="form-control"
                                required
                                minlength="8"
                                autocomplete="new-password"
                            >

                            <small class="text-muted">
                                Minimum 8 characters.
                            </small>

                        </div>

                        <div class="mb-3">

                            <label
                                for="confirm_password"
                                class="form-label"
                            >
                                Confirm Password
                            </label>

                            <input
                                type="password"
                                id="confirm_password"
                                name="confirm_password"
                                class="form-control"
                                required
                                minlength="8"
                                autocomplete="new-password"
                            >

                        </div>

                        <div class="d-grid gap-2">

                            <button
                                type="submit"
                                class="btn btn-primary"
                            >
                                Register
                            </button>

                            <a
                                href="student-login.php"
                                class="btn btn-outline-secondary"
                            >
                                Already have an account? Login
                            </a>

                        </div>

                    </form>

                </div>

            </div>

        </div>

    </div>

</div>

</body>
</html>