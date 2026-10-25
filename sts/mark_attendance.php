<?php
require_once 'functions.php';
require_student();
header('Content-Type: application/json');
$pdo = pdo_connect();
$data = json_decode(file_get_contents('php://input'), true);
if(!$data){ echo json_encode(['success'=>false,'error'=>'Invalid request']); exit; }
$token = $data['token'] ?? '';
$lat = $data['latitude'] ?? null;
$lng = $data['longitude'] ?? null;
$selfie = $data['selfie'] ?? '';

// validate QR session
$stmt = $pdo->prepare('SELECT * FROM qr_sessions WHERE qr_token = ? AND expires_at >= NOW()');
$stmt->execute([$token]);
$session = $stmt->fetch();
if(!$session){ echo json_encode(['success'=>false,'error'=>'QR invalid or expired']); exit; }

// check attendance not already submitted for this student & session date
$today = date('Y-m-d');
$stmt = $pdo->prepare('SELECT * FROM attendance WHERE student_id=? AND subject=? AND period=? AND attendance_date = ?');
$stmt->execute([$_SESSION['student_id'], $session['subject'], $session['period'], $today]);
if($stmt->fetch()){ echo json_encode(['success'=>false,'error'=>'Attendance already submitted']); exit; }

// save selfie
$selfie_path = null;
if(preg_match('/^data:image\/jpeg;base64,/', $selfie) || preg_match('/^data:image\/png;base64,/', $selfie)){
    $data_parts = explode(',', $selfie);
    $imgdata = base64_decode($data_parts[1]);
    $dir = __DIR__.'/uploads/selfies'; if(!file_exists($dir)) mkdir($dir,0755,true);
    $filename = $dir.'/s_'.time().'_'.rand(1000,9999).'.jpg';
    file_put_contents($filename, $imgdata);
    $selfie_path = 'uploads/selfies/'.basename($filename);
}

$ins = $pdo->prepare('INSERT INTO attendance (student_id,subject,period,attendance_date,latitude,longitude,selfie_path) VALUES (?,?,?,?,?,?,?)');
$ins->execute([$_SESSION['student_id'],$session['subject'],$session['period'],$today,$lat,$lng,$selfie_path]);
echo json_encode(['success'=>true]);
