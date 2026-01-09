<?php
session_start();
include('db.php');

if (!isset($_SESSION['username'])) exit();

$username = $_SESSION['username'];

/*
 Get ONE row per conversation partner
*/
$sql = "
SELECT 
    chat_user,
    MAX(created_at) AS last_time,
    SUM(unread) AS unread_count
FROM (
    SELECT 
        IF(LOWER(sender) = '$username', LOWER(receiver), LOWER(sender) AS chat_user,   
        created_at,
        CASE 
            WHEN receiver = '$username' AND is_read = 0 
            THEN 1 ELSE 0 
        END AS unread
    FROM chat_messages
    WHERE sender = '$username' OR receiver = '$username'
) AS conversations
GROUP BY chat_user
ORDER BY last_time DESC
";

$res = mysqli_query($conn, $sql);

while ($row = mysqli_fetch_assoc($res)) {

    $user = $row['chat_user'];
    $unread = (int)$row['unread_count'];

    echo "
    <a href='text.php?user=$user' class='contact'>
        <div class='avatar'>👤</div>
        <div class='contact-info'>
            <div class='name'>".ucfirst($user)."</div>";

    if ($unread > 0) {
        echo "<span class='unread'>$unread</span>";
    }

    echo "
        </div>
    </a>";
}
?>