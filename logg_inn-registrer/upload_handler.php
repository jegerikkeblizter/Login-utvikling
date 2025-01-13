<?php
session_start();
require_once '../database/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['photo'])) {
    $userId = $_SESSION['user_id'] ?? null;

    if (!$userId) {
        echo json_encode(['success' => false, 'error' => 'Ingen gyldig brukerøkt.']);
        exit;
    }

    $file = $_FILES['photo'];

    // Sjekk om filen ble lastet opp uten feil
    if ($file['error'] === UPLOAD_ERR_OK) {
        $imageData = file_get_contents($file['tmp_name']);

        // Sett inn bildet i databasen
        $stmt = $conn->prepare("INSERT INTO photos (user_id, image) VALUES (?, ?)");
        if (!$stmt) {
            echo json_encode(['success' => false, 'error' => 'Kunne ikke forberede spørringen: ' . $conn->error]);
            exit;
        }

        $stmt->bind_param("ib", $userId, $null);
        $stmt->send_long_data(1, $imageData);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Bilde lastet opp.']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Kunne ikke lagre bildet i databasen: ' . $stmt->error]);
        }

        $stmt->close();
    } else {
        // Håndter forskjellige filopplastingsfeil
        $errorMessage = match ($file['error']) {
            UPLOAD_ERR_INI_SIZE => "Filen overstiger maks størrelse (upload_max_filesize).",
            UPLOAD_ERR_FORM_SIZE => "Filen overstiger maks størrelse spesifisert i skjemaet.",
            UPLOAD_ERR_PARTIAL => "Filen ble bare delvis lastet opp.",
            UPLOAD_ERR_NO_FILE => "Ingen fil ble lastet opp.",
            UPLOAD_ERR_NO_TMP_DIR => "Midlertidig mappe mangler.",
            UPLOAD_ERR_CANT_WRITE => "Kunne ikke skrive filen til disken.",
            UPLOAD_ERR_EXTENSION => "En PHP-utvidelse stoppet filopplastingen.",
            default => "Ukjent feil oppstod under opplastingen.",
        };

        echo json_encode(['success' => false, 'error' => $errorMessage]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Ugyldig forespørsel.']);
}
