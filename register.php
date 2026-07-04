<?php
include 'config/database.php';
 include 'includes/navbar.php'; 
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Register | FindIt</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

    <link rel="stylesheet" href="assets/css/register.css">
    <link rel="stylesheet" href="assets/css/style.css">

</head>

<body>

<div class="overlay">

    <div class="register-card">

        

        <h2>Create Account</h2>

        <p class="subtitle">

            Join FindIt and help reconnect lost belongings with their rightful owners.

        </p>
        <form action="register_process.php"
      method="POST"
      enctype="multipart/form-data">

        <div class="row">

    <div class="col-md-6 mb-3">
        <label>Full Name</label>
        <input type="text"
               class="form-control"
               name="full_name"
               placeholder="Enter your full name"
               required>
    </div>

    <div class="col-md-6 mb-3">
        <label>University ID</label>
        <input type="text"
               class="form-control"
               name="university_id"
               placeholder="e.g. 221-35-xxxx"
               required>
    </div>

    <div class="col-md-6 mb-3">
        <label>Email</label>
        <input type="email"
               class="form-control"
               name="email"
               placeholder="Enter your email"
               required>
    </div>

    <div class="col-md-6 mb-3">
        <label>Phone Number</label>
        <input type="text"
               class="form-control"
               name="phone"
               placeholder="01XXXXXXXXX"
               required>
    </div>

    <div class="col-md-6 mb-3">
        <label>Department</label>

        <select class="form-select"
                name="department"
                required>

            <option value="">Select Department</option>

            <option>CSE</option>
            <option>EEE</option>
            <option>CE</option>
            <option>Business Administration</option>
            <option>English</option>
            <option>Law</option>
            <option>Pharmacy</option>
            <option>Architecture</option>
            <option>Other</option>

        </select>

    </div>

    <div class="col-md-6 mb-3">

        <label>Profile Picture</label>

        <input type="file"
               class="form-control"
               name="profile_image"
               accept=".jpg,.jpeg,.png">

    </div>

    <div class="col-md-6 mb-3">

        <label>Password</label>

        <input type="password"
               class="form-control"
               name="password"
               placeholder="Create password"
               required>

    </div>

    <div class="col-md-6 mb-4">

        <label>Confirm Password</label>

        <input type="password"
               class="form-control"
               name="confirm_password"
               placeholder="Confirm password"
               required>

    </div>

</div>

<div class="d-grid">

    <button class="btn btn-primary btn-lg">

        Create Account

    </button>

</div>

        <div class="login-link">

            Already have an account?

            <a href="login.php">Sign In</a>

        </div>
        </form>

    </div>

</div>

<script src="assets/js/register.js"></script>

</body>

</html>