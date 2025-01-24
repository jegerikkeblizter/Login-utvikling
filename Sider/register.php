<?php
include '../database/db_connect.php';

$message = "";
$toastColor = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Check if email already exists
    $checkEmailStmt = $conn->prepare("SELECT email FROM userdata WHERE email = ?");
    if (!$checkEmailStmt) {
        die("SQL error: " . $conn->error);
    }
    $checkEmailStmt->bind_param("s", $email);
    $checkEmailStmt->execute();
    $checkEmailStmt->store_result();

    if ($checkEmailStmt->num_rows > 0) {
        $message = "Email ID already exists.";
        $toastColor = "#007bff"; // Primary color
    } else {
        // Insert new user
        $stmt = $conn->prepare("INSERT INTO userdata (username, email, password) VALUES (?, ?, ?)");
        if (!$stmt) {
            die("SQL error: " . $conn->error);
        }
        $stmt->bind_param("sss", $username, $email, $hashed_password);

        if ($stmt->execute()) {
            $message = "Account created successfully.";
            $toastColor = "#28a745"; // Success color
        } else {
            $message = "Error: " . $stmt->error;
            $toastColor = "#dc3545"; // Error color
        }

        $stmt->close();
    }

    $checkEmailStmt->close();
    $conn->close();
}
?>


<!DOCTYPE html>
<html lang="no">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrer Deg</title>
    <style>
        /* Generelle stiler */
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
            z-index: 2;
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
            z-index: 1;
        }

        form {
            background: rgba(0, 0, 0, 0.8);
            border-radius: 10px;
            padding: 30px 40px;
            max-width: 400px;
            width: 100%;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.5);
            z-index: 3;
        }

        h5 {
            text-align: center;
            margin-bottom: 20px;
            font-size: 24px;
            color: #ff69b4;
        }

        .input-gruppe {
            margin-bottom: 15px;
        }

        .input-gruppe label {
            display: block;
            margin-bottom: 5px;
            font-size: 14px;
            color: #ddd;
        }

        .input-gruppe input {
            width: 100%;
            padding: 10px;
            border: 1px solid #8b008b;
            border-radius: 5px;
            font-size: 14px;
            background: #1a1a1a;
            color: #fff;
        }

        .input-gruppe input:focus {
            outline: none;
            border-color: #ff69b4;
        }

        .register-knapp {
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

        .register-knapp:hover {
            background: #ff69b4;
            color: #1a1a1a;
        }

        p {
            text-align: center;
            margin-top: 15px;
            font-size: 14px;
            color: #ddd;
        }

        .tilbake {
            color: #ff69b4;
            text-decoration: none;
            font-weight: bold;
        }

        .tilbake:hover {
            color: #fff;
        }

        .toast {
            margin-bottom: 20px;
            padding: 10px 20px;
            border-radius: 5px;
            font-size: 14px;
            position: relative;
        }

        .toast button {
            position: absolute;
            top: 5px;
            right: 10px;
            background: none;
            border: none;
            color: #fff;
            font-size: 18px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="background"></div>
    <div class="container">
        <?php if ($message): ?>
            <div class="toast" style="z-index: 10; background-color:<?php echo $toastColor; ?>;">
                <?php echo $message; ?>
                <button onclick="this.parentElement.style.display='none'">×</button>
            </div>
        <?php endif; ?>
        
        <form method="post">
            <h5>Opprett Konto</h5>
            <div class="input-gruppe">
                <label for="username">Brukernavn</label>
                <input type="text" name="username" id="username" required>
            </div>
            <div class="input-gruppe">
                <label for="email">E-post</label>
                <input type="email" name="email" id="email" required>
            </div>
            <div class="input-gruppe">
                <label for="password">Passord</label>
                <input type="password" name="password" id="password" required>
            </div>
            <button type="submit" class="register-knapp">Lag Bruker</button>
            <p>Har du en bruker? <a class="tilbake" href="./login.php">Logg inn</a></p>
            <p>Eller vil du tilbake?<a class="tilbake" href="./index.php"> Hjem</a></p>
        </form>
    </div>
</body>
</html>

