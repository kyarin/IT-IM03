<?php
session_start();
require 'vendor/autoload.php';

header('Content-Type: application/json');

if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

if (empty($_GET['address_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Address ID missing']);
    exit;
}

try {
    $conn = new MongoDB\Client("mongodb://localhost:27017");
    $db = $conn->paimon_db;
    
    $addressCollection = $db->user_address;
    $branchesCollection = $db->branches;

    $address = $addressCollection->findOne([
        '_id' => new MongoDB\BSON\ObjectId($_GET['address_id']),
        'user_id' => $_SESSION['user_id']
    ]);

    if (!$address || !isset($address['location'])) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid address']);
        exit;
    }

    $userLong = $address['location']['coordinates'][0];
    $userLat  = $address['location']['coordinates'][1];

    $pipeline = [
        [
            '$geoNear' => [
                'near' => ['type' => 'Point', 'coordinates' => [(float)$userLong, (float)$userLat]],
                'distanceField' => 'dist.calculated', 
                'maxDistance' => 50000, 
                'spherical' => true
            ]
        ],
        ['$limit' => 1]
    ];

    $nearestBranch = $branchesCollection->aggregate($pipeline)->toArray();

    if (empty($nearestBranch)) {
        echo json_encode([
            'status' => 'out_of_range', 
            'message' => "Paimon can't fly that far! The selected address is out of our 50km delivery zone."
        ]);
        exit;
    }

    $distanceInMeters = $nearestBranch[0]['dist']['calculated'];
    $distanceInKm = $distanceInMeters / 1000;
    
    // Example Fee: 50 base + 10 for every 10km
    $deliveryFee = 50 + (floor($distanceInKm / 10) * 10); 
    
    echo json_encode([
        'status' => 'success',
        'distance_km' => round($distanceInKm, 2),
        'fee' => $deliveryFee,
        'branch_name' => $nearestBranch[0]['name']
    ]);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
