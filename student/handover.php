<?php

session_start();

if (!isset($_SESSION['user_id'])) {

    header("Location: ../login.php");
    exit();

}

include "../config/database.php";

$user_id = (int)$_SESSION['user_id'];


/* ===========================
   ONLY POST REQUESTS
=========================== */

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    header("Location: notifications.php");
    exit();

}


$claim_id = isset($_POST['claim_id'])
    ? (int)$_POST['claim_id']
    : 0;

$action = $_POST['action'] ?? "";


if (
    $claim_id <= 0 ||
    !in_array(
        $action,
        ['schedule', 'accept', 'complete'],
        true
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

    die("Database error.");

}

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

    header("Location: notifications.php");
    exit();

}


/* ===========================
   USER ACCESS
=========================== */

$is_reporter =
    ((int)$claim['reporter_id'] === $user_id);

$is_claimant =
    ((int)$claim['claimant_id'] === $user_id);


if (!$is_reporter && !$is_claimant) {

    header(
        "Location: ../message.php?action=access_denied"
    );

    exit();

}


/* =========================================================
   SCHEDULE HANDOVER
   REPORTER ONLY
========================================================= */

if ($action === "schedule") {


    if (!$is_reporter) {

        die(
            "Access denied. Only the reporter can schedule the handover."
        );

    }


    if ($claim['claim_status'] !== "Approved") {

        die(
            "The claim must be approved before scheduling a handover."
        );

    }


    /* ===========================
       GET FORM DATA
    =========================== */

    $location =
        trim($_POST['location'] ?? "");

    $date =
        trim($_POST['handover_date'] ?? "");

    $time =
        trim($_POST['handover_time'] ?? "");

    $note =
        trim($_POST['reporter_note'] ?? "");


    /* ===========================
       ALLOWED LOCATIONS
    =========================== */

    $allowed_locations = [

        "Main Gate",
        "University Library",
        "Cafeteria",
        "Student Center",
        "Department Building"

    ];


    if (
        !in_array(
            $location,
            $allowed_locations,
            true
        )
    ) {

        die("Invalid meeting location.");

    }


    /* ===========================
       ALLOWED TIMES
    =========================== */

    $allowed_times = [

        "10:00:00",
        "12:00:00",
        "14:00:00",
        "16:00:00",
        "18:00:00"

    ];


    if (
        !in_array(
            $time,
            $allowed_times,
            true
        )
    ) {

        die("Invalid meeting time.");

    }


    /* ===========================
       VALIDATE DATE
    =========================== */

    $date_object =
        DateTime::createFromFormat(
            'Y-m-d',
            $date
        );


    if (
        !$date_object ||
        $date_object->format('Y-m-d') !== $date
    ) {

        die("Invalid date.");

    }


    $today =
        new DateTime(
            date('Y-m-d')
        );


    if ($date_object < $today) {

        die(
            "Handover date cannot be in the past."
        );

    }


    /* ===========================
       CHECK EXISTING HANDOVER
    =========================== */

    $sql = "
        SELECT id, status
        FROM handovers
        WHERE claim_id = ?
        LIMIT 1
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

    $existing =
        mysqli_fetch_assoc($result);

    mysqli_stmt_close($stmt);


    if ($existing) {

        die(
            "A handover meeting has already been scheduled."
        );

    }


    /* ===========================
       CREATE HANDOVER
    =========================== */

    $sql = "
        INSERT INTO handovers
        (
            claim_id,
            location,
            handover_date,
            handover_time,
            reporter_note,
            status
        )

        VALUES
        (?, ?, ?, ?, ?, 'Proposed')
    ";


    $stmt =
        mysqli_prepare($conn, $sql);


    if (!$stmt) {

        die(
            "Could not prepare handover."
        );

    }


    mysqli_stmt_bind_param(
        $stmt,
        "issss",
        $claim_id,
        $location,
        $date,
        $time,
        $note
    );


    if (!mysqli_stmt_execute($stmt)) {

        die(
            "Could not schedule handover: " .
            htmlspecialchars(
                mysqli_stmt_error($stmt)
            )
        );

    }


    mysqli_stmt_close($stmt);


    /* ===========================
       NOTIFY CLAIMANT
    =========================== */

    $message =
        'A handover meeting has been scheduled for "' .
        $claim['item_name'] .
        '". Please review the meeting details and accept the handover.';


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

        die(
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

        die(
            "Could not create notification."
        );

    }


    mysqli_stmt_close($stmt);


    header(
        "Location: claim_details.php?id=" .
        $claim_id
    );

    exit();

}


/* =========================================================
   ACCEPT HANDOVER
   CLAIMANT ONLY
========================================================= */

if ($action === "accept") {


    if (!$is_claimant) {

        die(
            "Access denied. Only the claimant can accept the handover."
        );

    }


    if ($claim['claim_status'] !== "Approved") {

        die(
            "This claim cannot accept a handover."
        );

    }


    /* ===========================
       GET HANDOVER
    =========================== */

    $sql = "
        SELECT id, status
        FROM handovers
        WHERE claim_id = ?
        LIMIT 1
    ";


    $stmt =
        mysqli_prepare($conn, $sql);


    mysqli_stmt_bind_param(
        $stmt,
        "i",
        $claim_id
    );


    mysqli_stmt_execute($stmt);

    $result =
        mysqli_stmt_get_result($stmt);

    $handover =
        mysqli_fetch_assoc($result);

    mysqli_stmt_close($stmt);


    if (!$handover) {

        die(
            "No handover meeting has been scheduled."
        );

    }


    if ($handover['status'] !== "Proposed") {

        die(
            "This handover is no longer waiting for acceptance."
        );

    }


    /* ===========================
       ACCEPT
    =========================== */

    $sql = "
        UPDATE handovers

        SET
            status = 'Confirmed',
            accepted_at = NOW()

        WHERE
            claim_id = ?
            AND status = 'Proposed'
    ";


    $stmt =
        mysqli_prepare($conn, $sql);


    if (!$stmt) {

        die(
            "Could not prepare handover acceptance."
        );

    }


    mysqli_stmt_bind_param(
        $stmt,
        "i",
        $claim_id
    );


    if (!mysqli_stmt_execute($stmt)) {

        die(
            "Could not accept handover."
        );

    }


    if (
        mysqli_stmt_affected_rows($stmt) !== 1
    ) {

        mysqli_stmt_close($stmt);

        die(
            "Handover was already processed."
        );

    }


    mysqli_stmt_close($stmt);


    /* ===========================
       NOTIFY REPORTER
    =========================== */

    $message =
        'The claimant accepted the handover meeting for "' .
        $claim['item_name'] .
        '".';


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


    mysqli_stmt_bind_param(
        $stmt,
        "iis",
        $claim['reporter_id'],
        $claim_id,
        $message
    );


    mysqli_stmt_execute($stmt);

    mysqli_stmt_close($stmt);


    header(
        "Location: claim_details.php?id=" .
        $claim_id
    );

    exit();

}


/* =========================================================
   COMPLETE HANDOVER
   CLAIMANT ONLY
========================================================= */

if ($action === "complete") {


    if (!$is_claimant) {

        die(
            "Access denied. Only the claimant can complete the handover."
        );

    }


    if ($claim['claim_status'] !== "Approved") {

        header(
            "Location: claim_details.php?id=" .
            $claim_id
        );

        exit();

    }


    mysqli_begin_transaction($conn);


    try {


        /* ===========================
           LOCK HANDOVER
        =========================== */

        $sql = "
            SELECT id, status
            FROM handovers
            WHERE claim_id = ?
            FOR UPDATE
        ";


        $stmt =
            mysqli_prepare($conn, $sql);


        if (!$stmt) {

            throw new Exception(
                "Could not prepare handover."
            );

        }


        mysqli_stmt_bind_param(
            $stmt,
            "i",
            $claim_id
        );


        mysqli_stmt_execute($stmt);

        $result =
            mysqli_stmt_get_result($stmt);

        $handover =
            mysqli_fetch_assoc($result);

        mysqli_stmt_close($stmt);


        if (!$handover) {

            throw new Exception(
                "Handover not found."
            );

        }


        if ($handover['status'] !== "Confirmed") {

            throw new Exception(
                "The claimant must accept the meeting before completing the handover."
            );

        }


        /* ===========================
           COMPLETE HANDOVER
        =========================== */

        $sql = "
            UPDATE handovers

            SET
                status = 'Completed',
                completed_at = NOW()

            WHERE
                claim_id = ?
                AND status = 'Confirmed'
        ";


        $stmt =
            mysqli_prepare($conn, $sql);


        if (!$stmt) {

            throw new Exception(
                "Could not prepare handover completion."
            );

        }


        mysqli_stmt_bind_param(
            $stmt,
            "i",
            $claim_id
        );


        if (!mysqli_stmt_execute($stmt)) {

            throw new Exception(
                "Could not complete handover."
            );

        }


        if (
            mysqli_stmt_affected_rows($stmt) !== 1
        ) {

            throw new Exception(
                "Handover was not completed."
            );

        }


        mysqli_stmt_close($stmt);


        /* ===========================
           COMPLETE CLAIM
        =========================== */

        $sql = "
            UPDATE claims

            SET status = 'Completed'

            WHERE
                id = ?
                AND status = 'Approved'
        ";


        $stmt =
            mysqli_prepare($conn, $sql);


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
                "Could not complete claim."
            );

        }


        if (
            mysqli_stmt_affected_rows($stmt) !== 1
        ) {

            throw new Exception(
                "Claim was not completed."
            );

        }


        mysqli_stmt_close($stmt);


        /* ===========================
           ITEM REMAINS CLAIMED
        =========================== */

        $sql = "
            UPDATE found_items

            SET status = 'Claimed'

            WHERE id = ?
        ";


        $stmt =
            mysqli_prepare($conn, $sql);


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
                "Could not update item."
            );

        }


        mysqli_stmt_close($stmt);


        /* =================================================
           TRUST SCORE
           
           SUCCESSFUL HANDOVER:
           REPORTER +1
           CLAIMANT +1
        ================================================= */

        $sql = "
            UPDATE users

            SET trust_score = trust_score + 1

            WHERE id IN (?, ?)
        ";


        $stmt =
            mysqli_prepare($conn, $sql);


        if (!$stmt) {

            throw new Exception(
                "Could not prepare trust score update."
            );

        }


        mysqli_stmt_bind_param(
            $stmt,
            "ii",
            $claim['reporter_id'],
            $claim['claimant_id']
        );


        if (!mysqli_stmt_execute($stmt)) {

            throw new Exception(
                "Could not update trust scores."
            );

        }


        /*
         * Both users must exist.
         * affected_rows should normally be 2.
         */

        if (
            mysqli_stmt_affected_rows($stmt) !== 2
        ) {

            throw new Exception(
                "Trust score could not be updated for both users."
            );

        }


        mysqli_stmt_close($stmt);


        /* ===========================
           NOTIFY REPORTER
        =========================== */

        $message =
            'The claimant has confirmed that the item "' .
            $claim['item_name'] .
            '" was successfully handed over. Both users received +1 trust score.';


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
                "Could not prepare notification."
            );

        }


        mysqli_stmt_bind_param(
            $stmt,
            "iis",
            $claim['reporter_id'],
            $claim_id,
            $message
        );


        if (!mysqli_stmt_execute($stmt)) {

            throw new Exception(
                "Could not notify reporter."
            );

        }


        mysqli_stmt_close($stmt);


        /* ===========================
           COMMIT EVERYTHING
        =========================== */

        mysqli_commit($conn);


        header(
            "Location: claim_details.php?id=" .
            $claim_id
        );

        exit();


    } catch (Exception $e) {

        mysqli_rollback($conn);

        die(
            "Handover completion failed: " .
            htmlspecialchars(
                $e->getMessage()
            )
        );

    }

}

?>