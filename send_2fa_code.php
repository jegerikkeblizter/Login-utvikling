<?php
session_start();
require 'vendor/autoload.php'; // SendGrid SDK

// Konfigurasjon for database
include '../database/db_connect.php';

// Sjekk om bruker er logget inn og har en session
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Hent e-post fra session (som ble satt ved innlogging)
$email = $_SESSION['email'];

// Generer 6-sifret kode
$code = random_int(100000, 999999);

// Lagre koden i session for senere verifisering
$_SESSION['2fa_code'] = $code;

// Send e-posten med SendGrid
$sendgridEmail = new \SendGrid\Mail\Mail();
$sendgridEmail->setFrom("Mathiashansen2007@gmail.com", "Mathias");
$sendgridEmail->setSubject("Your Two-Factor Authentication Code");
$sendgridEmail->addTo($email, "User");
$sendgridEmail->addContent("text/plain", "Your 2FA code is: $code");

$sendgrid = new \SendGrid('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');

try {
    $response = $sendgrid->send($sendgridEmail);
    // Omdiriger til verify_code.php for å angi koden
    header("Location: verify_code.php");
    exit();
} catch (Exception $e) {
    echo 'Error sending email: '. $e->getMessage();
}
?>
