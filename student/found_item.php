<?php

session_start();

if (!isset($_SESSION['user_id'])) {

    header("Location: ../login.php");
    exit();

}

include "../config/database.php";

$user_id = $_SESSION['user_id'];


/* ===========================
   VALIDATE PARAMETERS
=========================== */

if (
    !isset($_GET['id']) ||
    !is_numeric($_GET['id']) ||
    !isset($_GET['type'])
) {

    header("Location: search.php");
    exit();

}

$item_id = (int) $_GET['id'];
$item_type = $_GET['type'];


/* Only Lost items are allowed */

if ($item_type !== "Lost") {

    header("Location: search.php");
    exit();

}


/* ===========================
   GET USER'S UNIVERSITY
=========================== */

$sql = "SELECT university_ref_id
        FROM users
        WHERE id = ?";

$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $user_id
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$user = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);


if (!$user || empty($user['university_ref_id'])) {

    header("Location: ../message.php?action=invalid_university");
    exit();

}

$university_id = $user['university_ref_id'];


/* ===========================
   GET LOST ITEM
=========================== */

$sql = "SELECT
            l.*,
            u.name AS university_name
        FROM lost_items l
        LEFT JOIN universities u
            ON l.university_ref_id = u.id
        WHERE l.id = ?
        AND l.university_ref_id = ?";

$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "ii",
    $item_id,
    $university_id
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$item = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);


/* ===========================
   ITEM NOT FOUND
=========================== */

if (!$item) {

    header("Location: search.php");
    exit();

}


/* ===========================
   PREVENT OWNER
=========================== */

if ($item['user_id'] == $user_id) {

    header(
        "Location: item_details.php?id="
        . $item_id
        . "&type=Lost"
    );

    exit();

}


/* ===========================
   CHECK EXISTING RESPONSE
=========================== */

$sql = "SELECT id, status
        FROM found_responses
        WHERE lost_item_id = ?
        AND finder_id = ?";

$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "ii",
    $item_id,
    $user_id
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$existing_response = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);


/* ===========================
   IMAGE
=========================== */

if (
    !empty($item['image']) &&
    $item['image'] !== "default-item.png"
) {

    $image_path =
        "../uploads/lost_items/"
        . $item['image'];

} else {

    $image_path =
        "../assets/images/default-item.png";

}

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>I Found This Item | FindIt</title>


<link
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet">


<link
rel="stylesheet"
href="../assets/css/style.css">


<link
rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">


<style>

body {

    background: #f8fafc;

}

.found-section {

    padding: 130px 20px 70px;

}

.found-card {

    max-width: 900px;

    margin: auto;

    background: white;

    border-radius: 24px;

    overflow: hidden;

    box-shadow: 0 15px 45px rgba(0,0,0,.10);

}

.found-image {

    width: 100%;

    height: 300px;

    object-fit: cover;

    background: #f1f5f9;

}

.found-body {

    padding: 35px;

}

.found-title {

    font-size: 32px;

    font-weight: 700;

    color: #0f172a;

}

.item-info {

    background: #f8fafc;

    border-radius: 16px;

    padding: 20px;

    margin: 25px 0;

}

.item-info p {

    margin-bottom: 8px;

    color: #64748b;

}

.item-info i {

    width: 22px;

    color: #2563eb;

}

.form-label {

    font-weight: 600;

}

.form-control,
.form-select {

    border-radius: 12px;

    padding: 12px 14px;

}

.submit-btn,
.back-btn {

    border-radius: 30px;

    padding: 11px 25px;

    font-weight: 600;

}

.warning-box {

    background: #eff6ff;

    border: 1px solid #bfdbfe;

    color: #1e40af;

    padding: 15px;

    border-radius: 12px;

    margin-bottom: 25px;

}

</style>

</head>


<body>


<?php include "../includes/student_navbar.php"; ?>


<section class="found-section">


<div class="container">


<div class="found-card">


<!-- ===========================
     ITEM IMAGE
=========================== -->

<img
src="<?php echo htmlspecialchars($image_path); ?>"
class="found-image"
onerror="this.src='../assets/images/default-item.png';"
>


<div class="found-body">


<!-- ===========================
     TITLE
=========================== -->

<h1 class="found-title">

<i class="fa-solid fa-hand-holding-heart text-primary"></i>

I Found This Item

</h1>


<p class="text-muted">

If you found this item, provide some information
so the owner can verify and recover it.

</p>


<!-- ===========================
     ITEM INFORMATION
=========================== -->

<div class="item-info">


<h5 class="fw-bold mb-3">

<?php echo htmlspecialchars($item['item_name']); ?>

</h5>


<p>

<i class="fa-solid fa-layer-group"></i>

<strong>Category:</strong>

<?php echo htmlspecialchars($item['category']); ?>

</p>


<p>

<i class="fa-solid fa-location-dot"></i>

<strong>Lost At:</strong>

<?php echo htmlspecialchars($item['location']); ?>

</p>


<p>

<i class="fa-solid fa-calendar"></i>

<strong>Lost Date:</strong>

<?php echo htmlspecialchars($item['lost_date']); ?>

</p>


<p>

<i class="fa-solid fa-building-columns"></i>

<strong>University:</strong>

<?php echo htmlspecialchars($item['university_name']); ?>

</p>


</div>


<?php if ($existing_response) { ?>


<!-- ===========================
     ALREADY RESPONDED
=========================== -->

<div class="warning-box">

<i class="fa-solid fa-circle-info"></i>

You have already submitted a response for this lost item.

<br>

<strong>

Status:

<?php echo htmlspecialchars($existing_response['status']); ?>

</strong>

</div>


<a
href="item_details.php?id=<?php echo $item_id; ?>&type=Lost"
class="btn btn-outline-primary back-btn"
>

<i class="fa-solid fa-arrow-left"></i>

Back to Item

</a>


<?php } else { ?>


<!-- ===========================
     FOUND FORM
=========================== -->

<form
action="../found_item_process.php"
method="POST"
enctype="multipart/form-data"
>


<input
type="hidden"
name="lost_item_id"
value="<?php echo $item_id; ?>"
>
<!-- CONTACT NUMBER -->

<div class="mb-4">

<label class="form-label">

<i class="fa-solid fa-phone me-2"></i>

Contact Number

</label>

<input
type="tel"
name="contact_number"
class="form-control"
placeholder="Example: 01712345678"
pattern="[0-9+\-\s]{7,20}"
required
>

<small class="text-muted">

The owner can use this number to contact you about the item.

</small>

</div>

<!-- FOUND LOCATION -->

<div class="mb-4">

<label class="form-label">

<i class="fa-solid fa-location-dot me-2"></i>

Where did you find this item?

</label>


<select
name="found_location"
class="form-select"
required
>

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


<!-- FOUND DATE -->

<div class="mb-4">

<label class="form-label">

<i class="fa-solid fa-calendar me-2"></i>

When did you find it?

</label>


<input
type="date"
name="found_date"
class="form-control"
max="<?php echo date('Y-m-d'); ?>"
required
>

</div>



<!-- DESCRIPTION -->

<div class="mb-4">

<label class="form-label">

<i class="fa-solid fa-file-lines me-2"></i>

Additional Information

</label>


<textarea
name="found_description"
class="form-control"
rows="5"
placeholder="Describe where you found it, what it looked like, or any other information that may help the owner verify it."
required
></textarea>

</div>


<!-- PROOF IMAGE -->

<div class="mb-4">

<label class="form-label">

<i class="fa-solid fa-image me-2"></i>

Upload Proof Image

<span class="text-muted fw-normal">
(Optional)
</span>

</label>


<input
type="file"
name="proof_image"
class="form-control"
accept=".jpg,.jpeg,.png"
>


<small class="text-muted">

You may upload an image of the item you found.

</small>

</div>


<!-- BUTTONS -->

<div class="d-flex gap-2 flex-wrap">


<a
href="item_details.php?id=<?php echo $item_id; ?>&type=Lost"
class="btn btn-outline-primary back-btn"
>

<i class="fa-solid fa-arrow-left"></i>

Back

</a>


<button
type="submit"
class="btn btn-primary submit-btn"
>



Submit Response

</button>


</div>


</form>


<?php } ?>


</div>

</div>

</div>

</section>


</body>

</html>