<?php

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != "Admin") {

    header("Location: ../login.php");
    exit();

}

include "../config/database.php";
include "navbar.php";

$result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM users WHERE approval_status='Pending'");
$pending = mysqli_fetch_assoc($result)['total'];

?>

<!DOCTYPE html>

<html>

<head>

<meta charset="UTF-8">

<title>Admin Dashboard</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link rel="stylesheet" href="../assets/css/style.css">
<link rel="stylesheet" href="../assets/css/dashboard.css">

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

</head>

<body>

<section class="dashboard-header">

<div class="container text-center">

<h2>

Welcome Admin,

<span><?php echo htmlspecialchars($_SESSION['full_name']); ?></span>

</h2>

<p>

Manage the FindIt system.

</p>

</div>

</section>

<section class="dashboard-stats">

<div class="container">

<div class="row justify-content-center">

<div class="col-md-4">

<div class="stat-card">

<i class="fa-solid fa-user-clock stat-icon notify"></i>

<h2><?php echo $pending; ?></h2>

<p>Pending Registrations</p>

<a href="users.php" class="btn btn-primary mt-3">

Manage Users

</a>

</div>

</div>

</div>

</div>

</section>

</body>

</html>