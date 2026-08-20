
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
    !in_array($action, ['accept', 'reject', 'mark_found'])
) {

    header("Location: notifications.php");
    exit();

}


/* ===========================
   GET RESPONSE + LOST ITEM
=========================== */

$sql = "
    SELECT

        fr.id,
        fr.status,
        fr.finder_id,

        fr.lost_item_id,

        l.user_id AS reporter_id,
        l.item_name,
        l.status AS item_status

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


/* ==================================================
   MARK LOST ITEM AS FOUND
================================================== */

if ($action === 'mark_found') {

    /* Response must be accepted */

    if ($response['status'] !== 'Accepted') {

        header(
            "Location: found_response_details.php?id="
            . $response_id
        );

        exit();

    }


    /* Item must still be open */

    if ($response['item_status'] !== 'Open') {

        header(
            "Location: found_response_details.php?id="
            . $response_id
        );

        exit();

    }


    /* Update lost item */

    $sql = "
        UPDATE lost_items

        SET status = 'Found'

        WHERE id = ?
        AND user_id = ?
    ";


    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {

        die("Database error.");

    }

    mysqli_stmt_bind_param(
        $stmt,
        "ii",
        $response['lost_item_id'],
        $user_id
    );

    mysqli_stmt_execute($stmt);

    mysqli_stmt_close($stmt);


    /* Notify finder */

    $message =
        "The reporter has marked the lost item \""
        . $response['item_name']
        . "\" as Found.";


    $finder_id =
        (int)$response['finder_id'];


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


    header(
        "Location: found_response_details.php?id="
        . $response_id
        . "&found=1"
    );

    exit();

}


/* ==================================================
   ACCEPT / REJECT
================================================== */

/* Only Pending response can be accepted/rejected */

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

if (!$stmt) {

    die("Database error.");

}

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
