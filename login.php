<?php 
session_start();
include('db.php');

// Initialize variables to avoid "undefined variable" warnings
$registered = false; // change to true if coming from registration
$error = "";          // single error message

if (isset($_SESSION['username'])) {
    header("Location: text.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = $_POST['username'];
    $password = $_POST['password'];

    // Use prepared statements to prevent SQL injection
    $stmt = $conn->prepare("SELECT * FROM users WHERE username=? OR email=? LIMIT 1");
    $stmt->bind_param("ss", $username, $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();

        // Verify hashed password
        if (password_verify($password, $user['password'])) {
            $_SESSION['username'] = strtolower($user['username']);        

            header("Location: text.php");
            exit();
        } else {
            $error = "Invalid username or password";
        }
    } else {
        $error = "User not found";
    }

    $stmt->close();
}

$conn->close();
?>

<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Login - Chat System</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="container">
    <h2>Login</h2>
    <?php if ($registered) echo "<p class='success'>Registration successful. Please log in.</p>"; ?>
    <?php if ($error) echo "<p class='errors'>$error</p>"; ?>
    <form method="post" action="">
      <input name="username" placeholder="Username" required>
      <input name="password" type="password" placeholder="Password" required>
      <button type="submit">Login</button>
    </form>
    <p>No account? <a href="register.php">Sign Up</a></p>
  </div>
</body>
</html>
