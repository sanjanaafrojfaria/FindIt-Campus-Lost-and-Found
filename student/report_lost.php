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

<title>Report Lost Item | FindIt</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link rel="stylesheet" href="../assets/css/style.css">
<link rel="stylesheet" href="../assets/css/report.css">

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

</head>

<body>

<?php include "../includes/student_navbar.php"; ?>

<section class="report-section">

<div class="container">
    <div class="report-card">

<h2 class="report-title">
    <i class="fa-solid fa-circle-exclamation text-danger"></i>
    Report Lost Item
</h2>

<p class="report-subtitle">
    Help the FindIt community locate your lost belongings.
</p>

<form action="../report_lost_process.php"
method="POST"
enctype="multipart/form-data">

<div class="mb-3">

<label class="form-label">

<i class="fa-solid fa-tag me-2"></i>

Item Name

</label>

<input
type="text"
class="form-control"
name="item_name"
placeholder="Example: Black Wallet"
required>

</div>

<div class="mb-3">

<label class="form-label">

<i class="fa-solid fa-layer-group me-2"></i>

Category

</label>

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

<div class="row">

    <div class="col-md-6 mb-3">

<label class="form-label">

<i class="fa-solid fa-location-dot me-2"></i>

Location Lost

</label>

<select
class="form-select"
name="location"
required>

<option value="">Select Location</option>

<option>Cafeteria</option>
<option>Library</option>
<option>Main Building</option>
<option>Textile Building</option>
<option>Female Prayer Room</option>
<option>Male Common Room</option>
<option>Female Common Room</option>

</select>

</div>

<div class="col-md-6 mb-3">

<label class="form-label">

<i class="fa-solid fa-calendar me-2"></i>

Date Lost

</label>

<input
type="date"
class="form-control"
name="lost_date"
required>

</div>
</div>

<div class="mb-3">

<label class="form-label">

<i class="fa-solid fa-image me-2"></i>

Upload Image

</label>

<input
type="file"
class="form-control"
name="image"
accept=".jpg,.jpeg,.png">

</div>

<div class="mb-4">

<label class="form-label">

<i class="fa-solid fa-file-lines me-2"></i>

Description

</label>

<textarea

class="form-control"

rows="4"

name="description"

placeholder="Mention color, brand, special marks or any identifying information."

required></textarea>

</div>

<button
type="submit"
class="btn btn-danger btn-submit">

<i class="fa-solid fa-paper-plane"></i>

Submit Lost Report

</button>

</form>

</div>

</div>

</section>

</body>

</html>