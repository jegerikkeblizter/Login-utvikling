<?php
session_start();
include '../database/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['2fa_code'], $_SESSION['2fa_time'])) {
        header("Location: login.php");
        exit();
    }

    $user_code = $_POST['code'];
    $session_code = $_SESSION['2fa_code'];
    $time_diff = time() - $_SESSION['2fa_time'];

    if ($time_diff > 300) { // 5-minute expiration
        session_unset();
        session_destroy();
        die("2FA-koden er utløpt.");
    }

    if ($user_code == $session_code) {
        session_regenerate_id(true);

        // Set required session variables
        $_SESSION['authenticated'] = true;
        $_SESSION['user_ip'] = $_SERVER['REMOTE_ADDR'];
        $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
        $_SESSION['last_activity'] = time();

        // Remove 2FA session variables
        unset($_SESSION['2fa_code'], $_SESSION['2fa_time']);

        header("Location: dashboard.php");
        exit();
    } else {
        echo "Feil 2FA-kode.";
    }
}
?>




<!DOCTYPE html>
<html lang="no">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifiser 2FA</title>
    <style>
        /* General styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            color: #fff;
        }

        .background {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            background: radial-gradient(circle, #4b0082, #1a1a1a, #8b008b);
            background-size: 200% 200%;
            animation: moveSmoke 700s infinite;
        }

        @keyframes moveSmoke {
            0% { background-position: 0% 50%; }
            50% { background-position: 700% 1400%; }
            100% { background-position: 0% 50%; }
        }

        .container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }

        form {
            background: rgba(0, 0, 0, 0.8);
            border-radius: 10px;
            padding: 30px 40px;
            max-width: 400px;
            width: 100%;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.5);
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
            font-size: 24px;
            color: #ff69b4;
        }

        label {
            display: block;
            margin-bottom: 10px;
            font-size: 14px;
            color: #ddd;
        }

        input[type="text"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #8b008b;
            border-radius: 5px;
            font-size: 14px;
            background: #1a1a1a;
            color: #fff;
            margin-bottom: 20px;
        }

        input:focus {
            outline: none;
            border-color: #ff69b4;
        }

        .verify-knapp {
            display: block;
            width: 100%;
            padding: 10px;
            background: #8b008b;
            color: #fff;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .verify-knapp:hover {
            background: #ff69b4;
            color: #1a1a1a;
        }

        .error-message {
            margin-top: 10px;
            padding: 10px;
            background-color: rgba(255, 0, 0, 0.2);
            color: #ff4444;
            border-radius: 5px;
            text-align: center;
            font-size: 14px;
        }

    </style>
</head>
<body>
    <div class="background"></div>
    <div class="container">
        <h2>To-faktor autentisering</h2>
        <form method="POST" action="">
            <label for="code">Skriv inn den 6-sifrede koden sendt til e-posten din:</label>
            <input type="text" id="code" name="code" required>
            <button type="submit" class="verify-knapp">Verifiser</button>
            <?php if (!empty($error_message)): ?>
                <div class="error-message"><?php echo $error_message; ?></div>
            <?php endif; ?>
        </form>
    </div>
</body>
</html>
