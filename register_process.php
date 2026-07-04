<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include 'config/database.php';

if ($_SERVER["REQUEST_METHOD"] != "POST") {

    header("Location: register.php");
    exit();

}

/* ===========================
   GET FORM DATA
=========================== */

$full_name = trim($_POST['full_name']);
$university_id = trim($_POST['university_id']);
$email = trim($_POST['email']);
$phone = trim($_POST['phone']);
$department = trim($_POST['department']);
$password = $_POST['password'];
$confirm_password = $_POST['confirm_password'];



/* ===========================
   VALIDATION
=========================== */

if (
    empty($full_name) ||
    empty($university_id) ||
    empty($email) ||
    empty($phone) ||
    empty($department) ||
    empty($password) ||
    empty($confirm_password)
) {

    header("Location: message.php?action=empty_fields");
    exit();

}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

    header("Location: message.php?action=invalid_email");
    exit();

}

if ($password !== $confirm_password) {

    header("Location: message.php?action=password_mismatch");
    exit();

}
/* ===========================
   HASH PASSWORD
=========================== */

$hashed_password = password_hash($password, PASSWORD_DEFAULT);
/* ===========================
   INSERT USER
=========================== */

$sql = "INSERT INTO users
(full_name, university_id, email, phone, department, password)
VALUES (?, ?, ?, ?, ?, ?)";

$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "ssssss",
    $full_name,
    $university_id,
    $email,
    $phone,
    $department,
    $hashed_password
);

if (mysqli_stmt_execute($stmt)) {

    header("Location: message.php?action=register_success");
    exit();

} else {

    echo "Database Error: " . mysqli_error($conn);

}
header("Location: message.php?action=register_success");
exit();

?>