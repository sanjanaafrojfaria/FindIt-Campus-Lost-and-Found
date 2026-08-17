<?php

session_start();

if (!isset($_SESSION['user_id'])) {

    header("Location: ../login.php");
    exit();

}

include "../config/database.php";

$user_id = $_SESSION['user_id'];

/* ===========================
   VALIDATE ITEM
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

if ($item_type !== "Found") {

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
   GET FOUND ITEM
=========================== */

$sql = "SELECT
            f.*,
            u.name AS university_name
        FROM found_items f
        LEFT JOIN universities u
            ON f.university_ref_id = u.id
        WHERE f.id = ?
        AND f.university_ref_id = ?";

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
   ITEM ALREADY CLAIMED
=========================== */

if ($item['status'] !== "Available") {

    header("Location: item_details.php?id="
        . $item_id
        . "&type=Found");

    exit();

}

/* ===========================
   PREVENT OWNER FROM CLAIMING
=========================== */

if ($item['user_id'] == $user_id) {

    header("Location: item_details.php?id="
        . $item_id
        . "&type=Found");

    exit();

}

/* ===========================
   CHECK EXISTING CLAIM
=========================== */

$sql = "SELECT id, status
        FROM claims
        WHERE item_id = ?
        AND item_type = 'Found'
        AND user_id = ?";

$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "ii",
    $item_id,
    $user_id
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$existing_claim = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

/* ===========================
   IMAGE
=========================== */

if (
    !empty($item['image']) &&
    $item['image'] !== "default-item.png"
) {

    $image_path =
        "../uploads/found_items/"
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

<title>Claim Item | FindIt</title>

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

.claim-section {

    padding: 130px 20px 70px;

}

.claim-card {

    max-width: 900px;

    margin: auto;

    background: white;

    border-radius: 24px;

    overflow: hidden;

    box-shadow: 0 15px 45px rgba(0,0,0,.10);

}

.claim-image {

    width: 100%;

    height: 300px;

    object-fit: cover;

    background: #f1f5f9;

}

.claim-body {

    padding: 35px;

}

.claim-title {

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

.form-control {

    border-radius: 12px;

    padding: 12px 14px;

}

.submit-btn {

    border-radius: 30px;

    padding: 11px 25px;

    font-weight: 600;

}

.back-btn {

    border-radius: 30px;

    padding: 11px 25px;

    font-weight: 600;

}

.warning-box {

    background: #fff7ed;

    border: 1px solid #fed7aa;

    color: #9a3412;

    padding: 15px;

    border-radius: 12px;

    margin-bottom: 25px;

}

</style>

</head>

<body>

<?php include "../includes/student_navbar.php"; ?>

<section class="claim-section">

<div class="container">

<div class="claim-card">

<img
src="<?php echo htmlspecialchars($image_path); ?>"
class="claim-image"
onerror="this.src='../assets/images/default-item.png';"
>

<div class="claim-body">

<h1 class="claim-title">

<i class="fa-solid fa-hand-holding-heart text-success"></i>

Claim This Item

</h1>

<p class="text-muted">

If you believe this found item belongs to you,
provide some information that can help verify your claim.

</p>

<!-- ITEM INFORMATION -->

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

<strong>Found At:</strong>

<?php echo htmlspecialchars($item['location']); ?>

</p>

<p>

<i class="fa-solid fa-calendar"></i>

<strong>Found Date:</strong>

<?php echo htmlspecialchars($item['found_date']); ?>

</p>

<p>

<i class="fa-solid fa-building-columns"></i>

<strong>University:</strong>

<?php echo htmlspecialchars($item['university_name']); ?>

</p>

</div>

<?php if ($existing_claim) { ?>

<div class="warning-box">

<i class="fa-solid fa-circle-info"></i>

You have already submitted a claim for this item.

<strong>

Status:
<?php echo htmlspecialchars($existing_claim['status']); ?>

</strong>

</div>

<a
href="item_details.php?id=<?php echo $item_id; ?>&type=Found"
class="btn btn-outline-primary back-btn">

<i class="fa-solid fa-arrow-left"></i>

Back to Item

</a>

<?php } else { ?>

<form
action="../claim_process.php"
method="POST"
enctype="multipart/form-data">

<input
type="hidden"
name="item_id"
value="<?php echo $item_id; ?>">

<input
type="hidden"
name="item_type"
value="Found">

<!-- CLAIM REASON -->

<div class="mb-4">

<label class="form-label">

<i class="fa-solid fa-file-lines me-2"></i>

Why do you believe this item belongs to you?

</label>

<textarea
name="claim_reason"
class="form-control"
rows="5"
placeholder="Describe identifying details, marks, contents, brand, or any other information that can prove the item belongs to you."
required></textarea>

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
accept=".jpg,.jpeg,.png">

<small class="text-muted">

You may upload an image that helps prove ownership.

</small>

</div>

<div class="d-flex gap-2 flex-wrap">

<a
href="item_details.php?id=<?php echo $item_id; ?>&type=Found"
class="btn btn-outline-primary back-btn">

<i class="fa-solid fa-arrow-left"></i>

Back

</a>

<button
type="submit"
class="btn btn-success submit-btn">

<i class="fa-solid fa-paper-plane"></i>

Submit Claim

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