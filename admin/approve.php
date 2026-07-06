<?php

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != "Admin") {

    header("Location: ../login.php");
    exit();

}

include "../config/database.php";

if (!isset($_GET['id'])) {

    header("Location: users.php");
    exit();

}

$id = (int) $_GET['id'];

$sql = "UPDATE users
        SET approval_status='Approved'
        WHERE id=?";

$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param($stmt, "i", $id);

mysqli_stmt_execute($stmt);

mysqli_stmt_close($stmt);

header("Location: users.php");

exit();

?>