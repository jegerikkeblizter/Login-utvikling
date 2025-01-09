<?php
session_start();
require 'vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

include '../database/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$email = $_SESSION['email'];

$code = random_int(100000, 999999);

$_SESSION['2fa_code'] = $code;

$sendgridEmail = new \SendGrid\Mail\Mail();
$sendgridEmail->setFrom("Mathiashansen2007@gmail.com", "Mathias");
$sendgridEmail->setSubject("Your Two-Factor Authentication Code");
$sendgridEmail->addTo($email, "User");
$sendgridEmail->addContent("text/plain", "Your 2FA code is: $code");

$sendgridApiKey = $_ENV['SENDGRID_API_KEY'];
$sendgrid = new \SendGrid($sendgridApiKey);

try {
    $response = $sendgrid->send($sendgridEmail);
    header("Location: verify_code.php");
    exit();
} catch (Exception $e) {
    echo 'Error sending email: '. $e->getMessage();
}
?>
