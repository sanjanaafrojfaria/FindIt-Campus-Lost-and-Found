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

$action = $_POST['action'] ?? '';


if (
    $claim_id <= 0 ||
    !in_array(
        $action,
        ['approve', 'reject']
    )
) {

    header("Location: notifications.php");
    exit();

}


/* ===========================
   GET CLAIM
=========================== */

$sql = "
    SELECT
        c.id,
        c.item_id,
        c.user_id AS claimant_id,
        c.status,
        c.item_type,

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

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $claim_id
);

mysqli_stmt_execute($stmt);

$result =
    mysqli_stmt_get_result($stmt);

$claim =
    mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);


/* ===========================
   CLAIM NOT FOUND
=========================== */

if (!$claim) {

    die("Claim not found.");

}


/* ===========================
   ONLY REPORTER CAN DECIDE
=========================== */

if (
    (int)$claim['reporter_id'] !==
    (int)$user_id
) {

    die(
        "Access denied. You are not the reporter of this item."
    );

}


/* ===========================
   CLAIM MUST BE PENDING
=========================== */

if ($claim['status'] !== "Pending") {

    header("Location: notifications.php");
    exit();

}


/* =========================================================
   APPROVE
========================================================= */

if ($action === "approve") {

    mysqli_begin_transaction($conn);

    try {


        /* ===========================
           LOCK ITEM
        =========================== */

        $sql = "
            SELECT id, status
            FROM found_items
            WHERE id = ?
            FOR UPDATE
        ";

        $stmt =
            mysqli_prepare($conn, $sql);

        if (!$stmt) {

            throw new Exception(
                mysqli_error($conn)
            );

        }

        mysqli_stmt_bind_param(
            $stmt,
            "i",
            $claim['item_id']
        );

        mysqli_stmt_execute($stmt);

        $result =
            mysqli_stmt_get_result($stmt);

        $item =
            mysqli_fetch_assoc($result);

        mysqli_stmt_close($stmt);


        if (!$item) {

            throw new Exception(
                "Item not found."
            );

        }


        /* ===========================
           ITEM MUST BE AVAILABLE
        =========================== */

        if (
            $item['status'] !==
            "Available"
        ) {

            throw new Exception(
                "Item is no longer available."
            );

        }


        /* ===========================
           APPROVE CLAIM
        =========================== */

        $sql = "
            UPDATE claims
            SET status = 'Approved'
            WHERE
                id = ?
                AND status = 'Pending'
        ";

        $stmt =
            mysqli_prepare($conn, $sql);

        mysqli_stmt_bind_param(
            $stmt,
            "i",
            $claim_id
        );

        if (
            !mysqli_stmt_execute($stmt)
        ) {

            throw new Exception(
                "Could not approve claim."
            );

        }

        mysqli_stmt_close($stmt);


        /* ===========================
           MARK ITEM AS CLAIMED
        =========================== */

        $sql = "
            UPDATE found_items
            SET status = 'Claimed'
            WHERE id = ?
        ";

        $stmt =
            mysqli_prepare($conn, $sql);

        mysqli_stmt_bind_param(
            $stmt,
            "i",
            $claim['item_id']
        );

        if (
            !mysqli_stmt_execute($stmt)
        ) {

            throw new Exception(
                "Could not update item status."
            );

        }

        mysqli_stmt_close($stmt);


        /* ===========================
           REJECT OTHER CLAIMS
        =========================== */

        $sql = "
            UPDATE claims
            SET status = 'Rejected'
            WHERE
                item_id = ?
                AND item_type = 'Found'
                AND id != ?
                AND status = 'Pending'
        ";

        $stmt =
            mysqli_prepare($conn, $sql);

        mysqli_stmt_bind_param(
            $stmt,
            "ii",
            $claim['item_id'],
            $claim_id
        );

        if (
            !mysqli_stmt_execute($stmt)
        ) {

            throw new Exception(
                "Could not reject other claims."
            );

        }

        mysqli_stmt_close($stmt);


        /* ===========================
           NOTIFY CLAIMANT
        =========================== */

        $message =
            'Your claim for the found item "' .
            $claim['item_name'] .
            '" has been approved.';


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

        $stmt =
            mysqli_prepare($conn, $sql);

        if (!$stmt) {

            throw new Exception(
                "Could not prepare approval notification."
            );

        }

        mysqli_stmt_bind_param(
            $stmt,
            "iis",
            $claim['claimant_id'],
            $claim_id,
            $message
        );

        if (
            !mysqli_stmt_execute($stmt)
        ) {

            throw new Exception(
                "Could not create approval notification."
            );

        }

        mysqli_stmt_close($stmt);


        /* ===========================
           COMMIT
        =========================== */

        if (!mysqli_commit($conn)) {

            throw new Exception(
                "Transaction commit failed."
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

        die(
            "Approval failed: " .
            htmlspecialchars(
                $e->getMessage()
            )
        );

    }

}


/* =========================================================
   REJECT
========================================================= */

if ($action === "reject") {


    mysqli_begin_transaction($conn);


    try {


        /* ===========================
           REJECT CLAIM
        =========================== */

        $sql = "
            UPDATE claims
            SET status = 'Rejected'
            WHERE
                id = ?
                AND status = 'Pending'
        ";

        $stmt =
            mysqli_prepare($conn, $sql);

        mysqli_stmt_bind_param(
            $stmt,
            "i",
            $claim_id
        );

        if (
            !mysqli_stmt_execute($stmt)
        ) {

            throw new Exception(
                "Could not reject claim."
            );

        }

        mysqli_stmt_close($stmt);


        /* ===========================
           NOTIFY CLAIMANT
        =========================== */

        $message =
            'Your claim for the found item "' .
            $claim['item_name'] .
            '" has been rejected.';


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

        $stmt =
            mysqli_prepare($conn, $sql);

        if (!$stmt) {

            throw new Exception(
                "Could not prepare rejection notification."
            );

        }

        mysqli_stmt_bind_param(
            $stmt,
            "iis",
            $claim['claimant_id'],
            $claim_id,
            $message
        );

        if (
            !mysqli_stmt_execute($stmt)
        ) {

            throw new Exception(
                "Could not create rejection notification."
            );

        }

        mysqli_stmt_close($stmt);


        /* ===========================
           COMMIT
        =========================== */

        mysqli_commit($conn);


        header(
            "Location: notifications.php"
        );

        exit();


    } catch (Exception $e) {

        mysqli_rollback($conn);

        die(
            "Rejection failed: " .
            htmlspecialchars(
                $e->getMessage()
            )
        );

    }

}

?>