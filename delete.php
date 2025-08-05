
 <?php
session_start();
include "db.php";

$selectedYear = $_GET['year'] ?? '';
$selectedEvent = $_GET['event'] ?? '';

if ($_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

$successMsg = "";
$warningMsg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_images'])) {
    $year = $_POST['financial_year'];
    $eventId = $_POST['event_id'];
    $imagesToDelete = $_POST['images'] ?? [];

    $event = $conn->query("SELECT * FROM events WHERE id = $eventId")->fetch_assoc();
    $folder = "uploads/$year/" . $event['folder_name'];

    foreach ($imagesToDelete as $img) {
        $path = "$folder/" . basename($img);
        if (file_exists($path)) unlink($path);
    }

    if (in_array($event['thumbnail'], $imagesToDelete)) {
        $_SESSION['deleted_warning'] = "⚠️ Thumbnail image was deleted.";
        $conn->query("UPDATE events SET thumbnail = NULL WHERE id = $eventId");

     
        if (!empty($_POST['new_thumbnail'])) {
            $newThumb = basename($_POST['new_thumbnail']);
            $conn->query("UPDATE events SET thumbnail = '$newThumb' WHERE id = $eventId");
        }
    }

    $_SESSION['deleted_success'] = "✅ Selected images deleted successfully.";
    header("Location: delete.php?year=" . urlencode($year) . "&event=" . urlencode($eventId));
    exit;
}

if (isset($_SESSION['deleted_success'])) {
    $successMsg = $_SESSION['deleted_success'];
    unset($_SESSION['deleted_success']);
}
if (isset($_SESSION['deleted_warning'])) {
    $warningMsg = $_SESSION['deleted_warning'];
    unset($_SESSION['deleted_warning']);
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Delete Event Images</title>
    <style>
    body {
        margin: 0;
        padding: 0;
        font-family: 'Segoe UI', sans-serif;
        background: linear-gradient(135deg, #ffe4f0, #f06292);
        background-attachment: fixed;
        animation: fadeInBg 1.5s ease;
    }

    .container {
        max-width: 900px;
        margin: 80px auto;
        background: #fff0f5;
        padding: 35px;
        border-radius: 20px;
        box-shadow: 0 12px 30px rgba(0, 0, 0, 0.25);
        animation: fadeInBox 1s ease-in-out;
    }

    h2 {
        text-align: center;
        margin-bottom: 25px;
        color: #880e4f;
        font-weight: bold;
        font-size: 1.8rem;
    }

    .form-label {
        font-size: 1.15rem;
        font-weight: bold;
        display: block;
        margin-bottom: 8px;
        color: #6a1b4d;
    }

    .img-box {
        width: 140px;
        height: 140px;
        overflow: hidden;
        border: 3px solid transparent;
        border-radius: 12px;
        margin: 10px;
        position: relative;
        cursor: pointer;
        transition: transform 0.3s ease, border-color 0.3s ease;
    }

    .img-box:hover {
        transform: scale(1.05);
        border-color: #ec407a;
    }

    .img-box img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 10px;
    }

    .img-box input[type="checkbox"] {
        position: absolute;
        top: 10px;
        left: 10px;
        transform: scale(1.4);
        accent-color: #ec407a;
    }

    .btn {
        padding: 10px 22px;
        border: none;
        font-weight: bold;
        border-radius: 8px;
        cursor: pointer;
        font-size: 16px;
        transition: all 0.3s ease;
    }

    .btn-purple {
        background-color: #ec407a;
        color: white;
    }

    .btn-purple:hover {
        background-color: #d81b60;
    }

    .btn-outline {
        background: transparent;
        color: #880e4f;
        border: 2px solid #d81b60;
    }
     .heading-box {
            background-color: #80c0c0;;
            max-width: 850px;
            margin: 4rem auto 40px auto;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.4);
            padding:0.1rem
        }

        .gallery-title {
            color:white;
            text-align: center;
            margin-top: 5px;
            margin-bottom: 10px;
            font-size: 2rem;
            font-weight: bold;
            text-shadow: 2px 2px 5px rgba(0,0,0,0.6);
        }

    .btn-outline:hover {
        background: #f8bbd0;
    }

   .toast-container {
    position: fixed;
    top: 1rem;
    right: 1rem;
    z-index: 999;
    display: flex;
    flex-direction: column;
    gap: 12px; /* ⬅️ Adds spacing between toasts */
}

    .toast {
        padding: 14px 20px;
        border-radius: 8px;
        background-color: #ec407a;
        color: white;
        font-weight: bold;
        margin-bottom: 12px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25);
    }

    .toast-warning {
        background-color: #ffeb3b;
        color: #5c005c;
    }

    select, input[type="file"], .form-select {
        width: 100%;
        padding: 10px 14px;
        border-radius: 8px;
        border: 2px solid #f48fb1;
        font-size: 15px;
        margin-bottom: 18px;
        background: #fff7fb;
        transition: border 0.3s ease;
    }

    select:focus, input[type="file"]:focus {
        outline: none;
        border-color: #ec407a;
        box-shadow: 0 0 0 3px rgba(236, 64, 122, 0.2);
    }

    #imageGrid {
        display: flex;
        flex-wrap: wrap;
        justify-content: flex-start;
    }

    .mb-3 {
        margin-bottom: 1.5rem;
    }

    @keyframes fadeInBox {
        from { opacity: 0; transform: translateY(40px); }
        to { opacity: 1; transform: translateY(0); }
    }

    @keyframes fadeInBg {
        from { opacity: 0; }
        to { opacity: 1; }
    }

     .img-box input[type="radio"] {
            position: absolute;
            bottom: 10px;
            left: 10px;
            transform: scale(1.2);
            accent-color: #ff4081;
        }


    </style>
</head>
<body>
    <div class="heading-box text-center text-white py-3 px-4 shadow">
        <h1 class="gallery-title m-0">
            📸 फोटो गैलरी / Photo Gallery 📸
        </h1>
    </div>
    <div class="container">
        <h2>🧹 इवेंट की तस्वीरें हटाएँ / Delete Event Images 🧹</h2>

        <?php if (!empty($successMsg) || !empty($warningMsg)): ?>
        <div class="toast-container">
            <?php if (!empty($successMsg)): ?>
                <div class="toast"><?php echo $successMsg; ?></div>
            <?php endif; ?>
            <?php if (!empty($warningMsg)): ?>
                <div class="toast toast-warning"><?php echo $warningMsg; ?></div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label">वर्ष / Year</label>
                <select name="financial_year" id="financialYear" class="form-control" required>
                    <option value="">वर्ष चुनें / Select Year</option>
                    <?php
                    $result = $conn->query("SELECT DISTINCT financial_year FROM events ORDER BY financial_year DESC");
                    while ($row = $result->fetch_assoc()) {
                        $year = $row['financial_year'];
                        if (preg_match('/^\d{4}$/', $year)) {
                            echo "<option value='$year' " . ($year == $selectedYear ? 'selected' : '') . ">$year</option>";
                        }
                    }
                    ?>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">इवेंट चुनें / Select Event</label>
                <select name="event_id" id="eventSelect" required></select>
            </div>

            <div id="imageGrid"></div>

            <div id="newThumbnailSection" style="display:none; margin-top: 20px;">
                <h3 style="color:#880e4f; font-weight:bold;">📌 कृपया एक नई कवर फोटो चुनें / Please select a new cover photo</h3>
                <p style="color:#444;">कृपया नीचे दी गई छवियों में से एक को नए थंबनेल के रूप में चुनें। / Please choose a new image to be set as the cover photo.</p>
            </div>

            <div class="d-flex justify-content-between mt-4" style="margin-top: 25px;">
                <button type="button" class="btn btn-purple" onclick="goToEventGallery()">🖼️ Back to Gallery</button>
                <button type="submit" name="delete_images" class="btn btn-purple">🗑️ डिलीट करें / Delete</button>
            </div>
        </form>
    </div>
    <script>
      
window.addEventListener('DOMContentLoaded', () => {
    const selectedYear = '<?php echo $selectedYear; ?>';
    const selectedEvent = '<?php echo $selectedEvent; ?>';

    if (selectedYear) {
        const yearSelect = document.getElementById('financialYear');
        const eventSelect = document.getElementById('eventSelect');
        yearSelect.value = selectedYear;

        // Fetch events for selectedYear
        fetch("get_events.php?year=" + selectedYear)
            .then(res => res.json())
            .then(data => {
                eventSelect.innerHTML = '<option value="">Select Event</option>';
                data.forEach(ev => {
                    const opt = document.createElement("option");
                    opt.value = ev.id;
                    opt.textContent = ev.name;
                    if (ev.id == selectedEvent) opt.selected = true;
                    eventSelect.appendChild(opt);
                });

                // If event selected, trigger change to load images
                if (selectedEvent) {
                    eventSelect.dispatchEvent(new Event('change'));
                }
            });
    }
});

document.getElementById("financialYear").addEventListener("change", function () {
    const year = this.value;
    const eventSelect = document.getElementById("eventSelect");
    eventSelect.innerHTML = '<option value="">Loading...</option>';

    fetch("get_events.php?year=" + year)
        .then(res => res.json())
        .then(data => {
            eventSelect.innerHTML = '<option value="">Select Event</option>';
            data.forEach(ev => {
                const opt = document.createElement("option");
                opt.value = ev.id;
                opt.textContent = ev.name;
                eventSelect.appendChild(opt);
            });

         
            document.getElementById("imageGrid").innerHTML = "";
            document.getElementById("newThumbnailSection").style.display = "none";
        });
});

document.getElementById("eventSelect").addEventListener("change", function () {
    const eventId = this.value;
    const year = document.getElementById("financialYear").value;
    const grid = document.getElementById("imageGrid");
    grid.innerHTML = "<p>Loading images...</p>";

    fetch("get_event_images.php?id=" + eventId)
        .then(res => res.json())
        .then(data => {
            const images = data.images;
            const thumbnail = data.thumbnail;
            grid.innerHTML = "";
            document.getElementById("newThumbnailSection").style.display = "none";

            images.forEach(img => {
                const ext = img.name.split('.').pop().toLowerCase();
              
                if (['mp4', 'webm', 'ogg'].includes(ext)) return;

                const wrapper = document.createElement("label");
                wrapper.className = "img-box";

                const checkbox = document.createElement("input");
                checkbox.type = "checkbox";
                checkbox.name = "images[]";
                checkbox.value = img.name;
                checkbox.onclick = () => confirmThumb(checkbox, img.name, thumbnail);

                const image = document.createElement("img");
                image.src = `uploads/${img.year}/${img.folder}/${img.name}`;

                wrapper.appendChild(checkbox);
                wrapper.appendChild(image);
                grid.appendChild(wrapper);
            });

            const newThumbGrid = document.getElementById("newThumbGrid");
            if (newThumbGrid) newThumbGrid.innerHTML = "";
        });
});

function confirmThumb(checkbox, filename, thumbnail) {
    if (filename === thumbnail && checkbox.checked) {
        const confirmed = confirm("⚠️ यह इस कार्यक्रम की कवर फोटो (थंबनेल) है। क्या आप इसे वाकई हटाना चाहते हैं? / This is the cover photo (thumbnail) of this event. Are you sure you want to delete it?");
        if (!confirmed) {
            checkbox.checked = false;
        } else {
            document.getElementById("newThumbnailSection").style.display = "block";
            enableThumbnailSelection(filename);
        }
    } else {
        checkIfThumbnailDeleted(thumbnail);
    }
}

function checkIfThumbnailDeleted(thumbnail) {
    const checkboxes = document.querySelectorAll('input[name="images[]"]');
    const thumbDeleted = Array.from(checkboxes).some(cb => cb.checked && cb.value === thumbnail);
    const section = document.getElementById("newThumbnailSection");

    if (thumbDeleted) {
        section.style.display = "block";
        enableThumbnailSelection(thumbnail);
    } else {
        section.style.display = "none";
        const newThumbGrid = document.getElementById("newThumbGrid");
        if (newThumbGrid) newThumbGrid.innerHTML = "";
    }
}

function enableThumbnailSelection(deletedThumbnail) {
    const checkboxes = document.querySelectorAll('input[name="images[]"]');

    const selectableImages = Array.from(checkboxes).filter(cb =>
        cb.value !== deletedThumbnail && !cb.checked
    ).map(cb => cb.value);

    let newThumbGrid = document.getElementById("newThumbGrid");
    if (!newThumbGrid) {
        const section = document.getElementById("newThumbnailSection");
        newThumbGrid = document.createElement("div");
        newThumbGrid.id = "newThumbGrid";
        newThumbGrid.style.display = "flex";
        newThumbGrid.style.flexWrap = "wrap";
        newThumbGrid.style.marginTop = "15px";
        section.appendChild(newThumbGrid);
    } else {
        newThumbGrid.innerHTML = "";
    }

    selectableImages.forEach(name => {
        const wrapper = document.createElement("label");
        wrapper.className = "img-box";

        const radio = document.createElement("input");
        radio.type = "radio";
        radio.name = "new_thumbnail";
        radio.value = name;

        const checkbox = Array.from(checkboxes).find(cb => cb.value === name);
        const img = checkbox ? checkbox.parentElement.querySelector("img") : null;

        const image = document.createElement("img");
        if (img) image.src = img.src;

        wrapper.appendChild(radio);
        wrapper.appendChild(image);

        newThumbGrid.appendChild(wrapper);
    });
}

function goToEventGallery() {
    const eventId = document.getElementById("eventSelect").value;
    if (eventId) {
        window.location.href = `event.php?id=${eventId}`;
    } else {
        alert("Please select an event first.");
    }
}

document.querySelector("form").addEventListener("submit", function(e) {
    const newThumbSection = document.getElementById("newThumbnailSection");
    const isVisible = newThumbSection.style.display === "block";
    const newThumb = document.querySelector('input[name="new_thumbnail"]:checked');

    if (isVisible && !newThumb) {
        e.preventDefault();
        alert("⚠️ कृपया एक नई कवर फोटो चुनें। / Please select a new cover photo before submitting.");
    }
});

</script>
</body>
</html>
