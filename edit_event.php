<?php
session_start();
include "db.php";

$selectedYear = $_GET['year'] ?? '';
$selectedEvent = $_GET['event'] ?? '';

if ($_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

$toastMsg = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $eventId = $_POST['event_id'];
    $financialYear = $_POST['financial_year'];
    $event = $conn->query("SELECT * FROM events WHERE id = $eventId")->fetch_assoc();
    $folder = "uploads/{$event['financial_year']}/{$event['folder_name']}";

    if (!empty($_POST['new_event_name'])) {
        $newName = $conn->real_escape_string($_POST['new_event_name']);
        $conn->query("UPDATE events SET name = '$newName' WHERE id = $eventId");
        $toastMsg .= "📝  इवेंट का नाम सफलतापूर्वक अपडेट किया गया।/ Event name updated. ";
    }

    if (isset($_POST['add_photos']) && !empty($_FILES['add_images']['name'][0])) {
        foreach ($_FILES['add_images']['tmp_name'] as $key => $tmp) {
            $filename = basename($_FILES['add_images']['name'][$key]);
            move_uploaded_file($tmp, "$folder/$filename");
        }
        $toastMsg .= "📷 फ़ोटो जोड़े गए / Photos added. ";
    }

    if (isset($_POST['replace_photos']) && !empty($_FILES['replace_images']['name'][0])) {
        foreach (scandir($folder) as $file) {
            if (!in_array($file, ['.', '..'])) unlink("$folder/$file");
        }
        foreach ($_FILES['replace_images']['tmp_name'] as $key => $tmp) {
            $filename = basename($_FILES['replace_images']['name'][$key]);
            move_uploaded_file($tmp, "$folder/$filename");
        }
        $conn->query("UPDATE events SET thumbnail = NULL WHERE id = $eventId");
        $toastMsg .= "🔁 सभी फ़ोटो बदल दिए गए हैं। थंबनेल हटा दिया गया है / All photos replaced. Thumbnail cleared. ";
    }

    if (!empty($_POST['selected_thumbnail'])) {
        $thumb = $conn->real_escape_string($_POST['selected_thumbnail']);
        $conn->query("UPDATE events SET thumbnail = '$thumb' WHERE id = $eventId");
        $toastMsg .= "🌟 इवेंट की कवर फ़ोटो बदल दी गई है / Cover image updated. ";
    }

    $_SESSION['toast'] = $toastMsg;
      header("Location: edit_event.php?year=$financialYear&event=$eventId");
    exit;
}

if (isset($_SESSION['toast'])) {
    $toastMsg = $_SESSION['toast'];
    unset($_SESSION['toast']);
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Event</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
     body {
        margin: 0;
        padding: 0;
        font-family: 'Segoe UI', sans-serif;
        background: linear-gradient(135deg, #a16ae8, #ffd86f);
        min-height: 100vh;
        display: flex;
         flex-direction: column;
         align-items: center; 
        justify-content: flex-start; 
        overflow-x: hidden;
        animation: bgFade 2s ease-in-out;
        padding:20px;
    }

    @keyframes bgFade {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    .edit-box {
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.25), rgba(255, 255, 255, 0.1));
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        padding: 40px;
        border-radius: 20px;
        max-width: 950px;
        width: 95%;
        box-shadow: 0 12px 30px rgba(0, 0, 0, 0.2);
        animation: fadeInUp 1s ease-out both;
        border: 1px solid rgba(255, 255, 255, 0.25);
    }

    h3 {
        font-weight: bold;
        text-align: center;
        color: #4c1c70;
        margin-bottom: 35px;
        text-shadow: 1px 1px 3px rgba(255, 255, 255, 0.4);
    }
    .heading-box {
            background-color: #80c0c0;;
            max-width: 850px;
            margin: 4rem auto 20px auto;
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
    

    label.form-label {
        font-weight: 600;
       color: #1a237e;
       font-size:1.2rem;
    }

    .form-control, .form-select {
        border-radius: 10px;
        background: rgba(255, 255, 255, 0.85);
        transition: all 0.3s ease;
    }
    .form-check-label,
.form-label {
  font-weight: bold;
  font-size: 1rem;
  margin-bottom: 0.4rem; /* spacing below each label */
  display: inline-block; /* so margin-bottom works */
}

#addPhotosCheck + label.form-check-label {
  color: #155724; /* dark green */
}

/* Second checkbox label (Replace All Photos) - navy */
#replacePhotosCheck + label.form-check-label {
  color:rgb(8, 156, 175); /* navy */
}

/* Third checkbox label (Change Cover Image Only) - violet */
#changeThumbCheck + label.form-check-label {
  color: #6f42c1; /* violet */
}

/* Fourth one if exists (e.g. Edit Event Name checkbox) - red */
#editNameCheck + label.form-check-label {
  color: #d9534f; /* red */
}
    .form-control:focus, .form-select:focus {
        border-color: #0211b8ff;
        box-shadow: 0 0 0 0.2rem rgba(161, 106, 232, 0.3);
    }

    .btn-primary {
        background: #7b4397;
        border: none;
        color: #fff;
        transition: all 0.3s ease;
    }

    .btn-primary:hover {
        background: #a16ae8;
        transform: translateY(-2px);
    }

    .btn-secondary {
        background: #fff3b0;
        color: #4c1c70;
        border: none;
        transition: all 0.3s ease;
    }

    .btn-secondary:hover {
        background: #100f0fff;
        transform: translateY(-2px);
    }

    .toast-container {
        position: fixed;
        top: 1rem;
        right: 1rem;
        z-index: 1055;
    }

    #previewImages img {
        height: 100px;
        width: 100px;
        object-fit: cover;
        margin: 5px;
        cursor: pointer;
        border: 3px solid transparent;
        border-radius: 10px;
        transition: transform 0.3s, box-shadow 0.3s;
    }

    #previewImages img:hover {
        transform: scale(1.08);
    }

    #previewImages img.selected {
        border-color: #ffd86f;
        box-shadow: 0 0 12px #ffd86f;
    }

    .hidden {
        display: none;
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(40px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>

</head>
<body>
  <div class="heading-box text-center text-white py-3 px-4 rounded shadow">
        <h1 class="gallery-title m-0">
            📸 फोटो गैलरी / Photo Gallery 📸
        </h1>
    </div>
<div class="edit-box">
    <h3>🖊️  इवेंट संपादित करें / Edit Event 🖊️</h3>
    <form method="POST" enctype="multipart/form-data">
        <div class="mb-3">
            <label class="form-label">वर्ष / Year</label>
<select id="financialYear" class="form-select" name="financial_year" required>
    <option value="">वर्ष चुनें / Select Year</option>
    <?php
    $res = $conn->query("SELECT DISTINCT financial_year FROM events ORDER BY financial_year DESC");
    while ($row = $res->fetch_assoc()) {
        $year = htmlspecialchars($row['financial_year']);
        // Only include 4-digit years to avoid malformed entries
        if (preg_match('/^\d{4}$/', $year)) {
            echo "<option value='$year'>$year</option>";
        }
    }
    ?>
</select>

        </div>

        <div class="mb-3">
            <label class="form-label"> इवेंट का चयन करें / Select Event</label>
            <select id="eventSelect" class="form-select" name="event_id" required></select>
        </div>

        <div class="form-check mb-2">
            <input class="form-check-input" type="checkbox" id="editNameCheck">
            <label class="form-check-label">इवेंट का नाम संपादित करें / Edit Event Name</label>
        </div>
        <div class="mb-3 hidden" id="editNameBox">
            <input type="text" class="form-control" name="new_event_name" placeholder="New Event Name">
        </div>

        <div class="form-check mb-2">
            <input class="form-check-input" type="checkbox" id="addPhotosCheck" name="add_photos">
            <label class="form-check-label">और फ़ोटो जोड़ें / Add More Photos</label>
        </div>
        <div class="mb-3 hidden" id="addPhotosBox">
            <input type="file" class="form-control" name="add_images[]" multiple>
        </div>

        <div class="form-check mb-2">
            <input class="form-check-input" type="checkbox" id="replacePhotosCheck" name="replace_photos">
            <label class="form-check-label">सभी फ़ोटो बदलें / Replace All Photos</label>
        </div>
        <div class="mb-3 hidden" id="replacePhotosBox">
            <input type="file" class="form-control" name="replace_images[]" webkitdirectory directory multiple>
        </div>

        <div class="form-check mb-2">
            <input class="form-check-input" type="checkbox" id="changeThumbCheck">
            <label class="form-check-label">कवर बदलें / Change Cover Image Only</label>
        </div>
        <div class="mb-3 hidden" id="previewImages"></div>
        <input type="hidden" name="selected_thumbnail" id="selectedThumbnail">

        <div class="d-flex justify-content-start gap-3 mt-4">
        <button type="button" class="btn btn-warning" onclick="goToEventGallery()">⬅️ Back to Gallery</button>
            <button type="submit" class="btn btn-primary">✅ इवेंट अपडेट करें / Update Event</button>
        </div>
    </form>
</div>

<?php if (!empty($toastMsg)): ?>
<div class="toast-container">
    <?php foreach (explode('. ', trim($toastMsg, '. ')) as $msg): ?>
        <div class="toast show text-bg-success border-0 mb-2" role="alert">
            <div class="d-flex">
                <div class="toast-body"><?php echo $msg; ?></div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<script>
setTimeout(() => {
    document.querySelectorAll('.toast').forEach(toast => {
        bootstrap.Toast.getOrCreateInstance(toast).hide();
    });
}, 3000);
</script>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.getElementById("financialYear").addEventListener("change", function () {
    const year = this.value;
    const eventSelect = document.getElementById("eventSelect");
    eventSelect.innerHTML = '<option value="">Loading...</option>';

    fetch('get_events.php?year=' + year)
        .then(res => res.json())
        .then(data => {
            eventSelect.innerHTML = '<option value="">Select Event</option>';
            data.forEach(event => {
                const opt = document.createElement('option');
                opt.value = event.id;
                opt.textContent = event.name;
                eventSelect.appendChild(opt);
            });
        });
});

document.getElementById("changeThumbCheck").addEventListener("change", function () {
    const preview = document.getElementById("previewImages");
    const eventId = document.getElementById("eventSelect").value;
    const year = document.getElementById("financialYear").value;
    preview.innerHTML = "";

    if (this.checked && eventId && year) {
        preview.classList.remove("hidden");

        fetch("get_event_images.php?id=" + eventId)
            .then(res => res.json())
            .then(data => {
                const images = data.images; // ✅ FIXED: Access 'images' array inside data
                const thumbnail = data.thumbnail; // ✅ Optional: highlight current thumbnail if needed

                preview.innerHTML = "";
                images.forEach(imgData => {
    const ext = imgData.name.split('.').pop().toLowerCase();
    const allowedImageExts = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'];

    if (!allowedImageExts.includes(ext)) return; // Skip if not image

    const img = document.createElement("img");
    img.src = `uploads/${imgData.year}/${imgData.folder}/${imgData.name}`;
    img.dataset.filename = imgData.name;
    img.title = "Click to select as thumbnail";

    if (imgData.name === thumbnail) {
        img.classList.add("selected");
    }

    img.onclick = () => {
        document.querySelectorAll("#previewImages img").forEach(i => i.classList.remove("selected"));
        img.classList.add("selected");
        document.getElementById("selectedThumbnail").value = imgData.name;
    };

    preview.appendChild(img);
});

            });
    } else {
        preview.classList.add("hidden");
        document.getElementById("selectedThumbnail").value = "";
    }
});
function goToEventGallery() {
    const eventId = document.getElementById("eventSelect").value;
    if (eventId) {
        window.location.href = `event.php?id=${eventId}`;
    } else {
        alert("Please select an event first.");
    }
}

// ✅ Auto-select year & event from query params on reload/back
window.addEventListener("DOMContentLoaded", () => {
    const selectedYear = "<?php echo $selectedYear; ?>";
    const selectedEvent = "<?php echo $selectedEvent; ?>";

    if (selectedYear) {
        document.getElementById("financialYear").value = selectedYear;

        fetch("get_events.php?year=" + selectedYear)
            .then(res => res.json())
            .then(data => {
                const eventSelect = document.getElementById("eventSelect");
                eventSelect.innerHTML = '<option value="">Select Event</option>';

                data.forEach(ev => {
                    const opt = document.createElement("option");
                    opt.value = ev.id;
                    opt.textContent = ev.name;
                    if (ev.id == selectedEvent) opt.selected = true;
                    eventSelect.appendChild(opt);
                });

                if (selectedEvent) {
                    // Load images
                    fetch("get_event_images.php?id=" + selectedEvent)
                        .then(res => res.json())
                        .then(data => {
                            const grid = document.getElementById("imageGrid");
                            const images = data.images;
                            const thumbnail = data.thumbnail;
                            grid.innerHTML = "";

                            images.forEach(img => {
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
                        });
                }
            });
    }
});



document.getElementById("editNameCheck").addEventListener("change", function () {
    document.getElementById("editNameBox").classList.toggle("hidden", !this.checked);
});
document.getElementById("addPhotosCheck").addEventListener("change", function () {
    document.getElementById("addPhotosBox").classList.toggle("hidden", !this.checked);
});
document.getElementById("replacePhotosCheck").addEventListener("change", function () {
    document.getElementById("replacePhotosBox").classList.toggle("hidden", !this.checked);
});
</script>
</body>
</html>
