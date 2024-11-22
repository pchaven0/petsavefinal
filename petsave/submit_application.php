<?php
session_start();
require 'config.php';

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pet_id = $_POST['pet_id'];

    // Ensure the user is logged in
    if (!isset($_SESSION['user_id'])) {
        echo "User not logged in.";
        exit();
    }

    $user_id = $_SESSION['user_id']; // Get the logged-in user's ID
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $occupation = $_POST['occupation'];
    $previous_pet = isset($_POST['previous_pet']) ? 'yes' : 'no';
    $marital_status = $_POST['marital_status'];
    $message = $_POST['message'];

    // Handle file upload
    $target_dir = "uploads/";
    $target_file = $target_dir . basename($_FILES["image"]["name"]);
    move_uploaded_file($_FILES["image"]["tmp_name"], $target_file);

    // Prepare SQL statement
    $query = "INSERT INTO adoption_applications (pet_id, user_id, name, email, message, phone, address, occupation, previous_pet, marital_status, id_image) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iisssssssss", $pet_id, $user_id, $name, $email, $message, $phone, $address, $occupation, $previous_pet, $marital_status, $target_file);

    // Execute the statement and check for success
    if ($stmt->execute()) {
        header("Location: dashboard.php");
        exit(); // Make sure to exit after the redirect
    } else {
        echo "<p>Error: " . $stmt->error . "</p>";
    }

    $stmt->close();
}

$conn->close();
?>
