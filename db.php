<?php


$servername = 'localhost';
$username = 'root';
$password = ''; // XAMPP default (if you set a password, put it here)
$dbname = 'text';

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>