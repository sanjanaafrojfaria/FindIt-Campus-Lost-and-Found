<?php
include 'config/database.php';
 include 'includes/navbar.php'; 
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login | FindIt</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

    <link rel="stylesheet" href="assets/css/login.css">
    <link rel="stylesheet" href="assets/css/style.css">

</head>

<body>

<div class="overlay">

    <div class="register-card">

        

        <h2>Welcome Back!</h2>

        <p class="subtitle">

            Sign in to continue to your FindIt account.

        </p>
        <form action="login_process.php" method="POST">

        <div class="row">

    
    <div class=" mb-3">
        <label>Email</label>
        <input type="email"
               class="form-control"
               name="email"
               placeholder="Enter your email"
               required>
    </div>

    <div class=" mb-3">

        <label>Password</label>

        <input type="password"
               class="form-control"
               name="password"
               placeholder="Enter your password"
               required>

    </div>

    <div class="d-flex justify-content-between align-items-center mb-4">

    <div class="form-check">

        <input class="form-check-input"
               type="checkbox"
               id="remember">

        <label class="form-check-label"
               for="remember">

            Remember Me

        </label>

    </div>

    <a href="#" class="forgot-link">

        Forgot Password?

    </a>

</div>

</div>

<div class="d-grid">

    <button class="btn btn-primary btn-lg">

        Sign In

    </button>

</div>

        <div class="login-link">

            Don't have an account?

            <a href="register.php">Register</a>

        </div>
        </form>

    </div>

</div>

<script src="assets/js/register.js"></script>

</body>

</html>