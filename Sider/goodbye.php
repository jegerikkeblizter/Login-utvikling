<?php
session_start();

// Hvis brukeren er logget inn, logg ut
if (isset($_SESSION['user_id'])) {
    session_unset();
    session_destroy();
}
?>
<!DOCTYPE html>
<html lang="no">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Farvel</title>
    <style>
        /* Tema og bakgrunn */
        body {
            font-family: 'Arial', sans-serif;
            color: #fff;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background: radial-gradient(circle, #4b0082, #1a1a1a, #8b008b);
            background-size: 200% 200%;
            animation: moveSmoke 700s infinite;
            text-align: center;
        }

        @keyframes moveSmoke {
            0% {
                background-position: 0% 50%;
            }
            50% {
                background-position: 700% 1400%;
            }
            100% {
                background-position: 0% 50%;
            }
        }

        .container {
            padding: 20px;
            border-radius: 10px;
            background: rgba(0, 0, 0, 0.5); /* Gjennomsiktig boks */
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.4);
        }

        h1 {
            font-size: 44px;
            color: #ff69b4; /* Rosa */
            margin-bottom: 20px;
        }

        p {
            font-size: 18px;
            color: #ddd;
            margin-bottom: 30px;
        }

        .btn {
            display: inline-block;
            background: #8b008b; /* Lilla */
            color: #fff;
            padding: 15px 25px;
            font-size: 16px;
            font-weight: bold;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .btn:hover {
            background: #ff69b4; /* Rosa hover */
            color: #1a1a1a;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Farvel!</h1>
        <p>Kontoen din er nå slettet. Takk for at du var en del av PhotoShare. Vi håper å se deg igjen!</p>
        <a href="index.php" class="btn">Tilbake til Hjem</a>
    </div>
</body>
</html>
