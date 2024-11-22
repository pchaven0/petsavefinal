<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <title>Contact Us</title>
    <style>
    
    </style>
</head>
<body>

<nav class="navbar">
    <a href="index.php"><div class="logo"><span class="text-primary">Pet</span>Save</div></a>
    <ul>
        <li><a href="javascript:void(0);" onclick="openModal('loginModal')">Login</a></li>
        <li><a href="javascript:void(0);" onclick="openModal('registerModal')">Register</a></li>
        <li><a href="faq.php">FAQ</a></li>
        <li><a href="services.php">Services</a></li>
        <li><a href="contact.php">Contact</a></li>
    </ul>
    <div class="hamburger" onclick="toggleMenu()">
      <span></span>
      <span></span>
      <span></span>
    </div>
  </nav>


     <!-- This is login php logic -->
<?php
@include 'config.php';

session_start();

if (isset($_POST['submit'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $pass = md5($_POST['password']); 

    // Selecting the user based on email and password
    $select = "SELECT * FROM users WHERE email = '$email'";
    $result = mysqli_query($conn, $select);

    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_array($result);

        // Verify the password
        if ($row['password'] === $pass) {
            // Check user type and set session variables
            if ($row['user_type'] == 'admin') {
                $_SESSION['admin_name'] = $row['name'];
                header('location: adminp.php'); // Redirect to admin page
                exit();
            } elseif ($row['user_type'] == 'user') {
                $_SESSION['user_name'] = $row['name'];
                $_SESSION['user_id'] = $row['id']; // Optional: Store user ID if needed
                header('location: dashboard.php'); // Redirect to user dashboard
                exit();
            }
        } else {
            $error[] = 'Incorrect password!';
        }
    } else {
        $error[] = 'No account found with that email!';
    }
}
?>

<!-- Login Modal -->
<div id="loginModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('loginModal')">&times;</span>
        <h2>Login</h2>
        <form action="" method="post">
            <?php
            if (isset($error)) {
                foreach ($error as $error) {
                    echo '<span class="error-msg">' . $error . '</span>';
                }
            }
            ?>
            <input type="email" name="email" required placeholder="Enter your email">
            <input type="password" name="password" required placeholder="Enter your password">
            <input type="submit" name="submit" value="Login Now" class="form-btn">
        </form>
        <p>Don't have an account? <a href="javascript:void(0);" onclick="openModal('registerModal')">Register here</a></p>
    </div>
</div>



<!-- This is register php logic -->
<?php

@include 'config.php';

if (isset($_POST['submit'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $pass = md5($_POST['password']);
    $cpass = md5($_POST['cpassword']);
    $user_type = $_POST['user_type'];

    // Check if user already exists
    $select = "SELECT * FROM users WHERE email = '$email' AND password = '$pass'";
    $result = mysqli_query($conn, $select);

    if (mysqli_num_rows($result) > 0) {
        $error[] = 'User already exists!';
    } else {
        // If user is an admin, check how many admins exist
        if ($user_type === 'admin') {
            $admin_count_query = "SELECT COUNT(*) as admin_count FROM users WHERE user_type = 'admin'";
            $admin_count_result = mysqli_query($conn, $admin_count_query);
            $admin_count = mysqli_fetch_assoc($admin_count_result)['admin_count'];

            if ($admin_count >= 5) {
                $error[] = 'Maximum of 5 admin accounts allowed!';
            }
        }

        // Check if passwords match
        if ($pass != $cpass) {
            $error[] = 'Passwords do not match!';
        } else {
            // Insert new user
            $insert = "INSERT INTO users(name, email, password, user_type) VALUES('$name', '$email', '$pass', '$user_type')";
            mysqli_query($conn, $insert);
            header('location:index.php');
        }
    }
}

?>

<!-- Register Modal -->
<div id="registerModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('registerModal')">&times;</span>
        <h2>Register Here</h2>
        <form action="" method="post">
            <?php
            if (isset($error)) {
                foreach ($error as $error) {
                    echo '<span class="error-msg">' . $error . '</span>';
                }
            }
            ?>
            <input type="text" name="name" required placeholder="Enter your name">
            <input type="email" name="email" required placeholder="Enter your email">
            <input type="password" name="password" required placeholder="Enter your password">
            <input type="password" name="cpassword" required placeholder="Confirm your password">
            <select name="user_type">
                <option value="user">User</option>
                <option value="admin">Admin</option>
            </select>
            <input type="submit" name="submit" value="Register Now" class="form-btn">
        </form>
        <p>Already have an account? <a href="javascript:void(0);" onclick="openModal('loginModal')">Login here</a></p>
    </div>
</div> 

  <div class="contact">
  <h1>Contact Us</h1>
<form action="contact.php" method="post">
    <label for="name">Name:</label>
    <input type="text" id="name" name="name" required>
    
    <label for="email">Email:</label>
    <input type="email" id="email" name="email" required>

    <label for="message">Message:</label>
    <textarea id="message" name="message" rows="4" required></textarea>

    <label>Services Interested In:</label>
    <label><input type="checkbox" name="services[]" value="Pet Adoption"> Pet Adoption</label>
    <label><input type="checkbox" name="services[]" value="Volunteer Opportunities"> Volunteer Opportunities</label>
    <label><input type="checkbox" name="services[]" value="Donations"> Donations</label>
    <label><input type="checkbox" name="services[]" value="Other"> Other</label><br>
    <input type="submit" value="Submit" class="submit-btn">
</form>
</div>


<?php
// Database connection
$servername = "localhost"; 
$username = "root";
$password = "";
$dbname = "petsave_db1";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data safely
    $name = isset($_POST['name']) ? $_POST['name'] : '';
    $email = isset($_POST['email']) ? $_POST['email'] : '';
    $message = isset($_POST['message']) ? $_POST['message'] : '';
    $services = isset($_POST['services']) ? implode(", ", $_POST['services']) : '';

    // Prepare and bind
    $stmt = $conn->prepare("INSERT INTO contacts (name, email, message, services) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $name, $email, $message, $services);
    
    // Execute
    if ($stmt->execute()) {
        // Redirect to the same page (or a different one) to prevent resubmission
        header("Location: " . $_SERVER['PHP_SELF']);
        exit(); 
    }
    
    $stmt->close();
} 

$conn->close(); 
?>



    <div id="map">
    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d207072.59500589583!2d120.05579796385491!3d15.33903632391043!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x339424b9cbc0412d%3A0xf00bca611e82baf1!2sIba%2C%20Zambales!5e0!3m2!1sen!2sph!4v1730254945666!5m2!1sen!2sph" 
      width="100%" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
    </div>





<!-- Footer Section -->
<footer>
    <div class="footer-content">
        <h2>Stay Connected</h2>
        <p>Follow us on our social media channels to stay updated!</p>
        <div class="social-icons">
            <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
            <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
            <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
        </div>
    </div>
    <div class="footer-bottom">
        <p>&copy; 2024 Petsave. All rights reserved.</p>
    </div>
</footer>

</body>
<script>
function toggleMenu() {
      document.querySelector('.navbar').classList.toggle('active');
    }
    function openModal(modalId) {
    document.getElementById(modalId).style.display = "block";
}
// this is for login and register pop up modal 
function closeModal(modalId) {
    document.getElementById(modalId).style.display = "none";
}

// Close the modal if the user clicks anywhere outside of the modal
window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
        closeModal('loginModal');
        closeModal('registerModal');
    }
}
</script>
</html>
