<?php
session_start();
include('db.php');

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];
$selectedUser = isset($_GET['user']) ? mysqli_real_escape_string($conn, $_GET['user']) : '';
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
            $sql = "SELECT username FROM users WHERE username != '$username'";
            $res = mysqli_query($conn, $sql);

            while ($row = mysqli_fetch_assoc($res)) {
                $user = $row['username'];
                $active = ($user == $selectedUser) ? 'active' : '';
                echo "
                <a href='text.php?user=$user' class='contact $active'>
                    <div class='avatar'>👤</div>
                    <div class='contact-info'>
                        <div class='name'>".ucfirst($user)."</div>
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
                <span>😊</span>
                <span>📎</span>

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

$('#send').click(function() {
    $.post("submit.php", {
        sender: $('#sender').val(),
        receiver: $('#receiver').val(),
        message: $('#message').val()
    }, function() {
        $('#message').val('');
        fetchMessages();
    });
});

<?php if ($selectedUser): ?>
setInterval(fetchMessages, 2000);
fetchMessages();
<?php endif; ?>
</script>

</body>
</html>