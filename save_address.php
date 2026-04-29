<?php
session_start();
require 'vendor/autoload.php';

// Guard: must be logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: User_login.php");
    exit;
}

// Guard: must be POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: User_address.php");
    exit;
}

// Validate required fields
$required = ['user_id', 'region', 'province', 'city', 'brgy', 'street', 'house_no', 'label'];
foreach ($required as $field) {
    if (empty($_POST[$field])) {
        header("Location: User_address.php?error=missing");
        exit;
    }
}

// Use session user_id for security
$user_id = $_SESSION['user_id'];
$region   = trim($_POST['region']);
$province = trim($_POST['province']);
$city     = trim($_POST['city']);
$brgy     = trim($_POST['brgy']);
$street   = trim($_POST['street']);
$house_no = trim($_POST['house_no']);
$label    = trim($_POST['label']);

try {
    $conn = new MongoDB\Client("mongodb://localhost:27017");
    $db = $conn->paimon_db;
    $addressCollection = $db->user_address;

    $addressCollection->insertOne([
        "user_id"   => $user_id,
        "region"    => $region,
        "province"  => $province,
        "city"      => $city,
        "brgy"      => $brgy,
        "street"    => $street,
        "house_no"  => $house_no,
        "label"     => $label,
        "created_at" => new MongoDB\BSON\UTCDateTime()
    ]);

    // Address saved > proceed to ordering 
    header("Location: menu.php"); 
    exit;

} catch (Exception $e) {
    error_log("Save address error: " . $e->getMessage());
    header("Location: User_address.php?error=save");
    exit;
}
?>
