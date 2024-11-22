<!DOCTYPE html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PetSave</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<style>
</style>

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

<body>
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

<section class="hero-section">
    <center>
        <div class="txthm">
        <div class="header-content">
            <img src="petsave.png" alt="Header Image" class="header-image">
            <h1 class="header-title">Welcome to PetSave</h1>
            <p class="header-subtitle">Your trusted partner in pet adoption</p>
        </div>
        </div>
    </center>
<br> <br> <br>
    <center>
        <div class="home_btn">
            <button class="find_pets"><a href="javascript:void(0);" onclick="openModal('loginModal')">Find Pets</a></button>
            <button class="learn_more"><a href="services.php">Learn More</a></button>
        </div>
    </center>
<br> <br>
    <div class="video-container">
    <iframe src="https://www.youtube.com/embed/LR5s0KPSbe8?si=JCgksCmDzE0SXD5H" 
        title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" 
        referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
        <p>CTTO, Video not ours.</p>
    </div>
    <p>&copy; CTTO, Video not ours.</p>
    </section>

 <!-- FAQ Section -->
 <section class="faq-section">
    <h1>Pet Adoption FAQ</h1>
    <div class="faq-grid">
        <div class="faq-item">
            <h2>How can I adopt from PETSAVE?</h2>
            <p> You Can adopt from our webiste by Registering an Account so that you can access Our Find Pets
                Feature there you can Fill up the Required Adoption Application Form and wait for confirmation,
                Ensure that your Details are correct and credible.
            </p>
        </div>
        <div class="faq-item">
            <h2>Can you adopt my pet?</h2>
            <p>PETSAVE does NOT adopt pets.</p>
        </div>
        <div class="faq-item">
            <h2>Can my adoption application get denied?</h2>
            <p>Yes. Please make sure you answered the Adoption Application form correctly and uploaded a readable Valid ID, Some reasons include incompatibility with the household or unsafe conditions for pets.</p>
        </div>
        <div class="faq-item">
            <h2>Can I adopt more than one pet?</h2>
            <p>Yes, but it depends on the situation. Some animals may be part of a bonded pair.</p>
        </div>  
        <div class="faq-item">
            <h2>Animal Welfare Laws</h2>
            <p>Animal welfare laws address issues around animal abuse, cruelty and neglect. 
                It is important to understand the laws enacted in the Philippines 
                n order to ensure humane treatment of our animals and promote their welfare.
            <a href="https://www.caraphil.org/animal-welfare-laws/">Read More.</a></p>
        </div>  
    </div>
</section>

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
