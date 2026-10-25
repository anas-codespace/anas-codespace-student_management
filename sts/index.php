<?php
require_once 'functions.php';
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Smart Attendance System</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/css/styles.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
  <div class="container-fluid">
    <a class="navbar-brand" href="index.php">Smart Attendance</a>
  </div>
</nav>
<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-md-6">
      <div class="card shadow-sm">
        <div class="card-body text-center">
          <h3 class="card-title">Prototype</h3>
          <p class="card-text">Choose your role to continue</p>
          <a href="student-login.php" class="btn btn-outline-primary m-2">Student Login</a>
          <a href="staff-login.php" class="btn btn-outline-secondary m-2">Staff Login</a>
        </div>
      </div>
    </div>
  </div>
</div>
</body>
</html>
