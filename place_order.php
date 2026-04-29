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

    $address = $addressCollection->findOne(['user_id' => $user_id]);
    $addressString = "N/A";
    if ($address) {
        $house_no = isset($address['house_no']) ? $address['house_no'] . ', ' : '';
        $addressString = "{$house_no}{$address['street']}, {$address['brgy']}, {$address['city']}, {$address['province']}, {$address['region']}";
    }

    $result = $orders->insertOne([
        'user_id'     => $user_id,
        'items'       => $items,
        'grand_total' => $grandTotal,
        'delivery_address' => $addressString,
        'ordered_at'  => new MongoDB\BSON\UTCDateTime(),
    ]);

    // Store receipt snapshot in session for order_success.php to display
    $_SESSION['last_order'] = [
        'order_id'   => (string)$result->getInsertedId(),
        'items'      => $items,
        'grand_total'=> $grandTotal,
        'delivery_address' => $addressString,
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
