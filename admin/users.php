<?php

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != "Admin") {

    header("Location: ../login.php");
    exit();

}

include "../config/database.php";
include "navbar.php";

$sql = "SELECT * FROM users WHERE approval_status='Pending'";

$result = mysqli_query($conn, $sql);

?>

<!DOCTYPE html>

<html>

<head>

<meta charset="UTF-8">

<title>Pending Users</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link rel="stylesheet" href="../assets/css/style.css">

<style>

body{

padding-top:120px;

background:#f8fafc;

}

</style>

</head>

<body>

<div class="container">

<h2 class="mb-4">

Pending Registrations

</h2>

<table class="table table-bordered table-hover">

<thead>

<tr>

<th>Name</th>

<th>ID</th>

<th>Email</th>

<th>Department</th>

<th>Action</th>

</tr>

</thead>

<tbody>

<?php while($user=mysqli_fetch_assoc($result)){ ?>

<tr>

<td><?= $user['full_name'] ?></td>

<td><?= $user['university_id'] ?></td>

<td><?= $user['email'] ?></td>

<td><?= $user['department'] ?></td>

<td>

<a href="approve.php?id=<?= $user['id'] ?>" class="btn btn-success btn-sm">

Approve

</a>

<a href="reject.php?id=<?= $user['id'] ?>" class="btn btn-danger btn-sm">

Reject

</a>

</td>

</tr>

<?php } ?>

</tbody>

</table>

</div>

</body>

</html>