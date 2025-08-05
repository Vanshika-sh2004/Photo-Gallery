<?php
session_start();
include "db.php";

// 🔒 Sanitize input
$eventName = trim($_POST['event_name']);
$financialYear = $_POST['financial_year'];
$thumbnail = isset($_POST['thumbnail']) ? basename($_POST['thumbnail']) : ''; // ✔️ ensure only filename
$uploaded = $_FILES['images'];

// 🧼 Sanitize folder name
$folderName = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $eventName);

// ❌ Check for duplicate event in the same financial year
$stmt = $conn->prepare("SELECT COUNT(*) FROM events WHERE name = ? AND financial_year = ?");
$stmt->bind_param("ss", $eventName, $financialYear);
$stmt->execute();
$stmt->bind_result($count);
$stmt->fetch();
$stmt->close();

if ($count > 0) {
    $_SESSION['error'] = "❌ इवेंट / Event '$eventName' पहले से मौजूद है / already exists for $financialYear.";
    header("Location: dashboard.php");
    exit;
}

// ✅ Create folder
$uploadPath = "uploads/" . $financialYear . "/" . $folderName;
if (!is_dir($uploadPath)) {
    mkdir($uploadPath, 0777, true);
}

// ✅ Upload files
foreach ($uploaded['tmp_name'] as $index => $tmpPath) {
    $filename = basename($uploaded['name'][$index]);
    move_uploaded_file($tmpPath, $uploadPath . "/" . $filename);
}

// ✅ Insert into DB (thumbnail is stored as-is)
$stmt = $conn->prepare("INSERT INTO events (name, folder_name, thumbnail, financial_year) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $eventName, $folderName, $thumbnail, $financialYear);
$stmt->execute();
$stmt->close();

// ✅ Set success flag
$_SESSION['upload_success'] = true;
header("Location: dashboard.php");
exit;
?>
