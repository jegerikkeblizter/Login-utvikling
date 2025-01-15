<?php
session_start();
require_once '../database/db_connect.php'; // Databaseforbindelse

// Sjekk om brukeren er autentisert
function isSessionValid() {
    return isset($_SESSION['authenticated'], $_SESSION['user_id'], $_SESSION['email'], $_SESSION['user_ip'], $_SESSION['user_agent'])
        && $_SESSION['authenticated'] === true
        && $_SESSION['user_ip'] === $_SERVER['REMOTE_ADDR']
        && $_SESSION['user_agent'] === $_SERVER['HTTP_USER_AGENT'];
}

if (!isSessionValid()) {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit();
}

// Hent alle brukeres bilder fra databasen
$stmt = $conn->prepare("
    SELECT u.username, p.image 
    FROM userdata u 
    JOIN photos p ON u.id = p.user_id 
    ORDER BY p.created_at DESC
");
if (!$stmt) {
    die("Forberedelse av spørring feilet: " . $conn->error);
}
$stmt->execute();
$stmt->bind_result($username, $image);

// Lagre resultatene i en array for visning
$posts = [];
while ($stmt->fetch()) {
    $posts[] = ['username' => $username, 'image' => $image];
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Utforsk</title>
    <style>
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
            flex-direction: column;
            align-items: center;
            justify-content: center; /* Center all content within the viewport */
            padding: 20px;
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

        .explore-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center; /* Center the grid horizontally */
            align-items: center; /* Center the grid vertically */
            margin-top: 20px;
            width: 90%;
            max-width: 1200px;
        }

        .post {
            background-color: #2b2b2b;
            border-radius: 10px;
            padding: 15px;
            text-align: center;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.5);
            display: flex;
            flex-direction: column;
            align-items: center;
            width: auto;
            max-width: 200px;
        }

        .post img {
            max-width: 200px;
            max-height: 200px;
            border-radius: 10px;
            margin-bottom: 10px;
            display: block;
            object-fit: contain;
        }

        .post p {
            margin: 10px 0 0;
            font-size: 16px;
            font-weight: bold;
            color: #ff69b4;
        }

        .home-link {
            position: absolute;
            top: 20px;
            left: 20px;
            background-color: #8b008b;
            color: #fff;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 5px;
            font-size: 16px;
            font-weight: bold;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
            transition: background-color 0.3s ease;
        }

        .home-link:hover {
            background-color: #ff69b4;
        }
    </style>
</head>
<body>
    <a href="dashboard.php" class="home-link">⬅️ Tilbake</a>
    <h1>Utforsk</h1>
    <div class="explore-container">
        <?php if (!empty($posts)): ?>
            <?php foreach ($posts as $post): ?>
                <div class="post">
                    <img src="data:image/png;base64,<?php echo base64_encode($post['image']); ?>" alt="Bilde">
                    <p><?php echo htmlspecialchars($post['username']); ?></p>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Ingen bilder å vise akkurat nå.</p>
        <?php endif; ?>
    </div>
</body>
</html>


