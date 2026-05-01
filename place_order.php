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
$subtotal = 0;

if (isset($_POST['items_json'])) {
    // If coming from Checkout confirm, items are passed as JSON
    $items = json_decode($_POST['items_json'], true);
    foreach ($items as $item) {
        $subtotal += $item['subtotal'];
    }
} else {
    // If coming from menu.php
    foreach ($postedItems as $itemData) {
        $qty = (int)($itemData['quantity'] ?? 0);
        if ($qty > 0) {
            $price = (int)($itemData['price'] ?? 0);
            $itemSubtotal = $price * $qty;
            
            $items[] = [
                'item'     => $itemData['name'],
                'rarity'   => (int)($itemData['rarity'] ?? 1),
                'price'    => $price,
                'quantity' => $qty,
                'subtotal' => $itemSubtotal,
            ];
            $subtotal += $itemSubtotal;
        }
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

    // Fetch user addresses
    $userAddresses = $addressCollection->find(['user_id' => $user_id])->toArray();

    // ---------------------------------------------------------
    // FLOW 2: CONFIRM ORDER
    // ---------------------------------------------------------
    if (isset($_POST['confirm']) && $_POST['confirm'] == '1') {
        $address_id = $_POST['address_id'] ?? '';
        
        $selectedAddress = $addressCollection->findOne([
            '_id' => new MongoDB\BSON\ObjectId($address_id),
            'user_id' => $user_id
        ]);

        if (!$selectedAddress) {
            die("Invalid address selected.");
        }

        // Re-calculate fee via $geoNear to ensure validity server-side
        $userLong = $selectedAddress['location']['coordinates'][0];
        $userLat  = $selectedAddress['location']['coordinates'][1];

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
            die("Error: Address is out of the 50km delivery range.");
        }

        $distanceInMeters = $nearestBranch[0]['dist']['calculated'];
        $distanceInKm = $distanceInMeters / 1000;
        $deliveryFee = 50 + (floor($distanceInKm / 10) * 10); 
        $grandTotal = $subtotal + $deliveryFee;

        $house_no = isset($selectedAddress['house_no']) ? $selectedAddress['house_no'] . ', ' : '';
        $addressString = "{$house_no}{$selectedAddress['street']}, {$selectedAddress['brgy']}, {$selectedAddress['city']}, {$selectedAddress['province']}, {$selectedAddress['region']}";
        $nearestBranchName = $nearestBranch[0]['name'];

        $result = $orders->insertOne([
            'user_id'     => $user_id,
            'items'       => $items,
            'subtotal'    => $subtotal,
            'delivery_fee'=> $deliveryFee,
            'grand_total' => $grandTotal,
            'delivery_address' => $addressString,
            'branch'      => $nearestBranchName,
            'status'      => 'delivered',
            'ordered_at'  => new MongoDB\BSON\UTCDateTime(),
        ]);

        $_SESSION['last_order'] = [
            'order_id'   => (string)$result->getInsertedId(),
            'items'      => $items,
            'grand_total'=> $grandTotal,
            'delivery_address' => $addressString,
            'branch'     => $nearestBranchName,
            'ordered_at' => date('F j, Y \a\t g:i A'),
        ];

        unset($_SESSION['cart']);
        header("Location: order_success.php");
        exit;
    }

    // ---------------------------------------------------------
    // FLOW 1: DISPLAY CHECKOUT UI (HTML Below)
    // ---------------------------------------------------------

} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Paimon's Kitchen</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { 
            font-family: 'Inter', sans-serif; 
            background: url('assets/genshin_bg.png') no-repeat center center fixed;
            background-size: cover;
            color: #1e293b; 
            padding: 40px 20px; 
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }
        .checkout-container { 
            width: 100%;
            max-width: 600px; 
            background: rgba(253, 251, 247, 0.96); 
            backdrop-filter: blur(10px);
            border-radius: 12px; 
            padding: 30px; 
            box-shadow: 0 10px 25px -5px rgba(0,0,0,0.5); 
            border: 2px solid #d4af37; 
        }
        h1 { color: #1e293b; text-align: center; margin-bottom: 20px; text-transform: uppercase; border-bottom: 2px solid #d4af37; padding-bottom: 10px; }
        .section { margin-bottom: 30px; border-bottom: 1px solid #e2e8f0; padding-bottom: 20px; }
        h3 { font-size: 16px; color: #475569; text-transform: uppercase; margin-bottom: 15px; }
        .item-row { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px dashed #cbd5e1; font-weight: 500; }
        .item-row:last-child { border-bottom: none; }
        .totals { text-align: right; margin-top: 15px; font-size: 16px; color: #334155; }
        .grand-total { font-size: 22px; font-weight: 800; color: #b8860b; margin-top: 10px; }
        select { 
            width: 100%; padding: 12px; border-radius: 8px; border: 1px solid #cbd5e1; font-size: 14px; margin-top: 10px;
            background-color: #ffffff; color: #1e293b; font-family: inherit; outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        select:focus { border-color: #d4af37; box-shadow: 0 0 0 3px rgba(212, 175, 55, 0.2); }
        .btn { 
            display: inline-block; width: 100%; padding: 15px; background: #1e293b; color: #d4af37; text-align: center; 
            font-size: 16px; font-weight: bold; border: 2px solid #d4af37; border-radius: 8px; cursor: pointer; 
            margin-top: 20px; transition: background 0.3s, transform 0.1s; text-transform: uppercase; letter-spacing: 1px;
        }
        .btn:hover:not(:disabled) { background: #334155; }
        .btn:active:not(:disabled) { transform: scale(0.98); }
        .btn:disabled { background: #94a3b8; border-color: #cbd5e1; color: #f1f5f9; cursor: not-allowed; }
        .alert { padding: 12px; border-radius: 8px; margin-top: 15px; display: none; font-size: 14px; text-align: center; font-weight: 500; }
        .alert-error { background: #fef2f2; color: #dc2626; border: 1px solid #fca5a5; }
        .alert-success { background: #f0fdf4; color: #16a34a; border: 1px solid #bbf7d0; }
        .add-address-link { display: inline-block; margin-top: 12px; color: #b8860b; text-decoration: none; font-size: 13px; font-weight: 600; }
        .add-address-link:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="checkout-container">
        <h1>Checkout</h1>

        <div class="section">
            <h3>Order Summary</h3>
            <?php foreach ($items as $item): ?>
                <div class="item-row">
                    <span><?= htmlspecialchars($item['item']) ?> x<?= $item['quantity'] ?></span>
                    <span>₱<?= number_format($item['subtotal']) ?></span>
                </div>
            <?php endforeach; ?>
            
            <div class="totals">
                Subtotal: <strong>₱<?= number_format($subtotal) ?></strong>
            </div>
        </div>

        <form action="place_order.php" method="POST" id="checkoutForm">
            <input type="hidden" name="confirm" value="1">
            <input type="hidden" name="items_json" value='<?= htmlspecialchars(json_encode($items), ENT_QUOTES, 'UTF-8') ?>'>

            <div class="section" style="border-bottom: none; margin-bottom: 0;">
                <h3>Delivery Address</h3>
                <?php if (empty($userAddresses)): ?>
                    <p style="color: #dc2626; font-weight: 600; font-size: 14px; margin-bottom: 15px;">You don't have any saved addresses!</p>
                    <a href="User_address.php?add_new=1" class="btn" style="text-decoration: none;">Set up Address</a>
                <?php else: ?>
                    <select name="address_id" id="addressSelect" required>
                        <option value="" disabled selected>Select an address...</option>
                        <?php foreach ($userAddresses as $addr): ?>
                            <?php 
                                $label = htmlspecialchars($addr['label'] ?? 'Address');
                                $fullAddr = htmlspecialchars(($addr['house_no'] ?? '') . ' ' . $addr['street'] . ', ' . $addr['city']);
                            ?>
                            <option value="<?= (string)$addr['_id'] ?>">
                                <?= $label ?> - <?= $fullAddr ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <a href="User_address.php?add_new=1" class="add-address-link">+ Add another address</a>
                    
                    <div id="statusAlert" class="alert"></div>
                <?php endif; ?>
            </div>

            <div class="totals" id="totalsSection" style="display: none; background: #f8fafc; padding: 15px; border-radius: 8px; border: 1px solid #e2e8f0; text-align: left;">
                <p style="margin-bottom: 8px;">Delivery Fee: <strong id="deliveryFeeDisplay" style="float: right;">₱0</strong></p>
                <p style="margin-bottom: 8px; font-size: 13px; color: #64748b;">Branch: <span id="branchNameDisplay"></span> (<span id="distanceDisplay"></span> km)</p>
                <div style="border-top: 1px dashed #cbd5e1; margin: 10px 0;"></div>
                <p class="grand-total" style="display: flex; justify-content: space-between; align-items: center; margin: 0;">
                    Grand Total: <span>₱<span id="grandTotalDisplay"><?= number_format($subtotal) ?></span></span>
                </p>
            </div>

            <?php if (!empty($userAddresses)): ?>
                <button type="submit" class="btn" id="confirmBtn" disabled>Confirm Order</button>
            <?php endif; ?>
            
            <div style="text-align: center; margin-top: 15px;">
                <a href="menu.php" style="color: #64748b; font-size: 13px; text-decoration: none;">Cancel and return to menu</a>
            </div>
        </form>
    </div>

    <script>
        const subtotal = <?= $subtotal ?>;
        const addressSelect = document.getElementById('addressSelect');
        const statusAlert = document.getElementById('statusAlert');
        const confirmBtn = document.getElementById('confirmBtn');
        const totalsSection = document.getElementById('totalsSection');
        
        const deliveryFeeDisplay = document.getElementById('deliveryFeeDisplay');
        const branchNameDisplay = document.getElementById('branchNameDisplay');
        const distanceDisplay = document.getElementById('distanceDisplay');
        const grandTotalDisplay = document.getElementById('grandTotalDisplay');

        if (addressSelect) {
            addressSelect.addEventListener('change', async function() {
                const addressId = this.value;
                if (!addressId) return;

                // Reset UI
                statusAlert.style.display = 'none';
                confirmBtn.disabled = true;
                totalsSection.style.display = 'none';
                
                statusAlert.className = 'alert';
                statusAlert.textContent = 'Calculating delivery fee...';
                statusAlert.style.display = 'block';
                statusAlert.style.background = '#f8fafc';
                statusAlert.style.color = '#475569';
                statusAlert.style.borderColor = '#cbd5e1';

                try {
                    const response = await fetch(`api_calculate_fee.php?address_id=${addressId}`);
                    const data = await response.json();

                    if (data.status === 'success') {
                        statusAlert.style.display = 'none';
                        
                        deliveryFeeDisplay.textContent = '₱' + data.fee;
                        branchNameDisplay.textContent = data.branch_name;
                        distanceDisplay.textContent = data.distance_km;
                        
                        const grandTotal = subtotal + data.fee;
                        grandTotalDisplay.textContent = grandTotal.toLocaleString();
                        
                        totalsSection.style.display = 'block';
                        confirmBtn.disabled = false;
                    } else if (data.status === 'out_of_range') {
                        statusAlert.className = 'alert alert-error';
                        statusAlert.textContent = data.message;
                    } else {
                        statusAlert.className = 'alert alert-error';
                        statusAlert.textContent = data.message || 'An error occurred';
                    }
                } catch (err) {
                    statusAlert.className = 'alert alert-error';
                    statusAlert.textContent = 'Failed to calculate fee. Please try again.';
                }
            });
        }
    </script>
</body>
</html>
