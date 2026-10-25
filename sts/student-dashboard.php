<?php
require_once 'functions.php';
require_student();
$pdo = pdo_connect();
$stmt = $pdo->prepare('SELECT * FROM attendance WHERE student_id = ? ORDER BY created_at DESC LIMIT 50');
$stmt->execute([$_SESSION['student_id']]);
$records = $stmt->fetchAll();
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Student Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/css/styles.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
  <div class="container-fluid">
    <a class="navbar-brand" href="student-dashboard.php">Student Dashboard</a>
    <div class="d-flex">
      <span class="navbar-text me-3"><?php echo h($_SESSION['student_name']);?></span>
      <a class="btn btn-outline-light" href="logout.php">Logout</a>
    </div>
  </div>
</nav>
<div class="container py-4">
  <div class="row">
    <div class="col-md-6">
      <div class="card mb-3">
        <div class="card-body text-center">
          <h5>Mark Attendance</h5>
          <p>Click the button to scan QR and submit GPS + selfie.</p>
          <a class="btn btn-primary" href="scan-qr.php">Start Scan</a>
        </div>
      </div>
      <div class="card">
        <div class="card-body">
          <h5>Your Recent Attendance</h5>
          <table class="table table-sm">
            <thead><tr><th>Date</th><th>Subject</th><th>Period</th><th>Photo</th></tr></thead>
            <tbody>
            <?php foreach($records as $r): ?>
              <tr>
                <td><?php echo h($r['attendance_date']);?></td>
                <td><?php echo h($r['subject']);?></td>
                <td><?php echo h($r['period']);?></td>
                <td><?php if($r['selfie_path']): ?><a target="_blank" href="<?php echo h($r['selfie_path']);?>">View</a><?php endif;?></td>
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
          <h5>Notes</h5>
          <ul>
            <li>GPS permission is required.</li>
            <li>QR token must be valid and not expired.</li>
            <li>Attendance cannot be submitted twice for same session.</li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</div>
</body>
</html>
