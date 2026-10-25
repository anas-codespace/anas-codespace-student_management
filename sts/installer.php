<?php
// Simple installer: creates database/tables and seeds sample users.
require_once 'config.php';

// Connect without DB to allow CREATE DATABASE
try{
    $dsn = 'mysql:host=' . DB_HOST . ';charset=utf8mb4';
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
}catch(PDOException $e){
    die('DB Connection failed: '.$e->getMessage());
}

$sql_file = __DIR__ . '/create_tables.sql';
if(!file_exists($sql_file)) die('create_tables.sql not found');
$sql = file_get_contents($sql_file);

// Split and execute statements
$statements = array_filter(array_map('trim', explode(";", $sql)));
foreach($statements as $stmt){
    if ($stmt === '') continue;
    try{ $pdo->exec($stmt); } catch(Exception $e){ /* skip errors like USE db duplication */ }
}

// Now connect to the newly created DB
$pdo = pdo_connect();

// Seed sample staff and students if tables empty
$cnt = $pdo->query('SELECT COUNT(*) FROM staff')->fetchColumn();
if($cnt == 0){
    $pw = password_hash('password123', PASSWORD_DEFAULT);
    $ins = $pdo->prepare('INSERT INTO staff (staff_id,name,password) VALUES (?,?,?)');
    $ins->execute(['STF001','Alice Instructor',$pw]);
    $ins->execute(['STF002','Bob Lecturer',$pw]);
}

$cnt2 = $pdo->query('SELECT COUNT(*) FROM students')->fetchColumn();
if($cnt2 == 0){
    $pw = password_hash('password123', PASSWORD_DEFAULT);
    $ins = $pdo->prepare('INSERT INTO students (reg_no,name,department,password) VALUES (?,?,?,?)');
    $ins->execute(['REG1001','Charlie Student','CSE',$pw]);
    $ins->execute(['REG1002','Dana Student','ECE',$pw]);
}

// Create uploads dirs
if(!file_exists(__DIR__.'/uploads/selfies')) mkdir(__DIR__.'/uploads/selfies',0755,true);
if(!file_exists(__DIR__.'/uploads/qrcodes')) mkdir(__DIR__.'/uploads/qrcodes',0755,true);

?>
<!doctype html>
<html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Installer</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body class="p-4 bg-light">
<div class="container">
  <div class="card">
    <div class="card-body">
      <h4 class="card-title">Installer</h4>
      <p>Database and tables created (if missing). Sample users seeded with password: <strong>password123</strong></p>
      <ul>
        <li>Staff: STF001 / STF002 (password123)</li>
        <li>Students: REG1001 / REG1002 (password123)</li>
      </ul>
      <p>For security, delete this file after running.</p>
      <a href="index.php" class="btn btn-primary">Go to App</a>
    </div>
  </div>
</div>
</body></html>
