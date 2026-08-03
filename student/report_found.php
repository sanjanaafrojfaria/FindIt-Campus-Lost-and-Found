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

<title>Report Found Item | FindIt</title>

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

<i class="fa-solid fa-hand-holding-heart text-success"></i>

Report Found Item

</h2>

<p class="report-subtitle">

Help someone reunite with their lost belongings.

</p>

<form action="../report_found_process.php"

method="POST"

enctype="multipart/form-data">

<div class="row">

<div class="col-md-6 mb-3">

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


<div class="col-md-6 mb-3">

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

</div>


<div class="row">

<div class="col-md-6 mb-3">

<label class="form-label">

<i class="fa-solid fa-location-dot me-2"></i>

Location Found

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

Date Found

</label>

<input
type="date"
class="form-control"
name="found_date"
max="<?php echo date('Y-m-d'); ?>"
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
rows="3"
name="description"
placeholder="Describe the item's color, brand, special marks or where you found it."
required></textarea>

</div>


<button
type="submit"
class="btn btn-success btn-submit">

<i class="fa-solid fa-paper-plane"></i>

Submit Found Report

</button>

</form>

</div>

</div>

</section>

</body>

</html>