<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Registration Successful</title>
    <link href='https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap' rel='stylesheet'>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', sans-serif;
            background: url('assets/genshin_bg.png') no-repeat center center fixed;
            background-size: cover;
            color: #1e293b;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .success-container {
            background-color: rgba(253, 251, 247, 0.95);
            backdrop-filter: blur(10px);
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.5), 0 8px 10px -6px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 400px;
            border: 2px solid #d4af37;
            position: relative;
            text-align: center;
        }
        .mascot {
            width: 180px;
            pointer-events: none;
            filter: drop-shadow(0 4px 6px rgba(0,0,0,0.3));
            animation: float 3s ease-in-out infinite;
            margin-bottom: -20px;
            margin-top: -80px;
        }
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0px); }
        }
        h2 {
            margin-bottom: 16px;
            font-size: 26px;
            font-weight: 700;
            color: #1e293b;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-bottom: 2px solid #d4af37;
            padding-bottom: 10px;
            display: inline-block;
        }
        p {
            font-size: 16px;
            color: #475569;
            margin-top: 16px;
            margin-bottom: 28px;
            line-height: 1.5;
        }
        .back-link {
            display: inline-block;
            padding: 12px 24px;
            background-color: #1e293b;
            color: #d4af37;
            border: 2px solid #d4af37;
            text-decoration: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 700;
            letter-spacing: 1px;
            transition: background-color 0.2s, transform 0.1s, box-shadow 0.2s;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.3);
        }
        .back-link:hover {
            background-color: #334155;
            box-shadow: 0 6px 8px -1px rgba(0, 0, 0, 0.4);
        }
        .back-link:active {
            transform: scale(0.98);
        }
    </style>
</head>
<body>
    <div class='success-container'>
        <img src="assets/paimon_cheer.png" class="mascot" alt="Cute Mascot">
        <br>
        <h2>Registration Successful!</h2>
        <p>Your account has been created securely.</p>
        <a href='User_reg.html' class='back-link'>RETURN</a>
    </div>
</body>
</html>
