<?php
session_start();
include('db.php');

$errors = [];

if (isset($_SESSION['username'])) {
    header("Location: text.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Validate inputs
    if (empty($username) || empty($email) || empty($password)) {
        $errors[] = "All fields are required";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }

    if (empty($errors)) {
        // Check if username or email already exists
        $stmt = $conn->prepare("SELECT * FROM users WHERE username=? OR email=? LIMIT 1");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $errors[] = "Username or email already exists";
        } else {
            // Insert new user
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $username, $email, $hashedPassword);

            if ($stmt->execute()) {
                // Redirect to login page with success message
                header("Location: login.php?registered=1");
                exit();
            } else {
                $errors[] = "Registration failed. Please try again.";
            }
        }

        $stmt->close();
    }
}

$conn->close();
?>

<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Sign Up - Chat System</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="container">
    <h2>Sign Up</h2>
    <?php if ($errors): ?>
      <div class="errors">
        <?php foreach($errors as $e) echo "<p>$e</p>"; ?>
      </div>
    <?php endif; ?>
    <form method="post" action="">
      <input name="username" placeholder="Username" required>
      <input name="email" type="email" placeholder="Email" required>
      <input name="password" type="password" placeholder="Password" required>
      <button type="submit">Create Account</button>
    </form>
    <p>Already have an account? <a href="login.php">Login</a></p>
  </div>
</body>
</html>