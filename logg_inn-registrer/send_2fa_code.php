<?php
session_start();
include '../database/db_connect.php';

// Include Composer autoloader
require 'vendor/autoload.php';

use SendGrid\Mail\Mail;

if (!isset($_SESSION['user_id'], $_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$email = $_SESSION['email'];

// Generate 2FA code
$code = random_int(100000, 999999);
$_SESSION['2fa_code'] = $code;
$_SESSION['2fa_time'] = time();

// Debugging output
echo "2FA Code: " . $_SESSION['2fa_code'] . "<br>";

// Fetch the API key from the database
$stmt = $conn->prepare("SELECT api_key FROM api_keys WHERE key_name = ?");
if (!$stmt) {
    die("SQL error: " . $conn->error);
}
$key_name = "SENDGRID_API_KEY";
$stmt->bind_param("s", $key_name);
$stmt->execute();
$stmt->bind_result($api_key);
$stmt->fetch();
$stmt->close();

if (!$api_key) {
    die("SENDGRID_API_KEY not found in database.");
}

// Create and send the email
$emailMessage = new Mail();
$emailMessage->setFrom("Mathiashansen2007@gmail.com", "PhotoShare");
$emailMessage->setSubject("Din 2FA-kode");
$emailMessage->addTo($email, "User");

// Email content
$htmlContent = "
<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; }
        .code { font-size: 1.5em; color: #007bff; }
    </style>
</head>
<body>
    <h1>Din 2FA-kode</h1>
    <p>Her er din tofaktorautentiseringskode:</p>
    <div class='code'>$code</div>
    <p>Koden er gyldig i 5 minutter.</p>
</body>
</html>
";

$plainTextContent = "Din 2FA-kode er: $code\nKoden er gyldig i 5 minutter.";

// Add content to email
$emailMessage->addContent("text/plain", $plainTextContent);
$emailMessage->addContent("text/html", $htmlContent);

// Send email
$sendgrid = new \SendGrid($api_key);

try {
    $response = $sendgrid->send($emailMessage);
    if ($response->statusCode() >= 200 && $response->statusCode() < 300) {
        header("Location: verify_code.php");
        exit();
    } else {
        echo "Kunne ikke sende 2FA-koden. Feilkode: " . $response->statusCode();
    }
} catch (Exception $e) {
    echo "Feil ved sending av e-post: " . $e->getMessage();
}
?>
