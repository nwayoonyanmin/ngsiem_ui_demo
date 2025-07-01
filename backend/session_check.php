<?php
session_start();

// If not logged in, redirect to login page
if (!isset($_SESSION['loggedin'])) {
    header("Location: /login.php");
    exit;
}
?>
