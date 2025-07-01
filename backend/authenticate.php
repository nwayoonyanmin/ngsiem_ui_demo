<?php
session_start();

// Sanitize inputs
$username = htmlspecialchars($_POST['username'] ?? '');
$password = htmlspecialchars($_POST['password'] ?? '');

// TODO: Replace these with DB check later
$valid_user = 'htut.ah';
$valid_pass = 'dash9436';

if ($username === $valid_user && $password === $valid_pass) {
    $_SESSION['loggedin'] = true;
    $_SESSION['username'] = $username;
    header('Location: ../index.php');
    exit;
} else {
    // Optional: log failed attempt
    error_log("Failed login for user: $username");

    echo "<script>
        alert('Invalid username or password.');
        window.location.href = '../login.php';
    </script>";
    exit;
}
?>
