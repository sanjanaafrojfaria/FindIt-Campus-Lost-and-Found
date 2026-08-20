<?php

session_start();

if (!isset($_SESSION['user_id'])) {

    header("Location: ../login.php");
    exit();

}

include "../config/database.php";

$user_id = (int)$_SESSION['user_id'];


/* ===========================
   CHECK REQUEST
=========================== */

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    header("Location: notifications.php");
    exit();

}


$response_id = isset($_POST['response_id'])
    ? (int)$_POST['response_id']
    : 0;

$action = $_POST['action'] ?? '';


if (
    $response_id <= 0 ||
    !in_array($action, ['accept', 'reject'])
) {

    header("Location: notifications.php");
    exit();

}


/* ===========================
   GET RESPONSE + OWNER
=========================== */

$sql = "
    SELECT

        fr.id,
        fr.status,
        fr.finder_id,
        l.user_id AS reporter_id,
        l.item_name

    FROM found_responses fr

    INNER JOIN lost_items l
        ON fr.lost_item_id = l.id

    WHERE fr.id = ?
";


$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $response_id
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$response = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);


if (!$response) {

    header("Location: notifications.php");
    exit();

}


/* ===========================
   ONLY REPORTER CAN ACT
=========================== */

if (
    (int)$response['reporter_id']
    !==
    $user_id
) {

    header("Location: notifications.php");
    exit();

}


/* ===========================
   ONLY PENDING RESPONSE
=========================== */

if ($response['status'] !== 'Pending') {

    header(
        "Location: found_response_details.php?id="
        . $response_id
    );

    exit();

}


/* ===========================
   NEW STATUS
=========================== */

$new_status =
    ($action === 'accept')
    ? 'Accepted'
    : 'Rejected';


/* ===========================
   UPDATE RESPONSE
=========================== */

$sql = "
    UPDATE found_responses

    SET status = ?

    WHERE id = ?
";


$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "si",
    $new_status,
    $response_id
);

mysqli_stmt_execute($stmt);

mysqli_stmt_close($stmt);


/* ===========================
   GET FINDER
=========================== */

$finder_id =
    (int)$response['finder_id'];


/* ===========================
   NOTIFICATION MESSAGE
=========================== */

if ($new_status === 'Accepted') {

    $message =
        "Your response for the lost item \""
        . $response['item_name']
        . "\" has been accepted by the reporter.";

} else {

    $message =
        "Your response for the lost item \""
        . $response['item_name']
        . "\" has been rejected by the reporter.";

}


/* ===========================
   NOTIFY FINDER
=========================== */

$sql = "
    INSERT INTO notifications
    (
        user_id,
        claim_id,
        found_response_id,
        message,
        is_read,
        created_at
    )

    VALUES
    (?, NULL, ?, ?, 0, NOW())
";


$stmt = mysqli_prepare($conn, $sql);

if ($stmt) {

    mysqli_stmt_bind_param(
        $stmt,
        "iis",
        $finder_id,
        $response_id,
        $message
    );

    mysqli_stmt_execute($stmt);

    mysqli_stmt_close($stmt);

}


/* ===========================
   REDIRECT
=========================== */

header(
    "Location: found_response_details.php?id="
    . $response_id
    . "&updated=1"
);

exit();

?>