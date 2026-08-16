
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

$full_name = trim($_POST['full_name']);

$university_id = trim($_POST['university_id']);

$university_ref_id = $_POST['university_ref_id'] ?? '';

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
   CHECK UNIVERSITY
=========================== */

$sql = "SELECT id FROM universities WHERE id = ?";

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

    header("Location: message.php?action=invalid_university");
    exit();

}

mysqli_stmt_close($stmt);


/* ===========================
   CHECK DUPLICATE EMAIL
=========================== */

$sql = "SELECT id FROM users WHERE email = ?";

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

    header("Location: message.php?action=email_exists");
    exit();

}

mysqli_stmt_close($stmt);


/* ===========================
   CHECK DUPLICATE UNIVERSITY ID
=========================== */

$sql = "SELECT id FROM users WHERE university_id = ?";

$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "s",
    $university_id
);

mysqli_stmt_execute($stmt);

mysqli_stmt_store_result($stmt);

if (mysqli_stmt_num_rows($stmt) > 0) {

    mysqli_stmt_close($stmt);

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
    password
)
VALUES (?, ?, ?, ?, ?, ?, ?)";


$stmt = mysqli_prepare($conn, $sql);


mysqli_stmt_bind_param(
    $stmt,
    "ssissss",
    $full_name,
    $university_id,
    $university_ref_id,
    $email,
    $phone,
    $department,
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

    echo "Database Error: " . mysqli_error($conn);

}

?>

