<?php
session_start();
include('db.php');

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];

/* selected user */
$selectedUser = '';
if (isset($_GET['user'])) {
    $selectedUser = $conn->real_escape_string($_GET['user']);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Chat System</title>
    <link rel="stylesheet" href="style_text.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
</head>

<body>

<div class="app">

    <!-- TOP BAR -->
    <div class="topbar">
        <div>Welcome, <?php echo ucfirst($username); ?></div>
        <a href="logout.php" class="logout">Logout</a>
    </div>

    <div class="layout">

        <!-- LEFT SIDEBAR -->
        <div class="sidebar">
            <div class="sidebar-header">Messages</div>

            <?php
            /* users with chat history */
            $sql = "
                SELECT 
                    IF(sender = '$username', receiver, sender) AS chat_user,
                    MAX(created_at) AS last_time,
                    SUM(CASE WHEN receiver = '$username' AND is_read = 0 THEN 1 ELSE 0 END) AS unread_count
                FROM chat_messages
                WHERE sender = '$username' OR receiver = '$username'
                GROUP BY chat_user
                ORDER BY last_time DESC
            ";

            $res = $conn->query($sql);
            if (!$res) {
                die("Sidebar query error: " . $conn->error);
            }

            $chattedUsers = [];

            while ($row = $res->fetch_assoc()) {
                $user = $row['chat_user'];
                $unread = $row['unread_count'];
                $chattedUsers[] = $user;
                $active = ($user == $selectedUser) ? 'active' : '';

                echo "
                <a href='text.php?user=$user' class='contact $active'>
                    <div class='avatar'>👤</div>
                    <div class='contact-info'>
                        <div class='name'>" . ucfirst($user) . "</div>
                    </div>
                    " . ($unread > 0 ? "<div class='unread'>$unread</div>" : "") . "
                </a>";
            }

            /* users with no chat yet */
            $excludeUsers = "'" . implode("','", $chattedUsers) . "'";
            $excludeUsers = $excludeUsers ?: "''";

            $sql2 = "
                SELECT username FROM users
                WHERE username != '$username'
                AND username NOT IN ($excludeUsers)
            ";

            $res2 = $conn->query($sql2);
            if (!$res2) {
                die("User list query error: " . $conn->error);
            }

            while ($row = $res2->fetch_assoc()) {
                $user = $row['username'];
                $active = ($user == $selectedUser) ? 'active' : '';

                echo "
                <a href='text.php?user=$user' class='contact $active'>
                    <div class='avatar'>👤</div>
                    <div class='contact-info'>
                        <div class='name'>" . ucfirst($user) . "</div>
                    </div>
                </a>";
            }
            ?>
        </div>

        <!-- RIGHT CHAT AREA -->
        <div class="chat">
            <?php if ($selectedUser): ?>

                <div class="chat-header">
                    <span><?php echo ucfirst($selectedUser); ?></span>
                </div>

                <div class="chat-body" id="chat-body"></div>

                <div class="chat-input">
                    <label for="image" class="camera-btn">🖼️</label>
                    <input type="file" id="image" accept="image/*" hidden>

                    <input type="text" id="message" placeholder="Type a message...">
                    <button id="send">➤</button>

                    <input type="hidden" id="sender" value="<?php echo $username; ?>">
                    <input type="hidden" id="receiver" value="<?php echo $selectedUser; ?>">
                </div>

            <?php else: ?>
                <div class="no-chat">Select a user to start chatting</div>
            <?php endif; ?>
        </div>

    </div>
</div>

<script>
function fetchMessages() {
    $.post("fetch_msg.php", {
        sender: $('#sender').val(),
        receiver: $('#receiver').val()
    }, function(data) {
        $('#chat-body').html(data);
        $('#chat-body').scrollTop($('#chat-body')[0].scrollHeight);
    });
}

function markAsRead() {
    $.post("mark_read.php", {
        sender: $('#receiver').val()
    });
}

$('#send').on('click', function () {

    let formData = new FormData();
    formData.append('sender', $('#sender').val());
    formData.append('receiver', $('#receiver').val());
    formData.append('message', $('#message').val());

    let imageFile = $('#image')[0].files[0];
    if (imageFile) {
        formData.append('image', imageFile);
    }

    $.ajax({
        url: 'submit.php',
        type: 'POST',
        data: formData,
        contentType: false,
        processData: false,
        success: function () {
            $('#message').val('');
            $('#image').val('');
            fetchMessages();
        }
    });
});

/* auto refresh */
setInterval(fetchMessages, 2000);

if ($('#receiver').val()) {
    fetchMessages();
    markAsRead();
}
</script>

</body>
</html>

