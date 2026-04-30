<?php
session_start();

// If already logged in, redirect to menu
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header("Location: menu.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paimon's Kitchen</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: url('assets/genshin_bg.png') no-repeat center center fixed;
            background-size: cover;
            color: #1e293b;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            overflow: hidden;
        }

        .overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.4), rgba(30, 58, 95, 0.2));
            z-index: 1;
        }

        .landing-container {
            position: relative;
            z-index: 2;
            background-color: rgba(253, 251, 247, 0.96);
            backdrop-filter: blur(12px);
            padding: 50px 40px;
            border-radius: 24px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.5), 0 10px 20px rgba(0,0,0,0.3);
            width: 100%;
            max-width: 480px;
            text-align: center;
            border: 2px solid #d4af37;
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0px); }
        }

        .paimon-asset {
            width: 160px;
            height: auto;
            margin-bottom: 20px;
            filter: drop-shadow(0 10px 15px rgba(0,0,0,0.2));
            animation: bounceIn 1s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        @keyframes bounceIn {
            0% { transform: scale(0); opacity: 0; }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); opacity: 1; }
        }

        h1 {
            font-size: 32px;
            font-weight: 800;
            color: #1e293b;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 10px;
        }

        h1 span {
            color: #d4af37;
        }

        .subtitle {
            font-size: 15px;
            color: #64748b;
            margin-bottom: 36px;
            line-height: 1.5;
        }

        .button-group {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .btn {
            display: inline-block;
            width: 100%;
            padding: 16px;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            text-decoration: none;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .btn-primary {
            background: linear-gradient(135deg, #1e293b, #0f172a);
            color: #d4af37;
            border: 2px solid #d4af37;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #334155, #1e293b);
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(212, 175, 55, 0.3);
        }

        .btn-primary:active {
            transform: scale(0.98);
        }

        .btn-secondary {
            background: rgba(255, 255, 255, 0.8);
            color: #1e293b;
            border: 2px solid #cbd5e1;
        }

        .btn-secondary:hover {
            background: #ffffff;
            border-color: #94a3b8;
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }

        .btn-secondary:active {
            transform: scale(0.98);
        }

        .glow {
            position: absolute;
            top: -20px;
            right: -20px;
            width: 100px;
            height: 100px;
            background: radial-gradient(circle, rgba(212,175,55,0.4) 0%, rgba(212,175,55,0) 70%);
            border-radius: 50%;
            z-index: -1;
        }
    </style>
</head>
<body>

    <div class="overlay"></div>

    <div class="landing-container">
        <div class="glow"></div>
        
        <img src="assets/paimon_cheer.png" alt="Paimon Cheering" class="paimon-asset" onerror="this.style.display='none';">

        <h1>Paimon's <span>Kitchen</span></h1>
        <p class="subtitle">The best food in all of Teyvat, delivered fresh to your coordinates!</p>

        <div class="button-group">
            <a href="User_login.php" class="btn btn-primary">Log In</a>
            <a href="User_reg.php" class="btn btn-secondary">Register New Account</a>
        </div>
    </div>

</body>
</html>
