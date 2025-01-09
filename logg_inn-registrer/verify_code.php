<?php
session_start();

// Sjekk om koden er sendt via POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $enteredCode = $_POST['code'];

    // Sjekk om koden samsvarer med den som er lagret i session
    if ($enteredCode == $_SESSION['2fa_code']) {
        echo "2FA successful!";
        // Omdiriger til dashboard eller annen beskyttet side
        header("Location: dashboard.php");
        exit();
    } else {
        echo "Invalid 2FA code.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify 2FA Code</title>
</head>
<body>
    <div class="container">
        <h2>To-faktor autentisering</h2>
        <form method="POST" action="">
            <label for="code">Skriv inn den 6-sifrede koden sendt til e-posten din:</label>
            <input type="text" id="code" name="code" required>
            <button type="submit" class="verify-knapp">Verifiser</button>
        </form>
    </div>
</body>
</html>

