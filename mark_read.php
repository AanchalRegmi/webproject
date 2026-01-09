<?php
session_start();
include('db.php');

if (!isset($_SESSION['username'])) exit();

$receiver = $_SESSION['username'];
$sender   = $_POST['sender'];

$sql = "
UPDATE chat_messages 
SET is_read = 1 
WHERE sender = '$sender' 
AND receiver = '$receiver'
AND is_read = 0
";

mysqli_query($conn, $sql);