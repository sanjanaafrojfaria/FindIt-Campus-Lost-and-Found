<?php

session_start();

include "config/database.php";

if (!isset($_SESSION['user_id'])) {

    header("Location: login.php");
    exit();

}

if ($_SERVER["REQUEST_METHOD"] != "POST") {

    header("Location: student/report_found.php");
    exit();

}

$user_id = $_SESSION['user_id'];

$item_name = trim($_POST['item_name']);
$category = trim($_POST['category']);
$location = trim($_POST['location']);
$found_date = $_POST['found_date'];
if (strtotime($found_date) > time()) {

    die("Future dates are not allowed.");

}
$description = trim($_POST['description']);

$image_name = "default-item.png";


/* ===========================
   VALIDATION
=========================== */

if (
    empty($item_name) ||
    empty($category) ||
    empty($location) ||
    empty($found_date) ||
    empty($description)
) {

    header("Location: message.php?action=empty_fields");
    exit();

}


/* ===========================
   IMAGE UPLOAD
=========================== */

if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {

    $upload_dir = "uploads/found_items/";

    $file_name = $_FILES['image']['name'];

    $file_tmp = $_FILES['image']['tmp_name'];

    $file_size = $_FILES['image']['size'];

    $file_ext = strtolower(
        pathinfo($file_name, PATHINFO_EXTENSION)
    );


    $allowed_extensions = [
        "jpg",
        "jpeg",
        "png"
    ];


    if (!in_array($file_ext, $allowed_extensions)) {

        header("Location: message.php?action=invalid_image");
        exit();

    }


    if ($file_size > 5 * 1024 * 1024) {

        header("Location: message.php?action=image_too_large");
        exit();

    }


    $new_file_name =
        time() . "_" . uniqid() . "." . $file_ext;


    $destination =
        $upload_dir . $new_file_name;


    if (move_uploaded_file(
        $file_tmp,
        $destination
    )) {

        $image_name = $new_file_name;

    }

}


/* ===========================
   INSERT INTO DATABASE
=========================== */

$sql = "INSERT INTO found_items
(
    user_id,
    item_name,
    category,
    location,
    found_date,
    description,
    image
)
VALUES (?, ?, ?, ?, ?, ?, ?)";


$stmt = mysqli_prepare($conn, $sql);


mysqli_stmt_bind_param(
    $stmt,
    "issssss",
    $user_id,
    $item_name,
    $category,
    $location,
    $found_date,
    $description,
    $image_name
);


if (mysqli_stmt_execute($stmt)) {

    mysqli_stmt_close($stmt);

    header("Location: message.php?action=found_report_success");

    exit();

} else {

    mysqli_stmt_close($stmt);

    header("Location: message.php?action=report_failed");

    exit();

}

?>