<?php

session_start();

if (!isset($_SESSION['user_id'])) {

    header("Location: ../login.php");
    exit();

}

include "../config/database.php";

$user_id = $_SESSION['user_id'];


/* ===========================
   GET NOTIFICATIONS
=========================== */

$sql = "
    SELECT
        n.id AS notification_id,
        n.claim_id,
        n.message,
        n.is_read,
        n.created_at,

        c.user_id AS claimant_id,
        c.status AS claim_status,

        f.id AS item_id,
        f.item_name,
        f.user_id AS reporter_id

    FROM notifications n

    LEFT JOIN claims c
        ON n.claim_id = c.id

    LEFT JOIN found_items f
        ON c.item_id = f.id

    WHERE n.user_id = ?

    ORDER BY n.created_at DESC
";


$stmt =
    mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $user_id
);

mysqli_stmt_execute($stmt);

$result =
    mysqli_stmt_get_result($stmt);

$notifications = [];

while (
    $row =
    mysqli_fetch_assoc($result)
) {

    $notifications[] = $row;

}

mysqli_stmt_close($stmt);


/* ===========================
   MARK AS READ
=========================== */

$sql = "
    UPDATE notifications
    SET is_read = 1
    WHERE user_id = ?
";

$stmt =
    mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $user_id
);

mysqli_stmt_execute($stmt);

mysqli_stmt_close($stmt);

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


<?php
include "../includes/student_navbar.php";
?>


<section class="notifications-section">

<div class="container">


<h1 class="page-title">

    <i class="fa-solid fa-bell text-primary"></i>

    Notifications

</h1>


<p class="page-subtitle">

    Stay updated about your claims and found items.

</p>


<?php if (count($notifications) > 0) { ?>


    <?php foreach (
        $notifications
        as $notification
    ) { ?>


        <?php

        $message =
            $notification['message'];

        $claim_status =
            $notification['claim_status'];

        $claim_id =
            (int)$notification['claim_id'];

        $reporter_id =
            (int)$notification['reporter_id'];

        $is_claim_notification =
            !empty($notification['claim_id']);

        ?>


        <?php

        /*
         * Determine icon/style from
         * the notification message.
         */

        if (
            stripos(
                $message,
                'approved'
            ) !== false
        ) {

            $card_class =
                'approved';

            $icon_class =
                'icon-approved';

            $icon =
                'fa-circle-check';

            $title =
                'Claim Approved';

        } elseif (
            stripos(
                $message,
                'rejected'
            ) !== false
        ) {

            $card_class =
                'rejected';

            $icon_class =
                'icon-rejected';

            $icon =
                'fa-circle-xmark';

            $title =
                'Claim Rejected';

        } elseif (
    stripos(
        $message,
        'claim'
    ) !== false
) {

    /*
     * This notification was created
     * when the claim was submitted.
     *
     * If the claim is still Pending,
     * show it as a new request.
     *
     * If it has already been processed,
     * keep the notification as history
     * but show that it was reviewed.
     */

    if ($claim_status === "Pending") {

        $card_class = '';

        $icon_class =
            'icon-claim';

        $icon =
            'fa-hand-holding-heart';

        $title =
            'New Claim Request';

    } elseif ($claim_status === "Approved") {

        $card_class =
            'approved';

        $icon_class =
            'icon-approved';

        $icon =
            'fa-circle-check';

        $title =
            'Claim Request Reviewed';

    } elseif ($claim_status === "Rejected") {

        $card_class =
            'rejected';

        $icon_class =
            'icon-rejected';

        $icon =
            'fa-circle-xmark';

        $title =
            'Claim Request Reviewed';

    } else {

        $card_class = '';

        $icon_class =
            'icon-default';

        $icon =
            'fa-bell';

        $title =
            'Notification';

    }

} else {

            $card_class =
                '';

            $icon_class =
                'icon-default';

            $icon =
                'fa-bell';

            $title =
                'Notification';

        }

        ?>


        <div
        class="notification-card
        <?php echo $card_class; ?>">

            <div
            class="d-flex
            gap-3
            align-items-start">


                <div
                class="notification-icon
                <?php echo $icon_class; ?>">

                    <i
                    class="fa-solid
                    <?php echo $icon; ?>">
                    </i>

                </div>


                <div
                class="flex-grow-1">


                    <div
                    class="notification-title">

                        <?php
                        echo htmlspecialchars(
                            $title
                        );
                        ?>

                    </div>


                    <div
class="notification-text">

    <?php
    echo htmlspecialchars(
        $message
    );
    ?>

</div>


<?php if (
    $is_claim_notification &&
    $reporter_id === (int)$user_id &&
    $claim_status !== "Pending"
) { ?>

    <div class="mt-2">

        <?php if ($claim_status === "Approved") { ?>

            <span class="badge bg-success">

                <i class="fa-solid fa-check"></i>

                Claim Approved

            </span>

        <?php } elseif ($claim_status === "Rejected") { ?>

            <span class="badge bg-danger">

                <i class="fa-solid fa-xmark"></i>

                Claim Rejected

            </span>

        <?php } ?>

    </div>

<?php } ?>


                    <div
                    class="notification-time">

                        <i
                        class="fa-regular
                        fa-clock">
                        </i>

                        <?php
                        echo htmlspecialchars(
                            $notification['created_at']
                        );
                        ?>

                    </div>


                   <?php

/*
 * Claim notification action
 *
 * Reporter can open the claim:
 *
 * Pending   -> View Claim Request
 * Approved  -> View Claim Details
 * Rejected  -> View Claim Details
 * Completed -> View Claim Details
 */

if (
    $is_claim_notification &&
    $reporter_id === (int)$user_id
) {

?>

    <a
        href="claim_details.php?id=<?php
            echo $claim_id;
        ?>"
        class="
        btn
        <?php
        echo ($claim_status === "Pending")
            ? "btn-primary"
            : "btn-success";
        ?>
        notification-btn
        mt-3">

        <i
            class="fa-solid
            <?php
            echo ($claim_status === "Pending")
                ? "fa-eye"
                : "fa-handshake";
            ?>">
        </i>

        <?php

        if ($claim_status === "Pending") {

            echo "View Claim Request";

        } elseif ($claim_status === "Approved") {

            echo "View Claim & Handover";

        } elseif ($claim_status === "Completed") {

            echo "View Claim Details";

        } elseif ($claim_status === "Rejected") {

            echo "View Claim Details";

        } else {

            echo "View Claim Details";

        }

        ?>

    </a>

<?php } ?>


                </div>


                <?php

                if (
                    !$notification['is_read']
                ) {

                ?>

                    <span
                    class="new-label">

                        NEW

                    </span>

                <?php } ?>


            </div>

        </div>


    <?php } ?>


<?php } else { ?>


    <div
    class="empty-notifications">

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


<?php } ?>


</div>

</section>


</body>

</html>