<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Genshin Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
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
        }
        .login-container {
            background-color: rgba(253, 251, 247, 0.95);
            backdrop-filter: blur(10px);
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.5), 0 8px 10px -6px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 400px;
            border: 2px solid #d4af37;
            position: relative;
        }

        h2 {
            margin-bottom: 24px;
            font-size: 26px;
            font-weight: 700;
            color: #1e293b;
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-bottom: 2px solid #d4af37;
            padding-bottom: 10px;
        }
        .form-group {
            margin-bottom: 16px;
        }
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            font-size: 14px;
            font-family: inherit;
            transition: border-color 0.2s, box-shadow 0.2s, background-color 0.2s;
            outline: none;
            background-color: #ffffff;
        }
        input::placeholder {
            color: #94a3b8;
        }
        input:focus {
            border-color: #d4af37;
            box-shadow: 0 0 0 3px rgba(212, 175, 55, 0.2);
        }
        button {
            width: 100%;
            padding: 12px;
            background-color: #1e293b;
            color: #d4af37;
            border: 2px solid #d4af37;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 700;
            letter-spacing: 1px;
            cursor: pointer;
            transition: background-color 0.2s, transform 0.1s, box-shadow 0.2s;
            margin-top: 8px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.3);
        }
        button:hover {
            background-color: #334155;
            box-shadow: 0 6px 8px -1px rgba(0, 0, 0, 0.4);
        }
        button:active {
            transform: scale(0.98);
        }
        .error-message {
            background-color: #fef2f2;
            color: #dc2626;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 16px;
            border: 1px solid #fca5a5;
            font-size: 14px;
            text-align: center;
        }
        .register-link {
            text-align: center;
            margin-top: 16px;
            font-size: 14px;
            color: #475569;
        }
        .register-link a {
            color: #d4af37;
            font-weight: 600;
            text-decoration: none;
        }
        .register-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Login</h2>
        
        <?php if(isset($_GET['error'])): ?>
            <div class="error-message">
                <?php 
                    if($_GET['error'] == 'invalid') echo 'Invalid email or password. Please try again.';
                    elseif($_GET['error'] == 'missing') echo 'Please fill in all fields.';
                    else echo 'An error occurred. Please try again.';
                ?>
            </div>
        <?php endif; ?>
        
        <form action="login.php" method="POST">
            <div class="form-group">
                <input type="email" name="email" placeholder="Email" required>
            </div>
            <div class="form-group">
                <input type="password" name="password" placeholder="Password" required>
            </div>
            <button type="submit">LOGIN</button>
        </form>
        
        <div class="register-link">
            Don't have an account? <a href="User_reg.php">Register here</a>
        </div>
    </div>
</body>
</html>
