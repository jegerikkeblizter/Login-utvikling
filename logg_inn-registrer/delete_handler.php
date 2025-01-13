<?php
session_start();
require_once '../database/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['photo_ids'])) {
    $userId = $_SESSION['user_id'] ?? null;
    $photoIds = explode(',', $_POST['photo_ids']);

    if (!$userId || empty($photoIds)) {
        echo json_encode(['success' => false, 'error' => 'Ugyldig forespørsel.']);
        exit;
    }

    // Lag plasserholdere for spørringen
    $placeholders = implode(',', array_fill(0, count($photoIds), '?'));

    // Dynamisk spørring for å slette valgte bilder
    $sql = "DELETE FROM photos WHERE id IN ($placeholders) AND user_id = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        // Bind parametere
        $types = str_repeat('i', count($photoIds)) . 'i';
        $values = array_merge($photoIds, [$userId]);
        $stmt->bind_param($types, ...$values);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Bilder slettet.']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Kunne ikke slette bilder: ' . $stmt->error]);
        }

        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'error' => 'Kunne ikke forberede spørringen: ' . $conn->error]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Ugyldig forespørsel.']);
}
?>
