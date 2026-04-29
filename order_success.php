<?php
session_start();

if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    header("Location: User_login.php");
    exit;
}
if (!isset($_SESSION['last_order'])) {
    header("Location: menu.php");
    exit;
}

$order     = $_SESSION['last_order'];
$user_name = $_SESSION['user_name'];
unset($_SESSION['last_order']); // consume receipt
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmed – Paimon's Kitchen</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', sans-serif;
            background-color: rgba(244, 242, 236);
            color: #1e293b;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }

        .receipt {
            background: rgba(253, 251, 247, 0.96);
            backdrop-filter: blur(10px);
            border-radius: 18px;
            border: 2px solid #d4af37;
            box-shadow: 0 20px 40px rgba(0,0,0,0.4);
            width: 100%;
            max-width: 480px;
            overflow: hidden;
            animation: popIn 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        @keyframes popIn {
            from { opacity: 0; transform: scale(0.9) translateY(20px); }
            to   { opacity: 1; transform: scale(1) translateY(0); }
        }

        /* ── Receipt Header ── */
        .receipt-header {
            background: linear-gradient(135deg, #0f172a, #1e3a5f);
            padding: 28px 28px 24px;
            text-align: center;
            border-bottom: 2px solid #d4af37;
        }
        .check-icon {
            width: 58px; height: 58px;
            background: linear-gradient(135deg, #d4af37, #f0d060);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 26px;
            margin: 0 auto 14px;
            box-shadow: 0 0 0 6px rgba(212,175,55,0.2);
            animation: pulse 2s ease infinite;
        }
        @keyframes pulse {
            0%, 100% { box-shadow: 0 0 0 6px rgba(212,175,55,0.2); }
            50%       { box-shadow: 0 0 0 12px rgba(212,175,55,0.1); }
        }
        .receipt-title {
            font-size: 20px;
            font-weight: 800;
            color: #d4af37;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            margin-bottom: 6px;
        }
        .receipt-subtitle {
            font-size: 13px;
            color: #64748b;
        }
        .receipt-subtitle strong { color: #94a3b8; }

        /* ── Receipt Meta ── */
        .receipt-meta {
            padding: 14px 24px;
            background: #f8f4e3;
            border-bottom: 1px dashed #d4af37;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 8px;
        }
        .meta-item { font-size: 11px; color: #78580a; }
        .meta-item strong { display: block; font-size: 12px; color: #1e293b; }

        /* ── Address ── */
        .receipt-address {
            padding: 14px 24px;
            background: #ffffff;
            border-bottom: 1px dashed #d4af37;
            font-size: 13px;
            color: #475569;
        }
        .receipt-address strong {
            display: block;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #d4af37;
            margin-bottom: 4px;
        }
        .receipt-address div {
            font-weight: 600;
            color: #1e293b;
            line-height: 1.4;
        }

        /* ── Order Items ── */
        .receipt-body { padding: 20px 24px; }

        .section-label {
            font-size: 11px;
            font-weight: 700;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 12px;
        }

        .order-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #f1f5f9;
        }
        .order-item:last-child { border-bottom: none; }

        .item-left {}
        .item-name { font-size: 13px; font-weight: 600; color: #1e293b; }
        .item-meta { font-size: 11px; color: #94a3b8; margin-top: 2px; }

        .item-right { text-align: right; }
        .item-subtotal { font-size: 14px; font-weight: 700; color: #1e293b; }

        /* ── Total ── */
        .receipt-total {
            margin: 0 24px;
            padding: 14px 0;
            border-top: 2px dashed #d4af37;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .total-label { font-size: 13px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.5px; }
        .total-amount { font-size: 26px; font-weight: 800; color: #1e293b; }

        /* ── Actions ── */
        .receipt-actions {
            padding: 20px 24px 24px;
            display: flex;
            gap: 10px;
        }
        .btn-order-again {
            flex: 1;
            padding: 13px;
            background: linear-gradient(135deg, #d4af37, #f0d060);
            color: #0f172a;
            border: none;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 800;
            letter-spacing: 0.5px;
            text-decoration: none;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
            box-shadow: 0 4px 12px rgba(212,175,55,0.3);
        }
        .btn-order-again:hover { transform: translateY(-2px); box-shadow: 0 6px 18px rgba(212,175,55,0.5); }

        .btn-logout {
            padding: 13px 18px;
            background: transparent;
            color: #475569;
            border: 2px solid #cbd5e1;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            text-align: center;
            transition: all 0.2s;
        }
        .btn-logout:hover { border-color: #94a3b8; color: #1e293b; }

        /* Rarity dot */
        .rarity-dot {
            display: inline-block;
            width: 8px; height: 8px;
            border-radius: 50%;
            margin-right: 5px;
            vertical-align: middle;
        }
    </style>
</head>
<body>
<div class="receipt">

    <div class="receipt-header">
        <div class="check-icon">✓</div>
        <div class="receipt-title">Order Confirmed!</div>
        <div class="receipt-subtitle">Thank you, <strong><?php echo htmlspecialchars($user_name); ?></strong> 🎉</div>
    </div>

    <div class="receipt-meta">
        <div class="meta-item">
            Order ID
            <strong><?php echo htmlspecialchars(substr($order['order_id'], -8)); ?>...</strong>
        </div>
        <div class="meta-item" style="text-align:center">
            Items
            <strong><?php echo count($order['items']); ?> dish(es)</strong>
        </div>
        <div class="meta-item" style="text-align:right">
            Date &amp; Time
            <strong><?php echo $order['ordered_at']; ?></strong>
        </div>
    </div>
    
    <div class="receipt-address">
        <strong>To be delivered to:</strong>
        <div><?php echo htmlspecialchars($order['delivery_address'] ?? 'N/A'); ?></div>
    </div>

    <div class="receipt-body">
        <div class="section-label">🧾 Order Summary</div>

        <?php
        $rarityColors = [1 => '#90a4ae', 2 => '#66bb6a', 3 => '#42a5f5', 5 => '#ffa726'];
        $rarityStars  = [1 => '⭐', 2 => '⭐⭐', 3 => '⭐⭐⭐', 5 => '⭐⭐⭐⭐⭐'];
        foreach ($order['items'] as $item):
            $color = $rarityColors[$item['rarity']] ?? '#94a3b8';
            $stars = $rarityStars[$item['rarity']] ?? '';
        ?>
        <div class="order-item">
            <div class="item-left">
                <div class="item-name">
                    <span class="rarity-dot" style="background:<?= $color ?>"></span>
                    <?php echo htmlspecialchars($item['item']); ?>
                </div>
                <div class="item-meta"><?= $stars ?> · ₱<?= number_format($item['price']) ?> × <?= $item['quantity'] ?></div>
            </div>
            <div class="item-right">
                <div class="item-subtotal">₱<?= number_format($item['subtotal']) ?></div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="receipt-total">
        <span class="total-label">Grand Total</span>
        <span class="total-amount">₱<?php echo number_format($order['grand_total']); ?></span>
    </div>

    <div class="receipt-actions">
        <a href="menu.php" class="btn-order-again">🍜 Order Again</a>
        <a href="logout.php" class="btn-logout">Logout</a>
    </div>

</div>
</body>
</html>
