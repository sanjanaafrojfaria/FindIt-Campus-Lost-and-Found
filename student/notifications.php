
<?php

session_start();

if (!isset($_SESSION['user_id'])) {

    header("Location: ../login.php");
    exit();

}

include "../config/database.php";

$user_id = (int)$_SESSION['user_id'];


/* ===========================
   GET NOTIFICATIONS
=========================== */

$sql = "
    SELECT

        n.id AS notification_id,
        n.claim_id,
        n.found_response_id,
        n.message,
        n.is_read,
        n.created_at,

        /* CLAIM INFORMATION */

        c.user_id AS claimant_id,
        c.status AS claim_status,

        f.id AS item_id,
        f.item_name,
        f.user_id AS reporter_id,

        /* FOUND RESPONSE INFORMATION */

        fr.finder_id AS found_finder_id,
        fr.status AS found_response_status,
        fr.lost_item_id AS found_lost_item_id

    FROM notifications n

    /* CLAIM SYSTEM */

    LEFT JOIN claims c
        ON n.claim_id = c.id

    LEFT JOIN found_items f
        ON c.item_id = f.id

    /* I FOUND THIS ITEM SYSTEM */

    LEFT JOIN found_responses fr
        ON n.found_response_id = fr.id

    WHERE n.user_id = ?

    ORDER BY n.created_at DESC
";


$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {

    die("Database error.");

}

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $user_id
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$notifications = [];

while ($row = mysqli_fetch_assoc($result)) {

    $notifications[] = $row;

}

mysqli_stmt_close($stmt);


/* ===========================
   MARK NOTIFICATIONS AS READ
=========================== */

$sql = "
    UPDATE notifications

    SET is_read = 1

    WHERE user_id = ?
";

$stmt = mysqli_prepare($conn, $sql);

if ($stmt) {

    mysqli_stmt_bind_param(
        $stmt,
        "i",
        $user_id
    );

    mysqli_stmt_execute($stmt);

    mysqli_stmt_close($stmt);

}

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta
name="viewport"
content="width=device-width, initial-scale=1.0">

<title>Notifications | FindIt</title>


<link
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet">


<link
rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">


<link
rel="stylesheet"
href="../assets/css/style.css">


<style>

body {

    background: #f8fafc;

}


.notifications-section {

    padding: 130px 20px 70px;

}


.page-title {

    text-align: center;

    font-size: 38px;

    font-weight: 700;

    color: #0f172a;

}


.page-subtitle {

    text-align: center;

    color: #64748b;

    margin-bottom: 40px;

}


.notification-card {

    background: white;

    border-radius: 18px;

    padding: 22px 25px;

    margin-bottom: 18px;

    box-shadow:
        0 8px 30px rgba(0,0,0,.07);

    border-left: 5px solid #2563eb;

    transition: .25s;

}


.notification-card:hover {

    transform: translateY(-3px);

    box-shadow:
        0 12px 35px rgba(0,0,0,.10);

}


.notification-icon {

    width: 48px;

    height: 48px;

    border-radius: 50%;

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 20px;

    flex-shrink: 0;

}


.icon-default {

    background: #dbeafe;

    color: #2563eb;

}


.icon-claim {

    background: #fef3c7;

    color: #d97706;

}


.icon-approved {

    background: #dcfce7;

    color: #16a34a;

}


.icon-rejected {

    background: #fee2e2;

    color: #dc2626;

}


.icon-completed {

    background: #dcfce7;

    color: #15803d;

}


.icon-found {

    background: #dbeafe;

    color: #2563eb;

}


.notification-title {

    font-size: 17px;

    font-weight: 700;

    color: #0f172a;

}


.notification-text {

    color: #64748b;

    margin: 5px 0;

}


.notification-time {

    font-size: 13px;

    color: #94a3b8;

}


.notification-btn {

    border-radius: 30px;

    font-weight: 600;

}


.empty-notifications {

    background: white;

    border-radius: 20px;

    padding: 80px 20px;

    text-align: center;

    box-shadow:
        0 10px 35px rgba(0,0,0,.07);

}


.empty-notifications i {

    font-size: 60px;

    color: #94a3b8;

    margin-bottom: 20px;

}


.new-label {

    display: inline-block;

    padding: 5px 11px;

    border-radius: 20px;

    background: #dbeafe;

    color: #2563eb;

    font-size: 12px;

    font-weight: 700;

}

</style>

</head>


<body>


<?php include "../includes/student_navbar.php"; ?>


<section class="notifications-section">

<div class="container">


<!-- ===========================
     PAGE TITLE
=========================== -->

<h1 class="page-title">

    <i class="fa-solid fa-bell text-primary"></i>

    Notifications

</h1>


<p class="page-subtitle">

    Stay updated about your claims and found items.

</p>


<?php if (count($notifications) > 0): ?>


    <?php foreach ($notifications as $notification): ?>


        <?php

        /* ===========================
           BASIC DATA
        =========================== */

        $message =
            $notification['message'];

        $claim_status =
            $notification['claim_status'];

        $claim_id =
            (int)$notification['claim_id'];

        $reporter_id =
            (int)$notification['reporter_id'];

        $claimant_id =
            (int)$notification['claimant_id'];


        /* ===========================
           FOUND RESPONSE DATA
        =========================== */

        $found_response_id =
            (int)$notification['found_response_id'];

        $found_finder_id =
            (int)$notification['found_finder_id'];

        $found_response_status =
            $notification['found_response_status'];


        /* ===========================
           NOTIFICATION TYPES
        =========================== */

        $is_claim_notification =
            !empty($notification['claim_id']);


        $is_found_response_notification =
    !empty(
        $notification['found_response_id']
    );

$is_found_response_for_reporter =
    $is_found_response_notification &&
    $notification['reporter_id'] !== null &&
    (int)$notification['reporter_id'] === $user_id;


        /* ===========================
           DEFAULT APPEARANCE
        =========================== */

        $icon_class =
            'icon-default';

        $icon =
            'fa-bell';

        $title =
            'Notification';


        /* ===========================
           HANDOVER COMPLETED
        =========================== */

        if (
            stripos(
                $message,
                'successfully handed over'
            ) !== false
        ) {

            $icon_class =
                'icon-completed';

            $icon =
                'fa-handshake';

            $title =
                'Handover Completed';


        /* ===========================
           HANDOVER ACCEPTED
        =========================== */

        } elseif (
            stripos(
                $message,
                'accepted the handover'
            ) !== false
        ) {

            $icon_class =
                'icon-approved';

            $icon =
                'fa-circle-check';

            $title =
                'Handover Accepted';


        /* ===========================
           HANDOVER MEETING
        =========================== */

        } elseif (
            stripos(
                $message,
                'handover meeting has been scheduled'
            ) !== false
        ) {

            $icon_class =
                'icon-claim';

            $icon =
                'fa-calendar-check';

            $title =
                'Handover Meeting Scheduled';


        /* ===========================
           CLAIM APPROVED
        =========================== */

        } elseif (
            stripos(
                $message,
                'approved'
            ) !== false
        ) {

            $icon_class =
                'icon-approved';

            $icon =
                'fa-circle-check';

            $title =
                'Claim Approved';


        /* ===========================
           CLAIM REJECTED
        =========================== */

        } elseif (
            stripos(
                $message,
                'rejected'
            ) !== false
        ) {

            $icon_class =
                'icon-rejected';

            $icon =
                'fa-circle-xmark';

            $title =
                'Claim Rejected';


        /* ===========================
           FOUND RESPONSE
        =========================== */

        } elseif (
    $is_found_response_for_reporter
) {

    $icon_class =
        'icon-found';

    $icon =
        'fa-hand-holding-heart';

    $title =
        'Someone Found Your Item';

        /* ===========================
           CLAIM NOTIFICATION
        =========================== */

        } elseif (
            stripos(
                $message,
                'claim'
            ) !== false
        ) {

            $icon_class =
                'icon-claim';

            $icon =
                'fa-hand-holding-heart';

            $title =
                'Claim Notification';

        }

        ?>


        <!-- ===========================
             NOTIFICATION CARD
        ============================ -->

        <div class="notification-card">


            <div
            class="d-flex
            gap-3
            align-items-start">


                <!-- ICON -->

                <div
                class="notification-icon
                <?= $icon_class ?>">

                    <i
                    class="fa-solid
                    <?= $icon ?>">
                    </i>

                </div>


                <!-- CONTENT -->

                <div class="flex-grow-1">


                    <!-- TITLE -->

                    <div class="notification-title">

                        <?= htmlspecialchars(
                            $title
                        ) ?>

                    </div>


                    <!-- MESSAGE -->

                    <div class="notification-text">

                        <?= htmlspecialchars(
                            $message
                        ) ?>

                    </div>


                    <!-- TIME -->

                    <div class="notification-time">

                        <i
                        class="fa-regular
                        fa-clock">
                        </i>

                        <?= htmlspecialchars(
                            $notification['created_at']
                        ) ?>

                    </div>


                    <!-- ===========================
                         CLAIM ACTION
                    ============================ -->

                    <?php if (
                        $is_claim_notification &&
                        (
                            $reporter_id === $user_id ||
                            $claimant_id === $user_id
                        )
                    ): ?>


                        <a
                            href="claim_details.php?id=<?= $claim_id ?>"
                            class="btn btn-primary notification-btn mt-3">

                            <i
                                class="fa-solid
                                fa-eye
                                me-1">
                            </i>

                            View Claim & Handover

                        </a>


                    <?php endif; ?>


                    <!-- ===========================
                         FOUND RESPONSE ACTION
                    ============================ -->

                    <?php if (
    $is_found_response_for_reporter &&
    $found_response_id > 0
): ?>

    <a
        href="found_response_details.php?id=<?= $found_response_id ?>"
        class="btn btn-success notification-btn mt-3">

        <i
            class="fa-solid fa-eye me-1">
        </i>

        View Found Response

    </a>

<?php endif; ?>


                </div>


                <!-- ===========================
                     NEW LABEL
                ============================ -->

                <?php if (
                    !$notification['is_read']
                ): ?>

                    <span class="new-label">

                        NEW

                    </span>

                <?php endif; ?>


            </div>


        </div>


    <?php endforeach; ?>


<?php else: ?>


    <!-- ===========================
         EMPTY
    ============================ -->

    <div class="empty-notifications">

        <i
        class="fa-regular
        fa-bell-slash">
        </i>


        <h4>

            No Notifications

        </h4>


        <p class="text-muted">

            You're all caught up!

        </p>

    </div>


<?php endif; ?>


</div>

</section>


</body>

</html>

