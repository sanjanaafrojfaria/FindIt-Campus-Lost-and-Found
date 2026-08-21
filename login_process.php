<?php

session_start();

include 'config/database.php';


/* ===========================
   CHECK REQUEST
=========================== */

if ($_SERVER["REQUEST_METHOD"] != "POST") {

    header("Location: login.php");
    exit();

}


/* ===========================
   GET FORM DATA
=========================== */

$email = trim($_POST['email']);

$password = $_POST['password'];


/* ===========================
   GET REDIRECT DESTINATION
=========================== */

$redirect = $_POST['redirect'] ?? '';



/* ===========================
   VALIDATION
=========================== */

if (empty($email) || empty($password)) {

    header("Location: message.php?action=empty_fields");
    exit();

}


/* ===========================
   FIND USER
=========================== */

$sql = "
    SELECT *
    FROM users
    WHERE email = ?
";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {

    header("Location: message.php?action=login_failed");
    exit();

}

mysqli_stmt_bind_param(
    $stmt,
    "s",
    $email
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$user = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);



/* ===========================
   USER NOT FOUND
=========================== */

if (!$user) {

    header("Location: message.php?action=login_failed");
    exit();

}


/* ===========================
   VERIFY PASSWORD
=========================== */

if (!password_verify(
    $password,
    $user['password']
)) {

    header("Location: message.php?action=login_failed");
    exit();

}


/* ===========================
   CHECK APPROVAL STATUS
=========================== */

if ($user['approval_status'] == "Pending") {

    header("Location: message.php?action=pending_approval");
    exit();

}


if ($user['approval_status'] == "Rejected") {

    header("Location: message.php?action=account_rejected");
    exit();

}


/* ===========================
   CREATE SESSION
=========================== */

$_SESSION['user_id'] = $user['id'];

$_SESSION['full_name'] = $user['full_name'];

$_SESSION['email'] = $user['email'];

$_SESSION['role'] = $user['role'];



/* ===========================
   QR / REDIRECT DESTINATION
=========================== */

/*
 * Only allow redirects to pages
 * inside the FindIt project.
 *
 * This prevents someone from using
 * the login URL to redirect users
 * to an external website.
 */

if (
    !empty($redirect) &&
    strpos($redirect, '://') === false &&
    strpos($redirect, '//') !== 0
) {

    header("Location: " . $redirect);
    exit();

}


/* ===========================
   NORMAL LOGIN
=========================== */

if ($user['role'] == "Admin") {

    header("Location: admin/dashboard.php");

} else {

    header("Location: student/dashboard.php");

}

exit();

?>