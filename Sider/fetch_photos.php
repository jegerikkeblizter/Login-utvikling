<?php
session_start();
require_once '../database/db_connect.php';

$userId = $_SESSION['user_id'] ?? null;
if (!$userId) {
    echo json_encode([]);
    exit;
}

$stmt = $conn->prepare("SELECT id, image FROM photos WHERE user_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$stmt->bind_result($photoId, $photoData);

$photos = [];
while ($stmt->fetch()) {
    $photos[] = [
        'id' => $photoId,
        'data' => base64_encode($photoData),
    ];
}

$stmt->close();
echo json_encode($photos);
?>
