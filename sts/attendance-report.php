<?php
require_once 'functions.php';
require_staff();
$pdo = pdo_connect();
$session_id = $_GET['session_id'] ?? null;
if($session_id){
  $stmt = $pdo->prepare('SELECT * FROM qr_sessions WHERE id=?'); $stmt->execute([$session_id]); $ses = $stmt->fetch();
  $att = $pdo->prepare('SELECT a.*, s.reg_no, s.name FROM attendance a JOIN students s ON s.id=a.student_id WHERE a.subject=? AND a.period=? AND a.attendance_date=? ORDER BY a.created_at DESC');
  $att->execute([$ses['subject'],$ses['period'],date('Y-m-d')]);
  $rows = $att->fetchAll();
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Attendance Report</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">
  <a href="staff-dashboard.php" class="btn btn-link">&larr; Back</a>
  <div class="container">
    <h4>Attendance Report</h4>
    <?php if(!empty($ses)): ?>
      <p>Session: <?php echo h($ses['subject'].' / '.$ses['period'].' / '.$ses['section']); ?> (<?php echo h($ses['created_at']); ?>)</p>
      <table class="table table-striped">
        <thead><tr><th>Reg No</th><th>Name</th><th>Date</th><th>GPS</th><th>Selfie</th></tr></thead>
        <tbody>
        <?php foreach($rows as $r): ?>
          <tr>
            <td><?php echo h($r['reg_no']);?></td>
            <td><?php echo h($r['name']);?></td>
            <td><?php echo h($r['attendance_date']);?></td>
            <td><?php echo h($r['latitude'].', '.$r['longitude']);?></td>
            <td><?php if($r['selfie_path']): ?><a target="_blank" href="<?php echo h($r['selfie_path']);?>">View</a><?php endif;?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    <?php else: ?>
      <div class="alert alert-info">Select a session from the dashboard</div>
    <?php endif; ?>
  </div>
</body>
</html>
