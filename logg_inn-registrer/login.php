<?php
include '../database/db_connect.php';

$message = "";
$toastClass = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, password FROM userdata WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($user_id, $db_password);
        $stmt->fetch();

        if (password_verify($password, $db_password)) {
            session_start();
            $session_id = session_create_id(); // Generer en ny sesjons-ID
            $_SESSION['user_id'] = $user_id;
            $_SESSION['email'] = $email;

            // Oppdater brukerens session_id i databasen
            $updateSessionStmt = $conn->prepare("UPDATE userdata SET session_id = ? WHERE id = ?");
            $updateSessionStmt->bind_param("si", $session_id, $user_id);
            $updateSessionStmt->execute();
            $updateSessionStmt->close();

            // Send brukeren til send_2fa_code.php
            header("Location: send_2fa_code.php");
            exit();
        } else {
            $message = "Feil passord";
            $toastClass = "bg-danger";
        }

    } else {
        $message = "Email ikke funnet";
        $toastClass = "bg-warning";
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Side</title>
</head>
<body>
    <div class="container">
        <?php if ($message): ?>
            <div class="toast <?php echo $toastClass; ?>" role="alert">
                <?php echo $message; ?>
                <button onclick="this.parentElement.style.display='none'">×</button>
            </div>
        <?php endif; ?>
        <form method="post">
            <h5>Login til brukeren din</h5>
            <div class="credentials">
                <div class="input-gruppe">
                    <label for="email">Email</label>
                    <input type="text" name="email" id="email" required>
                </div>

                <div class="input-gruppe">
                    <label for="password">Passord</label>
                    <input type="password" name="password" id="password" required>
                </div>
            </div>

            <button type="submit" class="login-knapp">Login</button>
            <p>Har du ikke bruker? <a class="tilbake" href="./register.php">Registrer deg</a></p>
        </form>
    </div>
</body>
</html>
