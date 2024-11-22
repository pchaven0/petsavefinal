<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pet Adoption FAQ</title>
    <link href="style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
 <style>
 /* FAQ Section */
.faq-section {
    padding: 40px 20px;
    background-image: url('faqbg.jpg'); 
    background-repeat: no-repeat; 
    background-size: cover; 
    background-position: center; 
    border-radius: 10px;
    margin: auto;
    width: 100%;
    max-width: 11000px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    text-align: left;
    height: 100vh; 
    overflow-y: auto; 
    color: white; 
}

.faq-section h1 {
    text-align: center;
    margin-bottom: 20px;
}

/* Adjust FAQ Item Padding */
.faq-item {
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s, box-shadow 0.3s;
    min-height: 150px; 
}

.faq-item:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
}

.faq-item h2 {
    margin-bottom: 10px;
}

.faq-item p {
    color: white;
    font-size: 16px;
    line-height: 1.6;
}

/* Responsive Design */
@media (max-width: 768px) {
    .hero-section {
        padding: 10px 0;
    }

    .txthm h1 {
        font-size: 24px;
    }

    .txthm p {
        font-size: 16px;
    }

    .home_btn button {
        padding: 8px 15px;
        font-size: 0.9em;
    }

    .faq-section {
        padding: 20px;
        width: 95%;
    }

    .faq-grid {
        grid-template-columns: 1fr;
        gap: 15px;
    }

    .faq-item h2 {
        font-size: 20px;
    }

    .faq-item p {
        font-size: 14px;
    }
}

@media (max-width: 480px) {
    .txthm h1 {
        font-size: 20px;
    }

    .txthm p {
        font-size: 14px;
    }

    .faq-section {
        padding: 15px;
    }

    .faq-item h2 {
        font-size: 18px;
    }

    .faq-item p {
        font-size: 13px;
    }
}
    </style>
</head>

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


<body>
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
            <p>he animal welfare laws address issues around animal abuse, cruelty and neglect. 
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
