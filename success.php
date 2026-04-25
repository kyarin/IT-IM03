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
            background: linear-gradient(135deg, #e0f2fe 0%, #bae6fd 100%);
            color: #334155;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .success-container {
            background-color: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(12px);
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 10px 25px -5px rgba(59, 130, 246, 0.15), 0 8px 10px -6px rgba(59, 130, 246, 0.1);
            width: 100%;
            max-width: 400px;
            border: 1px solid rgba(255, 255, 255, 0.5);
            text-align: center;
        }
        .success-icon {
            font-size: 56px;
            color: #3b82f6;
            margin-bottom: 20px;
        }
        h2 {
            margin-bottom: 16px;
            font-size: 26px;
            font-weight: 700;
            color: #0284c7;
        }
        p {
            font-size: 16px;
            color: #475569;
            margin-bottom: 28px;
            line-height: 1.5;
        }
        .back-link {
            display: inline-block;
            padding: 12px 24px;
            background-color: #3b82f6;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            transition: background-color 0.2s, transform 0.1s, box-shadow 0.2s;
            box-shadow: 0 4px 6px -1px rgba(59, 130, 246, 0.3);
        }
        .back-link:hover {
            background-color: #2563eb;
            box-shadow: 0 6px 8px -1px rgba(59, 130, 246, 0.4);
        }
        .back-link:active {
            transform: scale(0.98);
        }
    </style>
</head>
<body>
    <div class='success-container'>
        <div class='success-icon'>✓</div>
        <h2>Registration Successful!</h2>
        <p>Your account has been created securely.</p>
        <a href='#' class='back-link'>Login</a>
    </div>
</body>
</html>
