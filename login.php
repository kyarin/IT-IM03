<?php
session_start();
require 'vendor/autoload.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: User_login.php");
    exit;
}

if (empty($_POST['email']) || empty($_POST['password'])) {
    header("Location: User_login.php?error=missing");
    exit;
}

try {
    $conn = new MongoDB\Client("mongodb://localhost:27017");
    $db = $conn->paimon_db;
    $collection = $db->user_reg;

    $email = $_POST['email'];
    $password = $_POST['password'];

    $user = $collection->findOne(["email" => $email]);

    if (!$user) {
        header("Location: User_login.php?error=invalid");
        exit;
    }

    if (!password_verify($password, $user['password'])) {
        header("Location: User_login.php?error=invalid");
        exit;
    }

    $_SESSION['user_id'] = (string)$user['_id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['logged_in'] = true;

    header("Location: User_address.php");
    exit;

} catch (Exception $e) {
    error_log("Login error: " . $e->getMessage());
    header("Location: User_login.php?error=system");
    exit;
}
?>
