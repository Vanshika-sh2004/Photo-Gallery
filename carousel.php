<?php
session_start();
include "db.php";

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$selectedFile = isset($_GET['file']) ? $_GET['file'] : '';

if (!$id || empty($selectedFile)) die("Invalid request.");

$stmt = $conn->prepare("SELECT name, folder_name, financial_year FROM events WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) die("Event not found.");

$event = $result->fetch_assoc();
$eventName = $event['name'];
$folder = "uploads/" . $event['financial_year'] . "/" . $event['folder_name'];

$mediaFiles = [];
$allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'mp4', 'webm', 'ogg', 'avi', 'wmv'];
if (is_dir($folder)) {
    $allFiles = scandir($folder);
    foreach ($allFiles as $file) {
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        if (in_array($ext, $allowedExtensions)) {
            $mediaFiles[] = $file;
        }
    }
}

$startIndex = array_search($selectedFile, $mediaFiles);
if ($startIndex === false) $startIndex = 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($eventName); ?> - Carousel</title>
    <style>
        body {
            margin: 0;
            background: #000;
            font-family: 'Segoe UI', sans-serif;
            overflow: hidden;
            color: white;
        }

        .top-controls {
            position: absolute;
            top:0;
            left: 0;
            width: 100%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 30px;
            box-sizing: border-box;
            z-index: 10;
            background: rgba(0, 0, 0, 0.6);
        }

      #closeBtn {
    background: red;
    color: white;
    padding: 10px 15px;
    font-size: 1.2rem;
    border: none;
    cursor: pointer;
    text-decoration: none;
    box-shadow: 0 0 10px rgba(255, 0, 0, 0.7); /* red shadow */
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    border-radius: 8px;
}

#closeBtn:hover {
    transform: scale(1.1);
    box-shadow: 0 0 15px rgba(255, 0, 0, 0.9);
}

#downloadBtn {
    background: navy;
    color: white;
    border: none;
    padding: 10px 20px;
    cursor: pointer;
    font-size: 1rem;
    box-shadow: 0 0 10px rgba(0, 0, 128, 0.7); /* navy blue shadow */
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    border-radius: 8px;
    text-decoration: none;
    display: inline-block;
}

#downloadBtn:hover {
    transform: scale(1.1);
    box-shadow: 0 0 15px rgba(0, 0, 128, 0.9);
}

.media-count {
    color: white;
    font-size: 1rem;
    text-align: center;
    flex-grow: 1;
}


        .carousel-container {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            flex-direction: column;
            position: relative;
        }

        .carousel-media {
            max-width: 90%;
            max-height: 90vh;
            box-shadow: 0 0 30px rgba(255, 255, 255, 0.3);
            display: block;
            margin: 0 auto;
        }

        .nav-button {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            font-size: 3rem;
            color: white;
            background: rgba(255,255,255,0.1);
            border: none;
            padding: 12px 18px;
            cursor: pointer;
            border-radius: 50%;
            z-index: 10;
        }

        .nav-button:hover {
            background: rgba(255,255,255,0.3);
        }

        #prevBtn { left: 5%; }
        #nextBtn { right: 5%; }
    </style>
</head>
<body>

    <!-- ✅ Top Controls -->
    <div class="top-controls">
        <a id="closeBtn" href="event.php?id=<?php echo $id; ?>&file=<?php echo urlencode($selectedFile); ?>">✖</a>
        <div id="counter" class="media-count"></div>
        <button id="downloadBtn">Download</button>
    </div>

    <!-- ✅ Media Viewer -->
    <div class="carousel-container">
        <button id="prevBtn" class="nav-button">&#8249;</button>
        <div id="mediaDisplay"></div>
        <button id="nextBtn" class="nav-button">&#8250;</button>
    </div>

    <script>
        const mediaFiles = <?php echo json_encode($mediaFiles); ?>;
        let currentIndex = <?php echo $startIndex; ?>;
        const folder = <?php echo json_encode($folder); ?>;
        const eventId = <?php echo $id; ?>;

        const display = document.getElementById("mediaDisplay");
        const downloadBtn = document.getElementById("downloadBtn");
        const counter = document.getElementById("counter");

        function updateMedia() {
            const file = mediaFiles[currentIndex];
            const ext = file.split('.').pop().toLowerCase();
            const fullPath = folder + "/" + file;

            // Set media
            if (['mp4', 'webm', 'ogg', 'avi', 'wmv'].includes(ext)) {
                display.innerHTML = `
                    <video class="carousel-media" controls autoplay>
                        <source src="${fullPath}" type="video/${ext === 'wmv' ? 'x-ms-wmv' : ext}">
                        Your browser does not support the video tag.
                    </video>`;
            } else {
                display.innerHTML = `<img class="carousel-media" src="${fullPath}" alt="">`;
            }

            // Update download
            downloadBtn.onclick = () => {
                const link = document.createElement('a');
                link.href = fullPath;
                link.download = file;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            };

            // Update counter
            counter.textContent = `${currentIndex + 1} / ${mediaFiles.length}`;

            // Update close link
            document.getElementById("closeBtn").href = `event.php?id=${eventId}&file=${encodeURIComponent(file)}`;
        }

        function showPrev() {
            currentIndex = (currentIndex - 1 + mediaFiles.length) % mediaFiles.length;
            updateMedia();
        }

        function showNext() {
            currentIndex = (currentIndex + 1) % mediaFiles.length;
            updateMedia();
        }

        document.getElementById("prevBtn").addEventListener("click", showPrev);
        document.getElementById("nextBtn").addEventListener("click", showNext);

        // ✅ Arrow keys navigation
        document.addEventListener("keydown", (e) => {
            if (e.key === "ArrowLeft") showPrev();
            else if (e.key === "ArrowRight") showNext();
        });

        window.onload = updateMedia;
    </script>
</body>
</html>
