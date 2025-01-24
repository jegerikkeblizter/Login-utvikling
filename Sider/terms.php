<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Under Bygging</title>
    <style>
        /* Global styling */
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            min-height: 100vh;
            background: radial-gradient(circle, #4b0082, #1a1a1a, #8b008b);
            background-size: 200% 200%;
            animation: moveSmoke 700s infinite;
            color: #fff;
            display: flex;
            justify-content: center;
            align-items: center;
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

        /* Container styling */
        .construction-container {
            background-color: rgba(0, 0, 0, 0.8);
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.5);
            max-width: 500px;
            width: 100%;
        }

        .construction-container h1 {
            font-size: 36px;
            color: #ff69b4;
            margin-bottom: 20px;
        }

        .construction-container p {
            font-size: 18px;
            margin-bottom: 20px;
        }

        .construction-container .back-button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #8b008b;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            font-size: 16px;
            transition: background-color 0.3s ease;
        }

        .construction-container .back-button:hover {
            background-color: #ff69b4;
            color: #1a1a1a;
        }
    </style>
</head>
<body>
    <div class="construction-container">
        <h1>Siden er under bygging</h1>
        <p>Vi har ingen vilkår for nå siden nettsiden er forsatt under bygging, men kom gjerne tilbake for å se oppdateringer!</p>
        <a href="dashboard.php" class="back-button">Tilbake til Dashboard</a>
    </div>
</body>
</html>
