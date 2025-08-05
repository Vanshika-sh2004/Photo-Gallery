<?php
session_start();
if ($_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

$isToast = false;
if (isset($_SESSION['upload_success']) && $_SESSION['upload_success']) {
    $isToast = true;
    unset($_SESSION['upload_success']);
}

$errorToast = "";
if (isset($_SESSION['error'])) {
    $errorToast = $_SESSION['error'];
    unset($_SESSION['error']);
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Upload Event</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        :root {
            --blue-dark: #001f3f;
            --blue-light: #00cfff;
            --form-bg: #e0f7fa;
            --accent: #0077b6;
            --text-dark: #002333;
        }

        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(to right, var(--blue-dark), var(--blue-light));
            background-attachment: fixed;
            background-size: cover;
            overflow-x: hidden;
        }

        .container {
            margin: 60px auto;
            background-color: var(--form-bg);
            color: var(--text-dark);
            padding: 35px;
            border-radius: 16px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.3);
            width: 85%;
            animation: slideFadeIn 1s ease;
        }

        @keyframes slideFadeIn {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        h2, .form-label, h5 {
            color: var(--text-dark);
            font-weight: bold;
        }

        .form-control {
            border-radius: 10px;
            padding: 10px 15px;
            font-size: 1rem;
        }
        .heading-box {
            background-color:#80c0c0;
            max-width: 800px;
            margin: 2rem auto 40px auto;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.4);
        }

        .gallery-title {
            text-align: center;
            margin-top: 20px;
            margin-bottom: 40px;
            font-size: 2rem;
            font-weight: bold;
            text-shadow: 2px 2px 5px rgba(0,0,0,0.6);
        }

        .form-control:focus {
            box-shadow: 0 0 10px rgba(0, 207, 255, 0.5);
            border-color: var(--accent);
        }

        .btn-primary {
            background-color: var(--accent);
            border: none;
            color: white;
            font-weight: bold;
            padding: 10px 20px;
            border-radius: 10px;
            transition: all 0.3s ease-in-out;
        }

        .btn-primary:hover {
            background-color: #005a87;
            transform: scale(1.05);
        }

        #previewArea img {
            height: 100px;
            width: 100px;
            object-fit: cover;
            margin: 5px;
            cursor: pointer;
            border: 3px solid transparent;
            transition: 0.3s ease;
            border-radius: 10px;
        }

        #previewArea img:hover {
            transform: scale(1.05);
        }

        #previewArea img.selected {
            border-color: var(--accent);
            box-shadow: 0 0 10px var(--accent);
        }

        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1055;
        }

        .toast-body {
            font-weight: bold;
        }

        @media (max-width: 576px) {
            .container {
                padding: 20px;
                margin-top: 30px;
            }

            h2 {
                font-size: 1.4rem;
            }
        }
    </style>
</head>
<body>
  


<?php if ($isToast): ?>
    <div class="toast-container">
        <div class="toast align-items-center text-bg-success border-0 show" role="alert" id="uploadToast">
            <div class="d-flex">
                <div class="toast-body">✅ Images uploaded successfully!</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php if (!empty($errorToast)): ?>
    <div class="toast-container">
        <div class="toast align-items-center text-bg-danger border-0 show" role="alert" id="errorToast">
            <div class="d-flex">
                <div class="toast-body"><?php echo htmlspecialchars($errorToast); ?></div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    </div>
<?php endif; ?>

     <div class="heading-box text-center text-white py-3 px-4  shadow">
        <h1 class="gallery-title m-0">
            📸 फोटो गैलरी / Photo Gallery 📸
        </h1>
    </div>


<div class="container">
    <h2 class="text-center mb-4">📁 इवेंट फ़ोल्डर या चित्र अपलोड करें / Upload Event Folder or Images 📁</h2>
    <form action="upload_handler.php" method="POST" enctype="multipart/form-data">
        <div class="mb-3">
            <label class="form-label">इवेंट का नाम / Event Name</label>
            <input type="text" name="event_name" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label"> वर्ष / Year</label>
            <select name="financial_year" class="form-control" required>
                <option value="">वर्ष चुनें / Select Year</option>
<?php
for ($year = 2020; $year <= 2027; $year++) {
    echo "<option value='$year'>$year</option>";
}
?>



            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">चित्र या फ़ोल्डर चुनें / Select Images or Folder</label>
            <input type="file" name="images[]" multiple class="form-control" id="imageInput" required>
        </div>

        <div class="form-check mt-2">
            <input class="form-check-input" type="checkbox" id="folderMode">
            <label class="form-check-label" for="folderMode">फ़ोल्डर अपलोड करें / Upload Folder</label>
        </div>

        <input type="hidden" name="thumbnail" id="selectedThumbnail">
        <div class="mt-4">
            <h5>इवेंट के लिए कवर फोटो चुनें / Select Cover Photo For The Event:</h5>
            <div id="previewArea" class="d-flex flex-wrap"></div>
        </div>

        <button type="submit" class="btn btn-primary mt-3"> अपलोड करें / Upload</button>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
const folderModeCheckbox = document.getElementById('folderMode');
const imageInput = document.getElementById('imageInput');
const previewArea = document.getElementById('previewArea');
const thumbnailInput = document.getElementById('selectedThumbnail');

folderModeCheckbox.addEventListener('change', function () {
    if (this.checked) {
        imageInput.setAttribute('webkitdirectory', '');
        imageInput.setAttribute('directory', '');
    } else {
        imageInput.removeAttribute('webkitdirectory');
        imageInput.removeAttribute('directory');
    }
});

imageInput.addEventListener('change', function () {
    previewArea.innerHTML = '';
    const files = Array.from(this.files);
    files.forEach((file) => {
        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function (e) {
                const img = document.createElement('img');
                img.src = e.target.result;
                img.dataset.filename = file.name;
                img.onclick = function () {
                    document.querySelectorAll('#previewArea img').forEach(i => i.classList.remove('selected'));
                    img.classList.add('selected');
                    thumbnailInput.value = img.dataset.filename;
                };
                previewArea.appendChild(img);
            };
            reader.readAsDataURL(file);
        }
    });
});

<?php if ($isToast || $errorToast): ?>
setTimeout(() => {
    const success = document.getElementById('uploadToast');
    const error = document.getElementById('errorToast');
    if (success) bootstrap.Toast.getOrCreateInstance(success).hide();
    if (error) bootstrap.Toast.getOrCreateInstance(error).hide();
    window.location.href = "index.php";
}, 3000);
<?php endif; ?>
</script>
</body>
</html>
