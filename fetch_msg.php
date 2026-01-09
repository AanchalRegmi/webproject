<?php
session_start();
include('db.php');

if (!isset($_SESSION['username'])) {
    exit("You are not logged in");
}

$currentUser = strtolower($_SESSION['username']);
$receiver = strtolower($_POST['receiver']);

// mark messages as read
$update = "
UPDATE chat_messages
SET is_read = 1
WHERE sender = '$receiver'
AND receiver = '$currentUser'
";
$conn->query($update);

// fetch messages
$sql = "
SELECT sender, message, image 

FROM chat_messages
WHERE 
   (sender = '$currentUser' AND receiver = '$receiver')
OR (sender = '$receiver' AND receiver = '$currentUser')
ORDER BY created_at ASC
";

$result = $conn->query($sql);

while ($row = $result->fetch_assoc()) {
    $class = ($row['sender'] === $currentUser) ? 'sent' : 'received';
    echo '<div class="bubble ' . $class . '">';

    if (!empty($row['message'])) {
        echo htmlspecialchars($row['message']) . "<br>";
    }
    
    if (!empty($row['image'])) {
        echo "<img src='{$row['image']}' class='chat-image'>";
    }
    
    echo '</div>';
    
}

