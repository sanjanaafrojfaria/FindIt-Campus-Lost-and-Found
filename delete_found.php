<?php

session_start();

if (!isset($_SESSION['user_id'])) {

    header("Location: login.php");
    exit();

}

include "config/database.php";

$user_id = (int)$_SESSION['user_id'];

$id = isset($_GET['id'])
    ? (int)$_GET['id']
    : 0;

if ($id <= 0) {

    header("Location: student/my_reports.php");
    exit();

}


/* ===========================
   GET OWNED ITEM
=========================== */

$sql = "
    SELECT
        id,
        image,
        status
    FROM found_items
    WHERE
        id = ?
        AND user_id = ?
    LIMIT 1
";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {

    die("Database error.");

}

mysqli_stmt_bind_param(
    $stmt,
    "ii",
    $id,
    $user_id
);

mysqli_stmt_execute($stmt);

$result =
    mysqli_stmt_get_result($stmt);

$item =
    mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);


if (!$item) {

    die(
        "Found item not found or access denied."
    );

}


/* ===========================
   CHECK ACTIVE CLAIMS
=========================== */

$sql = "
    SELECT COUNT(*) AS total
    FROM claims
    WHERE
        item_id = ?
        AND item_type = 'Found'
        AND status IN ('Pending', 'Approved', 'Completed')
";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {

    die("Database error.");

}

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $id
);

mysqli_stmt_execute($stmt);

$result =
    mysqli_stmt_get_result($stmt);

$claim_data =
    mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);


if (
    (int)$claim_data['total'] > 0
) {

    die(
        "This found item cannot be deleted because it has an active claim or completed handover."
    );

}


/* ===========================
   DELETE ITEM
=========================== */

$sql = "
    DELETE FROM found_items
    WHERE
        id = ?
        AND user_id = ?
";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {

    die("Could not prepare delete.");

}

mysqli_stmt_bind_param(
    $stmt,
    "ii",
    $id,
    $user_id
);


if (!mysqli_stmt_execute($stmt)) {

    mysqli_stmt_close($stmt);

    die("Could not delete report.");

}

mysqli_stmt_close($stmt);


/* ===========================
   DELETE IMAGE
=========================== */

if (
    !empty($item['image']) &&
    $item['image'] !== 'default-item.png'
) {

    $image_path =
        "uploads/found_items/" .
        $item['image'];

    if (
        file_exists($image_path)
    ) {

        unlink($image_path);

    }

}


header(
    "Location: student/my_reports.php"
);

exit();

?>