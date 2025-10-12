<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "se_project";

// Create connection
$conn = new mysqli($servername, $username, $password);

// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// Create database
$sql = "CREATE DATABASE IF NOT EXISTS $dbname";
if ($conn->query($sql) === TRUE) {
  // echo "Database created successfully";
} else {
  echo "Error creating database: " . $conn->error;
}

$conn->select_db($dbname);

// sql to create table
$sql = "CREATE TABLE IF NOT EXISTS users (
id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
firstname VARCHAR(30) NOT NULL,
lastname VARCHAR(30) NOT NULL,
email VARCHAR(50),
password VARCHAR(255) NOT NULL,
reg_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === TRUE) {
  // echo "Table Users created successfully";
} else {
  echo "Error creating table: " . $conn->error;
}

$sql = "CREATE TABLE IF NOT EXISTS ngos (
id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
name VARCHAR(50) NOT NULL,
email VARCHAR(50),
password VARCHAR(255) NOT NULL,
reg_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === TRUE) {
  // echo "Table NGOs created successfully";
} else {
  echo "Error creating table: " . $conn->error;
}

$sql = "CREATE TABLE IF NOT EXISTS donations (
id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
user_id INT(6) UNSIGNED,
ngo_id INT(6) UNSIGNED,
amount DECIMAL(10, 2) NOT NULL,
donation_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
FOREIGN KEY (user_id) REFERENCES users(id),
FOREIGN KEY (ngo_id) REFERENCES ngos(id)
)";

if ($conn->query($sql) === TRUE) {
  // echo "Table Donations created successfully";
} else {
  echo "Error creating table: " . $conn->error;
}

$sql = "CREATE TABLE IF NOT EXISTS campaigns (
id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
ngo_id INT(6) UNSIGNED,
title VARCHAR(255) NOT NULL,
description TEXT,
type_of_donation VARCHAR(255) NOT NULL,
image VARCHAR(255) NULL,
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
FOREIGN KEY (ngo_id) REFERENCES ngos(id)
)";

if ($conn->query($sql) === TRUE) {
  // echo "Table Campaigns created successfully";
} else {
  echo "Error creating table: " . $conn->error;
}

?>
