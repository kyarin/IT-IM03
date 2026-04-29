<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Genshin Registration</title>
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
        .register-container {
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
        input[type="text"],
        input[type="number"],
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
    </style>
</head>
<body>
    <div class="register-container">

        <h2>Register</h2>
        <form action="register.php" method="POST">
            <div class="form-group">
                <input type="text" name="name" placeholder="Full Name" required>
            </div>
            <div class="form-group">
                <input type="number" name="age" placeholder="Age">
            </div>
            <div class="form-group">
                <input type="email" name="email" placeholder="Email" required>
            </div>
            <div class="form-group">
                <input type="password" name="password" placeholder="Password" required>
            </div>
            <div class="form-group">
                <input type="text" name="contact" placeholder="Contact Number">
            </div>
            <button type="submit">REGISTER</button>
        </form>
    </div>
</body>
</html>
