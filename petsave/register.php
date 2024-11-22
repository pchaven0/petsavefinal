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
            header('location:loginf.php');
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Form</title>
    <link rel="stylesheet" href="user.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>

<nav class="navbar">
    <a href="index.php"><div class="logo"><span class="text-primary">Pet</span>Save</div></a>
    <ul>
        <li><a href="loginf.php">Login</a></li>
        <li><a href="register.php">Register</a></li>
        <li><a href="faq.php">FAQs</a></li>
        <li><a href="services.php">Services</a></li>
        <li><a href="contact.php">Contact</a></li>
    </ul>
    <div class="hamburger" onclick="toggleMenu()">
        <span></span>
        <span></span>
        <span></span>
    </div>
</nav>

<div class="form-container">
    <form action="" method="post">
        <h3>Register Now</h3>
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
        <br><br>
        <p>Already have an account? <a href="loginf.php">Login now</a></p>
    </form>
</div>

</body>
<script>
    function toggleMenu() {
      document.querySelector('.navbar').classList.toggle('active');
    }
  </script>
</html>
