<?php
session_start();
include "db.php";

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");


$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';

$selectedYear = $_GET['year'] ?? '';
if (empty($selectedYear)) {
    $selectedYear = date('Y');
}


$stmt = $conn->prepare("SELECT * FROM events WHERE financial_year = ? ORDER BY created_at DESC");
$stmt->bind_param("s", $selectedYear);
$stmt->execute();
$result = $stmt->get_result();

$yearsResult = $conn->query("SELECT DISTINCT financial_year FROM events ORDER BY financial_year DESC");
$allYears = [];
while ($yr = $yearsResult->fetch_assoc()) {
    if (!empty($yr['financial_year'])) {
        $allYears[] = $yr['financial_year'];
    }
}


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gallery</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        :root {
            --blue-1: #82b7d2;
            --blue-2: #5a97b5;
            --blue-3: #bfd9e9;
            --blue-4: #003e7e;
            --blue-5: #0275c2;
        }

        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            color: #fff;
            min-height: 100vh;
            overflow-x: hidden;
            background-image: url('best.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
        }

        .page-wrapper {
            display: flex;
            flex-direction: column;
            align-items: center;
            min-height: 100vh;
        }

        .container {
            width: 100%;
            max-width: 1200px;
            padding: 10px 15px;
            margin: 0 auto;
        }

        .event-card {
            position: relative;
            height: 200px;
            border-radius: 15px;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            text-shadow: 1px 1px 3px #000;
            background-size: cover;
            background-position: center;
            transition: transform 0.4s ease, box-shadow 0.4s ease;
            box-shadow: 0 6px 15px rgba(0, 153, 255, 0.25);
            overflow: hidden;
            cursor: pointer;
        }

        .event-card::after {
            content: "";
            position: absolute;
            width: 100px;
            height: 100px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            opacity: 0;
            transform: scale(0);
            transition: transform 0.6s ease-out, opacity 0.6s ease-out;
            z-index: 1;
        }

        .event-card:hover::after {
            transform: scale(8);
            opacity: 1;
        }

        .event-card:hover {
            transform: translateY(-15px) scale(1.07);
            box-shadow: 0 20px 50px rgba(255, 105, 180, 0.5), 0 0 10px rgba(255, 105, 180, 0.5);
            z-index: 2;
        }

        .event-card h5 {
            background-color: rgba(0, 0, 0, 0.7);
            padding: 8px 12px;
            border-radius: 8px;
            width: 90%;
            text-align: center;
            margin: 0;
            position: relative;
            z-index: 2;
            text-transform: capitalize;
            font-size: 1.1rem;
            font-weight: 600;
        }

        .heading-box {
            background-color: #80c0c0;
            max-width: 900px;
            margin: 1rem auto 2rem auto;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.4);
            border-radius: 10px;
            position: relative;
        }

        .login-button-container {
            position: absolute;
            top: 15px;
            right: 20px;
        }

        .gallery-title {
            text-align: center;
            margin: 0;
            font-size: 2rem;
            font-weight: bold;
            text-shadow: 2px 2px 5px rgba(0,0,0,0.6);
            color: #fff;
            padding-right: 120px; /* Give space for the login button */
        }

        a {
            text-decoration: none;
        }

        .filter-bar {
            margin-bottom: 25px;
        }

        .filter-bar select {
            max-width: 250px;
            margin-left: auto;
            margin-right: auto;
            display: block;
        }

        .overlay-box {
            background-color: rgba(12, 99, 239, 0.4);
            backdrop-filter: blur(2px);
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .btn {
            font-size: 1.1rem;
            font-weight: 600;
            margin: 2px;
            padding:0.5rem;
        }

        .filter-bar select,
        #yearFilterForm select,
        form#yearFilterForm select.form-select {
            font-size: 0.9rem;
            font-weight: 700;
        }

        #yearFilterForm select option {
            font-weight: 700;
        }

        .overlay-box .text-white.fw-semibold {
            font-weight: 700;
            font-size: 1.2rem;
        }

        .admin-controls {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;
            justify-content: space-between;
        }

        .user-controls {
            display: flex;
            align-items: center;
            gap: 15px;
            width: 100%;
            justify-content: center;
            flex-wrap: wrap;
        }

        .year-form {
            min-width: 220px;
        }

        .alert {
            background-color: rgba(255, 255, 255, 0.9);
            color: #333;
            border: none;
            border-radius: 10px;
        }

        @media (max-width: 768px) {
            .gallery-title {
                font-size: 1.5rem;
                padding-right: 0;
                margin-bottom: 40px; /* Space for button below on mobile */
            }
            
            .login-button-container {
                position: absolute;
                top: auto;
                bottom: 10px;
                right: 50%;
                transform: translateX(50%);
            }
            
            .admin-controls {
                flex-direction: column;
                align-items: stretch;
            }
            
            .btn-group {
                width: 100%;
                margin-bottom: 10px;
            }
            
            .year-form {
                width: 100%;
            }
        }
    </style>
</head>
<body>

<div class="page-wrapper">
    <div class="container">
        <div class="row align-items-center mb-3">
            <div class="col-12">
                <div class="heading-box text-white py-3 px-3 position-relative">
                    <h1 class="gallery-title">
                        📸 फोटो गैलरी / Photo Gallery 📸
                    </h1>
                    <div class="login-button-container">
                        <?php if (!$isAdmin): ?>
                            <a href="login.php" class="btn btn-success btn-sm">🔐 Login</a>
                        <?php else: ?>
                            <a href="logout.php" class="btn btn-danger btn-sm">🚪 Logout</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Controls Section -->
    <div class="container">
        <div class="overlay-box">
            <?php if ($isAdmin): ?>
                <!-- Admin Controls -->
                <div class="admin-controls">
                    <div class="btn-group" role="group" aria-label="Admin Actions">
                        <a href="dashboard.php" class="btn btn-success">➕ इवेंट दर्ज करें / Add Event</a>
                        <a href="delete.php" class="btn btn-danger">🗑️ तस्वीरें हटाएँ / Delete Images</a>
                        <a href="edit_event.php" class="btn btn-warning">✏️ इवेंट संपादित करें / Edit Events</a>
                    </div>

                    <form method="GET" id="yearFilterForm" class="year-form">
                        <select name="year" class="form-select" onchange="document.getElementById('yearFilterForm').submit();">
                            <option value="">🔎 वर्ष द्वारा खोजें / Search by Year</option>
                            <?php foreach ($allYears as $year): ?>
                                <option value="<?php echo htmlspecialchars($year); ?>" <?php echo ($selectedYear === $year) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($year); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                </div>
            <?php else: ?>
                <!-- User Controls -->
                <div class="user-controls">
                    <div class="text-white fw-semibold text-center">
                        कृपया वह वर्ष चुनें जिसके लिए आप चित्र देखना चाहते हैं / Please select the year for which you want to see the images
                    </div>

                    <form method="GET" id="yearFilterForm" class="year-form">
                        <select name="year" class="form-select" onchange="document.getElementById('yearFilterForm').submit();">
                            <option value="">🔎 वर्ष द्वारा खोजें / Search by Year</option>
                            <?php foreach ($allYears as $year): ?>
                                <option value="<?php echo htmlspecialchars($year); ?>" <?php echo ($selectedYear === $year) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($year); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Events Grid -->
    <div class="container">
        <?php if ($result->num_rows > 0): ?>
            <div class="row">
                <?php while ($row = $result->fetch_assoc()):
                    $folder = "uploads/" . $row['financial_year'] . "/" . $row['folder_name'];
                    $thumb = $row['thumbnail'] ? $folder . '/' . $row['thumbnail'] : '';
                    
                    // Check if thumbnail exists, if not use a default placeholder
                    if (!file_exists($thumb) || empty($thumb)) {
                        $thumb = 'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" width="400" height="300" viewBox="0 0 400 300"><rect width="400" height="300" fill="#ccc"/><text x="200" y="150" text-anchor="middle" font-family="Arial" font-size="16" fill="#666">No Image</text></svg>');
                    }
                ?>
                <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                    <a href="event.php?id=<?php echo $row['id']; ?>">
                        <div class="event-card" style="background-image: url('<?php echo htmlspecialchars($thumb); ?>');">
                            <h5><?php echo htmlspecialchars($row['name']); ?></h5>
                        </div>
                    </a>
                </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-info text-center mt-4">
                <h5>📅 कोई इवेंट उपलब्ध नहीं है / No events found</h5>
                <?php if (!empty($selectedYear)): ?>
                <p>वर्ष <?php echo htmlspecialchars($selectedYear); ?> के लिए कोई इवेंट नहीं मिला / No events found for year <?php echo htmlspecialchars($selectedYear); ?></p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>