<?php
session_start();
require_once '../database/db_connect.php'; // Databaseforbindelse

function isSessionValid() {
    return isset($_SESSION['authenticated'], $_SESSION['user_id'], $_SESSION['email'], $_SESSION['user_ip'], $_SESSION['user_agent'])
        && $_SESSION['authenticated'] === true
        && $_SESSION['user_ip'] === $_SERVER['REMOTE_ADDR']
        && $_SESSION['user_agent'] === $_SERVER['HTTP_USER_AGENT'];
}

// Definerer setNotification-funksjonen
function setNotification($message) {
    if (!isset($_SESSION)) {
        session_start();
    }
    $_SESSION['notification'] = $message;
}

if (!isSessionValid()) {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];

if (empty($userId)) {
    die("Feil: Bruker-ID mangler i session.");
}

// Håndtering av skjemaer
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Slett brukerkonto
    if (isset($_POST['delete_account']) && isset($_POST['delete_confirmation'])) {
        if ($_POST['delete_confirmation'] === 'jeg-er-certified-pedo') {
            $stmt = $conn->prepare("DELETE FROM userdata WHERE id = ?");
            if (!$stmt) {
                error_log("Databasefeil: " . $conn->error);
                die("Forberedelse av spørring feilet.");
            }
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $stmt->close();

            session_unset();
            session_destroy();
            setNotification("Brukerkonto slettet.");
            header("Location: goodbye.php");
            exit();
        } else {
            setNotification("Bekreftelsen mislyktes. Kontoen ble ikke slettet.");
        }
    }

    // Oppdater brukernavn
    if (isset($_POST['update_username']) && isset($_POST['username'])) {
        $newUsername = htmlspecialchars($_POST['username']);
        if (!empty($newUsername)) {
            $stmt = $conn->prepare("UPDATE userdata SET username = ? WHERE id = ?");
            if (!$stmt) {
                error_log("Databasefeil: " . $conn->error);
                die("Forberedelse av spørring feilet.");
            }
            $stmt->bind_param("si", $newUsername, $userId);
            $stmt->execute();
            $stmt->close();
            setNotification("Brukernavn oppdatert.");
        } else {
            setNotification("Brukernavn kan ikke være tomt.");
        }
    }

    // Oppdater passord
    if (isset($_POST['update_password']) && isset($_POST['old_password'], $_POST['new_password'])) {
        $oldPassword = $_POST['old_password'];
        $newPassword = $_POST['new_password'];

        $stmt = $conn->prepare("SELECT password FROM userdata WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $stmt->bind_result($storedPassword);
        $stmt->fetch();
        $stmt->close();

        if (password_verify($oldPassword, $storedPassword)) {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE userdata SET password = ? WHERE id = ?");
            if (!$stmt) {
                error_log("Databasefeil: " . $conn->error);
                die("Forberedelse av spørring feilet.");
            }
            $stmt->bind_param("si", $hashedPassword, $userId);
            $stmt->execute();
            $stmt->close();
            setNotification("Passord oppdatert.");
        } else {
            setNotification("Gammelt passord er feil.");
        }
    }

    // Oppdater profilbilde
    if (isset($_POST['update_profile_picture']) && isset($_FILES['profile_picture'])) {
        $file = $_FILES['profile_picture'];
        if ($file['error'] === UPLOAD_ERR_OK) {
            $imageData = file_get_contents($file['tmp_name']);

            if ($imageData) {
                $stmt = $conn->prepare("UPDATE userdata SET profile_picture = ? WHERE id = ?");
                if (!$stmt) {
                    error_log("Databasefeil: " . $conn->error);
                    die("Forberedelse av spørring feilet.");
                }
                $stmt->bind_param("bi", $null, $userId);
                $stmt->send_long_data(0, $imageData);
                $stmt->execute();
                $stmt->close();
                setNotification("Profilbilde oppdatert.");
            } else {
                setNotification("Kunne ikke lese bildefilen.");
            }
        } else {
            setNotification("Opplastingsfeil: " . $file['error']);
        }
    }

    // Etter handling, omdiriger tilbake for å vise meldingen
    header("Location: settings.php");
    exit();
}

// Hent brukerdata inkludert profilbilde
$stmt = $conn->prepare("SELECT username, profile_picture FROM userdata WHERE id = ?");
if (!$stmt) {
    error_log("Databasefeil: " . $conn->error);
    die("Forberedelse av spørring feilet.");
}
$stmt->bind_param("i", $userId);
$stmt->execute();
$stmt->bind_result($username, $profilePicture);
if ($stmt->fetch()) {
    $stmt->close();
} else {
    $username = "Ukjent bruker";
    $profilePicture = null;
    $stmt->close();
    setNotification("Ingen data funnet for bruker-ID $userId.");
}

// Standard bilde hvis ingen profilbilde er satt
$defaultImage = 'data:image/png;base64,' . base64_encode(file_get_contents('default.jpg'));
$profileImage = $profilePicture ? 'data:image/png;base64,' . base64_encode($profilePicture) : $defaultImage;
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Innstillinger</title>
    <style>
        /* Global styling */
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            min-height: 94vh;
            background: radial-gradient(circle, #4b0082, #1a1a1a, #8b008b);
            background-size: 200% 200%;
            animation: moveSmoke 700s infinite;
            color: #fff;
            display: flex;
            flex-direction: column;
            align-items: center;
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

        /* Home button styling */
        .home-button {
            position: fixed;
            top: 10px;
            left: 10px;
            background-color: #8b008b;
            color: #fff;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 5px;
            font-size: 16px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
            transition: background-color 0.3s ease;
        }

        .home-button:hover {
            background-color: #ff69b4;
        }

        /* Container styling */
        .settings-container {
            margin-top: 50px;
            background-color: #1a1a1a;
            border-radius: 10px;
            padding: 20px;
            max-width: 500px;
            width: 100%;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.5);
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center; /* Sentrer innhold horisontalt */
        }

        .settings-container img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.5);
        }

        .settings-container p {
            margin: 10px 0;
            font-size: 18px;
        }

        .settings-container button {
            display: block;
            width: 80%;
            padding: 10px;
            margin: 15px 0;
            background-color: #8b008b;
            color: #fff;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .settings-container button:hover {
            background-color: #ff69b4;
            color: #1a1a1a;
        }

        /* Pop-up styling */
        .popup-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            visibility: hidden;
            opacity: 0;
            transition: visibility 0s, opacity 0.3s;
        }

        .popup-overlay.active {
            visibility: visible;
            opacity: 1;
        }

        .popup {
            background-color: #2b2b2b;
            border-radius: 10px;
            padding: 20px;
            max-width: 400px;
            width: 90%;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.5);
            text-align: center;
        }

        .popup h2 {
            color: #ff69b4;
        }

        .popup label {
            display: block;
            margin: 15px 0 5px;
            text-align: left;
        }

        .popup input {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #4b0082;
            border-radius: 5px;
            background-color: #333;
            color: #fff;
        }

        .popup button {
            width: 100%;
            padding: 10px;
            margin-top: 10px;
            background-color: #8b008b;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .popup button:hover {
            background-color: #ff69b4;
            color: #1a1a1a;
        }

        .notification {
            position: fixed;
            top: -50px; /* Starter utenfor synlig område */
            left: 50%;
            transform: translateX(-50%);
            background-color: #4b0082;
            color: #fff;
            padding: 15px 30px;
            border-radius: 5px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
            font-size: 16px;
            font-weight: bold;
            text-align: center;
            z-index: 2000;
            transition: top 0.5s ease, opacity 0.5s ease;
            opacity: 0; /* Starter usynlig */
        }
        .notification.show {
            top: 20px; /* Flytter ned til synlig område */
            opacity: 1; /* Gjør den synlig */
        }

    </style>
</head>
<body>
    <a href="dashboard.php" class="home-button"> ⬅️ tilbake</a>
    <div class="settings-container">
        <h1>Innstillinger</h1>
        <img src="<?php echo $profileImage; ?>" alt="Profilbilde">
        <p>Brukernavn: <?php echo htmlspecialchars($username); ?></p>

        <!-- Knappene som åpner pop-ups -->
        <button onclick="openPopup('usernamePopup')">Endre brukernavn</button>
        <button onclick="openPopup('passwordPopup')">Endre passord</button>
        <button onclick="openPopup('profilePicturePopup')">Endre profilbilde</button>
        <button onclick="openPopup('deletePopup')">Slett konto</button>
    </div>

    <!-- Pop-up for endring av profilbilde -->
    <div class="popup-overlay" id="profilePicturePopup">
        <div class="popup">
            <h2>Endre Profilbilde</h2>
            <form method="post" enctype="multipart/form-data">
                <label for="profile_picture">Velg nytt bilde:</label>
                <input type="file" name="profile_picture" id="profile_picture" accept="image/*" required>
                <button type="submit" name="update_profile_picture">Oppdater profilbilde</button>
                <button type="button" onclick="closePopup('profilePicturePopup')">Lukk</button>
            </form>
        </div>
    </div>


    <!-- Pop-ups -->
    <div class="popup-overlay" id="usernamePopup">
        <div class="popup">
            <h2>Endre brukernavn</h2>
            <form method="post">
                <label for="username">Nytt brukernavn:</label>
                <input type="text" name="username" id="username" required>
                <button type="submit" name="update_username">Oppdater brukernavn</button>
                <button type="button" onclick="closePopup('usernamePopup')">Lukk</button>
            </form>
        </div>
    </div>

    <div class="popup-overlay" id="passwordPopup">
        <div class="popup">
            <h2>Endre passord</h2>
            <form method="post">
                <label for="old_password">Gammelt passord:</label>
                <input type="password" name="old_password" id="old_password" required>
                <label for="new_password">Nytt passord:</label>
                <input type="password" name="new_password" id="new_password" required>
                <button type="submit" name="update_password">Oppdater passord</button>
                <button type="button" onclick="closePopup('passwordPopup')">Lukk</button>
            </form>
        </div>
    </div>

    <div class="popup-overlay" id="deletePopup">
        <div class="popup">
            <h2>Slett konto😔</h2>
            <form method="post">
                <label for="delete_confirmation">Skriv "jeg-er-certified-pedo" for å bekrefte:</label>
                <input type="text" name="delete_confirmation" id="delete_confirmation" required>
                <button type="submit" name="delete_account">Slett konto</button>
                <button type="button" onclick="closePopup('deletePopup')">Lukk</button>
            </form>
        </div>
    </div>
    <div id="notification" class="notification"></div>


    <script>
    // Funksjon for å vise notifikasjoner
    function showNotification(message) {
        const notification = document.getElementById('notification');
        notification.textContent = message; // Sett meldingen
        notification.classList.add('show'); // Vis notifikasjonen

        // Skjul notifikasjonen etter 3 sekunder
        setTimeout(() => {
            notification.classList.remove('show');
        }, 3000);
    }

    // Når siden lastes inn, vis eventuell melding fra PHP
    document.addEventListener('DOMContentLoaded', () => {
        const notificationMessage = '<?php echo $_SESSION["notification"] ?? ""; ?>';
        if (notificationMessage) {
            showNotification(notificationMessage);
            <?php unset($_SESSION["notification"]); ?>
        }
    });

    // Åpne pop-ups
    function openPopup(id) {
        document.getElementById(id).classList.add('active');
    }

    // Lukk pop-ups
    function closePopup(id) {
        document.getElementById(id).classList.remove('active');
    }


    </script>
</body>
</html>
