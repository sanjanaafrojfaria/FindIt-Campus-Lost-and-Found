<?php

session_start();

if (!isset($_SESSION['user_id'])) {

    header("Location: ../login.php");
    exit();

}

include "../config/database.php";

$user_id = $_SESSION['user_id'];


/* ===========================
   CHECK REQUEST
=========================== */

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    header("Location: notifications.php");
    exit();

}


$claim_id = isset($_POST['claim_id'])
    ? (int)$_POST['claim_id']
    : 0;


if ($claim_id <= 0) {

    header("Location: notifications.php");
    exit();

}


/* ===========================
   GET CLAIM
=========================== */

$sql = "
    SELECT
        c.id AS claim_id,
        c.item_id,
        c.user_id AS claimant_id,
        c.status AS claim_status,

        f.user_id AS reporter_id,
        f.item_name,
        f.status AS item_status

    FROM claims c

    INNER JOIN found_items f
        ON c.item_id = f.id

    WHERE
        c.id = ?
        AND c.item_type = 'Found'
";


$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {

    die(
        "Database error: " .
        htmlspecialchars(mysqli_error($conn))
    );

}

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $claim_id
);


if (!mysqli_stmt_execute($stmt)) {

    die(
        "Database error: " .
        htmlspecialchars(mysqli_stmt_error($stmt))
    );

}


$result = mysqli_stmt_get_result($stmt);

$claim = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);


/* ===========================
   CLAIM NOT FOUND
=========================== */

if (!$claim) {

    header("Location: notifications.php");
    exit();

}


/* ===========================
   ONLY REPORTER CAN HAND OVER
=========================== */

if ((int)$claim['reporter_id'] !== (int)$user_id) {

    header(
        "Location: ../message.php?action=access_denied"
    );

    exit();

}


/* ===========================
   CLAIM MUST BE APPROVED
=========================== */

if ($claim['claim_status'] !== "Approved") {

    header(
        "Location: notifications.php"
    );

    exit();

}


/* ===========================
   ITEM MUST BE CLAIMED
=========================== */

if ($claim['item_status'] !== "Claimed") {

    header(
        "Location: notifications.php"
    );

    exit();

}


/* ===========================
   START TRANSACTION
=========================== */

mysqli_begin_transaction($conn);


try {


    /* ===========================
       STEP 1
       UPDATE CLAIM
    =========================== */

    $sql = "
        UPDATE claims
        SET status = 'Completed'
        WHERE
            id = ?
            AND status = 'Approved'
    ";


    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {

        throw new Exception(
            "Could not prepare claim update."
        );

    }


    mysqli_stmt_bind_param(
        $stmt,
        "i",
        $claim_id
    );


    if (!mysqli_stmt_execute($stmt)) {

        throw new Exception(
            "Could not complete claim: " .
            mysqli_stmt_error($stmt)
        );

    }


    $affected = mysqli_stmt_affected_rows($stmt);

    mysqli_stmt_close($stmt);


    if ($affected !== 1) {

        throw new Exception(
            "Claim was not updated."
        );

    }


    /* ===========================
       STEP 2
       KEEP ITEM CLAIMED
    =========================== */

    $sql = "
        UPDATE found_items
        SET status = 'Claimed'
        WHERE
            id = ?
    ";


    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {

        throw new Exception(
            "Could not prepare item update."
        );

    }


    mysqli_stmt_bind_param(
        $stmt,
        "i",
        $claim['item_id']
    );


    if (!mysqli_stmt_execute($stmt)) {

        throw new Exception(
            "Could not update item: " .
            mysqli_stmt_error($stmt)
        );

    }


    mysqli_stmt_close($stmt);


    /* ===========================
       STEP 3
       NOTIFY CLAIMANT
    =========================== */

    $message =
        'Item "' .
        $claim['item_name'] .
        '" has been handed over to you.';


    $sql = "
        INSERT INTO notifications
        (
            user_id,
            claim_id,
            message,
            is_read
        )
        VALUES
        (?, ?, ?, 0)
    ";


    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {

        throw new Exception(
            "Could not prepare notification."
        );

    }


    mysqli_stmt_bind_param(
        $stmt,
        "iis",
        $claim['claimant_id'],
        $claim_id,
        $message
    );


    if (!mysqli_stmt_execute($stmt)) {

        throw new Exception(
            "Could not create notification: " .
            mysqli_stmt_error($stmt)
        );

    }


    mysqli_stmt_close($stmt);


    /* ===========================
       STEP 4
       COMMIT
    =========================== */

    if (!mysqli_commit($conn)) {

        throw new Exception(
            "Could not commit transaction."
        );

    }


    /* ===========================
       SUCCESS
    =========================== */

    header(
        "Location: notifications.php"
    );

    exit();


} catch (Exception $e) {


    mysqli_rollback($conn);


    echo "<h2>Handover Failed</h2>";

    echo "<p>";

    echo htmlspecialchars(
        $e->getMessage()
    );

    echo "</p>";


    echo "<p>";

    echo "<a href='claim_details.php?id=" .
        (int)$claim_id .
        "'>";

    echo "Back to Claim";

    echo "</a>";

    echo "</p>";

    exit();

}

?>