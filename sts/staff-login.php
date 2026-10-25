<?php
require_once 'functions.php';

$pdo = pdo_connect();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $sid = trim($_POST['staff_id'] ?? '');
    $pass = $_POST['password'] ?? '';

    $stmt = $pdo->prepare(
        'SELECT * FROM staff WHERE staff_id = ?'
    );

    $stmt->execute([$sid]);
    $user = $stmt->fetch();

    if ($user && password_verify($pass, $user['password'])) {

        // Regenerate session ID after successful login
        session_regenerate_id(true);

        $_SESSION['staff_id'] = $user['id'];
        $_SESSION['staff_name'] = $user['name'];

        header('Location: staff-dashboard.php');
        exit;

    } else {
        $error = 'Invalid staff ID or password.';
    }
}
?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Staff Login</title>

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

                    <h4 class="card-title text-center mb-2">
                        Staff Login
                    </h4>

                    <p class="text-muted text-center mb-4">
                        Login to access your staff dashboard.
                    </p>


                    <!-- REGISTRATION SUCCESS MESSAGE -->
                    <?php
                    if (
                        isset($_GET['registered']) &&
                        $_GET['registered'] === '1'
                    ):
                    ?>

                        <div class="alert alert-success" role="alert">
                            Registration successful!
                            You can now log in.
                        </div>

                    <?php endif; ?>


                    <!-- LOGIN ERROR MESSAGE -->
                    <?php if ($error !== ''): ?>

                        <div class="alert alert-danger" role="alert">
                            <?= h($error) ?>
                        </div>

                    <?php endif; ?>


                    <!-- STAFF LOGIN FORM -->
                    <form method="post">

                        <div class="mb-3">

                            <label for="staff_id" class="form-label">
                                Staff ID
                            </label>

                            <input
                                type="text"
                                id="staff_id"
                                name="staff_id"
                                class="form-control"
                                placeholder="Enter your staff ID"
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
                                placeholder="Enter your password"
                                required
                                autocomplete="current-password"
                            >

                        </div>


                        <div class="d-grid gap-2">

                            <button
                                type="submit"
                                class="btn btn-primary"
                            >
                                Login
                            </button>

                            <a
                                href="index.php"
                                class="btn btn-outline-secondary"
                            >
                                Back to Home
                            </a>

                        </div>

                    </form>


                    <hr class="my-4">


                    <!-- NEW STAFF REGISTRATION -->
                    <div class="text-center">

                        <p class="mb-2">
                            New staff member?
                        </p>

                        <a
                            href="staff-register.php"
                            class="btn btn-outline-primary w-100"
                        >
                            Create New Account
                        </a>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

</body>
</html>