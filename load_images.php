<?php
$folder = $_GET['folder'] ?? '';
$media = [];

$baseDir = realpath(__DIR__ . '/uploads');
$targetDir = realpath($folder);

// Security check: ensure folder is inside uploads/
if ($targetDir && strpos($targetDir, $baseDir) === 0 && is_dir($targetDir)) {
    $files = scandir($targetDir);
    foreach ($files as $file) {
        if (preg_match('/\.(jpe?g|png|gif|bmp|webp|mp4|wmv)$/i', $file)) {
            $media[] = $folder . '/' . $file;
        }
    }
}

header('Content-Type: application/json');
echo json_encode($media);
