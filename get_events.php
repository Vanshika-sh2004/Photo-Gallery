<?php
include "db.php";

$year = $_GET['year'] ?? '';
$data = [];

if ($year) {
    $stmt = $conn->prepare("SELECT id, name FROM events WHERE financial_year = ? ORDER BY id DESC");
    $stmt->bind_param("s", $year);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $data[] = $row;
    }
}

header('Content-Type: application/json');
echo json_encode($data);
