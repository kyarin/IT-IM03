<?php
require 'vendor/autoload.php';

$conn = new MongoDB\Client("mongodb://localhost:27017");

$db = $conn->paimon_db;
$collection = $db->user_reg;

// get form data
$name = $_POST['name'];
$email = $_POST['email'];
$age = (int)$_POST['age'];
$contact = $_POST['contact'];
$password = $_POST['password'];

// hash password
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// insert into MongoDB
$insertOneResult = $collection->insertOne([
    "name" => $name,
    "email" => $email,
    "password" => $hashedPassword,
    "age" => $age,
    "contact" => $contact,
    "status" => "active",
    "date_registered" => new MongoDB\BSON\UTCDateTime()
]);

// redirect to success page
header("Location: success.php");
exit;
?>
