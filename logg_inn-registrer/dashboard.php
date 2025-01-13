<?php
$lifetime = 15 * 24 * 60 * 60; // 15 dager
session_set_cookie_params($lifetime);
session_start();

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

// Sjekk session timeout
$timeout_duration = 15 * 24 * 60 * 60;
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout_duration) {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit();
}

// Oppdater siste aktivitetstid
$_SESSION['last_activity'] = time();

$email = htmlspecialchars($_SESSION['email']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
</head>
<body>
    <div class="container">
        <h1>Velkommen, <?php echo $email; ?>!</h1>
        <p>Du er logget inn via session eller cookie.</p>
        
        <nav>
            <ul>
                <li><a href="settings.php">Innstillinger</a></li>
                <li><a href="utforsk.php">Utforsk</a></li>
            </ul>
        </nav>

        <form action="logout.php" method="post">
            <button type="submit">Logg ut</button>
        </form>
    </div>
</body>
</html>
