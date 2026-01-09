<?php
session_start();
include('db.php');

$sender   = strtolower($_POST['sender']);
$receiver = strtolower($_POST['receiver']);
$message  = trim($_POST['message']);
$imagePath = null;

if (!empty($_FILES['image']['name'])) {

    $uploadDir = "uploads/";
    $fileName = time() . "_" . basename($_FILES['image']['name']);
    $targetPath = $uploadDir . $fileName;

    $fileType = strtolower(pathinfo($targetPath, PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'gif'];

    if (in_array($fileType, $allowed)) {
        move_uploaded_file($_FILES['image']['tmp_name'], $targetPath);
        $imagePath = $targetPath;
    }
}

$stmt = $conn->prepare(
    "INSERT INTO chat_messages (sender, receiver, message, image) 
     VALUES (?, ?, ?, ?)"
);

$stmt->bind_param("ssss", $sender, $receiver, $message, $imagePath);
$stmt->execute();
$stmt->close();
$conn->close();
