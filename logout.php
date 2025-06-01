<?php
session_start();

if (isset($_SESSION['user_id'])) {
    session_unset();
    session_destroy();
    header("Location: user_login.php");
    exit();
} else {
    // Optional: redirect if accessed without user session
    header("Location: user_login.php");
    exit();
}
?>
