<?php
session_start();
require 'config.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "Access denied. Please log in.";
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch user data
$query = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$userData = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Fetch notifications for the logged-in user
$notification_query = "SELECT * FROM notifications WHERE user_id = ? AND read_at IS NULL ORDER BY created_at DESC"; // Fetch only unread notifications
$notification_stmt = $conn->prepare($notification_query);
$notification_stmt->bind_param("i", $user_id);
$notification_stmt->execute();
$notifications = $notification_stmt->get_result();
$unread_count = $notifications->num_rows; // Count unread notifications

// Handle new pet submission
if (isset($_POST["submit"])) {
    $name = $_POST["name"];
    $breed = $_POST["breed"];
    $bday = $_POST["bday"];
    $vaccinated = isset($_POST["vaccinated"]) ? 1 : 0;
    $description = $_POST['description'];

    if ($_FILES["image"]["error"] == 4) {
        echo "<script>alert('Image does not exist.');</script>";
    } else {
        $fileName = $_FILES["image"]["name"];
        $fileSize = $_FILES["image"]["size"];
        $tmpName = $_FILES["image"]["tmp_name"];

        // Validate the image extension
        $validImageExtensions = ['jpg', 'jpeg', 'png'];
        $imageExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if (!in_array($imageExtension, $validImageExtensions)) {
            echo "<script>alert('Invalid image extension.');</script>";
        } elseif ($fileSize > 10000000) { // Limit to 10MB
            echo "<script>alert('Image size is too large.');</script>";
        } else {
            $newImageName = uniqid() . '.' . $imageExtension;

            // Attempt to move the uploaded file
            if (move_uploaded_file($tmpName, 'img/' . $newImageName)) {
                // Insert into the database with user_id
                $query = "INSERT INTO pets_info (user_id, name, breed, bday, vaccinated, image, description) VALUES (?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("isssiss", $user_id, $name, $breed, $bday, $vaccinated, $newImageName, $description);

                if ($stmt->execute()) {
                    echo "<script>alert('Successfully added pet.'); document.location.href = 'findpets.php';</script>";
                } else {
                    echo "<script>alert('Error adding pet.');</script>";
                }
                $stmt->close();
            } else {
                echo "<script>alert('Failed to upload image.');</script>";
            }
        }
    }
}

// Fetch user's contact information
$contact_query = "SELECT * FROM user_contacts WHERE user_id = ?";
$contact_stmt = $conn->prepare($contact_query);
$contact_stmt->bind_param("i", $user_id);
$contact_stmt->execute();
$contactInfo = $contact_stmt->get_result()->fetch_assoc();
$contact_stmt->close();

// Handle contact information update
if (isset($_POST['update_contact'])) {
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $telegram = $_POST['telegram'];
    $facebook = $_POST['facebook'];

    if ($contactInfo) {
        // Update existing contact
        $update_query = "UPDATE user_contacts SET phone = ?, email = ?, telegram = ?, facebook = ? WHERE user_id = ?";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param("ssssi", $phone, $email, $telegram, $facebook, $user_id);
    } else {
        // Insert new contact
        $insert_query = "INSERT INTO user_contacts (user_id, phone, email, telegram, facebook) VALUES (?, ?, ?, ?, ?)";
        $insert_stmt = $conn->prepare($insert_query);
        $insert_stmt->bind_param("issss", $user_id, $phone, $email, $telegram, $facebook);
    }

    if (($contactInfo && $update_stmt->execute()) || (!$contactInfo && $insert_stmt->execute())) {
        echo "<script>alert('Contact information updated successfully.');</script>";
    } else {
        echo "<script>alert('Error updating contact information.');</script>";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
    <link rel="stylesheet" href="dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
<div class="container">

<div class="sidebar">
    <section>
        <div class="session-container">
            <div class="session">
                <h1>Welcome <br> <span><?php echo htmlspecialchars($_SESSION['user_name']); ?></span></h1>
            </div>
        </div>
    </section>

    <h2 class="h2-notif">
        <a href="#notifications" onclick="openNotificationModal()">
            <i class="fas fa-bell"></i> Notifications 
            <?php if ($notifications && $notifications->num_rows > 0): ?>
                <span class="notification-badge"><?php echo $notifications->num_rows; ?></span>
            <?php else: ?>
                <span class="notification-badge">0</span>
            <?php endif; ?>
        </a>
    </h2>
    <ul>
        <li><a href="index.php">Home</a></li>
        <li><a href="findpets.php">Find Pets</a></li>
        <li><a href="#" onclick="openNewPetModal()">New Pets</a></li>
        <li><a href="#pet-div" onclick="toggleSection('pet-div')">Your Pets</a></li>
        <li><a href="#application-div" onclick="toggleSection('application-div')">Submitted Applications</a></li>
        <li><a href="javascript:void(0)" onclick="openContactModal()">Your Contact Info</a></li>
        <li><a href="logout.php">Logout</a></li>
    </ul>
</div>

 
<div class="main-dash">
    <!-- Instruction on how Pet Adoption Works -->
        <div class="instruction" id="instruction">
        <h2>Owner Of Pet Before Adoption</h2>
        <p>Expect to recieve updates from the new owner as they are obliged to update you as former pet owner.
        <p>Make Sure to Input/Update your Contact Information if you have new information so that you can be easily contacted when needed.

            <h3>How Pet Adoption Works</h3>
            <p>Adopting a pet is a rewarding experience that involves several steps. Here's how it works:</p>

            <h4>Before Adoption</h4>
            <ul>
                <li><strong>Step 1:</strong> Browse available pets and select the one you'd like to adopt.</li>
                <li><strong>Step 2:</strong> Submit an application with details about yourself and why you'd like to adopt this pet.</li>
                <li><strong>Step 3:</strong> Wait for the Admin to review and approve your application.</li>
                <li><strong>Step 4:</strong> If approved, chat with the pet owner by getting their contact information through notifications on other platforms, finalize the adoption process, and prepare to bring your new pet home!</li>
            </ul>
            
        <h4>After Adoption</h4>
        <ul>
            <li><strong>Step 5:</strong> Schedule a visit to your new pet's home if required, to ensure a smooth transition.</li>
            <li><strong>Step 6:</strong> Prepare your home for the new pet ensure you have all necessary supplies such as food, bedding, toys, and grooming tools.</li>
            <li><strong>Step 7:</strong> Introduce your new pet to their new environment gradually and with patience, allowing them time to adjust.</li>
            <li><strong>Step 8:</strong> You are obliged to follow up with the pet adoption agency or owner for updates and support during the transition period.</li>
            <li><strong>Step 9:</strong> Enjoy the companionship of your new pet, and remember to provide them with love, care, and attention!</li>
        </ul>
    </div>

<!-- Pet Adoption Laws Section -->
<div class="instruction" id="pet-laws">
    <h3>Pet Adoption Laws & Guidelines in the Philippines</h3>
    <p>Before adopting a pet, it's important to understand the laws and regulations in place to protect both animals and pet owners. The following are some legal considerations:</p>
    
    <h4>Legal Requirements for Pet Adoption</h4>
    <ul>
        <li><strong>Adopter's Legal Age:</strong> The adopter must be of legal age (18 years or older) to adopt a pet. This is to ensure that the adopter can take full responsibility for the pet's care and welfare.</li>
        <li><strong>Adoption Process:</strong> Adoption agencies or pet owners must ensure that potential adopters meet the legal requirements set out by the <em>Animal Welfare Act of 1998 (RA 10631)</em> and other relevant laws.</li>
        <li><strong>Vaccination & Health Requirements:</strong> The adopted pet must be vaccinated against rabies and other diseases before the adoption, in compliance with the <em>Anti-Rabies Act of 2007 (RA 9482)</em>.</li>
        <li><strong>Spaying/Neutering:</strong> Some adoption agencies require pets to be spayed or neutered before adoption as part of the process to control the pet population. Check with the agency for specific requirements.</li>
        <li><strong>Microchipping:</strong> Microchipping may be required for the identification of adopted pets, as mandated by the <em>Animal Welfare Act</em> and the <em>Anti-Rabies Act</em>.</li>
        <li><strong>Animal Cruelty Laws:</strong> All pets must be adopted in compliance with animal cruelty prevention laws. Pet owners must treat the adopted animal with respect, providing proper care, food, shelter, and medical attention as required by law.</li>
    </ul>

    <h4>Responsibilities of the Pet Owner Post-Adoption</h4>
    <ul>
        <li><strong>Updates on the Adopted Pet:</strong> Pet owners are legally required to receive updates from the new pet owner. This is to ensure the well-being of the pet after adoption and to monitor their adjustment to the new environment. Regular updates should include the pet’s health status, behavior, and general welfare.</li>
        <li><strong>Reporting Concerns:</strong> If the new pet owner encounters any problems or is unable to care for the pet properly, the original pet owner or adoption agency should be contacted immediately for support or potential re-homing.</li>
        <li><strong>Legal Liability:</strong> If the adopted pet faces mistreatment or neglect, both the adopter and the adoption agency (if applicable) may be held legally responsible. It's important to document any concerns and address them with the appropriate authorities.</li>
    </ul>

    <h4>Animal Welfare and Protection</h4>
    <ul>
        <li><strong>Prevention of Cruelty:</strong> Under the Animal Welfare Act, cruelty to animals is illegal. Pets must be treated humanely and with respect at all times, and abuse or neglect may lead to legal consequences, including fines and imprisonment.</li>
        <li><strong>Pet Ownership Rights:</strong> As a pet owner, you are legally responsible for your pet’s health, safety, and well-being. Make sure you comply with all relevant local and national regulations regarding pet care, including licensing and vaccination requirements.</li>
    </ul>

    <p>Understanding and complying with these legal frameworks and responsibilities is essential for ensuring the safety and well-being of your adopted pet.</p>
</div>

        <h1>Your Records</h1>

        <?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['adopt_pet'])) {
    $pet_id = $_POST['pet_id'];

    // Insert the pet into the adopted_pets table
    $adopted_query = "INSERT INTO adopted_pets (pet_id, user_id, name, breed, bday, vaccinated, image) 
                      SELECT pet_id, user_id, name, breed, bday, vaccinated, image 
                      FROM pets_info WHERE pet_id = ?";
    $adopted_stmt = $conn->prepare($adopted_query);
    $adopted_stmt->bind_param("i", $pet_id);

    if ($adopted_stmt->execute()) {
        // Update the pet's status in the pets_info table
        $update_query = "UPDATE pets_info SET adopted = 1 WHERE pet_id = ?";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param("i", $pet_id);

        if ($update_stmt->execute()) {
            echo "<p>Pet has been marked as adopted.</p>";
        } else {
            echo "<p>Error updating pet's adoption status.</p>";
        }
        $update_stmt->close();
    } else {
        echo "<p>Error adopting the pet: " . $conn->error . "</p>";
    }
    $adopted_stmt->close();

    // Redirect to the same page to prevent resubmission
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}
?>

  <!-- Your Pets Section -->
<div id="pet-div" class="pet-div">
    <?php
    // Pagination settings
    $limit = 5; // Number of pets per page
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $offset = ($page - 1) * $limit;

    // Count total pets for pagination
    $count_query = "SELECT COUNT(*) as total FROM pets_info WHERE user_id = ?";
    $count_stmt = $conn->prepare($count_query);
    $count_stmt->bind_param("i", $user_id);
    $count_stmt->execute();
    $total_result = $count_stmt->get_result()->fetch_assoc();
    $total_pets = $total_result['total'];
    $total_pages = ceil($total_pets / $limit);
    $count_stmt->close();

    // Query to get the pets for the current user with pagination
    $query = "SELECT * FROM pets_info WHERE user_id = ? LIMIT ?, ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iii", $user_id, $offset, $limit);
    $stmt->execute();
    $pets = $stmt->get_result();

    if ($pets->num_rows > 0) {
        echo "<div class='pets-grid'>";
        
        while ($pet = $pets->fetch_assoc()) {
            $pet_id = $pet['pet_id'];
            
            // Query to get applications for this pet with a limit of 2
            $app_query = "SELECT * FROM adoption_applications WHERE pet_id = ? AND user_id != ? LIMIT 2";
            $app_stmt = $conn->prepare($app_query);
            $app_stmt->bind_param("ii", $pet_id, $user_id);
            $app_stmt->execute();
            $applications = $app_stmt->get_result();

            echo "<div class='pet-card'>";
            echo "<img src='img/" . htmlspecialchars($pet['image']) . "' alt='Pet Image' width='100'>";
            echo "<h3>" . htmlspecialchars($pet['name']) . "</h3>";
            echo "<p><strong>Breed:</strong> " . htmlspecialchars($pet['breed']) . "</p>";
            echo "<p><strong>Birthday:</strong> " . htmlspecialchars($pet['bday']) . "</p>";
            echo "<p><strong>Vaccinated:</strong> " . ($pet['vaccinated'] ? 'Yes' : 'No') . "</p>";

           // Display applications for the pet
if ($applications->num_rows > 0) {
    echo "<p><strong>Applications:</strong></p>";
    echo "<ul>";

    while ($application = $applications->fetch_assoc()) {
        $applicantName = htmlspecialchars($application['name']);
        $email = htmlspecialchars($application['email']);
        $message = htmlspecialchars($application['message']);
        $status = htmlspecialchars($application['status']);
        $phone = htmlspecialchars($application['phone']);
        $address = htmlspecialchars($application['address']);
        $occupation = htmlspecialchars($application['occupation']);
        $previousPet = htmlspecialchars($application['previous_pet']);
        $maritalStatus = htmlspecialchars($application['marital_status']);
        $idImage = htmlspecialchars($application['id_image']);

        echo "<li>";
        echo "<p><strong>Applicant Name:</strong> $applicantName</p>";
        echo "<p><strong>Email:</strong> $email</p>";
        echo "<p><strong>Phone:</strong> $phone</p>";
        echo "<p><strong>Address:</strong> $address</p>";
        echo "<p><strong>Occupation:</strong> $occupation</p>";
        echo "<p><strong>Previous Pet Experience:</strong> " . ($previousPet === 'yes' ? 'Yes' : 'No') . "</p>";
        echo "<p><strong>Marital Status:</strong> $maritalStatus</p>";
        echo "<p><strong>Status:</strong> $status</p>";
        if (!empty($message)) {
            echo "<p><strong>Message:</strong> $message</p>";
        }
        
        // Check if the ID image exists before displaying it
        if (!empty($idImage)) {
            $imgPath = "uploads/id_image/" . $idImage; 
            if (file_exists($imgPath)) { // Check if the file exists
                echo "<img src='" . $imgPath . "' alt='ID Image' style='width: 100px; height: auto; border-radius: 8px;'>";
            } else {
                echo "<p>ID image not found.</p>"; 
            }
        }
        echo "</li><hr>";
    }
    echo "</ul>";
} else {
    echo "<p>No applications</p>";
}

            // Adopted button: Only show if the pet is not already adopted
            if (!$pet['adopted']) {
                echo "<form method='POST' action=''>";
                echo "<input type='hidden' name='pet_id' value='" . $pet['pet_id'] . "'>";
                echo "<button type='submit' name='adopt_pet'>Mark as Adopted</button>";
                echo "</form>";
            } else {
                echo "<p>This pet has been adopted.</p>";
            }
            echo "</div>";
            $app_stmt->close();
        }
        echo "</div>";
    } else {
        echo "<p>You have no pets listed.</p>";
    }
    $stmt->close();

    // Handle the adoption process
    if (isset($_POST['adopt_pet'])) {
        $pet_id = $_POST['pet_id'];
    
        // Insert the pet into the adopted_pets table
        $adopted_query = "INSERT INTO adopted_pets (pet_id, user_id, name, breed, bday, vaccinated, image) 
                          SELECT pet_id, user_id, name, breed, bday, vaccinated, image 
                          FROM pets_info WHERE pet_id = ?";
        $adopted_stmt = $conn->prepare($adopted_query);
        $adopted_stmt->bind_param("i", $pet_id);
    
        if ($adopted_stmt->execute()) {
            // Update the pet's status in the pets_info table
            $update_query = "UPDATE pets_info SET adopted = 1 WHERE pet_id = ?";
            $update_stmt = $conn->prepare($update_query);
            $update_stmt->bind_param("i", $pet_id);
    
            if ($update_stmt->execute()) {
                echo "<p>Pet has been marked as adopted.</p>";
            } else {
                echo "<p>Error updating pet's adoption status.</p>";
            }
            $update_stmt->close();
        } else {
            echo "<p>Error adopting the pet: " . $conn->error . "</p>";
        }
        $adopted_stmt->close();
    }

    // Pagination controls
    echo "<div class='pagination'>";
    if ($page > 1) {
        echo "<a href='?page=" . ($page - 1) . "'>&laquo; Previous</a>";
    }
    for ($i = 1; $i <= $total_pages; $i++) {
        echo "<a href='?page=$i'>$i</a>";
    }
    if ($page < $total_pages) {
        echo "<a href='?page=" . ($page + 1) . "'>Next &raquo;</a>";
    }
    echo "</div>";
    ?>
</div>


        <!-- Your Submitted Applications Section -->
        <div id="application-div" class="application-div">
            <?php
            $query = "SELECT aa.*, pi.name AS pet_name, u.name AS user_name
                      FROM adoption_applications aa 
                      JOIN pets_info pi ON aa.pet_id = pi.pet_id
                      JOIN users u ON aa.user_id = u.id
                      WHERE aa.user_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $applications = $stmt->get_result();

            if ($applications->num_rows > 0) {
                while ($application = $applications->fetch_assoc()) {
                    echo "<div class='application-card'>";
                    echo "<p><strong>Pet Name:</strong> " . htmlspecialchars($application['pet_name']) . "</p>";
                    echo "<p><strong>Status:</strong> " . htmlspecialchars($application['status']) . "</p>";
                    echo "<br>";
                    echo "</div>";
                }
            } else {
                echo "<p>No submitted applications.</p>";
            }
            ?>
        </div>

    </div>
</div>

<!-- New pets form -->
<div id="addPetModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeNewPetModal()">&times;</span>
        <h2>Add New Pet</h2>
        <form id="addPetForm" action="" method="post" enctype="multipart/form-data">
            <label for="name">Pets Name:</label>
            <input type="text" name="name" id="name" required>

            <label for="breed">Breed:</label>
            <input type="text" name="breed" id="breed" required>

            <label for="bday">Birthday:</label>
            <input type="date" name="bday" id="bday" required>

            <label for="vaccinated">Vaccinated: If Yes Please Check</label>
            <input type="checkbox" name="vaccinated" id="vaccinated">

            <label for="description">Description:</label>
            <textarea name="description" id="description" rows="4" placeholder="Describe your pet..."></textarea>

            <label for="image">Image:</label>
            <input type="file" name="image" id="image" accept=".jpg, .jpeg, .png" required>

            <button type="submit" name="submit">Submit</button>
        </form>
    </div>
</div>

<!-- Notification Modal -->
<div id="notificationModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeNotificationModal()">&times;</span>
        <h2>Your Notifications</h2>
        <?php if ($notifications && $notifications->num_rows > 0): ?>
            <ul>
                <?php while ($notification = $notifications->fetch_assoc()): ?>
                    <li>
                        <?php echo htmlspecialchars($notification['message']); ?> 
                        (<?php echo $notification['created_at']; ?>)
                        <?php if (is_null($notification['read_at'])): ?>
                            <form method="POST" action="mark_as_read.php" style="display:inline;" 
                                  onsubmit="markAsRead(event, <?php echo $notification['id']; ?>)">
                                <input type="hidden" name="notification_id" value="<?php echo $notification['id']; ?>">
                                <button type="submit" name="mark_as_read">Mark as Read</button>
                            </form>
                        <?php endif; ?>
                    </li>
                <?php endwhile; ?>
            </ul>
        <?php else: ?>
            <p>No notifications.</p>
        <?php endif; ?>
    </div>
</div>

<!-- Contact Information Modal -->
<div id="contactModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeContactModal()">&times;</span>
        <h2>Your Contact Information</h2>
        <form action="" method="POST">
            <label for="phone">
                <i class="fas fa-phone"></i> Phone:
            </label>
            <input type="text" name="phone" id="phone" value="<?php echo htmlspecialchars($contactInfo['phone'] ?? ''); ?>" required>

            <label for="email">
                <i class="fas fa-envelope"></i> Email:
            </label>
            <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($contactInfo['email'] ?? ''); ?>" required>

            <label for="telegram">
                <i class="fab fa-telegram-plane"></i> Telegram Username:
            </label>
            <input type="text" name="telegram" id="telegram" value="<?php echo htmlspecialchars($contactInfo['telegram'] ?? ''); ?>">

            <label for="facebook">
                <i class="fab fa-facebook"></i> Facebook:
            </label>
            <input type="text" name="facebook" id="facebook" value="<?php echo htmlspecialchars($contactInfo['facebook'] ?? ''); ?>">

            <button type="submit" name="update_contact">Update Contact Info</button>
        </form>
    </div>
</div>

<script>
  // Select the pet and application sections and the instruction section
  const petSection = document.querySelector('.pet-div');
    const applicationSection = document.querySelector('.application-div');
    const instruction = document.querySelector('.instruction');

    // Toggle visibility of instruction
    function toggleInstructionVisibility() {
        // Toggle the 'show' class to show or hide the instruction section
        instruction.classList.toggle('show');
    }

    // Add click event listeners to both the pet and application sections
    petSection.addEventListener('click', toggleInstructionVisibility);
    applicationSection.addEventListener('click', toggleInstructionVisibility);

// Function to open the notification modal
function openNotificationModal() {
    const notificationModal = document.getElementById('notificationModal');
    if (notificationModal) {
        notificationModal.style.display = 'block';
    }
}

function closeNotificationModal() {
    var modal = document.getElementById("notificationModal");
    modal.style.display = "none"; // Hide the modal
}

// Function to open the new pet modal
function openNewPetModal() {
    const newPetModal = document.getElementById('addPetModal');
    if (newPetModal) {
        newPetModal.style.display = 'block';
    }
}

function closeNewPetModal() {
    var modal = document.getElementById("addPetModal");
    modal.style.display = "none"; // Hide the modal
}

// Function to open the contact info modal
function openContactModal() {
    const contactModal = document.getElementById('contactModal');
    if (contactModal) {
        contactModal.style.display = 'block';
    }
}

function closeContactModal() {
    var modal = document.getElementById("contactModal");
    modal.style.display = "none"; // Hide the modal
}

// Function to close modals
function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'none';
    }
}

// Event listener to close modals when clicking outside of them
window.onclick = function(event) {
    const modalIds = ['notificationModal', 'addPetModal', 'contactModal'];
    modalIds.forEach(id => {
        const modal = document.getElementById(id);
        if (modal && event.target == modal) {
            modal.style.display = "none";
        }
    });
}

function markAsRead(event, notificationId) {
    event.preventDefault(); // Prevent default form submission

    const formData = new FormData(event.target);

    fetch('mark_as_read.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update the badge count
            const badge = document.querySelector('.notification-badge');
            if (badge) {
                let currentCount = parseInt(badge.textContent);
                currentCount--; // Decrement badge count
                badge.textContent = currentCount;

                if (currentCount <= 0) {
                    badge.style.display = 'none'; 
                }
            }
            // Optionally, remove the notification from the modal
            event.target.closest('li').remove();
        } else {
            alert('Failed to mark as read.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('There was an error processing your request.');
    });
}

</script>

</body>
</html>

<?php
$conn->close();