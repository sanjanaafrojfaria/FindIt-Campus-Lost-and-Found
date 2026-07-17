<?php

session_start();

if (!isset($_SESSION['user_id'])) {

    header("Location: login.php");
    exit();

}

include "config/database.php";

/* ===========================
   GET FORM DATA
=========================== */

$item_name = trim($_POST['item_name']);
$category = trim($_POST['category']);
$location = trim($_POST['location']);
$lost_date = $_POST['lost_date'];
$description = trim($_POST['description']);

$user_id = $_SESSION['user_id'];

/* ===========================
   VALIDATION
=========================== */

if (
    empty($item_name) ||
    empty($category) ||
    empty($location) ||
    empty($lost_date) ||
    empty($description)
) {

    header("Location: message.php?action=empty_fields");
    exit();

}

/* ===========================
   IMAGE UPLOAD
=========================== */

$imageName = "";

if (!empty($_FILES['image']['name'])) {

    $imageName = time() . "_" . basename($_FILES['image']['name']);

    $target = "uploads/lost_items/" . $imageName;

    move_uploaded_file($_FILES['image']['tmp_name'], $target);

}

/* ===========================
   INSERT INTO DATABASE
=========================== */

$sql = "INSERT INTO lost_items
(user_id, item_name, category, location, lost_date, description, image)
VALUES (?, ?, ?, ?, ?, ?, ?)";

$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "issssss",
    $user_id,
    $item_name,
    $category,
    $location,
    $lost_date,
    $description,
    $imageName
);

mysqli_stmt_execute($stmt);

mysqli_stmt_close($stmt);

/* ===========================
   SUCCESS
=========================== */

header("Location: message.php?action=lost_report_success");
exit();

?>

