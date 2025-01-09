<?php
include '../database/db_connect.php';

$message = "";
$toastColor = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $session_id = session_create_id(); // Generer en unik sesjons-ID

    // Sjekk om e-post allerede finnes
    $checkEmailStmt = $conn->prepare("SELECT email FROM userdata WHERE email = ?");
    $checkEmailStmt->bind_param("s", $email);
    $checkEmailStmt->execute();
    $checkEmailStmt->store_result();

    if ($checkEmailStmt->num_rows > 0) {
        $message = "Email ID already exists";
        $toastColor = "#007bff"; // Primærfarge
    } else {
        // Legg til ny bruker
        $stmt = $conn->prepare("INSERT INTO userdata (username, email, password, session_id) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $username, $email, $hashed_password, $session_id);

        if ($stmt->execute()) {
            $message = "Account created successfully";
            $toastColor = "#28a745"; // Suksessfarge
        } else {
            $message = "Error: " . $stmt->error;
            $toastColor = "#dc3545"; // Feilfarge
        }

        $stmt->close();
    }

    $checkEmailStmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasjon</title>
</head>
<body>
    <div class="container">
        <?php if ($message): ?>
            <div class="toast" style="background-color: <?php echo $toastColor; ?>;">
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
                <label for="email">Email</label>
                <input type="email" name="email" id="email" required>
            </div>

            <div class="input-gruppe">
                <label for="password">Passord</label>
                <input type="password" name="password" id="password" required>
            </div>

            <button type="submit" class="register-knapp">Lag Bruker</button>
            <p>Har du en bruker? <a class="tilbake" href="./login.php">Login</a></p>
        </form>
    </div>
</body>
</html>
