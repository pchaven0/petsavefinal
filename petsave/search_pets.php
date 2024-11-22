<?php 
session_start(); 
require 'config.php'; 

if (!isset($_SESSION['user_id'])) {
    header("Location: loginf.php");
    exit();
}

$searchTerm = isset($_GET['search']) ? $_GET['search'] : '';

if (!empty($searchTerm)) {
    $searchTerm = "%" . $conn->real_escape_string($searchTerm) . "%";
    $stmt = $conn->prepare("SELECT * FROM pets_info WHERE is_adopted = 0 AND (name LIKE ? OR breed LIKE ?)");
    $stmt->bind_param("ss", $searchTerm, $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();

    $pets = [];
    while ($row = $result->fetch_assoc()) {
        $pets[] = $row;
    }

    // Return JSON response
    header('Content-Type: application/json');
    echo json_encode($pets);
} else {
    // Return an empty array if no search term
    echo json_encode([]);
}
?>
