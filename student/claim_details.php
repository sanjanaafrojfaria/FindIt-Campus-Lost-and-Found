<?php

session_start();

if (!isset($_SESSION['user_id'])) {

    header("Location: ../login.php");
    exit();

}

include "../config/database.php";

$user_id = $_SESSION['user_id'];

$claim_id = isset($_GET['id'])
    ? (int)$_GET['id']
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
        c.claim_reason,
        c.proof_image,
        c.status AS claim_status,
        c.created_at,

        claimant.full_name AS claimant_name,
        claimant.email AS claimant_email,
        claimant.university_ref_id AS claimant_university_id,

        f.user_id AS reporter_id,
        f.item_name,
        f.category,
        f.location,
        f.found_date,
        f.description,
        f.image AS item_image,
        f.status AS item_status

    FROM claims c

    INNER JOIN users claimant
        ON c.user_id = claimant.id

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

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$claim = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);


/* ===========================
   CHECK CLAIM
=========================== */

if (!$claim) {

    header("Location: notifications.php");
    exit();

}


/* ===========================
   ACCESS CONTROL
=========================== */

/*
 * Only:
 *
 * 1. Reporter
 * 2. Claimant
 *
 * can access this claim.
 */

if (
    (int)$claim['reporter_id'] !== (int)$user_id &&
    (int)$claim['claimant_id'] !== (int)$user_id
) {

    header("Location: ../message.php?action=access_denied");
    exit();

}


/* ===========================
   CHECK USER TYPE
=========================== */

$is_reporter =
    (int)$claim['reporter_id'] === (int)$user_id;

$is_claimant =
    (int)$claim['claimant_id'] === (int)$user_id;
    

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>Claim Request | FindIt</title>


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

.claim-section {

    padding: 130px 20px 70px;

}

.claim-container {

    max-width: 950px;

    margin: auto;

}

.page-title {

    text-align: center;

    font-size: 36px;

    font-weight: 700;

    margin-bottom: 10px;

}

.page-subtitle {

    text-align: center;

    color: #64748b;

    margin-bottom: 35px;

}

.claim-card {

    background: white;

    border-radius: 20px;

    padding: 30px;

    box-shadow: 0 10px 35px rgba(0,0,0,.08);

}

.section-title {

    font-size: 18px;

    font-weight: 700;

    margin-bottom: 18px;

    color: #0f172a;

}

.info-label {

    font-size: 13px;

    font-weight: 600;

    color: #64748b;

    margin-bottom: 4px;

}

.info-value {

    font-weight: 500;

    margin-bottom: 18px;

}

.reason-box {

    background: #f8fafc;

    border-radius: 15px;

    padding: 20px;

    line-height: 1.7;

    color: #475569;

}

.claim-image {

    width: 100%;

    height: 220px;

    object-fit: cover;

    border-radius: 15px;

    border: 1px solid #e2e8f0;

}

.status-badge {

    display: inline-block;

    padding: 8px 15px;

    border-radius: 30px;

    font-size: 13px;

    font-weight: 700;

}

.status-pending {

    background: #fef3c7;

    color: #b45309;

}

.status-approved {

    background: #dcfce7;

    color: #15803d;

}

.status-rejected {

    background: #fee2e2;

    color: #dc2626;

}

.status-completed {

    background: #dbeafe;

    color: #1d4ed8;

}

.action-btn {

    border-radius: 30px;

    padding: 11px 25px;

    font-weight: 600;

}

</style>

</head>


<body>


<?php include "../includes/student_navbar.php"; ?>


<section class="claim-section">

<div class="claim-container">


    <h1 class="page-title">

        <i class="fa-solid fa-hand-holding-heart text-primary"></i>

        Claim Request

    </h1>


    <p class="page-subtitle">

        Review the ownership request for your found item.

    </p>


    <div class="claim-card">


        <!-- ===========================
             ITEM
        ============================ -->

        <h5 class="section-title">

            <i class="fa-solid fa-box-open text-primary me-2"></i>

            Found Item

        </h5>


        <div class="row g-4 mb-4">


            <div class="col-md-6">


                <div class="info-label">
                    Item Name
                </div>

                <div class="info-value">

                    <?php
                    echo htmlspecialchars(
                        $claim['item_name']
                    );
                    ?>

                </div>


                <div class="info-label">
                    Category
                </div>

                <div class="info-value">

                    <?php
                    echo htmlspecialchars(
                        $claim['category']
                    );
                    ?>

                </div>


                <div class="info-label">
                    Location
                </div>

                <div class="info-value">

                    <?php
                    echo htmlspecialchars(
                        $claim['location']
                    );
                    ?>

                </div>


                <div class="info-label">
                    Found Date
                </div>

                <div class="info-value">

                    <?php
                    echo htmlspecialchars(
                        $claim['found_date']
                    );
                    ?>

                </div>


            </div>


            <div class="col-md-6">


                <?php

                if (
                    !empty($claim['item_image']) &&
                    $claim['item_image'] !== 'default-item.png'
                ) {

                    $item_image =
                        "../uploads/found_items/" .
                        $claim['item_image'];

                } else {

                    $item_image =
                        "../assets/images/default-item.png";

                }

                ?>


                <a
                    href="<?php
                        echo htmlspecialchars($item_image);
                    ?>"
                    target="_blank">


                    <img
                        src="<?php
                            echo htmlspecialchars($item_image);
                        ?>"
                        class="claim-image"
                        alt="Found item"
                        onerror="
                            this.src='../assets/images/default-item.png';
                        ">


                </a>


            </div>


        </div>


        <hr>


        <!-- ===========================
             CLAIMANT
        ============================ -->

        <h5 class="section-title mt-4">

            <i class="fa-solid fa-user text-primary me-2"></i>

            Claimant Information

        </h5>


        <div class="row">


            <div class="col-md-6">

                <div class="info-label">
                    Name
                </div>

                <div class="info-value">

                    <?php
                    echo htmlspecialchars(
                        $claim['claimant_name']
                    );
                    ?>

                </div>

            </div>


            <div class="col-md-6">

                <div class="info-label">
                    Email
                </div>

                <div class="info-value">

                    <?php
                    echo htmlspecialchars(
                        $claim['claimant_email']
                    );
                    ?>

                </div>

            </div>


            <div class="col-md-6">

                <div class="info-label">
                    University ID
                </div>

                <div class="info-value">

                    <?php
                    echo htmlspecialchars(
                        $claim['claimant_university_id']
                    );
                    ?>

                </div>

            </div>


        </div>


        <hr>


        <!-- ===========================
             CLAIM REASON
        ============================ -->

        <h5 class="section-title mt-4">

            <i class="fa-solid fa-file-lines text-primary me-2"></i>

            Claim Reason

        </h5>


        <div class="reason-box">

            <?php

            echo nl2br(
                htmlspecialchars(
                    $claim['claim_reason']
                )
            );

            ?>

        </div>


        <!-- ===========================
             PROOF
        ============================ -->

        <h5 class="section-title mt-4">

            <i class="fa-solid fa-image text-primary me-2"></i>

            Ownership Proof

        </h5>


        <?php if (!empty($claim['proof_image'])) { ?>


            <?php

            $proof_image =
                "../uploads/claim_proofs/" .
                $claim['proof_image'];

            ?>


            <a
                href="<?php
                    echo htmlspecialchars($proof_image);
                ?>"
                target="_blank">


                <img
                    src="<?php
                        echo htmlspecialchars($proof_image);
                    ?>"
                    class="claim-image"
                    alt="Claim proof">


            </a>


        <?php } else { ?>


            <p class="text-muted">

                No proof image was uploaded.

            </p>


        <?php } ?>


        <!-- ==================================================
     CLAIM STATUS / ACTIONS
================================================== -->

<hr class="my-4">

<?php

/*
=========================================================
CLAIM STATUS
=========================================================
*/

?>

<?php if ($claim['claim_status'] === "Pending") { ?>

    <!-- ================================================
         PENDING CLAIM
    ================================================= -->

    <?php if ($is_reporter) { ?>

        <h5 class="section-title">

            <i class="fa-solid fa-gavel text-primary me-2"></i>

            Review Claim

        </h5>

        <p class="text-muted">

            Check the claimant's information and proof before
            making your decision.

        </p>


        <div class="d-flex gap-3 flex-wrap">

            <!-- APPROVE -->

            <form
                action="claim_action.php"
                method="POST"
                onsubmit="return confirm(
                    'Approve this claim? The item will be marked as Claimed.'
                );">

                <input
                    type="hidden"
                    name="claim_id"
                    value="<?php echo (int)$claim['claim_id']; ?>">

                <input
                    type="hidden"
                    name="action"
                    value="approve">

                <button
                    type="submit"
                    class="btn btn-success action-btn">

                    <i class="fa-solid fa-check me-1"></i>

                    Approve Claim

                </button>

            </form>


            <!-- REJECT -->

            <form
                action="claim_action.php"
                method="POST"
                onsubmit="return confirm(
                    'Reject this claim?'
                );">

                <input
                    type="hidden"
                    name="claim_id"
                    value="<?php echo (int)$claim['claim_id']; ?>">

                <input
                    type="hidden"
                    name="action"
                    value="reject">

                <button
                    type="submit"
                    class="btn btn-danger action-btn">

                    <i class="fa-solid fa-xmark me-1"></i>

                    Reject Claim

                </button>

            </form>

        </div>


    <?php } else { ?>

        <!-- CLAIMANT -->

        <div class="alert alert-warning">

            <i class="fa-solid fa-clock me-2"></i>

            <strong>Claim Pending</strong>

            <br>

            Your claim is currently waiting for the reporter's
            decision.

        </div>

    <?php } ?>


<?php } elseif ($claim['claim_status'] === "Approved") { ?>

    <!-- ================================================
         APPROVED CLAIM
    ================================================= -->

    <?php if ($is_reporter) { ?>

        <!-- REPORTER -->

        <div class="alert alert-success">

            <h5>

                <i class="fa-solid fa-circle-check me-2"></i>

                Claim Approved

            </h5>

            <p class="mb-2">

                You approved this claim.

            </p>

            <p class="mb-0">

                Please hand the item to the claimant in person,
                then click the button below.

            </p>

        </div>


        <!-- HANDOVER BUTTON -->

        <form
            action="handover.php"
            method="POST"
            onsubmit="return confirm(
                'Have you physically handed this item to the claimant?'
            );">

            <input
                type="hidden"
                name="claim_id"
                value="<?php echo (int)$claim['claim_id']; ?>">

            <button
                type="submit"
                class="btn btn-success action-btn">

                <i class="fa-solid fa-handshake me-2"></i>

                Mark as Handed Over

            </button>

        </form>


    <?php } elseif ($is_claimant) { ?>

        <!-- CLAIMANT -->

        <div class="alert alert-success">

            <h5>

                <i class="fa-solid fa-circle-check me-2"></i>

                Claim Approved

            </h5>

            <p class="mb-2">

                Your ownership claim has been approved.

            </p>

            <p class="mb-0">

                Please meet the reporter and collect your item.

            </p>

        </div>

    <?php } ?>


<?php } elseif ($claim['claim_status'] === "Completed") { ?>

    <!-- ================================================
         COMPLETED
    ================================================= -->

    <div class="alert alert-success">

        <h5>

            <i class="fa-solid fa-handshake me-2"></i>

            Handover Completed

        </h5>

        <p class="mb-0">

            This item has been successfully handed over
            to the claimant.

        </p>

    </div>


    <span class="status-badge status-completed">

        Completed

    </span>


<?php } elseif ($claim['claim_status'] === "Rejected") { ?>

    <!-- ================================================
         REJECTED
    ================================================= -->

    <div class="alert alert-danger">

        <h5>

            <i class="fa-solid fa-circle-xmark me-2"></i>

            Claim Rejected

        </h5>

        <p class="mb-0">

            This claim was rejected by the reporter.

        </p>

    </div>


    <span class="status-badge status-rejected">

        Rejected

    </span>


<?php } else { ?>

    <!-- ================================================
         UNKNOWN STATUS
    ================================================= -->

    <div class="alert alert-secondary">

        <strong>Claim Status:</strong>

        <?php
        echo htmlspecialchars(
            $claim['claim_status']
        );
        ?>

    </div>

<?php } ?>
</div>

</div>

</section>


</body>

</html>