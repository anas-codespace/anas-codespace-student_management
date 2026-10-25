<?php
// config.php - database connection
define('DB_HOST','127.0.0.1');
define('DB_NAME','smart_attendance');
define('DB_USER','root');
define('DB_PASS','');

function pdo_connect(){
    static $pdo = null;
    if ($pdo === null) {
        $dsn = 'mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8mb4';
        try{
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        }catch(PDOException $e){
            die('DB Connection failed: '.$e->getMessage());
        }
    }
    return $pdo;
}

// Ensure uploads dir exists
if (!file_exists(__DIR__.'/uploads')) {
    mkdir(__DIR__.'/uploads', 0755, true);
}
