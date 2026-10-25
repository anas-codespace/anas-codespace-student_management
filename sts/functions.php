<?php
require_once __DIR__ . '/config.php';
session_start();

function is_staff_logged_in(){
    return !empty($_SESSION['staff_id']);
}

function is_student_logged_in(){
    return !empty($_SESSION['student_id']);
}

function require_staff(){
    if(!is_staff_logged_in()){
        header('Location: staff-login.php'); exit;
    }
}

function require_student(){
    if(!is_student_logged_in()){
        header('Location: student-login.php'); exit;
    }
}

function h($s){ return htmlspecialchars($s,ENT_QUOTES); }
