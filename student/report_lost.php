<?php

session_start();

if (!isset($_SESSION['user_id'])) {

    header("Location: ../login.php");
    exit();

}


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
<link rel="stylesheet" href="../assets/css/report.css">

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

</head>

<body>
<?php include "../includes/student_navbar.php"; ?>

<section class="report-section">

<div class="container">

<div class="report-card">

<h2>

<i class="fa-solid fa-circle-exclamation text-danger"></i>

Report Lost Item

</h2>

<p>

Fill in the details below so others can help you find your item.

</p>

<form action="../report_lost_process.php"

method="POST"

enctype="multipart/form-data">

<div class="mb-3">

<label>Item Name</label>

<input
type="text"
class="form-control"
name="item_name"
required>

</div>

<div class="mb-3">

<label>Category</label>

<select
class="form-select"
name="category"
required>

<option value="">Select Category</option>

<option>Electronics</option>

<option>Wallet</option>

<option>ID Card</option>

<option>Keys</option>

<option>Books</option>

<option>Bag</option>

<option>Note</option>

<option>Jewelry</option>

<option>Other</option>

</select>

</div>

<div class="mb-3">

<label>Location Lost</label>


    <select class="form-select" name="location" required>

        <option value="">Select Location</option>

        <option>Cafeteria</option>

        <option>Library</option>

        <option>Main Building </option>

        <option>Textile Building</option>

        <option>Female Prayer Room</option>

        <option>Male Common Room</option>

        <option>Female Common Room</option>

    </select>

</div>

<div class="mb-3">

<label>Date Lost</label>

<input
type="date"
class="form-control"
name="lost_date"
required>

</div>

<div class="mb-3">

<label>Upload Image</label>

<input
type="file"
class="form-control"
name="image"
accept=".jpg,.jpeg,.png">

</div>

<div class="mb-4">

<label>Description</label>

<textarea

class="form-control"

rows="5"

name="description"

required></textarea>

</div>

<div class="d-grid">

<button class="btn btn-danger btn-lg">

Submit Lost Report

</button>

</div>

</form>

</div>

</div>

</section>