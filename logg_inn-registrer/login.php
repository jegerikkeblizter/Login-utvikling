<?php
session_start();
include '../database/db_connect.php';

$message = "";
$toastClass = "";

if (isset($_SESSION['user_id'], $_SESSION['email'])) {
    $stmt = $conn->prepare("SELECT id FROM userdata WHERE id = ? AND email = ?");
    $stmt->bind_param("is", $_SESSION['user_id'], $_SESSION['email']);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        header("Location: dashboard.php");
        exit();
    }
    session_unset();
    session_destroy();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, password FROM userdata WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($user_id, $hashed_password);
        $stmt->fetch();

        if (password_verify($password, $hashed_password)) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user_id;
            $_SESSION['email'] = $email;

            header("Location: send_2fa_code.php");
            exit();
        } else {
            $message = "Feil passord.";
            $toastClass = "bg-danger";
        }
    } else {
        $message = "E-post ikke funnet.";
        $toastClass = "bg-warning";
    }

    $stmt->close();
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="no">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logg Inn</title>
    <style>
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
            z-index: 2;
            background: radial-gradient(circle, #4b0082, #1a1a1a, #8b008b);
            background-size: 200% 200%;
            animation: moveSmoke 700s infinite;
        }

        @keyframes moveSmoke {
            0% { background-position: 200% 400%; }
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

        .input-gruppe input[type="text"],
        .input-gruppe input[type="password"] {
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

        .checkbox-gruppe {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 15px;
        }

        .checkbox-gruppe input[type="checkbox"] {
            appearance: none;
            width: 20px;
            height: 20px;
            border: 2px solid #8b008b;
            border-radius: 5px;
            background: #1a1a1a;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .checkbox-gruppe input[type="checkbox"]:checked {
            background: #ff69b4;
            border-color: #ff69b4;
        }

        .checkbox-gruppe input[type="checkbox"]:hover {
            border-color: #ff69b4;
        }

        .checkbox-gruppe label {
            font-size: 14px;
            color: #ddd;
            cursor: pointer;
        }

        .login-knapp {
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

        .login-knapp:hover {
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
            <div class="toast <?php echo $toastClass; ?>" role="alert" style="background-color: red; <?php echo $toastColor; ?>;">
                <?php echo $message; ?>
                <button onclick="this.parentElement.style.display='none'">×</button>
            </div>
        <?php endif; ?>
        <form method="post">
            <h5>Logg inn til brukeren din</h5>
            <div class="input-gruppe">
                <label for="email">E-post</label>
                <input type="text" name="email" id="email" required>
            </div>
            <div class="input-gruppe">
                <label for="password">Passord</label>
                <input type="password" name="password" id="password" required>
            </div>
            <div class="checkbox-gruppe">
                <input type="checkbox" name="remember_me" id="remember_me">
                <label for="remember_me">Husk meg</label>
            </div>
            <button type="submit" class="login-knapp">Logg inn</button>
            <p>Har du ikke bruker? <a class="tilbake" href="./register.php">Registrer deg</a></p>
            <p>Eller vil du tilbake?<a class="tilbake" href="./index.php"> Hjem</a></p>
        </form>
    </div>
</body>
</html>
