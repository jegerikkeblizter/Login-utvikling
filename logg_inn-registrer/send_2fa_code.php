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
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
            color: #333;
        }
        .email-container {
            max-width: 600px;
            margin: 20px auto;
            background: #ffffff;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }
        .header {
            text-align: center;
            padding: 20px;
            background: #007bff;
            color: white;
            border-radius: 8px 8px 0 0;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .content {
            padding: 20px;
            text-align: center;
        }
        .code-box {
            display: inline-block;
            margin: 20px 0;
            padding: 15px 30px;
            font-size: 28px;
            font-weight: bold;
            color: #007bff;
            background: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class='email-container'>
        <div class='header'>
            <h1>Din 2FA-kode</h1>
        </div>
        <div class='content'>
            <p>Hei,</p>
            <p>Her er din tofaktorautentiseringskode for å logge inn på kontoen din:</p>
            <div class='code-box'>$code</div>
            <p>Koden er gyldig i 5 minutter.</p>
            <p>Hvis du ikke prøvde å logge inn, vennligst kontakt oss umiddelbart.</p>
        </div>
        <div class='footer'>
            &copy; 2025 PhotoShare. Alle rettigheter forbeholdt.
        </div>
    </div>
</body>
</html>
";

$plainTextContent = "Din 2FA-kode er: $code\nKoden er gyldig i 5 minutter.";

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
