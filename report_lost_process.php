
<?php

session_start();

if (!isset($_SESSION['user_id'])) {

    header("Location: login.php");
    exit();

}

include "config/database.php";


/* ===========================
   GET USER ID
=========================== */

$user_id = $_SESSION['user_id'];


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

    header("Location: message.php?action=invalid_university");
    exit();

}

$university_ref_id = $user['university_ref_id'];


/* ===========================
   GET FORM DATA
=========================== */

$item_name = trim($_POST['item_name']);

$category = trim($_POST['category']);

$location = trim($_POST['location']);

$lost_date = $_POST['lost_date'];

$description = trim($_POST['description']);


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
   CHECK FUTURE DATE
=========================== */

if (strtotime($lost_date) > time()) {

    header("Location: message.php?action=invalid_date");
    exit();

}


/* ===========================
   IMAGE UPLOAD
=========================== */

$imageName = "";


if (!empty($_FILES['image']['name'])) {

    $imageName = time() . "_" .
                 basename($_FILES['image']['name']);

    $target = "uploads/lost_items/" . $imageName;

    move_uploaded_file(
        $_FILES['image']['tmp_name'],
        $target
    );

}


/* ===========================
   INSERT INTO DATABASE
=========================== */

$sql = "INSERT INTO lost_items
(
    user_id,
    university_ref_id,
    item_name,
    category,
    location,
    lost_date,
    description,
    image
)
VALUES (?, ?, ?, ?, ?, ?, ?, ?)";


$stmt = mysqli_prepare($conn, $sql);


mysqli_stmt_bind_param(
    $stmt,
    "iissssss",
    $user_id,
    $university_ref_id,
    $item_name,
    $category,
    $location,
    $lost_date,
    $description,
    $imageName
);


if (mysqli_stmt_execute($stmt)) {

    mysqli_stmt_close($stmt);

    header("Location: message.php?action=lost_report_success");

    exit();

} else {

    mysqli_stmt_close($stmt);

    header("Location: message.php?action=report_failed");

    exit();

}

?>

