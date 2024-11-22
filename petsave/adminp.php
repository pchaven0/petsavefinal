<?php
@include 'config.php';

session_start();

if (!isset($_SESSION['admin_name'])) {
    header('location:loginform.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle application approval or cancellation
    if (isset($_POST['approve'])) {
        $id = $_POST['application_id'];
        
        // Get application details before approving
        $query = "SELECT * FROM adoption_applications WHERE adopt_id=?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $result->num_rows > 0) {
            $application = $result->fetch_assoc();
            
            // Insert into processed_applications
            $insert_sql = "INSERT INTO processed_applications (adopt_id, user_id, pet_id, status, name, email, phone, address, occupation, previous_pet, marital_status, message, id_image) VALUES (?, ?, ?, 'approved', ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $insert_stmt = $conn->prepare($insert_sql);
            $insert_stmt->bind_param("iiisssssssss", $application['adopt_id'], $application['user_id'], $application['pet_id'], $application['name'], $application['email'], $application['phone'], $application['address'], $application['occupation'], $application['previous_pet'], $application['marital_status'], $application['message'], $application['id_image']);
            $insert_stmt->execute();
            $insert_stmt->close();
            
            // Now update the status
            $update_sql = "UPDATE adoption_applications SET status='approved' WHERE adopt_id=?";
            $stmt = $conn->prepare($update_sql);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            
            // Notify users (rest of your notification code)
            // Get user ID and pet ID for notification
            $applicant_user_id = $application['user_id'];
            $pet_id = $application['pet_id'];

            // Get pet owner's user ID
            $pet_owner_query = "SELECT user_id FROM pets_info WHERE pet_id=?";
            $pet_owner_stmt = $conn->prepare($pet_owner_query);
            $pet_owner_stmt->bind_param("i", $pet_id);
            $pet_owner_stmt->execute();
            $pet_owner_result = $pet_owner_stmt->get_result();
            
            if ($pet_owner_result && $pet_owner_result->num_rows > 0) {
                $pet_owner = $pet_owner_result->fetch_assoc();
                $pet_owner_user_id = $pet_owner['user_id']; // User who owns the pet

                // Fetch contact information for both users
                $contact_query = "SELECT phone, email, telegram, facebook FROM user_contacts WHERE user_id=?";
                
                // Get applicant's contact info
                $contact_stmt = $conn->prepare($contact_query);
                $contact_stmt->bind_param("i", $applicant_user_id);
                $contact_stmt->execute();
                $applicant_contact_result = $contact_stmt->get_result();
                $applicant_contact = $applicant_contact_result->fetch_assoc();

                // Get pet owner's contact info
                $contact_stmt->bind_param("i", $pet_owner_user_id);
                $contact_stmt->execute();
                $pet_owner_contact_result = $contact_stmt->get_result();
                $pet_owner_contact = $pet_owner_contact_result->fetch_assoc();

                // Create notification messages with contact information
                $applicant_notification_message = "Your application for pet ID $pet_id has been approved! You can now contact the pet owner. Here are their contact details: 
                    Phone: " . htmlspecialchars($pet_owner_contact['phone']) . ", 
                    Email: " . htmlspecialchars($pet_owner_contact['email']) . ",  
                    Telegram: " . htmlspecialchars($pet_owner_contact['telegram']) . ", 
                    Facebook: " . htmlspecialchars($pet_owner_contact['facebook']) . ".";

                $pet_owner_notification_message = "Your pet ID $pet_id has an application approved! You can now contact the applicant. Here are their contact details: 
                    Phone: " . htmlspecialchars($applicant_contact['phone']) . ", 
                    Email: " . htmlspecialchars($applicant_contact['email']) . ", 
                    Telegram: " . htmlspecialchars($applicant_contact['telegram']) . ", 
                    Facebook: " . htmlspecialchars($applicant_contact['facebook']) . ".";

                // Insert notifications for both users
                $notification_query = "INSERT INTO notifications ( user_id, message) VALUES (?, ?)";
                
                // Notify applicant
                $notification_stmt = $conn->prepare($notification_query);
                $notification_stmt->bind_param("is", $applicant_user_id, $applicant_notification_message);
                $notification_stmt->execute();
                
                // Notify pet owner
                $notification_stmt->bind_param("is", $pet_owner_user_id, $pet_owner_notification_message);
                $notification_stmt->execute();
                
                $notification_stmt->close();
            }
        }
        
        $stmt->close();
    }

    if (isset($_POST['cancel'])) {
        $id = $_POST['application_id'];
        
        // Get application details before canceling
        $query = "SELECT * FROM adoption_applications WHERE adopt_id=?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $result->num_rows > 0) {
            $application = $result->fetch_assoc();
            
            // Insert into processed_applications
            $insert_sql = "INSERT INTO processed_applications (adopt_id, user_id, pet_id, status, name, email, phone, address, occupation, previous_pet, marital_status, message, id_image) VALUES (?, ?, ?, 'canceled', ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $insert_stmt = $conn->prepare($insert_sql);
            $insert_stmt->bind_param("iiisssssssss", $application['adopt_id'], $application['user_id'], $application['pet_id'], $application['name'], $application['email'], $application['phone'], $application['address'], $application['occupation'], $application['previous_pet'], $application['marital_status'], $application['message'], $application['id_image']);
            $insert_stmt->execute();
            $insert_stmt->close();
            
            // Now update the status
            $update_sql = "UPDATE adoption_applications SET status='canceled' WHERE adopt_id=?";
            $stmt = $conn->prepare($update_sql);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            
            // Notify user
            $user_id = $application['user_id'];
            $notification_message = "Your application for pet ID " . htmlspecialchars($application['pet_id']) . " has been canceled.";
            
            // Insert notification
            $notification_query = "INSERT INTO notifications (user_id, message) VALUES (?, ?)";
            $notification_stmt = $conn->prepare($notification_query);
            $notification_stmt->bind_param("is", $user_id, $notification_message);
            $notification_stmt->execute();
            $notification_stmt->close();
        }

        $stmt->close();
    }
    
    // Handle user account deletion
    if (isset($_POST['delete_user'])) {
        $user_id = $_POST['user_id'];
        $delete_sql = "DELETE FROM users WHERE id=?";
        $stmt = $conn->prepare($delete_sql);
        $stmt->bind_param("i", $user_id);
        
        if ($stmt->execute()) {
            $_SESSION['message'] = $stmt->affected_rows > 0 ? "User  deleted successfully." : "User  not found or already deleted.";
        } else {
            $_SESSION['message'] = "Failed to delete user: " . $stmt->error;
        }
        
        $stmt->close();
    }

    // Handle pet listing deletion
    if (isset($_POST['delete_pet'])) {
        $pet_id = $_POST['pet_id'];
        $delete_pet_sql = "DELETE FROM pets_info WHERE pet_id=?";
        $stmt = $conn->prepare($delete_pet_sql);
        $stmt->bind_param("i", $pet_id);
        
        if ($stmt->execute()) {
            $_SESSION['message'] = $stmt->affected_rows > 0 ? "Pet listing deleted successfully." : "Pet not found or already deleted.";
        } else {
            $_SESSION['message'] = "Failed to delete pet: " . $stmt->error;
        }
        
        $stmt->close();
    }

    // Refresh the page to show updated status
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Set up pagination for applications
$limit = 3; 
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Fetch total count for applications
$total_sql = "SELECT COUNT(*) FROM adoption_applications WHERE status='pending'";
$total_result = $conn->query($total_sql);
$total_rows = $total_result ? $total_result->fetch_row()[0] : 0;
$total_pages = ceil($total_rows / $limit);

// Fetch adoption applications with pagination
$sql = "SELECT * FROM adoption_applications WHERE status='pending' LIMIT $limit OFFSET $offset";
$result = $conn->query($sql);

// Fetch pet listings
$pet_sql = "SELECT * FROM pets_info";
$pet_result = $conn->query($pet_sql);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="admin.css">
    <title>Admin Dashboard</title>
</head>
<style>
    /* Pet Listings Section */
    .pet-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); /* Responsive grid */
        gap: 20px;
        margin-top: 20px;
    }

    .pet-card {
        background-color: white;
        padding: 15px;
        text-align: center;
        border-radius: 5px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .pet-card h3 {
        font-size: 18px;
        margin: 10px 0;
    }

    .pet-card p {
        font-size: 14px;
        color: #666;
    }

    .pet-card img {
        width: 200px; /* Fixed width */
        height: 200px; /* Fixed height */
        object-fit: cover; /* Ensures the image covers the box without distortion */
        border-radius: 5px;
        margin-bottom: 10px;
    }

    .pet-card .btn-delete {
        background-color: #f44336;
        color: white;
        padding: 8px 12px;
        border: none;
        cursor: pointer;
        border-radius: 5px;
        margin-top: 10px;
    }

    .pet-card .btn-delete:hover {
        background-color: #d32f2f;
    }

    /* Pagination Styles for Pet Listings */
    .pagination {
        display: flex;
        justify-content: center;
        align-items: center;
        margin-top: 20px;
    }

    .page-btn {
        padding: 10px;
        background-color: #333;
        color: white;
        text-decoration: none;
        border-radius: 5px;
        margin: 0 5px;
    }

    .page-info {
        font-size: 14px;
    }

    /* Media Queries for Responsive Design */
    @media (max-width: 768px) {
        .pet-grid {
            grid-template-columns: 1fr; /* Single column on smaller screens */
        }
    }

    /* Adoption Applications Grid */
    .application-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr); /* Create 3 columns for each item */
        gap: 15px; /* Space between columns */
        margin-top: 20px;
        overflow-x: auto; /* Makes it horizontally scrollable if the content overflows */
        width: 100%; /* Ensure it takes up full width of the parent container */
        max-width: 100%; /* Ensure it is responsive */
    }

    .grid-body {
        display: contents; /* Makes each grid item align with the grid layout */
    }

    .grid-body .grid-item {
        background-color: white;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1); /* Adding shadow to grid items */
        border-radius: 5px;
        padding: 10px;
        text-align: center;
        font-size: 14px;
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    /* Column-specific styles */
    .grid-body .grid-item:nth-child(odd) {
        background-color: #f9f9f9; /* Alternate background color for readability */
    }

    /* Image styles inside the grid */
    .grid-item img {
        max-width: 100px;
        max-height: 100px;
        object-fit: cover;
        border-radius: 5px;
        margin-top: 10px;
    }

    /* Action Buttons */
    .btn-approve, .btn-cancel {
        padding: 6px 12px;
        font-size: 14px;
        border-radius: 5px;
        cursor: pointer;
        margin-top: 5px;
    }

    .btn-approve {
        background-color ```php
        #4CAF50;
        color: white;
        border: none;
    }

    .btn-approve:hover {
        background-color: #45a049;
    }

    .btn-cancel {
        background-color: #f44336;
        color: white;
        border: none;
    }

    .btn-cancel:hover {
        background-color: #d32f2f;
    }

    /* Pagination styles */
    .pagination {
        display: flex;
        justify-content: center;
        align-items: center;
        margin-top: 20px;
    }

    .page-btn {
        padding: 10px;
        background-color: #333;
        color: white;
        text-decoration: none;
        border-radius: 5px;
        margin: 0 5px;
    }

    .page-info {
        font-size: 14px;
    }

    /* Media Queries for Responsiveness */
    @media (max-width: 1024px) {
        .application-grid {
            grid-template-columns: repeat(2, 1fr); /* 2 columns on medium screens */
        }
    }

    @media (max-width: 768px) {
        .application-grid {
            grid-template-columns: 1fr; /* 1 column on small screens */
        }
    }

    /* Modal styles */
    .modal {
        display: none; /* Initially hidden */
        position: fixed;
        z-index: 1; /* Sit on top */
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.7); /* Black background with opacity */
        
        /* Flexbox for centering the modal content */
        display: flex;
        justify-content: center;
        align-items: center;
    }

    /* Modal content (the image) */
    .modal-content {
        max-width: 90%;
        max-height: 80%;
        display: block;
        margin: auto; /* Center the image horizontally */
        object-fit: contain; /* Ensure the image maintains its aspect ratio */
    }

    /* Caption under the image */
    #caption {
        text-align: center;
        color: white;
        padding: 10px;
        font-size: 18px;
    }

    /* Close button */
    .close {
        position: absolute;
        top: 10px;
        right: 25px;
        color: white;
        font-size: 36px;
        font-weight: bold;
        cursor: pointer;
    }

    .close:hover,
    .close:focus {
        color: #f1f1f1;
        text-decoration: none;
        cursor: pointer;
    }
</style>
<body>
<div class="dashboard-container">
    <header class="dashboard-header">
        <h1>Welcome, <span><?php echo htmlspecialchars($_SESSION['admin_name']); ?></span></h1>
        <br> <br>
        <a href="logout.php" class="btn-logout">Logout</a>
    </header>

    <nav class="dashboard-nav">
        <ul>
            <li><a href="#applications">Adoption Applications</a></li>
            <li><a href="#users">User  Accounts</a></li>
            <li><a href="#pets">Pet Listings</a></li>
        </ul>
    </nav>

    <main class="dashboard-content">
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></div>
        <?php endif; ?>
        
<!-- Adoption Applications Section -->
<section id="applications">
    <h2>Submitted Applications</h2>
    <div class="application-grid">
        <!-- Grid Body -->
        <div class="grid-body">
            <?php
            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo '<div class="grid-item">';
                    echo '<div>Name: ' . htmlspecialchars($row['name']) . '</div>';
                    echo '<div>Email: ' . htmlspecialchars($row['email']) . '</div>';
                    echo '<div>Phone: ' . htmlspecialchars($row['phone']) . '</div>';
                    echo '<div>Address: ' . htmlspecialchars($row['address']) . '</div>';
                    echo '<div>Occupation: ' . htmlspecialchars($row['occupation']) . '</div>';
                    echo '<div>Adopted Before: ' . htmlspecialchars($row['previous_pet']) . '</div>';
                    echo '<div>Marital Status: ' . htmlspecialchars($row['marital_status']) . '</div>';
                    echo '<div>' . nl2br(htmlspecialchars($row['message'])) . '</div>';
                    echo '<div>Pet ID: ' . htmlspecialchars($row['pet_id']) . '</div>';
                    echo '<div>Status: ' . htmlspecialchars($row['status']) . '</div>';
                    
                    // Image column
                    echo '<div class="grid-item image">';
                    if (!empty($row['id_image'])) {
                        echo '<img src="' . htmlspecialchars($row['id_image']) . '" alt="Pet Image" class="application-image" onclick="openModal(this.src)">';
                    } else {
                        echo 'No Image'; // Fallback text if no image exists
                    }
                    echo '</div>';

                    // Actions column
                    echo '<div>';
                    echo '<form method="POST" action="" style="display:inline;">';
                    echo '<input type="hidden" name="application_id" value="' . htmlspecialchars($row['adopt_id']) . '">';
                    if ($row['status'] === 'pending') {
                        echo '<button type="submit" name="approve" class="btn-approve">Approve</button>';
                        echo '<button type="submit" name="cancel" class="btn-cancel">Cancel</button>';
                    }
                    echo '</form>';
                    echo '</div>';
                    echo '</div>';  // End of grid item
                }
            } else {
                echo '<div class="grid-item" colspan="12">No applications found.</div>';
            }
            ?>
        </div>
    </div>

    <!-- Pagination -->
    <div class="pagination">
        <?php if ($page > 1): ?>
            <a href="?page=<?php echo $page - 1; ?>" class="page-btn">Previous</a>
        <?php endif; ?>
        <span class="page-info">Page <?php echo $page; ?> of <?php echo $total_pages; ?></span>
        <?php if ($page < $total_pages): ?>
            <a href="?page=<?php echo $page + 1; ?>" class="page-btn">Next</a>
        <?php endif; ?>
    </div>
</section>

<!-- Modal for Image Viewing -->
<div id="imageModal" class="modal">
    <span class="close" onclick="closeModal()">&times;</span>
    <img class="modal-content" id="modalImage">
    <div id="caption"></div>
</div>

<!-- User Accounts Section -->
<section id="users">
    <h2>User Accounts</h2>
    <div class="user-grid">
        <?php
        
        $users_per_page = 6;
        $user_page = isset($_GET['user_page']) ? (int)$_GET['user_page'] : 1;

        // Calculate the OFFSET (start point for SQL query)
        $offset = ($user_page - 1) * $users_per_page;

        // SQL query with pagination (LIMIT and OFFSET)
        $user_sql = "
            SELECT users.id AS user_id, users.name, users.email, users.user_type, 
                   GROUP_CONCAT(pets_info.pet_id ORDER BY pets_info.pet_id) AS pet_ids
            FROM users
            LEFT JOIN pets_info ON users.id = pets_info.user_id
            GROUP BY users.id
            ORDER BY users.user_type DESC, 
                     FIELD(users.user_type, 'admin', 'user'), 
                     LENGTH(GROUP_CONCAT(pets_info.pet_id)) DESC, 
                     users.name ASC
            LIMIT $users_per_page OFFSET $offset
        ";

        // Execute the query
        $user_result = $conn->query($user_sql);

        // Check if there are users to display
        if ($user_result && $user_result->num_rows > 0) {
            while ($user = $user_result->fetch_assoc()) {
                $user_id = $user['user_id'];
                $user_name = htmlspecialchars($user['name']);
                $user_email = htmlspecialchars($user['email']);
                $user_type = $user['user_type'];
                $pet_ids = $user['pet_ids']; 

                echo '<div class="user-card">';
                echo '<h3>' . $user_name . '</h3>';
                echo '<p>Email: ' . $user_email . '</p>';

                if ($user_type == 'admin') {
                    echo '<span class="role-badge">Admin</span>';
                }

                if ($pet_ids) {
                    echo '<p>Owned Pet IDs: ' . htmlspecialchars($pet_ids) . '</p>';
                } else {
                    echo '<p>No pets uploaded.</p>';
                }

                // Delete user form
                echo '<form method="POST" action="">';
                echo '<input type="hidden" name="user_id" value="' . $user_id . '">';
                echo '<button type="submit" name ```php
="delete_user" onclick="return confirm(\'Are you sure you want to delete this user?\');" class="btn-delete">Delete User</button>';
                echo '</form>';

                echo '</div>'; // End of user card
            }
        } else {
            echo '<p>No users found.</p>';
        }

        // SQL query to count total number of users
        $count_sql = "
            SELECT COUNT(*) AS total_users
            FROM users
        ";
        $count_result = $conn->query($count_sql);
        $total_users = $count_result->fetch_assoc()['total_users'];

        // Calculate total number of pages
        $total_user_pages = ceil($total_users / $users_per_page);
        ?>

        <!-- Pagination -->
        <div class="pagination">
            <?php if ($user_page > 1): ?>
                <a href="?user_page=<?php echo $user_page - 1; ?>" class="page-btn">Previous</a>
            <?php endif; ?>

            <span class="page-info">Page <?php echo $user_page; ?> of <?php echo $total_user_pages; ?></span>

            <?php if ($user_page < $total_user_pages): ?>
                <a href="?user_page=<?php echo $user_page + 1; ?>" class="page-btn">Next</a>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Pet Listing -->
<section id="pets">
    <h2>Pet Listings</h2>
    <div class="pet-grid">
        <?php
        // Set up pagination for pet listings
        $pet_limit = 4; // Set the limit to 4 per page
        $pet_page = isset($_GET['pet_page']) ? (int)$_GET['pet_page'] : 1;
        $pet_offset = ($pet_page - 1) * $pet_limit;

        // Fetch total count for pet listings
        $total_pet_sql = "SELECT COUNT(*) FROM pets_info";
        $total_pet_result = $conn->query($total_pet_sql);
        $total_pet_rows = $total_pet_result ? $total_pet_result->fetch_row()[0] : 0;
        $total_pet_pages = ceil($total_pet_rows / $pet_limit);

        // Fetch pet listings with pagination, and join with users to get the user_id
        $pet_sql = "
            SELECT pets_info.*, users.id AS user_id 
            FROM pets_info 
            LEFT JOIN users ON pets_info.user_id = users.id 
            LIMIT $pet_limit OFFSET $pet_offset
        ";
        $pet_result = $conn->query($pet_sql);

        if ($pet_result && $pet_result->num_rows > 0) {
            while ($pet = $pet_result->fetch_assoc()) {
                $pet_user_id = $pet['user_id']; // Get the user_id (the ID of the user who uploaded the pet)
                echo '<div class="pet-card">';
                echo '<img src="img/' . htmlspecialchars($pet['image']) . '" alt="' . htmlspecialchars($pet['name']) . '" class="pet-image">';
                echo '<h3>' . htmlspecialchars($pet['name']) . '</h3>';
                echo '<p>Breed: ' . htmlspecialchars($pet['breed']) . '</p>';
                echo '<p>Age: ' . htmlspecialchars($pet['bday']) . '</p>';
                echo '<p>Description: ' . htmlspecialchars($pet['description']) . '</p>';
                echo '<p>Uploaded by User ID: ' . htmlspecialchars($pet_user_id) . '</p>'; // Display the user ID
                echo '<form method="POST" action="">';
                echo '<input type="hidden" name="pet_id" value="' . htmlspecialchars($pet['pet_id']) . '">';
                echo '<button type="submit" name="delete_pet" onclick="return confirm(\'Are you sure you want to delete this pet?\');" class="btn-delete">Delete Pet</button>';
                echo '</form>';
                echo '</div>';
            }
        } else {
            echo '<p>No pet listings found.</p>';
        }
        ?>
    </div>

    <!-- Pagination for Pet Listings -->
    <div class="pagination">
        <?php if ($pet_page > 1): ?>
            <a href="?pet_page=<?php echo $pet_page - 1; ?>" class="page-btn">Previous</a>
        <?php endif; ?>
        <span class="page-info">Page <?php echo $pet_page; ?> of <?php echo $total_pet_pages; ?></span>
        <?php if ($pet_page < $total_pet_pages): ?>
            <a href="?pet_page=<?php echo $pet_page + 1; ?>" class="page-btn">Next</a>
        <?php endif; ?>
    </div>
</section>

    </main>
</div>

</body>
<script>
// Function to open the modal with the image
function openModal(imageSrc) {
    var modal = document.getElementById("imageModal");
    var modalImg = document.getElementById("modalImage");
    var caption = document.getElementById("caption");

    // Set the modal image source and caption
    modal.style.display = "flex"; // Use flex display for centering
    modalImg.src = imageSrc;
    caption.innerHTML = "Click to close";
}

// Function to close the modal
function closeModal() {
    var modal = document.getElementById("imageModal");
    modal.style.display = "none"; // Hide the modal
}

// Add event listener to close modal when clicked outside the image
window.onclick = function(event) {
    var modal = document.getElementById("imageModal");
    if (event.target === modal) {
        closeModal();
    }
}

// Ensure the modal is hidden when the page loads
window.onload = function() {
    var modal = document.getElementById("imageModal");
    modal.style.display = "none"; // Hide modal on page load
};

</script>
</html>

<?php
$conn->close();
?>