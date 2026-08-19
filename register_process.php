<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'config/database.php';


/* ===========================
   CHECK REQUEST
=========================== */

if ($_SERVER["REQUEST_METHOD"] != "POST") {

    header("Location: register.php");
    exit();

}


/* ===========================
   GET FORM DATA
=========================== */

$full_name = trim($_POST['full_name'] ?? '');

$university_id = trim($_POST['university_id'] ?? '');

$university_ref_id = $_POST['university_ref_id'] ?? '';

$email = trim($_POST['email'] ?? '');

$phone = trim($_POST['phone'] ?? '');

$department = trim($_POST['department'] ?? '');

$password = $_POST['password'] ?? '';

$confirm_password = $_POST['confirm_password'] ?? '';


/* ===========================
   CHECK PROFILE IMAGE / ID CARD
=========================== */

if (
    !isset($_FILES['profile_image']) ||
    $_FILES['profile_image']['error'] !== UPLOAD_ERR_OK
) {

    header("Location: message.php?action=id_card_required");
    exit();

}


/* ===========================
   BASIC VALIDATION
=========================== */

if (
    empty($full_name) ||
    empty($university_id) ||
    empty($university_ref_id) ||
    empty($email) ||
    empty($phone) ||
    empty($department) ||
    empty($password) ||
    empty($confirm_password)
) {

    header("Location: message.php?action=empty_fields");
    exit();

}


/* ===========================
   VALIDATE EMAIL
=========================== */

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

    header("Location: message.php?action=invalid_email");
    exit();

}


/* ===========================
   CHECK PASSWORDS
=========================== */

if ($password !== $confirm_password) {

    header("Location: message.php?action=password_mismatch");
    exit();

}


/* ===========================
   GET UPLOADED IMAGE
=========================== */

$profile_image = $_FILES['profile_image'];

$max_file_size = 5 * 1024 * 1024; // 5 MB


/* ===========================
   CHECK FILE SIZE
=========================== */

if ($profile_image['size'] > $max_file_size) {

    header("Location: message.php?action=id_card_too_large");
    exit();

}


/* ===========================
   VERIFY IMAGE
=========================== */

$image_info = getimagesize($profile_image['tmp_name']);

if ($image_info === false) {

    header("Location: message.php?action=invalid_id_card");
    exit();

}


/* ===========================
   ALLOWED IMAGE TYPES
=========================== */

$allowed_types = [
    IMAGETYPE_JPEG,
    IMAGETYPE_PNG
];

if (!in_array($image_info[2], $allowed_types, true)) {

    header("Location: message.php?action=invalid_id_card");
    exit();

}


/* ===========================
   CREATE PROFILE DIRECTORY
=========================== */

$upload_directory = __DIR__ . "/uploads/profile/";


if (!is_dir($upload_directory)) {

    if (!mkdir($upload_directory, 0755, true)) {

        die("Failed to create profile image directory.");

    }

}


/* ===========================
   CREATE UNIQUE FILE NAME
=========================== */

$file_extension = ($image_info[2] === IMAGETYPE_PNG)
    ? "png"
    : "jpg";


$new_file_name =
    "profile_" .
    bin2hex(random_bytes(16)) .
    "." .
    $file_extension;


$upload_path = $upload_directory . $new_file_name;


/* ===========================
   MOVE IMAGE
=========================== */

if (!move_uploaded_file(
    $profile_image['tmp_name'],
    $upload_path
)) {

    header("Location: message.php?action=id_card_upload_failed");
    exit();

}


/* ===========================
   CHECK UNIVERSITY
=========================== */

$sql = "SELECT id
        FROM universities
        WHERE id = ?";

$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $university_ref_id
);

mysqli_stmt_execute($stmt);

mysqli_stmt_store_result($stmt);


if (mysqli_stmt_num_rows($stmt) == 0) {

    mysqli_stmt_close($stmt);

    unlink($upload_path);

    header("Location: message.php?action=invalid_university");
    exit();

}

mysqli_stmt_close($stmt);


/* ===========================
   CHECK DUPLICATE EMAIL
=========================== */

$sql = "SELECT id
        FROM users
        WHERE email = ?";

$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "s",
    $email
);

mysqli_stmt_execute($stmt);

mysqli_stmt_store_result($stmt);


if (mysqli_stmt_num_rows($stmt) > 0) {

    mysqli_stmt_close($stmt);

    unlink($upload_path);

    header("Location: message.php?action=email_exists");
    exit();

}

mysqli_stmt_close($stmt);


/* ===========================
   CHECK DUPLICATE UNIVERSITY ID
   WITHIN SAME UNIVERSITY
=========================== */

$sql = "SELECT id
        FROM users
        WHERE university_id = ?
        AND university_ref_id = ?";

$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "si",
    $university_id,
    $university_ref_id
);

mysqli_stmt_execute($stmt);

mysqli_stmt_store_result($stmt);


if (mysqli_stmt_num_rows($stmt) > 0) {

    mysqli_stmt_close($stmt);

    unlink($upload_path);

    header("Location: message.php?action=id_exists");
    exit();

}

mysqli_stmt_close($stmt);


/* ===========================
   HASH PASSWORD
=========================== */

$hashed_password = password_hash(
    $password,
    PASSWORD_DEFAULT
);


/* ===========================
   INSERT USER
=========================== */

$sql = "INSERT INTO users
(
    full_name,
    university_id,
    university_ref_id,
    email,
    phone,
    department,
    profile_image,
    password
)
VALUES (?, ?, ?, ?, ?, ?, ?, ?)";


$stmt = mysqli_prepare($conn, $sql);


if (!$stmt) {

    unlink($upload_path);

    die("Database Error: " . mysqli_error($conn));

}


/* ===========================
   BIND PARAMETERS
=========================== */

mysqli_stmt_bind_param(
    $stmt,
    "ssisssss",
    $full_name,
    $university_id,
    $university_ref_id,
    $email,
    $phone,
    $department,
    $new_file_name,
    $hashed_password
);


/* ===========================
   EXECUTE INSERT
=========================== */

if (mysqli_stmt_execute($stmt)) {

    mysqli_stmt_close($stmt);

    header("Location: message.php?action=register_success");

    exit();

} else {

    unlink($upload_path);

    echo "Database Error: " . mysqli_error($conn);

    mysqli_stmt_close($stmt);

}

?>