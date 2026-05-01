<?php
session_start();
require 'vendor/autoload.php';

// Check if user is logged in (could add an isAdmin check later)
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    header("Location: User_login.php");
    exit;
}

try {
    $conn = new MongoDB\Client("mongodb://localhost:27017");
    $db = $conn->paimon_db;
    $menuCollection = $db->menu;
    $ordersCollection = $db->user_orders;

    // Handle form submission to add a new food item
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_item') {
        $name = trim($_POST['name']);
        $rarity = (int)$_POST['rarity'];
        $price = (float)$_POST['price'];
        $desc = trim($_POST['desc']);
        $category = trim($_POST['category']);

        $menuCollection->insertOne([
            'name' => $name,
            'rarity' => $rarity,
            'price' => $price,
            'desc' => $desc,
            'category' => $category
        ]);
        
        $success_message = "Food item '$name' added successfully!";
    }

    // Handle form submission to deactivate a food item
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'deactivate_item') {
        $item_id = $_POST['item_id'];
        
        $menuCollection->updateOne(
            ['_id' => new MongoDB\BSON\ObjectId($item_id)],
            ['$set' => ['status' => 'inactive']]
        );
        
        $success_message = "Item successfully deactivated and removed from the public menu.";
    }

    // Unit 5, Variation 3: Projection 1 (Fetch ONLY item_name for active items)
    $itemNamesCursor = $menuCollection->find(
        ['status' => ['$ne' => 'inactive']], 
        ['projection' => ['name' => 1, '_id' => 1]]
    );
    $itemNames = iterator_to_array($itemNamesCursor);

    // Unit 5, Variation 5: Parameter + Projection 1 (Find total_price of delivered orders)
    $totalsCursor = $ordersCollection->find(
        ['status' => 'delivered'], 
        ['projection' => ['grand_total' => 1, '_id' => 0]]
    );
    
    $totalRevenue = 0;
    foreach ($totalsCursor as $order) {
        if (isset($order['grand_total'])) {
            $totalRevenue += $order['grand_total'];
        }
    }

} catch (Exception $e) {
    die("Database Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paimon's Kitchen - Admin Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', sans-serif;
            background-color: rgba(244, 242, 236);
            color: #1e293b;
            padding: 40px 24px;
            max-width: 1000px;
            margin: 0 auto;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 2px solid #d4af37;
        }
        h1 { font-size: 28px; color: #b8860b; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; }
        .back-btn { text-decoration: none; color: #475569; font-weight: 600; border: 2px solid #cbd5e1; padding: 10px 16px; border-radius: 8px; }
        
        .dashboard-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
        }

        .card {
            background: #fff;
            padding: 24px;
            border-radius: 16px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            border: 1px solid #e2e8f0;
        }

        .card h2 { font-size: 18px; font-weight: 700; color: #1e293b; margin-bottom: 16px; display: flex; align-items: center; gap: 8px; }
        .card h2 span { color: #d4af37; }

        /* Form */
        .form-group { margin-bottom: 16px; }
        label { display: block; font-size: 12px; font-weight: 600; color: #64748b; margin-bottom: 6px; text-transform: uppercase; }
        input, select, textarea { width: 100%; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-family: inherit; font-size: 14px; }
        input:focus, select:focus, textarea:focus { outline: none; border-color: #d4af37; box-shadow: 0 0 0 3px rgba(212, 175, 55, 0.2); }
        button { background: #1e293b; color: #d4af37; border: none; padding: 12px; width: 100%; border-radius: 8px; font-weight: 700; cursor: pointer; transition: 0.2s; }
        button:hover { background: #0f172a; }

        .success-msg { background: #dcfce7; color: #166534; padding: 12px; border-radius: 8px; margin-bottom: 16px; font-size: 14px; font-weight: 500; text-align: center; }

        /* Metrics */
        .metric-box { background: #f8fafc; border: 1px solid #e2e8f0; padding: 20px; border-radius: 12px; text-align: center; }
        .metric-title { font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px; }
        .metric-value { font-size: 36px; font-weight: 800; color: #10b981; }

        /* Dropdown Showcase */
        .dropdown-showcase { background: #f8fafc; border: 1px dashed #cbd5e1; padding: 20px; border-radius: 12px; }
    </style>
</head>
<body>

    <div class="header">
        <h1>
            <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="vertical-align:-4px">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
            </svg>
            Admin Dashboard
        </h1>
        <a href="menu.php" class="back-btn">Back to Menu</a>
    </div>

    <?php if (isset($success_message)): ?>
        <div class="success-msg">✓ <?php echo $success_message; ?></div>
    <?php endif; ?>

    <div class="dashboard-grid">
        <!-- Add Item Form -->
        <div class="card">
            <h2><span>+</span> Add New Food Item</h2>
            <form action="admin.php" method="POST">
                <input type="hidden" name="action" value="add_item">
                <div class="form-group">
                    <label>Item Name</label>
                    <input type="text" name="name" required placeholder="e.g. Jade Parcels">
                </div>
                <div class="form-group" style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div>
                        <label>Rarity (1-5)</label>
                        <select name="rarity" required>
                            <option value="1">1 Star</option>
                            <option value="2">2 Star</option>
                            <option value="3">3 Star</option>
                            <option value="5">5 Star</option>
                        </select>
                    </div>
                    <div>
                        <label>Price (Mora/PHP)</label>
                        <input type="number" name="price" required placeholder="e.g. 150">
                    </div>
                </div>
                <div class="form-group">
                    <label>Category</label>
                    <select name="category" required>
                        <option value="Main">Main Course</option>
                        <option value="Soup">Soup</option>
                        <option value="Snack">Snack</option>
                        <option value="Dessert">Dessert</option>
                        <option value="Drink">Drink</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="desc" rows="3" required placeholder="Item description..."></textarea>
                </div>
                <button type="submit">ADD TO MENU</button>
            </form>
        </div>

        <!-- Queries Showcase -->
        <div style="display: flex; flex-direction: column; gap: 24px;">
            
            <div class="card">
                <h2>
                    <span>
                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="vertical-align:-2px">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </span> 
                    Sales Metrics
                </h2>
                <p style="font-size: 13px; color: #64748b; margin-bottom: 16px;">
                    <strong>Unit 5, Variation 5: Parameter + Projection 1</strong><br>
                    Querying <code>status: 'delivered'</code> and projecting only <code>grand_total</code>.
                </p>
                <div class="metric-box">
                    <div class="metric-title">Total Delivered Revenue</div>
                    <div class="metric-value">₱<?php echo number_format($totalRevenue); ?></div>
                </div>
            </div>

            <div class="card">
                <h2>
                    <span>
                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="vertical-align:-2px">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </span> 
                    Manage Inventory
                </h2>
                <p style="font-size: 13px; color: #64748b; margin-bottom: 16px;">
                    <strong>Unit 5, Variation 3: Projection 1</strong><br>
                    Populating a dropdown by fetching <em>only</em> <code>item_name</code> from the database.
                </p>
                <div class="dropdown-showcase">
                    <form action="admin.php" method="POST" style="display: flex; flex-direction: column; gap: 10px;">
                        <input type="hidden" name="action" value="deactivate_item">
                        <label>Select Item to Deactivate</label>
                        <select name="item_id" required>
                            <option value="" disabled selected>-- Select an active item --</option>
                            <?php foreach ($itemNames as $item): ?>
                                <option value="<?php echo htmlspecialchars((string)$item['_id']); ?>">
                                    <?php echo htmlspecialchars($item['name'] ?? 'Unknown Item'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" style="background: #ef4444; color: white; border-radius: 8px; font-size: 14px; padding: 10px;" onclick="return confirm('Are you sure you want to hide this item from the menu?');">
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="vertical-align:-2px; margin-right: 4px;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                            Deactivate Item
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>

</body>
</html>
