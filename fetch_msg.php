<?php
session_start();
include('db.php');

if (!isset($_SESSION['username'])) {
    exit("You are not logged in");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $currentUser = $_SESSION['username'];
    $sender = $_POST['sender'];
    $receiver = $_POST['receiver'];

    $sql = "SELECT sender, message FROM chat_messages 
            WHERE (sender='$sender' AND receiver='$receiver') 
               OR (sender='$receiver' AND receiver='$sender') 
            ORDER BY created_at ASC";

    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {

            // Check who sent the message
            $class = ($row['sender'] === $currentUser) ? 'sent' : 'received';

            echo '
                <div class="bubble ' . $class . '">
                    ' . htmlspecialchars($row['message']) . '
                </div>
            ';
        }
    }
}
?>
