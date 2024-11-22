<?php
// Fetch messages for a specific user
$user_id = $_SESSION['user_id'];
$query = "SELECT m.*, u.name AS sender_name FROM messages m 
          JOIN users u ON m.sender_id = u.id 
          WHERE m.receiver_id = ? ORDER BY m.created_at DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$messages = $stmt->get_result();

// Display messages
if ($messages->num_rows > 0) {
    echo "<h2>Your Messages</h2>";
    while ($message = $messages->fetch_assoc()) {
        echo "<div class='message'>";
        echo "<strong>" . htmlspecialchars($message['sender_name']) . ":</strong> ";
        echo htmlspecialchars($message['message']);
        echo "<span class='timestamp'>" . htmlspecialchars($message['created_at']) . "</span>";
        echo "</div>";
    }
} else {
    echo "<p>No messages.</p>";
}
$stmt->close();
?>

<form action="send_message.php" method="post">
    <input type="hidden" name="receiver_id" value="<?php echo $receiver_id; ?>">
    <textarea name="message" placeholder="Type your message..." required></textarea>
    <button type="submit">Send Message</button>
</form>
