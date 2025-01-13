<?php
session_start();
require_once '../database/db_connect.php'; // Databaseforbindelse

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

$userId = $_SESSION['user_id'];

// Håndtering av skjemaer
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_account'])) {
        $stmt = $conn->prepare("DELETE FROM userdata WHERE id = ?");
        if (!$stmt) {
            die("Forberedelse av spørring feilet: " . $conn->error);
        }
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $stmt->close();
        
        session_unset();
        session_destroy();
        header("Location: goodbye.php");
        exit();
    }

    if (isset($_POST['update_username'])) {
        $newUsername = htmlspecialchars($_POST['username']);
        $stmt = $conn->prepare("UPDATE userdata SET username = ? WHERE id = ?");
        if (!$stmt) {
            die("Forberedelse av spørring feilet: " . $conn->error);
        }
        $stmt->bind_param("si", $newUsername, $userId);
        $stmt->execute();
        $stmt->close();
        echo "Brukernavn oppdatert.";
    }

    if (isset($_POST['update_password'])) {
        $newPassword = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE userdata SET password = ? WHERE id = ?");
        if (!$stmt) {
            die("Forberedelse av spørring feilet: " . $conn->error);
        }
        $stmt->bind_param("si", $newPassword, $userId);
        $stmt->execute();
        $stmt->close();
        echo "Passord oppdatert.";
    }

    if (isset($_POST['update_profile_picture']) && isset($_FILES['profile_picture'])) {
        $file = $_FILES['profile_picture'];
        $imageData = file_get_contents($file['tmp_name']); // Henter binærdata fra bildet
        
        if ($imageData) {
            $stmt = $conn->prepare("UPDATE userdata SET profile_picture = ? WHERE id = ?");
            if (!$stmt) {
                die("Forberedelse av spørring feilet: " . $conn->error);
            }
            $stmt->bind_param("bi", $null, $userId);
            $stmt->send_long_data(0, $imageData);
            $stmt->execute();
            $stmt->close();
            echo "Profilbilde oppdatert.";
        } else {
            echo "Opplasting mislyktes.";
        }
    }
}

// Hent brukerdata inkludert profilbilde
$stmt = $conn->prepare("SELECT username, profile_picture FROM userdata WHERE id = ?");
if (!$stmt) {
    die("Forberedelse av spørring feilet: " . $conn->error);
}
$stmt->bind_param("i", $userId);
$stmt->execute();
$stmt->bind_result($username, $profilePicture);
$stmt->fetch();
$stmt->close();

// Standard bilde hvis ingen profilbilde er satt
$defaultImage = 'data:image/png;base64,' . base64_encode(file_get_contents('default.png'));
$profileImage = $profilePicture ? 'data:image/png;base64,' . base64_encode($profilePicture) : $defaultImage;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Innstillinger</title>
</head>
<body>
    <h1>Innstillinger</h1>
    <img src="<?php echo $profileImage; ?>" alt="Profilbilde" width="100">
    <p>Brukernavn: <?php echo htmlspecialchars($username); ?></p>
    
    <form method="post" enctype="multipart/form-data">
        <h2>Endre brukernavn</h2>
        <label for="username">Nytt brukernavn:</label>
        <input type="text" name="username" id="username" required>
        <button type="submit" name="update_username">Oppdater brukernavn</button>
    </form>
    
    <form method="post">
        <h2>Endre passord</h2>
        <label for="password">Nytt passord:</label>
        <input type="password" name="password" id="password" required>
        <button type="submit" name="update_password">Oppdater passord</button>
    </form>
    
    <form method="post" enctype="multipart/form-data">
        <h2>Endre profilbilde</h2>
        <label for="profile_picture">Velg et nytt bilde:</label>
        <input type="file" name="profile_picture" id="profile_picture" accept="image/*" required>
        <button type="submit" name="update_profile_picture">Oppdater profilbilde</button>
    </form>
    
    <form method="post">
        <h2>Slett brukerkonto</h2>
        <button type="submit" name="delete_account" onclick="return confirm('Er du sikker på at du vil slette kontoen din?')">Slett konto</button>
    </form>
</body>
</html>
