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

/* Only Lost or Found is allowed */

if ($item_type !== "Lost" && $item_type !== "Found") {

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
   GET ITEM
=========================== */

if ($item_type === "Lost") {

    $sql = "SELECT
                l.*,
                u.name AS university_name
            FROM lost_items l
            LEFT JOIN universities u
                ON l.university_ref_id = u.id
            WHERE l.id = ?
            AND l.university_ref_id = ?";

} else {

    $sql = "SELECT
                f.*,
                u.name AS university_name
            FROM found_items f
            LEFT JOIN universities u
                ON f.university_ref_id = u.id
            WHERE f.id = ?
            AND f.university_ref_id = ?";
}

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
   DATE
=========================== */

if ($item_type === "Lost") {

    $date = $item['lost_date'];

} else {

    $date = $item['found_date'];

}

/* ===========================
   IMAGE
=========================== */

if (
    !empty($item['image']) &&
    $item['image'] !== "default-item.png"
) {

    if ($item_type === "Lost") {

        $image_path =
            "../uploads/lost_items/" .
            $item['image'];

    } else {

        $image_path =
            "../uploads/found_items/" .
            $item['image'];
    }

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

<title>
<?php echo htmlspecialchars($item['item_name']); ?>
| FindIt
</title>

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

.details-section {

    padding: 130px 20px 70px;

}

.details-card {

    max-width: 950px;

    margin: auto;

    background: white;

    border-radius: 24px;

    overflow: hidden;

    box-shadow: 0 15px 45px rgba(0,0,0,.10);

}

.details-image {

    width: 100%;

    height: 430px;

    object-fit: cover;

    background: #f1f5f9;

}

.details-body {

    padding: 35px;

}

.details-title {

    font-size: 34px;

    font-weight: 700;

    color: #0f172a;

    margin-bottom: 20px;

}

.type-badge {

    display: inline-block;

    padding: 8px 16px;

    border-radius: 30px;

    font-size: 13px;

    font-weight: 700;

    margin-bottom: 15px;

}

.type-lost {

    background: #fee2e2;

    color: #dc2626;

}

.type-found {

    background: #dcfce7;

    color: #16a34a;

}

.details-row {

    display: flex;

    align-items: center;

    gap: 12px;

    margin-bottom: 15px;

    color: #64748b;

}

.details-row i {

    width: 22px;

    color: #2563eb;

}

.details-row strong {

    color: #334155;

}

.details-description {

    margin-top: 28px;

    padding: 22px;

    background: #f8fafc;

    border-radius: 16px;

}

.details-description h5 {

    font-weight: 700;

    color: #0f172a;

    margin-bottom: 12px;

}

.details-description p {

    color: #64748b;

    line-height: 1.7;

    margin-bottom: 0;

}

.back-btn {

    border-radius: 30px;

    padding: 10px 22px;

    font-weight: 600;

}

@media(max-width:768px) {

    .details-section {

        padding: 110px 15px 50px;

    }

    .details-image {

        height: 280px;

    }

    .details-body {

        padding: 25px;

    }

    .details-title {

        font-size: 28px;

    }

}

</style>

</head>

<body>

<?php include "../includes/student_navbar.php"; ?>

<section class="details-section">

<div class="container">

<div class="details-card">

<!-- IMAGE -->

<img
src="<?php echo htmlspecialchars($image_path); ?>"
class="details-image"
onerror="this.src='../assets/images/default-item.png';"
>

<div class="details-body">

<!-- TYPE -->

<?php if ($item_type === "Lost") { ?>

<span class="type-badge type-lost">

<i class="fa-solid fa-circle-exclamation"></i>

LOST ITEM

</span>

<?php } else { ?>

<span class="type-badge type-found">

<i class="fa-solid fa-hand-holding-heart"></i>

FOUND ITEM

</span>

<?php } ?>

<!-- ITEM NAME -->

<h1 class="details-title">

<?php echo htmlspecialchars($item['item_name']); ?>

</h1>

<hr>

<!-- CATEGORY -->

<div class="details-row">

<i class="fa-solid fa-layer-group"></i>

<strong>Category:</strong>

<span>
<?php echo htmlspecialchars($item['category']); ?>
</span>

</div>

<!-- LOCATION -->

<div class="details-row">

<i class="fa-solid fa-location-dot"></i>

<strong>Location:</strong>

<span>
<?php echo htmlspecialchars($item['location']); ?>
</span>

</div>

<!-- DATE -->

<div class="details-row">

<i class="fa-solid fa-calendar"></i>

<strong>

<?php

echo $item_type === "Lost"
    ? "Lost Date:"
    : "Found Date:";

?>

</strong>

<span>
<?php echo htmlspecialchars($date); ?>
</span>

</div>

<!-- UNIVERSITY -->

<div class="details-row">

<i class="fa-solid fa-building-columns"></i>

<strong>University:</strong>

<span>
<?php echo htmlspecialchars($item['university_name']); ?>
</span>

</div>

<!-- STATUS -->

<div class="details-row">

<i class="fa-solid fa-circle-info"></i>

<strong>Status:</strong>

<span>
<?php echo htmlspecialchars($item['status']); ?>
</span>

</div>

<!-- DESCRIPTION -->

<div class="details-description">

<h5>

<i class="fa-solid fa-file-lines"></i>

Description

</h5>

<p>

<?php

echo nl2br(
    htmlspecialchars($item['description'])
);

?>

</p>

</div>

<!-- BACK -->

<div class="mt-4">

<div class="mt-4 d-flex gap-2 flex-wrap">

    <?php if (
        $item_type === "Found" &&
        $item['status'] === "Available"
    ) { ?>

        <?php
        // Check if the logged-in student is the person
        // who reported this found item.

        if ($item['user_id'] != $user_id) {
        ?>

            <a
                href="claim_item.php?id=<?php echo $item_id; ?>&type=Found"
                class="btn btn-success back-btn">

                <i class="fa-solid fa-hand-holding-heart"></i>

                Claim This Item

            </a>

        <?php } ?>

    <?php } ?>

    <a
        href="search.php"
        class="btn btn-outline-primary back-btn">

        <i class="fa-solid fa-arrow-left"></i>

        Back to Search

    </a>

</div>

</div>

</div>

</div>

</div>

</section>

</body>

</html>