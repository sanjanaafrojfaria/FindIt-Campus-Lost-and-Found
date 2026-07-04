<?php

session_start();

if (!isset($_SESSION['user_id'])) {

    header("Location: ../login.php");
    exit();

}

include "../includes/navbar.php";

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Student Dashboard | FindIt</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link rel="stylesheet" href="../assets/css/style.css">
<link rel="stylesheet" href="../assets/css/dashboard.css">

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

</head>

<body>

<section class="dashboard-hero">

<div class="container">

<h1>

Welcome Back,

<span><?php echo htmlspecialchars($_SESSION['full_name']); ?></span>

👋

</h1>

<p>

Manage your lost and found activities from one place.

</p>

</div>

</section>
<section class="dashboard-stats">

    <div class="container">

        <div class="row g-4">

            <div class="col-lg-3 col-md-6">

                <div class="stat-card">

                    <i class="fa-solid fa-circle-exclamation stat-icon lost"></i>

                    <h2>0</h2>

                    <p>Lost Items</p>

                </div>

            </div>

            <div class="col-lg-3 col-md-6">

                <div class="stat-card">

                    <i class="fa-solid fa-hand-holding-heart stat-icon found"></i>

                    <h2>0</h2>

                    <p>Found Items</p>

                </div>

            </div>

            <div class="col-lg-3 col-md-6">

                <div class="stat-card">

                    <i class="fa-solid fa-bell stat-icon notify"></i>

                    <h2>0</h2>

                    <p>Notifications</p>

                </div>

            </div>

            <div class="col-lg-3 col-md-6">

                <div class="stat-card">

                    <i class="fa-solid fa-check-circle stat-icon returned"></i>

                    <h2>0</h2>

                    <p>Returned Items</p>

                </div>

            </div>

        </div>

    </div>

</section>

</body>

</html>