<?php
$host = "localhost";
$user = "root";
$password = "";
$database = "gallery"; 
$port = 3307;

$conn = new mysqli($host, $user, $password, $database, $port);


if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8");

$conn->query("CREATE TABLE IF NOT EXISTS events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255),
    folder_name VARCHAR(255),
    thumbnail VARCHAR(255),
    financial_year VARCHAR(15),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");
$conn->query("CREATE TABLE IF NOT EXISTS employees (
    employee_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(100),
    email VARCHAR(100),
    department VARCHAR(100),
    designation VARCHAR(100),
    password VARCHAR(100),
    role ENUM('user', 'admin') NOT NULL DEFAULT 'user'
)");
?>