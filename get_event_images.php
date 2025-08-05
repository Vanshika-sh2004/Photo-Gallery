<?php
include "db.php";

$eventId = $_GET['id'] ?? 0;
$event = $conn->query("SELECT * FROM events WHERE id=$eventId ORDER BY id DESC")->fetch_assoc();

$folderPath = "uploads/{$event['financial_year']}/{$event['folder_name']}";
$images = [];

if (is_dir($folderPath)) {
    foreach (scandir($folderPath) as $file) {
        if (!in_array($file, ['.', '..']) && !is_dir("$folderPath/$file")) {
            $images[] = [
                'name' => $file,
                'folder' => $event['folder_name'],
                'year' => $event['financial_year']
            ];
        }
    }
}

echo json_encode([
    'images' => $images,
    'thumbnail' => $event['thumbnail']
]);
?>
