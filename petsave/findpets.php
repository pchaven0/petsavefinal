<?php 
session_start(); 
require 'config.php'; 

if (!isset($_SESSION['user_id'])) {
    header("Location: loginf.php");
    exit();
}

// Pagination variables
$limit = 6; 
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$filtervalue = '';
$searchTerm = '';
// Fetch user ID from session
$user_id = $_SESSION['user_id'];

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $filtervalue = $_GET['search'];
    $searchTerm = "%" . $filtervalue . "%"; // Add wildcards for LIKE
    $stmt = $conn->prepare("SELECT * FROM pets_info WHERE adopted = 0 AND user_id != ? AND (name LIKE ? OR breed LIKE ?) ORDER BY pet_id DESC");
    $stmt->bind_param("iss", $user_id, $searchTerm, $searchTerm); // Bind user_id, and both search parameters
    $stmt->execute();
    $result = $stmt->get_result();
    $totalRecords = $result->num_rows; // Get the number of records that match the search
} else {
    $stmt = $conn->prepare("SELECT * FROM pets_info WHERE adopted = 0 AND user_id != ? ORDER BY pet_id DESC LIMIT ?, ?");
    $stmt->bind_param("iii", $user_id, $offset, $limit); // Bind user_id and pagination parameters
    $stmt->execute();
    $result = $stmt->get_result();
    $totalRecords = $conn->query("SELECT COUNT(*) FROM pets_info WHERE adopted = 0 AND user_id != $user_id")->fetch_row()[0];
}

// Calculate total pages
$totalPages = ceil($totalRecords / $limit);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Find Pets</title>
    <link rel="stylesheet" href="pets.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.3/css/bootstrap.min.css">
</head>
<body>
<div class="container">
    <h1>Pets for Adoption</h1>
    <p>They are looking for their forever homes. One of them (or two) might be the perfect addition to your family.</p>

    <div style="text-align: center; margin-bottom: 20px;">
        <button class="btn" style="background-color: lime; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; font-size: 16px;" onclick="goToDashboard()">Back to Dashboard</button>
    </div>

    <div class="search-container">
        <input type="text" name="search" id="searchInput" placeholder="Search for pets..." class="form-control mb-3" value="<?php echo htmlspecialchars($filtervalue); ?>" oninput="searchPets(this.value)">
        <div id="searchResults" style="display: none;"></div>
    </div>

    <div class="grid-container" id="petList">
        <?php if ($totalRecords > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="grid-item" onclick="showPetDetails(<?php echo htmlspecialchars(json_encode($row)); ?>)">
                    <h5><?php echo htmlspecialchars($row['name']); ?></h5>
                    <p><strong>Breed:</strong> <?php echo htmlspecialchars($row['breed']); ?></p>
                    <p><strong>Birthday:</strong> <?php echo htmlspecialchars($row['bday']); ?></p>
                    <p><strong>Vaccinated:</strong> <?php echo $row['vaccinated'] ? 'Yes' : 'No'; ?></p>
                    <?php $imagePath = 'img/' . htmlspecialchars($row['image']); ?>
                    <img src="<?php echo $imagePath; ?>" alt="Pet Image"> 
                    <a href="javascript:void(0);" onclick="openAdoptionForm(<?php echo $row['pet_id']; ?>)" class="btn btn-success">Adopt</a>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No pets found matching your search.</p>
        <?php endif; ?>
    </div>

    <nav>
        <ul class="pagination">
            <?php if ($page > 1 && empty($filtervalue)): ?>
                <li class="page-item"><a class="page-link" href="?page=<?php echo $page - 1; ?>">Previous</a></li>
            <?php endif; ?>
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li class="page-item <?php echo $i === $page && empty($filtervalue) ? 'active' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                </li>
            <?php endfor; ?>
            <?php if ($page < $totalPages && empty($filtervalue)): ?>
                <li class="page-item"><a class="page-link" href="?page=<?php echo $page + 1; ?>">Next</a></li>
            <?php endif; ?>
        </ul>
    </nav>

    <div class="adoption-tips">
        <h2>Adoption Tips</h2>
        <ul>
            <li>Prepare your home for a new pet.</li>
            <li>Consider the pet's needs and lifestyle.</li>
            <li>Schedule a vet visit soon after adoption.</li>
        </ul>
    </div>

    <div class="success-stories">
    <h2>Success Stories</h2>
    <p>Read how these pets found their forever homes!</p>
    
    <div class="story">
        <img src="max.jpg" alt="Max the Rescue Dog">
        <h3>Max the Rescue Dog</h3>
        <p>After being rescued from a shelter, Max found his forever home with the Johnson family. Initially shy and scared, he quickly adapted to his new environment. Now, he loves to play fetch in the backyard and cuddle with the kids at night. Max’s transformation into a loving companion is a testament to the power of adoption!</p>
    </div>
    
    <div class="story">
        <img src="luna.jpg" alt="Luna the Tabby Cat">
        <h3>Luna’s Second Chance</h3>
        <p>Luna, a playful tabby cat, was found as a stray wandering the streets. After being taken in by a local rescue, she was adopted by Emily, a college student who wanted a furry friend. Luna now enjoys lounging in sunny spots and watching birds from the window. Their bond has brought joy to both their lives!</p>
    </div>
    
    <div class="story">
        <img src="buddy.jpg" alt="Buddy the Beagle">
        <h3>Buddy the Beagle</h3>
        <p>Buddy spent months in a shelter before being adopted by the Smith family. With a little patience and training, Buddy overcame his initial anxiety and became a beloved family member. Now, he goes on weekly hikes and loves to play with the kids, proving that every pet deserves a chance at happiness.</p>
    </div>
</div>
<br>
<div class="feedback-form">
    <h2>Leave Your Feedback</h2>
    <form action="" method="post">
        <label for="feedback_text">Your Feedback:</label>
        <textarea id="feedback_text" name="feedback_text" required></textarea>
        <button type="submit">Submit Feedback</button>
    </form>
    <?php
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Check if user is logged in and user_id is set
    if (!isset($_SESSION['user_id'])) {
        die("You must be logged in to submit feedback.");
    }

    // Prepare and bind
    $stmt = $conn->prepare("INSERT INTO feedback (user_id, feedback_text) VALUES (?, ?)");
    $stmt->bind_param("is", $user_id, $feedback_text);

    // Set parameters and execute
    $user_id = $_SESSION['user_id']; // Fetching the logged-in user's ID
    $feedback_text = $_POST['feedback_text'];

    if ($stmt->execute()) {
        header("Location: dashboard.php");
        exit(); // Make sure to exit after the redirect
    } else {
        echo "<p>Error: " . $stmt->error . "</p>";
    }

    // Close statement
    $stmt->close();
}

// Close connection
$conn->close();
?>
</div>

    <!-- this is adoption form modal -->
<div id="adoptionForm" style="display:none;">
    <h2>Adoption Application Form</h2>
    <form id="applicationForm" enctype="multipart/form-data" method="POST" action="submit_application.php">
        <input type="hidden" name="pet_id" id="pet_id" value="">
        <label for="name">Full Name:</label>
        <input type="text" id="name" name="name" required class="form-control">
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required class="form-control">
        <label for="phone">Phone Number:</label>
        <input type="tel" id="phone" name="phone" required class="form-control">
        <label for="address">Address:</label>
        <input type="text" id="address" name="address" required class="form-control">
        <label for="occupation">Occupation:</label>
        <input type="text" id="occupation" name="occupation" required class="form-control">

        <label for="previous_pet">Have you adopted a pet before?</label>
        <input type="checkbox" id="previous_pet" name="previous_pet" value="yes"><br>

        <label>Marital Status:</label>
        <label for="single">
            <input type="radio" id="single" name="marital_status" value="single" required> Single
        </label>

        <label for="married">
            <input type="radio" id="married" name="marital_status" value="married" required> Married
        </label><br>

        <label for="message">Why do you want to adopt this pet?</label>
        <textarea id="message" name="message" required class="form-control"></textarea>

        <label for="image">Upload Your Valid ID</label>
        <input type="file" id="image" name="image" accept="image/*" class="form-control">
        <br>
        <button type="submit" class="btn btn-success">Submit Application</button>
        <button type="button" class="btn btn-danger" onclick="closeAdoptionForm()">Cancel</button>
    </form>
</div>

<div id="petDetailModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <div id="petDetails"></div>
    </div>
</div>

<script>

function goToDashboard() {
    window.location.href = 'dashboard.php'; 
}

function searchPets(query) {
    const resultsContainer = document.getElementById('searchResults');
    const petList = document.getElementById('petList');
    
    if (query.length === 0) {
        resultsContainer.innerHTML = ''; 
        resultsContainer.style.display = 'none'; 
        petList.style.display = 'grid'; 
        return;
    }

    const xhr = new XMLHttpRequest();
    xhr.open('GET', `search_pets.php?search=${encodeURIComponent(query)}`, true);
    xhr.onload = function() {
        if (xhr.status === 200) {
            const results = JSON.parse(xhr.responseText);
            displayResults(results);
        }
    };
    xhr.send();
}

function displayResults(results) {
    const resultsContainer = document.getElementById('searchResults');
    const petList = document.getElementById('petList');
    
    resultsContainer.innerHTML = '';

    if (results.length === 0) {
        resultsContainer.innerHTML = '<p>No pets found.</p>';
        resultsContainer.style.display = 'block';
        petList.style.display = 'none'; 
        return;
    }

    results.forEach(pet => {
        resultsContainer.innerHTML += `
            <div class="grid-item" onclick="showPetDetails(${JSON.stringify(pet)})">
                <h5>${pet.name}</h5>
                <p><strong>Breed:</strong> ${pet.breed}</p>
                <p><strong>Birthday:</strong> ${pet.bday}</p>
                <p><strong>Vaccinated:</strong> ${pet.vaccinated ? 'Yes' : 'No'}</p>
                <img src="img/${pet.image}" alt="Pet Image" style="width: 100%; height: 200px; object-fit: cover;">
                <a href="javascript:void(0);" onclick="openAdoptionForm(${pet.pet_id})" class="btn btn-success">Adopt</a>
            </div>
        `;
    });

    resultsContainer.style.display = 'grid';
    petList.style.display = 'none';
}

function openAdoptionForm(petId) {
    document.getElementById('pet_id').value = petId;
    document.getElementById('adoptionForm').style.display = 'block';
}

function closeAdoptionForm() {
    document.getElementById('adoptionForm').style.display = 'none';
}

function showPetDetails(pet) {
    const petDetails = document.getElementById('petDetails');
    petDetails.innerHTML = `
        <h2>${pet.name}</h2>
        <img src="img/${pet.image}" alt="Pet Image" style="width: 100%;">
        <p><strong>Breed:</strong> ${pet.breed}</p>
        <p><strong>Birthday:</strong> ${pet.bday}</p>
        <p><strong>Vaccinated:</strong> ${pet.vaccinated ? 'Yes' : 'No'}</p>
        <p><strong>Description:</strong> ${pet.description}</p>
        <a href="javascript:void(0);" onclick="openAdoptionForm(${pet.pet_id})" class="btn btn-success">Adopt</a>
    `;
    document.getElementById('petDetailModal').style.display = 'block';
}

function closeModal() {
    document.getElementById('petDetailModal').style.display = 'none';
}
</script>
</body>
</html>
