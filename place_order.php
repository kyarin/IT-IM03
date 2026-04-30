<?php
session_start();
require 'vendor/autoload.php';

// Guard: must be logged in
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    header("Location: User_login.php");
    exit;
}

// Guard: must be POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: menu.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$postedItems = $_POST['items'] ?? [];

$items = [];
$grandTotal = 0;

// Filter out items with 0 quantity and calculate totals
foreach ($postedItems as $itemData) {
    $qty = (int)($itemData['quantity'] ?? 0);
    if ($qty > 0) {
        $price = (int)($itemData['price'] ?? 0);
        $subtotal = $price * $qty;
        
        $items[] = [
            'item'     => $itemData['name'],
            'rarity'   => (int)($itemData['rarity'] ?? 1),
            'price'    => $price,
            'quantity' => $qty,
            'subtotal' => $subtotal,
        ];
        
        $grandTotal += $subtotal;
    }
}

// If no items were selected, redirect back to menu
if (empty($items)) {
    header("Location: menu.php");
    exit;
}

try {
    $conn   = new MongoDB\Client("mongodb://localhost:27017");
    $db     = $conn->paimon_db;
    $orders = $db->user_orders;
    $addressCollection = $db->user_address;
    $branchesCollection = $db->branches;

    // Unit 8, Operator 5: $exists Validation - Ensure user has a GeoJSON location
    $hasAddress = $addressCollection->findOne([
        'user_id' => $user_id,
        'location' => ['$exists' => true]
    ]);

    if (!$hasAddress) {
        header("Location: User_address.php?error=missing_location");
        exit;
    }

    $addressString = "N/A";
    $nearestBranchName = "Unknown Branch";

    $house_no = isset($hasAddress['house_no']) ? $hasAddress['house_no'] . ', ' : '';
    $addressString = "{$house_no}{$hasAddress['street']}, {$hasAddress['brgy']}, {$hasAddress['city']}, {$hasAddress['province']}, {$hasAddress['region']}";

    // Unit 7: $near Geospatial Query
    $userLong = $hasAddress['location']['coordinates'][0];
    $userLat  = $hasAddress['location']['coordinates'][1];

    $nearestBranch = $branchesCollection->findOne([
        'location' => [
            '$near' => [
                '$geometry' => [
                    'type' => 'Point',
                    'coordinates' => [$userLong, $userLat]
                ],
                // Let's use 50km (50000 meters) so the test branch matches easily
                '$maxDistance' => 50000 
            ]
        ]
    ]);

    if ($nearestBranch) {
        $nearestBranchName = $nearestBranch['name'];
    } else {
        $nearestBranchName = "Default Branch (Out of Range)";
    }

    $result = $orders->insertOne([
        'user_id'     => $user_id,
        'items'       => $items,
        'grand_total' => $grandTotal,
        'delivery_address' => $addressString,
        'branch'      => $nearestBranchName,
        'status'      => 'delivered', // Hardcoding to delivered so the Unit 5, Variation 5 projection works immediately
        'ordered_at'  => new MongoDB\BSON\UTCDateTime(),
    ]);

    // Store receipt snapshot in session for order_success.php to display (Legacy, Unit 5 Variation 2 will fetch from DB)
    $_SESSION['last_order'] = [
        'order_id'   => (string)$result->getInsertedId(),
        'items'      => $items,
        'grand_total'=> $grandTotal,
        'delivery_address' => $addressString,
        'branch'     => $nearestBranchName,
        'ordered_at' => date('F j, Y \a\t g:i A'),
    ];

    // Clear cart (if they happen to have an old session cart, clean it out)
    unset($_SESSION['cart']);

    // Redirect to success page
    header("Location: order_success.php");
    exit;

} catch (Exception $e) {
    error_log("Place order error: " . $e->getMessage());
    echo "Database error occurred while placing order. Please try again later.";
    exit;
}
