<?php
session_start();
require 'config.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['application_id'])) {
    $applicationId = $_POST['application_id'];

    // Ensure the user is logged in as admin
    if (!isset($_SESSION['admin_id'])) {
        echo "Admin not logged in.";
        exit();
    }

    // Update the application status to approved
    $stmt = $conn->prepare("UPDATE adoption_applications SET approved = 1 WHERE id = ?");
    $stmt->bind_param("i", $applicationId);
    
    if ($stmt->execute()) {
        // Get the corresponding pet_id
        $petIdStmt = $conn->prepare("SELECT pet_id FROM adoption_applications WHERE id = ?");
        $petIdStmt->bind_param("i", $applicationId);
        $petIdStmt->execute();
        $petIdStmt->bind_result($pet_id);
        $petIdStmt->fetch();
        
        // Update the pet's is_adopted status
        $updatePetStmt = $conn->prepare("UPDATE pets_info SET is_adopted = 1 WHERE pet_id = ?");
        $updatePetStmt->bind_param("i", $pet_id);
        $updatePetStmt->execute();
        
        echo "Application approved and pet status updated!";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $petIdStmt->close();
    $updatePetStmt->close();
}

$conn->close();
?>
