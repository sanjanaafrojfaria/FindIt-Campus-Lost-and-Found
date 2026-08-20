
<?php

session_start();

if (!isset($_SESSION['user_id'])) {

    header("Location: ../login.php");
    exit();

}

include "../config/database.php";

$user_id = (int)$_SESSION['user_id'];


/* ===========================
   VALIDATE ID
=========================== */

if (
    !isset($_GET['id']) ||
    !is_numeric($_GET['id'])
) {

    header("Location: notifications.php");
    exit();

}

$response_id = (int)$_GET['id'];

if ($response_id <= 0) {

    header("Location: notifications.php");
    exit();

}


/* ===========================
   GET FOUND RESPONSE
=========================== */

$sql = "
    SELECT

        fr.id,
        fr.lost_item_id,
        fr.finder_id,
        fr.contact_number,
        fr.found_location,
        fr.found_date,
        fr.found_description,
        fr.proof_image,
        fr.status,
        fr.created_at,

        /* LOST ITEM */

        l.item_name,
        l.category,
        l.location AS lost_location,
        l.lost_date,
        l.user_id AS reporter_id,
        l.university_ref_id,

        /* FINDER */

        finder.full_name AS finder_name,
        finder.university_id AS finder_university_id,

        /* REPORTER */

        reporter.full_name AS reporter_name

    FROM found_responses fr

    INNER JOIN lost_items l
        ON fr.lost_item_id = l.id

    INNER JOIN users finder
        ON fr.finder_id = finder.id

    INNER JOIN users reporter
        ON l.user_id = reporter.id

    WHERE fr.id = ?
";


$stmt = mysqli_prepare($conn, $sql);


/* ===========================
   CHECK SQL PREPARATION
=========================== */

if (!$stmt) {

    die(
        "Database error: "
        . htmlspecialchars(
            mysqli_error($conn),
            ENT_QUOTES,
            'UTF-8'
        )
    );

}


mysqli_stmt_bind_param(
    $stmt,
    "i",
    $response_id
);


if (!mysqli_stmt_execute($stmt)) {

    mysqli_stmt_close($stmt);

    die("Unable to load found response.");

}


$result = mysqli_stmt_get_result($stmt);

$response = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);


/* ===========================
   RESPONSE NOT FOUND
=========================== */

if (!$response) {

    header("Location: notifications.php");
    exit();

}


/* ===========================
   ACCESS CONTROL
=========================== */

/*
 * Only the person who reported the
 * lost item can review the finder response.
 */

if (
    (int)$response['reporter_id']
    !==
    $user_id
) {

    header("Location: notifications.php");
    exit();

}


/* ===========================
   PROOF IMAGE
=========================== */

$proof_image = null;

if (!empty($response['proof_image'])) {

    $proof_image =
        "../uploads/found_responses/"
        . $response['proof_image'];

}


/* ===========================
   STATUS STYLE
=========================== */

$status_class = "bg-warning text-dark";

if ($response['status'] === "Accepted") {

    $status_class = "bg-success";

} elseif ($response['status'] === "Rejected") {

    $status_class = "bg-danger";

}

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta
name="viewport"
content="width=device-width, initial-scale=1.0">

<title>Found Response | FindIt</title>


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

.response-section {

    padding: 130px 20px 70px;

}

.response-card {

    max-width: 900px;

    margin: auto;

    background: white;

    border-radius: 24px;

    padding: 35px;

    box-shadow:
        0 15px 45px rgba(0,0,0,.10);

}

.response-title {

    font-size: 32px;

    font-weight: 700;

    color: #0f172a;

}

.item-box {

    background: #f8fafc;

    border-radius: 16px;

    padding: 20px;

    margin: 25px 0;

}

.info-row {

    display: flex;

    gap: 12px;

    margin-bottom: 12px;

    color: #64748b;

    flex-wrap: wrap;

}

.info-row i {

    width: 22px;

    color: #2563eb;

}

.info-row strong {

    color: #334155;

}

.description-box {

    background: #f8fafc;

    border-radius: 16px;

    padding: 20px;

    margin-top: 20px;

}

.proof-image {

    width: 100%;

    max-height: 450px;

    object-fit: contain;

    border-radius: 16px;

    background: #f1f5f9;

    margin-top: 15px;

}

.action-btn {

    border-radius: 30px;

    padding: 11px 25px;

    font-weight: 600;

}

</style>

</head>


<body>


<?php include "../includes/student_navbar.php"; ?>


<section class="response-section">


<div class="container">


<div class="response-card">


<!-- ===========================
     TITLE
=========================== -->

<h1 class="response-title">

    <i class="fa-solid fa-hand-holding-heart text-success"></i>

    Found Item Response

</h1>


<p class="text-muted">

    Someone reported finding your lost item.

    Review the information below before responding.

</p>


<!-- ===========================
     STATUS
=========================== -->

<div class="mb-4">

<span class="badge <?= $status_class ?> p-2">

    Status:

    <?= htmlspecialchars(
        $response['status'],
        ENT_QUOTES,
        'UTF-8'
    ) ?>

</span>

</div>


<!-- ===========================
     LOST ITEM
=========================== -->

<div class="item-box">


<h4 class="fw-bold mb-3">

    <?= htmlspecialchars(
        $response['item_name'],
        ENT_QUOTES,
        'UTF-8'
    ) ?>

</h4>


<div class="info-row">

    <i class="fa-solid fa-layer-group"></i>

    <strong>Category:</strong>

    <span>

        <?= htmlspecialchars(
            $response['category'],
            ENT_QUOTES,
            'UTF-8'
        ) ?>

    </span>

</div>


<div class="info-row">

    <i class="fa-solid fa-location-dot"></i>

    <strong>Lost At:</strong>

    <span>

        <?= htmlspecialchars(
            $response['lost_location'],
            ENT_QUOTES,
            'UTF-8'
        ) ?>

    </span>

</div>


<div class="info-row">

    <i class="fa-solid fa-calendar"></i>

    <strong>Lost Date:</strong>

    <span>

        <?= htmlspecialchars(
            $response['lost_date'],
            ENT_QUOTES,
            'UTF-8'
        ) ?>

    </span>

</div>


</div>


<!-- ===========================
     FINDER
=========================== -->

<h4 class="fw-bold mt-4">

    <i class="fa-solid fa-user text-primary"></i>

    Finder Information

</h4>


<div class="item-box">


<div class="info-row">

    <i class="fa-solid fa-user"></i>

    <strong>Name:</strong>

    <span>

        <?= htmlspecialchars(
            $response['finder_name'],
            ENT_QUOTES,
            'UTF-8'
        ) ?>

    </span>

</div>


<div class="info-row">

    <i class="fa-solid fa-id-card"></i>

    <strong>University ID:</strong>

    <span>

        <?= htmlspecialchars(
            $response['finder_university_id'],
            ENT_QUOTES,
            'UTF-8'
        ) ?>

    </span>

</div>


<div class="info-row">

    <i class="fa-solid fa-phone"></i>

    <strong>Contact:</strong>

    <span>

        <?= htmlspecialchars(
            $response['contact_number'],
            ENT_QUOTES,
            'UTF-8'
        ) ?>

    </span>

</div>


<div class="info-row">

    <i class="fa-solid fa-location-dot"></i>

    <strong>Found Location:</strong>

    <span>

        <?= htmlspecialchars(
            $response['found_location'],
            ENT_QUOTES,
            'UTF-8'
        ) ?>

    </span>

</div>


<div class="info-row">

    <i class="fa-solid fa-calendar"></i>

    <strong>Found Date:</strong>

    <span>

        <?= htmlspecialchars(
            $response['found_date'],
            ENT_QUOTES,
            'UTF-8'
        ) ?>

    </span>

</div>


</div>


<!-- ===========================
     DESCRIPTION
=========================== -->

<div class="description-box">


<h5 class="fw-bold">

    <i class="fa-solid fa-file-lines"></i>

    Finder's Description

</h5>


<p class="text-muted mb-0">

<?= nl2br(
    htmlspecialchars(
        $response['found_description'],
        ENT_QUOTES,
        'UTF-8'
    )
) ?>


</p>


</div>


<!-- ===========================
     PROOF IMAGE
=========================== -->

<?php if ($proof_image): ?>


<div class="description-box">


<h5 class="fw-bold">

    <i class="fa-solid fa-image"></i>

    Proof Image

</h5>


<a
href="<?= htmlspecialchars(
    $proof_image,
    ENT_QUOTES,
    'UTF-8'
) ?>"
target="_blank">


<img
src="<?= htmlspecialchars(
    $proof_image,
    ENT_QUOTES,
    'UTF-8'
) ?>"
class="proof-image"
alt="Found item proof"
onerror="this.style.display='none';"
>


</a>


</div>


<?php else: ?>


<div class="description-box">


<h5 class="fw-bold">

    <i class="fa-solid fa-image"></i>

    Proof Image

</h5>


<p class="text-muted mb-0">

    No proof image was uploaded.

</p>


</div>


<?php endif; ?>


<!-- ===========================
     ACTIONS
=========================== -->

<?php if ($response['status'] === 'Pending'): ?>


<div class="mt-4">


<form
action="found_response_action.php"
method="POST"
class="d-flex gap-2 flex-wrap"
>


<input
type="hidden"
name="response_id"
value="<?= $response_id ?>"
>


<button
type="submit"
name="action"
value="accept"
class="btn btn-success action-btn"
onclick="return confirm('Accept this found response?');"
>


<i class="fa-solid fa-circle-check me-1"></i>

Accept Response


</button>


<button
type="submit"
name="action"
value="reject"
class="btn btn-danger action-btn"
onclick="return confirm('Reject this found response?');"
>


<i class="fa-solid fa-circle-xmark me-1"></i>

Reject Response


</button>


<a
href="notifications.php"
class="btn btn-outline-primary action-btn"
>


<i class="fa-solid fa-arrow-left me-1"></i>

Back


</a>


</form>


</div>


<?php else: ?>


<div class="mt-4">


<a
href="notifications.php"
class="btn btn-outline-primary action-btn"
>


<i class="fa-solid fa-arrow-left me-1"></i>

Back to Notifications


</a>


</div>


<?php endif; ?>


</div>


</div>


</section>


</body>

</html>

