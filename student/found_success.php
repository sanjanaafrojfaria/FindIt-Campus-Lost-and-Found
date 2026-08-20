
<?php

session_start();

if (!isset($_SESSION['user_id'])) {

    header("Location: ../login.php");
    exit();

}

include "../config/database.php";

$user_id = (int)$_SESSION['user_id'];

$response_id = isset($_GET['id'])
    ? (int)$_GET['id']
    : 0;


/* ===========================
   VALIDATE RESPONSE ID
=========================== */

if ($response_id <= 0) {

    header("Location: search.php");
    exit();

}


/* ===========================
   GET RESPONSE
=========================== */

$sql = "
    SELECT
        fr.id,
        fr.lost_item_id,
        fr.finder_id,
        fr.status,

        l.item_name

    FROM found_responses fr

    INNER JOIN lost_items l
        ON fr.lost_item_id = l.id

    WHERE fr.id = ?
    AND fr.finder_id = ?
";


$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {

    die("Database error.");

}

mysqli_stmt_bind_param(
    $stmt,
    "ii",
    $response_id,
    $user_id
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$response = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);


/* ===========================
   RESPONSE NOT FOUND
=========================== */

if (!$response) {

    header("Location: search.php");
    exit();

}

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta
name="viewport"
content="width=device-width, initial-scale=1.0">

<title>Response Submitted | FindIt</title>


<link
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet">


<link
rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">


<link
rel="stylesheet"
href="../assets/css/style.css">


<style>

body {

    background: #f8fafc;

}

.success-section {

    min-height: 100vh;

    display: flex;

    align-items: center;

    justify-content: center;

    padding: 100px 20px 50px;

}

.success-card {

    max-width: 650px;

    width: 100%;

    background: white;

    border-radius: 24px;

    padding: 50px 40px;

    text-align: center;

    box-shadow:
        0 15px 45px rgba(0,0,0,.10);

}

.success-icon {

    width: 90px;

    height: 90px;

    border-radius: 50%;

    background: #dcfce7;

    color: #16a34a;

    display: flex;

    align-items: center;

    justify-content: center;

    margin: 0 auto 25px;

    font-size: 42px;

}

.success-title {

    font-size: 32px;

    font-weight: 700;

    color: #0f172a;

    margin-bottom: 15px;

}

.success-text {

    color: #64748b;

    line-height: 1.7;

    margin-bottom: 25px;

}

.item-box {

    background: #f8fafc;

    border-radius: 15px;

    padding: 18px;

    margin-bottom: 30px;

}

.item-box strong {

    color: #0f172a;

}

.status-badge {

    display: inline-block;

    background: #fef3c7;

    color: #92400e;

    padding: 7px 15px;

    border-radius: 20px;

    font-size: 14px;

    font-weight: 600;

    margin-top: 10px;

}

.action-btn {

    border-radius: 30px;

    padding: 11px 24px;

    font-weight: 600;

}

</style>

</head>


<body>


<?php include "../includes/student_navbar.php"; ?>


<section class="success-section">


<div class="success-card">


<div class="success-icon">

    <i class="fa-solid fa-check"></i>

</div>


<h1 class="success-title">

    Response Submitted Successfully!

</h1>


<p class="success-text">

    Thank you for helping return a lost item.

    Your response has been sent to the owner.

    The owner can now review the information you provided.

</p>


<div class="item-box">

    <div class="mb-2">

        <strong>Item:</strong>

        <?= htmlspecialchars(
            $response['item_name'],
            ENT_QUOTES,
            'UTF-8'
        ) ?>

    </div>


    <div>

        <strong>Response Status:</strong>

        <br>

        <span class="status-badge">

            <?= htmlspecialchars(
                $response['status'],
                ENT_QUOTES,
                'UTF-8'
            ) ?>

        </span>

    </div>

</div>


<div class="d-flex justify-content-center gap-2 flex-wrap">


<a
href="search.php"
class="btn btn-outline-primary action-btn">

    <i class="fa-solid fa-magnifying-glass me-1"></i>

    Back to Search

</a>


<a
href="notifications.php"
class="btn btn-primary action-btn">

    <i class="fa-solid fa-bell me-1"></i>

    Notifications

</a>


</div>


</div>

</section>


</body>

</html>
