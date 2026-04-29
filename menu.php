<?php
session_start();

if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    header("Location: User_login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];

$menuItems = [
    ['name' => 'Steak', 'rarity' => 1, 'price' => 30, 'desc' => 'Revives a character (basic food)'],
    ['name' => 'Mondstadt Grilled Fish', 'rarity' => 1, 'price' => 35, 'desc' => 'Simple early-game healing dish'],
    ['name' => 'Radish Veggie Soup', 'rarity' => 1, 'price' => 40, 'desc' => 'Gradual healing over time'],
    ['name' => 'Mora Meat', 'rarity' => 1, 'price' => 45, 'desc' => 'Cheap revive food'],
    ['name' => 'Sweet Madame', 'rarity' => 2, 'price' => 90, 'desc' => 'One of the most popular healing foods'],
    ['name' => 'Fried Radish Balls', 'rarity' => 2, 'price' => 100, 'desc' => 'Boosts ATK for the party'],
    ['name' => 'Tea Break Pancake', 'rarity' => 2, 'price' => 80, 'desc' => 'Revive + moderate healing'],
    ['name' => 'Mushroom Pizza', 'rarity' => 3, 'price' => 180, 'desc' => 'Strong healing over time'],
    ['name' => 'Mondstadt Hash Brown', 'rarity' => 3, 'price' => 170, 'desc' => 'High HP restoration'],
    ['name' => "Adeptus' Temptation", 'rarity' => 5, 'price' => 500, 'desc' => 'Extremely powerful buff food (top-tier rarity)'],
];

$rarityConfig = [
    1 => ['label' => '1-Star', 'stars' => '⭐', 'color' => '#90a4ae', 'glow' => 'rgba(144,164,174,0.25)'],
    2 => ['label' => '2-Star', 'stars' => '⭐⭐', 'color' => '#66bb6a', 'glow' => 'rgba(102,187,106,0.25)'],
    3 => ['label' => '3-Star', 'stars' => '⭐⭐⭐', 'color' => '#42a5f5', 'glow' => 'rgba(66,165,245,0.25)'],
    5 => ['label' => '5-Star', 'stars' => '⭐⭐⭐⭐⭐', 'color' => '#ffa726', 'glow' => 'rgba(255,167,38,0.35)'],
];

$grouped = [];
foreach ($menuItems as $item) {
    $grouped[$item['rarity']][] = $item;
}
ksort($grouped);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paimon's Kitchen – Menu</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: rgba(244, 242, 236);
            min-height: 100vh;
            color: #1e293b;
        }

        /* ── TOP BAR ────*/
        .top-bar {
            position: sticky;
            top: 0;
            z-index: 100;
            background: rgb(235, 231, 220);
            border-bottom: 2px solid #d4af37;
            padding: 14px 28px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 64px;
            box-shadow: 0 2px 8px rgba(180, 150, 60, 0.12);
        }

        .brand {
            font-size: 20px;
            font-weight: 800;
            color: #b8860b;
            letter-spacing: 1px;
        }

        .brand span {
            color: #3d2b1f;
        }

        .user-section {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .user-greeting {
            font-size: 13px;
            color: #6b5a3d;
        }

        .user-greeting strong {
            color: #b8860b;
        }

        .logout-btn {
            font-size: 12px;
            color: #6b5a3d;
            text-decoration: none;
            padding: 5px 14px;
            border: 1px solid #c8b98a;
            border-radius: 6px;
            transition: all 0.2s;
        }

        .logout-btn:hover {
            border-color: #b8860b;
            color: #b8860b;
        }

        /* ── MENU AREA ────────────────────────────────────── */
        .menu-area {
            max-width: 1200px;
            margin: 0 auto;
            padding: 30px 28px 100px 28px; /* Extra bottom padding for floating bar */
        }

        .rarity-section {
            margin-bottom: 36px;
        }

        .rarity-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 16px;
            padding-bottom: 10px;
            border-bottom: 2px solid;
        }

        .rarity-label {
            font-size: 15px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .rarity-stars {
            font-size: 14px;
        }

        .food-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
            gap: 18px;
        }

        .food-card {
            background: rgba(250, 250, 247);
            border-radius: 12px;
            border: 2px solid transparent;
            padding: 20px;
            position: relative;
            transition: transform 0.2s, box-shadow 0.2s;
            backdrop-filter: blur(6px);
            display: flex;
            flex-direction: column;
        }

        .food-card:hover {
            transform: translateY(-3px);
        }

        .food-name {
            font-size: 16px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 5px;
        }

        .food-desc {
            font-size: 12px;
            color: #64748b;
            margin-bottom: 14px;
            line-height: 1.4;
            font-style: italic;
            flex-grow: 1;
        }

        .food-price {
            font-size: 22px;
            font-weight: 800;
            color: #1e293b;
            margin-bottom: 16px;
        }

        .food-price span {
            font-size: 12px;
            font-weight: 500;
            color: #94a3b8;
        }

        .qty-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #f8f9fa;
            padding: 10px;
            border-radius: 8px;
            border: 1px solid #cbd5e1;
        }

        .qty-label {
            font-size: 13px;
            color: #475569;
            font-weight: 600;
        }

        .qty-input {
            width: 70px;
            padding: 6px;
            font-size: 15px;
            font-weight: 700;
            text-align: center;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            outline: none;
            color: #1e293b;
        }
        
        .qty-input:focus {
            border-color: #d4af37;
            box-shadow: 0 0 0 2px rgba(212, 175, 55, 0.2);
        }

        /* ── FLOATING BOTTOM BAR ──────────────────────────── */
        .floating-bar {
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            background: rgba(235, 231, 220, 0.95);
            backdrop-filter: blur(10px);
            border-top: 2px solid #d4af37;
            padding: 16px 28px;
            display: flex;
            justify-content: flex-end;
            align-items: center;
            box-shadow: 0 -4px 15px rgba(180, 150, 60, 0.15);
            z-index: 100;
        }

        .place-order-btn {
            padding: 14px 40px;
            background: linear-gradient(135deg, #d4af37, #f0d060);
            color: #0f172a;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 800;
            letter-spacing: 1px;
            cursor: pointer;
            transition: all 0.2s;
            text-transform: uppercase;
            box-shadow: 0 4px 15px rgba(212, 175, 55, 0.25);
        }

        .place-order-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(212, 175, 55, 0.45);
        }

        .place-order-btn:active {
            transform: scale(0.98);
        }
    </style>
</head>

<body>

    <!-- TOP BAR -->
    <header class="top-bar">
        <div class="brand">🍴 Paimon's <span>Kitchen</span></div>
        <div class="user-section">
            <span class="user-greeting">Welcome, <strong><?php echo htmlspecialchars($user_name); ?></strong></span>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </header>

    <!-- FORM WRAPPING THE WHOLE MENU -->
    <form action="place_order.php" method="POST">
        
        <div class="menu-area">
            <?php foreach ($grouped as $rarity => $items):
                $cfg = $rarityConfig[$rarity];
                ?>
                <div class="rarity-section">
                    <div class="rarity-header" style="border-color:<?= $cfg['color'] ?>">
                        <span class="rarity-stars"><?= $cfg['stars'] ?></span>
                        <span class="rarity-label" style="color:<?= $cfg['color'] ?>"><?= $cfg['label'] ?></span>
                    </div>
                    <div class="food-grid">
                        <?php foreach ($items as $item): 
                            $safeName = htmlspecialchars($item['name'], ENT_QUOTES);
                        ?>
                            <div class="food-card" style="border-color:<?= $cfg['color'] ?>;box-shadow:0 4px 14px <?= $cfg['glow'] ?>">
                                <div class="food-name"><?= $safeName ?></div>
                                <div class="food-desc"><?= htmlspecialchars($item['desc']) ?></div>
                                <div class="food-price">₱<?= number_format($item['price']) ?> <span>/ order</span></div>

                                <!-- Hidden inputs to pass data to backend securely via standard form POST -->
                                <input type="hidden" name="items[<?= $safeName ?>][name]" value="<?= $safeName ?>">
                                <input type="hidden" name="items[<?= $safeName ?>][price]" value="<?= $item['price'] ?>">
                                <input type="hidden" name="items[<?= $safeName ?>][rarity]" value="<?= $rarity ?>">

                                <div class="qty-row">
                                    <span class="qty-label">Quantity:</span>
                                    <input type="number" class="qty-input" name="items[<?= $safeName ?>][quantity]" value="0" min="0">
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- FLOATING BOTTOM BAR -->
        <div class="floating-bar">
            <button type="submit" class="place-order-btn">🍜 Place Order</button>
        </div>
        
    </form>

</body>
</html>