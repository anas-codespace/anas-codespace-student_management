A simple web-based attendance management system developed as a college
final-year project using PHP, MySQL, and Bootstrap 5.

The system allows staff members to create attendance sessions using
unique alphanumeric codes. Students can log in and enter the active code
to mark their attendance. Each attendance code remains valid for 5
minutes, providing a simple time-limited validation mechanism.

Features

Student & Staff Authentication -- Separate login flows and
dashboards for students and staff.

Alphanumeric Attendance Code -- Staff can generate a unique code
for each attendance session.

5-Minute Code Validity -- Each generated attendance code
automatically expires after 5 minutes.

Digital Attendance Marking -- Students can enter the active code
to mark their attendance.

Attendance Reports -- Staff can view and manage student
attendance records.

Role-Based Dashboards -- Dedicated interfaces for students and
staff.

MySQL Database -- Stores users, attendance sessions, and
attendance records.

Responsive UI -- Built with Bootstrap 5 for desktop and
mobile-friendly access.

Local Deployment -- Designed to run locally using XAMPP.

Tech Stack

PHP 8

MySQL

Bootstrap 5

HTML5

CSS3

JavaScript

XAMPP

Requirements

XAMPP

PHP 8 or later

MySQL

Modern web browser

Installation & Setup

1. Create the Database

Open phpMyAdmin from XAMPP and create a database named:

smart_attendance

Import the create_tables.sql file into the database.

2. Configure Database Credentials

Open:

config.php

Update the database credentials if required:

DB_HOST
DB_NAME
DB_USER
DB_PASS

3. Place the Project in XAMPP

Copy the project folder into:

C:\xampp\htdocs\smart-attendance-system

4. Start XAMPP

Open the XAMPP Control Panel and start:

Apache

MySQL

5. Run the Application

Open the following URL in your browser:

http://localhost/smart-attendance-system/

Attendance Workflow

Staff

Log in through the staff login page.

Create an attendance session.

Generate a unique alphanumeric attendance code.

Share the code with students.

The code remains valid for 5 minutes.

View attendance records through the staff dashboard and reports.

Student

Log in through the student login page.

Enter the attendance code provided by the staff.

The system validates whether the code is active and within its
5-minute validity period.

If valid, attendance is recorded in the database.

Project Structure

smart-attendance-system/
│
├── index.php
├── student-login.php
├── staff-login.php
├── student-dashboard.php
├── staff-dashboard.php
├── generate-code.php
├── mark_attendance.php
├── attendance-report.php
├── logout.php
│
├── config.php
├── functions.php
├── create_tables.sql
│
└── assets/
    ├── css/
    └── js/

Security & Validation

Role-based authentication for students and staff.

Server-side validation of attendance codes.

Time-based expiration of attendance codes.

Attendance records linked to authenticated student accounts.

Database-backed attendance management.

Project Purpose

This project was developed as a college final-year project to
demonstrate practical implementation of web development, authentication,
database management, session handling, and time-based attendance
validation.

Author

Developed as a college final-year project.
