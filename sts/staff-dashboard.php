<?php
require_once 'functions.php';
require_staff();
$pdo = pdo_connect();
$stmt = $pdo->query('SELECT * FROM qr_sessions ORDER BY created_at DESC LIMIT 20');
$sessions = $stmt->fetchAll();
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Staff Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/css/styles.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
  <div class="container-fluid">
    <a class="navbar-brand" href="staff-dashboard.php">Staff Dashboard</a>
    <div class="d-flex">
      <span class="navbar-text me-3"><?php echo h($_SESSION['staff_name']);?></span>
      <a class="btn btn-outline-light" href="logout.php">Logout</a>
    </div>
  </div>
</nav>
<div class="container py-4">
  <div class="row">
    <div class="col-md-6">
      <div class="card mb-3">
        <div class="card-body">
          <h5>Generate QR</h5>
          <form method="post" action="staff-qr.php">
            <div class="mb-2"><input name="subject" class="form-control" placeholder="Subject" required></div>
            <div class="mb-2"><input name="period" class="form-control" placeholder="Period (e.g. P1)" required></div>
            <div class="mb-2"><input name="section" class="form-control" placeholder="Section" required></div>
            <button class="btn btn-primary">Generate QR (valid 5 minutes)</button>
          </form>
        </div>
      </div>
      <div class="card">
        <div class="card-body">
          <h5>Recent QR Sessions</h5>
          <table class="table table-sm">
            <thead><tr><th>Subject</th><th>Period</th><th>Section</th><th>Created</th><th>Expires</th><th></th></tr></thead>
            <tbody>
            <?php foreach($sessions as $s): ?>
              <tr>
                <td><?php echo h($s['subject']);?></td>
                <td><?php echo h($s['period']);?></td>
                <td><?php echo h($s['section']);?></td>
                <td><?php echo h($s['created_at']);?></td>
                <td><?php echo h($s['expires_at']);?></td>
                <td><a class="btn btn-sm btn-outline-primary" href="attendance-report.php?session_id=<?php echo $s['id'];?>">View</a></td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
    <div class="col-md-6">
      <div class="card">
        <div class="card-body">
          <h5>Instructions</h5>
          <ul>
            <li>Generate QR for a class. QR token is valid for 5 minutes.</li>
            <li>Students will scan QR and submit GPS + selfie.</li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</div>
</body>
</html>
