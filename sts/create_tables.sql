-- Active: 1792959878144@@127.0.0.1@3306@student
-- Create database schema for Smart Attendance System
CREATE DATABASE IF NOT EXISTS smart_attendance;
USE smart_attendance;
CREATE TABLE IF NOT EXISTS students (

    id INT AUTO_INCREMENT PRIMARY KEY,

    reg_no VARCHAR(50) NOT NULL,

    name VARCHAR(150) NOT NULL,

    department VARCHAR(100),

    password VARCHAR(255) NOT NULL,

    CONSTRAINT unique_student_reg_no UNIQUE (reg_no)

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS staff (

    id INT AUTO_INCREMENT PRIMARY KEY,

    staff_id VARCHAR(50) NOT NULL,

    name VARCHAR(150) NOT NULL,

    password VARCHAR(255) NOT NULL,

    CONSTRAINT unique_staff_id UNIQUE (staff_id)

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS qr_sessions (

    id INT AUTO_INCREMENT PRIMARY KEY,

    subject VARCHAR(150) NOT NULL,

    period VARCHAR(50) NOT NULL,

    section VARCHAR(50) NOT NULL,

    qr_token VARCHAR(255) NOT NULL,

    created_at DATETIME NOT NULL,

    expires_at DATETIME NOT NULL,

    CONSTRAINT unique_qr_token UNIQUE (qr_token)

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS attendance (

    id INT AUTO_INCREMENT PRIMARY KEY,

    student_id INT NOT NULL,

    subject VARCHAR(150) NOT NULL,

    period VARCHAR(50) NOT NULL,

    attendance_date DATE NOT NULL,

    latitude DECIMAL(10,7),

    longitude DECIMAL(10,7),

    selfie_path VARCHAR(255),

    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_attendance_student
        FOREIGN KEY (student_id)
        REFERENCES students(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,

    CONSTRAINT unique_student_attendance
        UNIQUE (
            student_id,
            subject,
            period,
            attendance_date
        )

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;