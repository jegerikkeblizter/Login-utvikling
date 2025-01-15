<?php
$lifetime = 15 * 24 * 60 * 60; // 15 dager
session_set_cookie_params($lifetime);
session_start();
require_once '../database/db_connect.php'; // Databasekobling

function isSessionValid() {
    return isset($_SESSION['authenticated'], $_SESSION['user_id'], $_SESSION['user_ip'], $_SESSION['user_agent'])
        && $_SESSION['authenticated'] === true
        && $_SESSION['user_ip'] === $_SERVER['REMOTE_ADDR']
        && $_SESSION['user_agent'] === $_SERVER['HTTP_USER_AGENT'];
}

if (!isSessionValid()) {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit();
}

// Sjekk session timeout
$timeout_duration = 15 * 24 * 60 * 60;
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout_duration) {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit();
}

// Oppdater siste aktivitetstid
$_SESSION['last_activity'] = time();

$userId = $_SESSION['user_id'];

// Hent brukerdata fra databasen
$stmt = $conn->prepare("SELECT username, profile_picture FROM userdata WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$stmt->bind_result($username, $profilePicture);
$stmt->fetch();
$stmt->close();

// Last opp bilde
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['photo'])) {
    $file = $_FILES['photo'];
    $imageData = file_get_contents($file['tmp_name']);
    $stmt = $conn->prepare("INSERT INTO photos (user_id, image) VALUES (?, ?)");
    $stmt->bind_param("ib", $userId, $null);
    $stmt->send_long_data(1, $imageData);
    $stmt->execute();
    $stmt->close();
}

// Slett valgte bilder
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_photos'])) {
    $photoIds = $_POST['photo_ids'] ?? [];
    if (!empty($photoIds)) {
        $placeholders = implode(',', array_fill(0, count($photoIds), '?'));
        $sql = "DELETE FROM photos WHERE id IN ($placeholders) AND user_id = ?";
        $stmt = $conn->prepare($sql);

        if ($stmt) {
            // Opprett en array med typer og verdier for bind_param
            $types = str_repeat('i', count($photoIds)) . 'i';
            $values = array_merge($photoIds, [$userId]);

            // Bind parametere dynamisk
            $stmt->bind_param($types, ...$values);

            // Utfør spørringen
            $stmt->execute();
            $stmt->close();
        } else {
            echo "Feil med spørringen: " . $conn->error;
        }
    }
}


// Hent brukerens bilder fra databasen
$photos = [];
$stmt = $conn->prepare("SELECT id, image FROM photos WHERE user_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$stmt->bind_result($photoId, $photoData);
while ($stmt->fetch()) {
    $photos[] = ['id' => $photoId, 'data' => $photoData];
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <style>
        /* Global styling */
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            min-height: 100vh; /* Ensure the body is at least the height of the viewport */
            background: radial-gradient(circle, #4b0082, #1a1a1a, #8b008b);
            background-size: 200% 200%;
            animation: moveSmoke 700s infinite;
            color: #fff;
            display: flex;
            flex-direction: column; /* To stack content vertically */
            z-index: -1; /* Ensure the background is behind all content */
        }


        @keyframes moveSmoke {
            0% {
                background-position: 0% 50%;
            }
            50% {
                background-position: 700% 1400%;
            }
            100% {
                background-position: 0% 50%;
            }
        }

        /* Side menu styling */
        .side-menu {
            position: fixed;
            top: 0;
            left: 0;
            width: 220px;
            height: 100%;
            background-color: #1a1a1a;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.5);
            display: flex;
            flex-direction: column;
            padding: 20px;
        }

        .side-menu a {
            text-decoration: none;
            color: #ddd;
            font-size: 16px;
            margin-bottom: 15px;
            padding: 10px;
            border-radius: 5px;
            transition: all 0.3s ease;
        }

        .side-menu a:hover {
            background-color: #4b0082;
            color: #fff;
        }

        .side-menu .explore {
            color: #fff;
        }

        .side-menu .explore:hover {
            background-color: #4b0082;
        }

        .logout {
            margin-top: 350px;
            text-align: center;
        }

        /* Main content styling */
        .content {
            margin-left: 240px;
            padding: 40px;
            flex: 1;
            transition: margin-right 0.3s ease;
        }

        .content.shifted {
            margin-right: 300px;
        }

        .profile-picture {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            margin: 20px auto;
            display: block;
        }

        .username {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            margin-top: 10px;
        }

        .photo-gallery {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 15px;
            margin-top: 35px;
        }

        .photo-gallery img {
            max-width: 200px;
            border-radius: 5px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
        }

        /* Edit profile panel */
        .edit-profile-panel {
            position: fixed;
            top: 0;
            right: -350px;
            width: 300px;
            height: 100%;
            background-color: #1a1a1a;
            box-shadow: -2px 0 10px rgba(0, 0, 0, 0.5);
            color: #ddd;
            padding: 20px;
            transition: all 0.3s ease;
            overflow-y: auto;
        }

        .edit-profile-panel.active {
            right: 0;
        }

        .edit-profile-panel h3 {
            color: #ff69b4;
        }

        .close-panel {
            background-color: #ff69b4;
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .close-panel:hover {
            background-color: #d63d7a;
        }

        button {
            background-color: #8b008b;
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
        }

        button:hover {
            background-color: #ff69b4;
            color: #1a1a1a;
        }

        /* Responsive styling */
@media (max-width: 768px) {
    .side-menu {
        width: 60px;
        padding: 10px;
    }

    .side-menu a {
        font-size: 12px;
        text-align: center;
        padding: 5px;
    }

    .side-menu .logout {
        margin-top: auto;
    }

    .content {
        margin-left: 80px; /* Adjust to account for smaller menu */
        padding: 20px;
    }

    .profile-picture {
        width: 100px;
        height: 100px;
    }

    .photo-gallery img {
        max-width: 150px; /* Reduce image size for smaller screens */
    }

    .edit-profile-panel {
        width: 250px;
    }

    .edit-profile-panel.active {
        right: 0;
    }
}

@media (max-width: 480px) {
    .side-menu {
        width: 50px;
    }

    .side-menu a {
        font-size: 10px;
        padding: 3px;
    }

    .content {
        margin-left: 60px;
        padding: 15px;
    }

    .profile-picture {
        width: 80px;
        height: 80px;
    }

    .photo-gallery img {
        max-width: 120px;
    }

    button {
        padding: 8px 15px;
        font-size: 14px;
    }

    .edit-profile-panel {
        width: 200px;
    }

    .edit-profile-panel h3 {
        font-size: 16px;
    }

    .close-panel {
        font-size: 14px;
    }
}



    </style>
</head>
<body>
    <!-- Side menu -->
    <div class="side-menu">
        <a href="explore.php" class="explore">Utforsk</a>
        <a href="#" onclick="openEditPanel()">Last opp</a>
        <a href="settings.php">Innstillinger</a>
        <a href="terms.php">Vilkår</a>
        <div class="logout">
            <form action="logout.php" method="post">
                <button type="submit">Logg ut</button>
            </form>
        </div>
    </div>

    <!-- Main content -->
    <div class="content" id="mainContent">
        <img class="profile-picture" src="<?php echo $profilePicture ? 'data:image/png;base64,' . base64_encode($profilePicture) : 'default.jpg'; ?>" alt="Profilbilde">
        <div class="username"><?php echo htmlspecialchars($username); ?></div>

        <div class="photo-gallery" id="photoGallery">
            <?php foreach ($photos as $photo): ?>
                <div class="photo-item" data-id="<?php echo $photo['id']; ?>">
                    <img
                        src="data:image/png;base64,<?php echo base64_encode($photo['data']); ?>"
                        alt="Bilde"
                        onclick="handleImageClick(this)">
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Edit Profile Panel -->
    <div class="edit-profile-panel" id="editProfilePanel">
        <h3>Last opp</h3>

        <!-- Opplastingsskjema -->
        <form id="uploadForm" enctype="multipart/form-data">
            <input type="file" name="photo" accept="image/*" required>
            <button type="submit">Last opp bilde</button>
        </form>

        <div id="uploadMessage"></div>

        <!-- Sletting -->
        <form id="deleteForm" method="post">
            <input type="hidden" id="selectedPhotoIds" name="photo_ids">
            <button type="submit" id="deleteButton" disabled>Slett valgte bilder</button>
        </form>

        <button class="close-panel" onclick="closeEditPanel()">Lukk</button>
    </div>

    <!-- JavaScript -->
    <script>
document.addEventListener('DOMContentLoaded', function () {
    const uploadForm = document.querySelector('#uploadForm');
    const deleteForm = document.querySelector('#deleteForm');
    const messageBox = document.querySelector('#uploadMessage');
    const photoGallery = document.querySelector('#photoGallery');
    const deleteButton = document.querySelector('#deleteButton');
    const selectedPhotoIdsInput = document.querySelector('#selectedPhotoIds');
    const selectedPhotos = new Set(); // Holder oversikt over valgte bilder
    let isEditPanelOpen = false; // Kontroll for om høyre panel er åpent

    // Åpne redigeringspanelet
    window.openEditPanel = function () {
        document.getElementById('editProfilePanel').classList.add('active');
        document.getElementById('mainContent').classList.add('shifted');
        enablePhotoSelection();
        isEditPanelOpen = true;
    };

    // Lukk redigeringspanelet
    window.closeEditPanel = function () {
        document.getElementById('editProfilePanel').classList.remove('active');
        document.getElementById('mainContent').classList.remove('shifted');
        disablePhotoSelection();
        isEditPanelOpen = false;
    };

    // Aktiver valg av bilder
    function enablePhotoSelection() {
        const images = document.querySelectorAll('.photo-item img');
        images.forEach(image => {
            image.style.cursor = 'pointer';
            image.addEventListener('click', handleImageClick);
        });
    }

    // Deaktiver valg av bilder
    function disablePhotoSelection() {
        const images = document.querySelectorAll('.photo-item img');
        images.forEach(image => {
            image.style.cursor = 'default';
            image.removeEventListener('click', handleImageClick);
        });
        selectedPhotos.clear(); // Tøm valgte bilder
        updateDeleteButton();
    }

    // Håndter bildeklikk
    function handleImageClick(event) {
        if (!isEditPanelOpen) return; // Bare tillat valg hvis høyre panel er åpent

        const image = event.target;
        const photoId = image.parentElement.getAttribute('data-id');

        if (selectedPhotos.has(photoId)) {
            selectedPhotos.delete(photoId);
            image.style.border = 'none';
        } else {
            selectedPhotos.add(photoId);
            image.style.border = '2px solid #ff69b4';
        }

        updateDeleteButton();
    }

    // Oppdater slett-knappen
    function updateDeleteButton() {
        if (selectedPhotos.size > 0) {
            deleteButton.disabled = false;
            selectedPhotoIdsInput.value = Array.from(selectedPhotos).join(',');
        } else {
            deleteButton.disabled = true;
            selectedPhotoIdsInput.value = '';
        }
    }

    // Last opp bilde via AJAX og oppdater galleri
    uploadForm.addEventListener('submit', function (event) {
        event.preventDefault(); // Forhindre sideoppdatering

        const formData = new FormData(uploadForm);

        fetch('upload_handler.php', {
            method: 'POST',
            body: formData,
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    messageBox.textContent = "Bilde lastet opp!";
                    messageBox.style.color = "green";

                    // Oppdater galleriet dynamisk
                    fetchGallery();
                } else {
                    messageBox.textContent = data.error || "En feil oppstod.";
                    messageBox.style.color = "red";
                }
            })
            .catch(error => {
                messageBox.textContent = "En feil oppstod under opplastingen.";
                messageBox.style.color = "red";
                console.error(error);
            });
    });

    // Slett valgte bilder via AJAX og oppdater galleri
    deleteForm.addEventListener('submit', function (event) {
        event.preventDefault(); // Forhindre sideoppdatering

        const formData = new FormData(deleteForm);

        fetch('delete_handler.php', {
            method: 'POST',
            body: formData,
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Fjern slettede bilder fra galleriet
                    selectedPhotos.forEach(photoId => {
                        const photoItem = document.querySelector(`.photo-item[data-id="${photoId}"]`);
                        if (photoItem) {
                            photoItem.remove();
                        }
                    });
                    selectedPhotos.clear(); // Tøm valgte bilder
                    updateDeleteButton();
                } else {
                    alert(data.error || "En feil oppstod under slettingen.");
                }
            })
            .catch(error => {
                console.error("En feil oppstod under slettingen:", error);
            });
    });

    // Hent og oppdater galleri dynamisk
    function fetchGallery() {
        fetch('fetch_photos.php')
            .then(response => response.json())
            .then(photos => {
                photoGallery.innerHTML = ''; // Tøm eksisterende galleri
                photos.forEach(photo => {
                    const photoItem = document.createElement('div');
                    photoItem.classList.add('photo-item');
                    photoItem.setAttribute('data-id', photo.id);
                    photoItem.innerHTML = `
                        <img src="data:image/png;base64,${photo.data}" alt="Bilde" onclick="handleImageClick(this)">
                    `;
                    photoGallery.appendChild(photoItem);
                });
                enablePhotoSelection(); // Aktiver valg for nye bilder
            })
            .catch(error => {
                console.error("En feil oppstod under henting av bilder:", error);
            });
    }
});
    </script>
</body>
</html>