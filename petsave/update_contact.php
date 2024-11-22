<?php
session_start();
require 'config.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "Access denied. Please log in.";
    exit;
}

$user_id = $_SESSION['user_id'];

// Check if the form was submitted
if (isset($_POST['update_contact'])) {
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $whatsapp = $_POST['whatsapp'];
    $telegram = $_POST['telegram'];
    $facebook = $_POST['facebook'];

    // Check if contact info exists, then update it
    $check_query = "SELECT * FROM user_contacts WHERE user_id = ?";
    $check_stmt = $conn->prepare($check_query);
    $check_stmt->bind_param("i", $user_id);
    $check_stmt->execute();
    $contact = $check_stmt->get_result()->fetch_assoc();
    $check_stmt->close();

    if ($contact) {
        // Update existing contact info
        $update_query = "UPDATE user_contacts SET phone = ?, email = ?, whatsapp = ?, telegram = ?, facebook = ? WHERE user_id = ?";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param("sssssi", $phone, $email, $whatsapp, $telegram, $facebook, $user_id);
        $update_stmt->execute();
        $update_stmt->close();
        echo "<script>alert('Contact information updated successfully.'); window.location.href = 'dashboard.php';</script>";
    } else {
        // Insert new contact info
        $insert_query = "INSERT INTO user_contacts (user_id, phone, email, whatsapp, telegram, facebook) VALUES (?, ?, ?, ?, ?, ?)";
        $insert_stmt = $conn->prepare($insert_query);
        $insert_stmt->bind_param("isssss", $user_id, $phone, $email, $whatsapp, $telegram, $facebook);
        $insert_stmt->execute();
        $insert_stmt->close();
        echo "<script>alert('Contact information added successfully.'); window.location.href = 'dashboard.php';</script>";
    }
}
?>
