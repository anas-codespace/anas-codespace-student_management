<?php
require_once 'functions.php';

$pdo = pdo_connect();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = trim($_POST['name'] ?? '');
    $sid = trim($_POST['staff_id'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if ($name === '' || $sid === '' || $password === '') {

        $error = 'Please fill in all required fields.';

    } elseif (strlen($password) < 8) {

        $error = 'Password must contain at least 8 characters.';

    } elseif ($password !== $confirm_password) {

        $error = 'Passwords do not match.';

    } else {

        // Check whether Staff ID already exists
        $stmt = $pdo->prepare(
            'SELECT id FROM staff WHERE staff_id = ?'
        );

        $stmt->execute([$sid]);

        if ($stmt->fetch()) {

            $error = 'This Staff ID is already registered.';

        } else {

            // Hash the password before saving
            $hashed_password = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            try {

                $stmt = $pdo->prepare(
                    'INSERT INTO staff (name, staff_id, password)
                     VALUES (?, ?, ?)'
                );

                $stmt->execute([
                    $name,
                    $sid,
                    $hashed_password
                ]);

                header('Location: staff-login.php?registered=1');
                exit;

            } catch (PDOException $e) {

                if ($e->getCode() === '23000') {
                    $error = 'This Staff ID is already registered.';
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

    <title>Staff Registration</title>

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
                        Staff Registration
                    </h4>

                    <p class="text-muted text-center mb-4">
                        Create your staff account.
                    </p>

                    <?php if ($error !== ''): ?>

                        <div class="alert alert-danger" role="alert">
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
                                placeholder="Enter your full name"
                                maxlength="150"
                                required
                                autocomplete="name"
                            >

                        </div>


                        <div class="mb-3">

                            <label for="staff_id" class="form-label">
                                Staff ID
                            </label>

                            <input
                                type="text"
                                id="staff_id"
                                name="staff_id"
                                class="form-control"
                                value="<?= h($_POST['staff_id'] ?? '') ?>"
                                placeholder="Enter your Staff ID"
                                maxlength="50"
                                required
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
                                placeholder="Create a password"
                                minlength="8"
                                required
                                autocomplete="new-password"
                            >

                            <small class="text-muted">
                                Minimum 8 characters.
                            </small>

                        </div>


                        <div class="mb-3">

                            <label for="confirm_password" class="form-label">
                                Confirm Password
                            </label>

                            <input
                                type="password"
                                id="confirm_password"
                                name="confirm_password"
                                class="form-control"
                                placeholder="Confirm your password"
                                minlength="8"
                                required
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
                                href="staff-login.php"
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