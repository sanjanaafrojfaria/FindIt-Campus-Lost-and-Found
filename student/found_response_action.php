<?php

session_start();

if (!isset($_SESSION['user_id'])) {

    header("Location: ../login.php");
    exit();

}

include "../config/database.php";

$user_id = (int)$_SESSION['user_id'];


/* ===========================
   VALIDATE REQUEST
=========================== */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header("Location: notifications.php");
    exit();

}


if (
    !isset($_POST['response_id']) ||
    !is_numeric($_POST['response_id']) ||
    !isset($_POST['action'])
) {

    header("Location: notifications.php");
    exit();

}


$response_id = (int)$_POST['response_id'];
$action = $_POST['action'];


if ($response_id <= 0) {

    header("Location: notifications.php");
    exit();

}


/* ===========================
   ALLOWED ACTIONS
=========================== */

$allowed_actions = [
    'accept',
    'reject',
    'mark_found'
];


if (!in_array($action, $allowed_actions, true)) {

    header("Location: notifications.php");
    exit();

}


/* ===========================
   GET RESPONSE
=========================== */

$sql = "
    SELECT

        fr.id,
        fr.lost_item_id,
        fr.finder_id,
        fr.status AS response_status,

        l.user_id AS reporter_id,
        l.status AS lost_item_status,
        l.item_name

    FROM found_responses fr

    INNER JOIN lost_items l
        ON fr.lost_item_id = l.id

    WHERE fr.id = ?
";


$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {

    die("Database error.");

}


mysqli_stmt_bind_param(
    $stmt,
    "i",
    $response_id
);


mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$response = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);


/* ===========================
   RESPONSE NOT FOUND
=========================== */

if (!$response) {

    header("Location: notifications.php");
    exit();

}


$reporter_id =
    (int)$response['reporter_id'];

$finder_id =
    (int)$response['finder_id'];

$lost_item_id =
    (int)$response['lost_item_id'];

$response_status =
    $response['response_status'];

$lost_item_status =
    strtolower(
        trim(
            $response['lost_item_status'] ?? ''
        )
    );


/* ===========================
   ACCEPT RESPONSE
=========================== */

if ($action === 'accept') {


    /*
     * Only the reporter can accept.
     */

    if ($reporter_id !== $user_id) {

        header("Location: notifications.php");
        exit();

    }


    /*
     * Only Pending responses can be accepted.
     */

    if ($response_status !== 'Pending') {

        header(
            "Location: found_response_details.php?id="
            . $response_id
        );

        exit();

    }


    /* ===========================
       UPDATE RESPONSE
    =========================== */

    $sql = "
        UPDATE found_responses

        SET status = 'Accepted'

        WHERE id = ?

        AND status = 'Pending'
    ";


    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {

        die("Database error.");

    }


    mysqli_stmt_bind_param(
        $stmt,
        "i",
        $response_id
    );


    mysqli_stmt_execute($stmt);

    mysqli_stmt_close($stmt);


    /* ===========================
       NOTIFY FINDER
    =========================== */

    $message =
        "The reporter accepted your found response for "
        . $response['item_name']
        . ".";


    $sql = "
        INSERT INTO notifications
        (
            user_id,
            found_response_id,
            message,
            is_read,
            created_at
        )

        VALUES
        (
            ?,
            ?,
            ?,
            0,
            NOW()
        )
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


    header(
        "Location: found_response_details.php?id="
        . $response_id
    );

    exit();

}


/* ===========================
   REJECT RESPONSE
=========================== */

if ($action === 'reject') {


    /*
     * Only the reporter can reject.
     */

    if ($reporter_id !== $user_id) {

        header("Location: notifications.php");
        exit();

    }


    /*
     * Only Pending responses can be rejected.
     */

    if ($response_status !== 'Pending') {

        header(
            "Location: found_response_details.php?id="
            . $response_id
        );

        exit();

    }


    /* ===========================
       UPDATE RESPONSE
    =========================== */

    $sql = "
        UPDATE found_responses

        SET status = 'Rejected'

        WHERE id = ?

        AND status = 'Pending'
    ";


    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {

        die("Database error.");

    }


    mysqli_stmt_bind_param(
        $stmt,
        "i",
        $response_id
    );


    mysqli_stmt_execute($stmt);

    mysqli_stmt_close($stmt);


    /* ===========================
       NOTIFY FINDER
    =========================== */

    $message =
        "The reporter rejected your found response for "
        . $response['item_name']
        . ".";


    $sql = "
        INSERT INTO notifications
        (
            user_id,
            found_response_id,
            message,
            is_read,
            created_at
        )

        VALUES
        (
            ?,
            ?,
            ?,
            0,
            NOW()
        )
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


    header(
        "Location: found_response_details.php?id="
        . $response_id
    );

    exit();

}


/* ===========================
   MARK ITEM AS FOUND
=========================== */

if ($action === 'mark_found') {


    /*
     * ONLY THE REPORTER CAN DO THIS.
     */

    if ($reporter_id !== $user_id) {

        header("Location: notifications.php");
        exit();

    }


    /*
     * The response MUST already be accepted.
     */

    if ($response_status !== 'Accepted') {

        header(
            "Location: found_response_details.php?id="
            . $response_id
        );

        exit();

    }


    /*
     * IMPORTANT:
     *
     * If the lost item is already Found,
     * do absolutely nothing.
     *
     * This prevents the reporter from
     * performing the action repeatedly.
     */

    if ($lost_item_status === 'found') {

        header(
            "Location: found_response_details.php?id="
            . $response_id
        );

        exit();

    }


    /* ===========================
       UPDATE LOST ITEM
=========================== */

    $sql = "
        UPDATE lost_items

        SET status = 'Found'

        WHERE id = ?

        AND user_id = ?

        AND status <> 'Found'
    ";


    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {

        die("Database error.");

    }


    mysqli_stmt_bind_param(
        $stmt,
        "ii",
        $lost_item_id,
        $reporter_id
    );


    mysqli_stmt_execute($stmt);

    $affected_rows =
        mysqli_stmt_affected_rows($stmt);

    mysqli_stmt_close($stmt);


    /*
     * If nothing was updated, the item
     * was already Found or unavailable.
     */

    if ($affected_rows <= 0) {

        header(
            "Location: found_response_details.php?id="
            . $response_id
        );

        exit();

    }


    /* ===========================
       NOTIFY FINDER
    =========================== */

    $message =
        "The reporter confirmed that they found their item. "
        . "The lost item has been marked as Found.";


    $sql = "
        INSERT INTO notifications
        (
            user_id,
            found_response_id,
            message,
            is_read,
            created_at
        )

        VALUES
        (
            ?,
            ?,
            ?,
            0,
            NOW()
        )
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
    );

    exit();

}


header("Location: notifications.php");
exit();

?>