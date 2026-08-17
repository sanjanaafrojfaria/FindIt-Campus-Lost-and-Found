<?php

session_start();

include "../config/database.php";


/* ===========================
   CHECK LOGIN
=========================== */

if (!isset($_SESSION['user_id'])) {

    header("Location: ../login.php");
    exit();

}

$admin_id = $_SESSION['user_id'];


/* ===========================
   CHECK ADMIN
=========================== */

$sql = "SELECT role
        FROM users
        WHERE id = ?";

$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $admin_id
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$admin = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);


if (!$admin || $admin['role'] !== "Admin") {

    header("Location: ../message.php?action=access_denied");
    exit();

}


/* ===========================
   CHECK REQUEST
=========================== */

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    header("Location: claims.php");
    exit();

}


/* ===========================
   GET DATA
=========================== */

$claim_id = isset($_POST['claim_id'])
    ? (int) $_POST['claim_id']
    : 0;

$action = $_POST['action'] ?? '';


if ($claim_id <= 0) {

    header("Location: ../message.php?action=claim_action_failed");
    exit();

}


if (!in_array($action, ['approve', 'reject'])) {

    header("Location: ../message.php?action=claim_action_failed");
    exit();

}


/* ===========================
   START TRANSACTION
=========================== */

mysqli_begin_transaction($conn);


try {

    /* ===========================
       GET CLAIM
    =========================== */

    $sql = "SELECT
                id,
                item_id,
                item_type,
                user_id,
                status
            FROM claims
            WHERE id = ?
            FOR UPDATE";

    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param(
        $stmt,
        "i",
        $claim_id
    );

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    $claim = mysqli_fetch_assoc($result);

    mysqli_stmt_close($stmt);


    if (!$claim) {

        throw new Exception("Claim not found.");

    }


    /* ===========================
       CLAIM MUST BE PENDING
    =========================== */

    if ($claim['status'] !== "Pending") {

        throw new Exception("This claim has already been processed.");

    }


    /* ===========================
       ONLY FOUND ITEMS
    =========================== */

    if ($claim['item_type'] !== "Found") {

        throw new Exception("Invalid item type.");

    }


    /* ===========================
       APPROVE CLAIM
    =========================== */

    if ($action === "approve") {


        /* ---------------------------
           CHECK ITEM
        --------------------------- */

        $sql = "SELECT id, status
                FROM found_items
                WHERE id = ?
                FOR UPDATE";

        $stmt = mysqli_prepare($conn, $sql);

        mysqli_stmt_bind_param(
            $stmt,
            "i",
            $claim['item_id']
        );

        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);

        $item = mysqli_fetch_assoc($result);

        mysqli_stmt_close($stmt);


        if (!$item) {

            throw new Exception("Found item not found.");

        }


        /* ---------------------------
           ITEM MUST BE AVAILABLE
        --------------------------- */

        if ($item['status'] !== "Available") {

            throw new Exception(
                "This item is no longer available."
            );

        }


        /* ---------------------------
           APPROVE CLAIM
        --------------------------- */

        $sql = "UPDATE claims
                SET status = 'Approved'
                WHERE id = ?";

        $stmt = mysqli_prepare($conn, $sql);

        mysqli_stmt_bind_param(
            $stmt,
            "i",
            $claim_id
        );

        if (!mysqli_stmt_execute($stmt)) {

            mysqli_stmt_close($stmt);

            throw new Exception(
                "Failed to approve claim."
            );

        }

        mysqli_stmt_close($stmt);


        /* ---------------------------
           MARK ITEM AS CLAIMED
        --------------------------- */

        $sql = "UPDATE found_items
                SET status = 'Claimed'
                WHERE id = ?";

        $stmt = mysqli_prepare($conn, $sql);

        mysqli_stmt_bind_param(
            $stmt,
            "i",
            $claim['item_id']
        );

        if (!mysqli_stmt_execute($stmt)) {

            mysqli_stmt_close($stmt);

            throw new Exception(
                "Failed to update item status."
            );

        }

        mysqli_stmt_close($stmt);


        /* ---------------------------
           REJECT OTHER PENDING CLAIMS
           FOR SAME ITEM
        --------------------------- */

        $sql = "UPDATE claims
                SET status = 'Rejected'
                WHERE item_id = ?
                AND item_type = 'Found'
                AND id != ?
                AND status = 'Pending'";

        $stmt = mysqli_prepare($conn, $sql);

        mysqli_stmt_bind_param(
            $stmt,
            "ii",
            $claim['item_id'],
            $claim_id
        );

        if (!mysqli_stmt_execute($stmt)) {

            mysqli_stmt_close($stmt);

            throw new Exception(
                "Failed to update other claims."
            );

        }

        mysqli_stmt_close($stmt);


        /* ---------------------------
           COMMIT
        --------------------------- */

        mysqli_commit($conn);


        header(
            "Location: ../message.php?action=claim_approved"
        );

        exit();

    }


    /* ===========================
       REJECT CLAIM
    =========================== */

    if ($action === "reject") {


        $sql = "UPDATE claims
                SET status = 'Rejected'
                WHERE id = ?
                AND status = 'Pending'";

        $stmt = mysqli_prepare($conn, $sql);

        mysqli_stmt_bind_param(
            $stmt,
            "i",
            $claim_id
        );

        if (!mysqli_stmt_execute($stmt)) {

            mysqli_stmt_close($stmt);

            throw new Exception(
                "Failed to reject claim."
            );

        }

        mysqli_stmt_close($stmt);


        /* ---------------------------
           COMMIT
        --------------------------- */

        mysqli_commit($conn);


        header(
            "Location: ../message.php?action=claim_rejected"
        );

        exit();

    }


} catch (Exception $e) {


    /* ===========================
       ROLLBACK
    =========================== */

    mysqli_rollback($conn);


    header(
        "Location: ../message.php?action=claim_action_failed"
    );

    exit();

}

?>