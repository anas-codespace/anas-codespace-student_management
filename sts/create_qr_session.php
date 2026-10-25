<?php
require_once 'functions.php';
require_staff();

header('Content-Type: application/json; charset=utf-8');

// Use the same timezone consistently for QR creation.
$timezone = new DateTimeZone('Asia/Kolkata');
date_default_timezone_set('Asia/Kolkata');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'error' => 'Method not allowed.'
    ]);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!is_array($data)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'Invalid request.'
    ]);
    exit;
}

$subject = trim($data['subject'] ?? '');
$period  = trim($data['period'] ?? '');
$section = trim($data['section'] ?? '');

if (
    $subject === '' ||
    $period === '' ||
    $section === '' ||
    mb_strlen($subject) > 150 ||
    mb_strlen($period) > 50 ||
    mb_strlen($section) > 50
) {
    http_response_code(422);
    echo json_encode([
        'success' => false,
        'error' => 'Enter valid subject, period and section details.'
    ]);
    exit;
}

try {
    $pdo = pdo_connect();

    // Keep MySQL NOW() aligned with Asia/Kolkata for this connection.
    $pdo->exec("SET time_zone = '+05:30'");

    // Generate a cryptographically secure token.
    $token = bin2hex(random_bytes(32));

    // Start the 5-minute validity period at session creation.
    $createdAt = new DateTimeImmutable('now', $timezone);
    $expiresAt = $createdAt->modify('+5 minutes');

    $createdAtSql = $createdAt->format('Y-m-d H:i:s');
    $expiresAtSql = $expiresAt->format('Y-m-d H:i:s');

    $stmt = $pdo->prepare(
        'INSERT INTO qr_sessions
            (subject, period, section, qr_token, created_at, expires_at)
         VALUES (?, ?, ?, ?, ?, ?)'
    );

    $stmt->execute([
        $subject,
        $period,
        $section,
        $token,
        $createdAtSql,
        $expiresAtSql
    ]);

    echo json_encode([
        'success' => true,
        'token' => $token,
        'subject' => $subject,
        'period' => $period,
        'section' => $section,

        // ISO 8601 timestamp with timezone offset for JavaScript.
        'created_at' => $createdAt->format(DateTimeInterface::ATOM),
        'expires_at' => $expiresAt->format(DateTimeInterface::ATOM)
    ], JSON_UNESCAPED_SLASHES);

} catch (Throwable $e) {
    error_log('QR generation failed: ' . $e->getMessage());

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Unable to generate QR code. Please try again.'
    ]);
}