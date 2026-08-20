<?php

session_start();

if (!isset($_SESSION['user_id'])) {

    header("Location: ../login.php");
    exit();

}

include "../config/database.php";

$user_id = (int)$_SESSION['user_id'];

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
        claimant.university_id AS claimant_university_id,
        claimant.trust_score AS claimant_trust_score,

        reporter.full_name AS reporter_name,
        reporter.trust_score AS reporter_trust_score,

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

    INNER JOIN users reporter
        ON f.user_id = reporter.id

    INNER JOIN found_items f
        ON c.item_id = f.id

    WHERE
        c.id = ?
        AND c.item_type = 'Found'
";


/*
 * IMPORTANT:
 * MySQL requires found_items to be joined before using
 * f.user_id in the users join.
 *
 * So we rebuild the query correctly below.
 */

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
        claimant.university_id AS claimant_university_id,
        claimant.trust_score AS claimant_trust_score,

        reporter.full_name AS reporter_name,
        reporter.trust_score AS reporter_trust_score,

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

    INNER JOIN users reporter
        ON f.user_id = reporter.id

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


/* ===========================
   GET HANDOVER
=========================== */

$sql = "
    SELECT *
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

$result = mysqli_stmt_get_result($stmt);

$handover = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);


/* ===========================
   HELPER
=========================== */

function e($value)
{
    return htmlspecialchars(
        $value ?? "",
        ENT_QUOTES,
        'UTF-8'
    );
}

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta
name="viewport"
content="width=device-width, initial-scale=1.0">

<title>Claim Details | FindIt</title>


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

.action-btn {

    border-radius: 30px;

    padding: 11px 25px;

    font-weight: 600;

}

.handover-box {

    background: #eff6ff;

    border: 1px solid #bfdbfe;

    border-radius: 16px;

    padding: 25px;

}

.handover-confirmed {

    background: #f0fdf4;

    border: 1px solid #bbf7d0;

}

.handover-completed {

    background: #f0fdf4;

    border: 1px solid #86efac;

}

.form-label {

    font-weight: 600;

}

.trust-score {

    display: inline-flex;

    align-items: center;

    gap: 6px;

    background: #eff6ff;

    color: #2563eb;

    border: 1px solid #bfdbfe;

    border-radius: 20px;

    padding: 6px 12px;

    font-weight: 700;

    font-size: 14px;

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

        Review the ownership request and handover details.

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
                    <?= e($claim['item_name']) ?>
                </div>


                <div class="info-label">
                    Category
                </div>

                <div class="info-value">
                    <?= e($claim['category']) ?>
                </div>


                <div class="info-label">
                    Location
                </div>

                <div class="info-value">
                    <?= e($claim['location']) ?>
                </div>


                <div class="info-label">
                    Found Date
                </div>

                <div class="info-value">
                    <?= e($claim['found_date']) ?>
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
                    href="<?= e($item_image) ?>"
                    target="_blank">

                    <img
                        src="<?= e($item_image) ?>"
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
                    <?= e($claim['claimant_name']) ?>
                </div>

            </div>


            <div class="col-md-6">

                <div class="info-label">
                    Email
                </div>

                <div class="info-value">
                    <?= e($claim['claimant_email']) ?>
                </div>

            </div>


            <div class="col-md-6">

                <div class="info-label">
                    University ID
                </div>

                <div class="info-value">
                    <?= e($claim['claimant_university_id']) ?>
                </div>

            </div>


            <div class="col-md-6">

                <div class="info-label">
                    Trust Score
                </div>

                <div class="info-value">

                    <span class="trust-score">

                        <i class="fa-solid fa-star"></i>

                        <?= (int)$claim['claimant_trust_score'] ?>

                    </span>

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

            <?= nl2br(
                e($claim['claim_reason'])
            ) ?>

        </div>


        <!-- ===========================
             PROOF
        ============================ -->

        <h5 class="section-title mt-4">

            <i class="fa-solid fa-image text-primary me-2"></i>

            Ownership Proof

        </h5>


        <?php if (!empty($claim['proof_image'])): ?>

            <?php

            $proof_image =
                "../uploads/claim_proofs/" .
                $claim['proof_image'];

            ?>

            <a
                href="<?= e($proof_image) ?>"
                target="_blank">

                <img
                    src="<?= e($proof_image) ?>"
                    class="claim-image"
                    alt="Claim proof">

            </a>

        <?php else: ?>

            <p class="text-muted">
                No proof image was uploaded.
            </p>

        <?php endif; ?>


        <hr class="my-4">


        <!-- ==================================================
             PENDING CLAIM
        ================================================== -->

        <?php if ($claim['claim_status'] === "Pending"): ?>


            <?php if ($is_reporter): ?>

                <h5 class="section-title">

                    <i class="fa-solid fa-gavel text-primary me-2"></i>

                    Review Claim

                </h5>


                <p class="text-muted">

                    Check the claimant's information and proof
                    before making your decision.

                </p>


                <div class="d-flex gap-3 flex-wrap">


                    <!-- APPROVE -->

                    <form
                        action="claim_action.php"
                        method="POST"
                        onsubmit="return confirm(
                            'Approve this claim?'
                        );">

                        <input
                            type="hidden"
                            name="claim_id"
                            value="<?= (int)$claim['claim_id'] ?>">

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
                            value="<?= (int)$claim['claim_id'] ?>">

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


            <?php else: ?>

                <div class="alert alert-warning">

                    <i class="fa-solid fa-clock me-2"></i>

                    <strong>Claim Pending</strong>

                    <br>

                    Your claim is waiting for the reporter's
                    decision.

                </div>

            <?php endif; ?>


        <!-- ==================================================
             APPROVED CLAIM
        ================================================== -->

        <?php elseif ($claim['claim_status'] === "Approved"): ?>


            <?php if (!$handover): ?>


                <?php if ($is_reporter): ?>

                    <div class="handover-box">

                        <h5 class="section-title mb-2">

                            <i class="fa-solid fa-calendar-check text-primary me-2"></i>

                            Schedule Handover

                        </h5>


                        <p class="text-muted">

                            The claim has been approved.
                            Please schedule a meeting with the
                            claimant.

                        </p>


                        <form
                            action="handover.php"
                            method="POST">


                            <input
                                type="hidden"
                                name="claim_id"
                                value="<?= (int)$claim['claim_id'] ?>">


                            <input
                                type="hidden"
                                name="action"
                                value="schedule">


                            <div class="mb-3">

                                <label class="form-label">
                                    Meeting Location
                                </label>

                                <select
                                    name="location"
                                    class="form-select"
                                    required>

                                    <option value="">
                                        Select a location
                                    </option>

                                    <option value="Main Gate">
                                        Main Gate
                                    </option>

                                    <option value="University Library">
                                        University Library
                                    </option>

                                    <option value="Cafeteria">
                                        Cafeteria
                                    </option>

                                    <option value="Student Center">
                                        Student Center
                                    </option>

                                    <option value="Department Building">
                                        Department Building
                                    </option>

                                </select>

                            </div>


                            <div class="mb-3">

                                <label class="form-label">
                                    Date
                                </label>

                                <input
                                    type="date"
                                    name="handover_date"
                                    class="form-control"
                                    min="<?= date('Y-m-d') ?>"
                                    required>

                            </div>


                            <div class="mb-3">

                                <label class="form-label">
                                    Time
                                </label>

                                <select
                                    name="handover_time"
                                    class="form-select"
                                    required>

                                    <option value="">
                                        Select a time
                                    </option>

                                    <option value="10:00:00">
                                        10:00 AM
                                    </option>

                                    <option value="12:00:00">
                                        12:00 PM
                                    </option>

                                    <option value="14:00:00">
                                        2:00 PM
                                    </option>

                                    <option value="16:00:00">
                                        4:00 PM
                                    </option>

                                    <option value="18:00:00">
                                        6:00 PM
                                    </option>

                                </select>

                            </div>


                            <div class="mb-3">

                                <label class="form-label">
                                    Optional Note
                                </label>

                                <textarea
                                    name="reporter_note"
                                    class="form-control"
                                    rows="3"
                                    placeholder="Example: I will be near the main entrance."></textarea>

                            </div>


                            <button
                                type="submit"
                                class="btn btn-primary action-btn">

                                <i class="fa-solid fa-calendar-plus me-2"></i>

                                Propose Handover

                            </button>


                        </form>

                    </div>


                <?php else: ?>

                    <div class="alert alert-info">

                        <i class="fa-solid fa-clock me-2"></i>

                        <strong>Claim Approved</strong>

                        <br>

                        The reporter has not scheduled the
                        handover meeting yet.

                    </div>

                <?php endif; ?>


            <?php elseif ($handover['status'] === "Proposed"): ?>


                <div class="handover-box">

                    <h5 class="section-title">

                        <i class="fa-solid fa-calendar-check text-primary me-2"></i>

                        Handover Meeting Proposed

                    </h5>


                    <div class="row">


                        <div class="col-md-6">

                            <div class="info-label">
                                Location
                            </div>

                            <div class="info-value">
                                <?= e($handover['location']) ?>
                            </div>

                        </div>


                        <div class="col-md-6">

                            <div class="info-label">
                                Date
                            </div>

                            <div class="info-value">

                                <?= date(
                                    'd F Y',
                                    strtotime(
                                        $handover['handover_date']
                                    )
                                ) ?>

                            </div>

                        </div>


                        <div class="col-md-6">

                            <div class="info-label">
                                Time
                            </div>

                            <div class="info-value">

                                <?= date(
                                    'h:i A',
                                    strtotime(
                                        $handover['handover_time']
                                    )
                                ) ?>

                            </div>

                        </div>


                    </div>


                    <?php if (!empty($handover['reporter_note'])): ?>

                        <div class="reason-box mb-3">

                            <strong>
                                Reporter Note:
                            </strong>

                            <br>

                            <?= nl2br(
                                e($handover['reporter_note'])
                            ) ?>

                        </div>

                    <?php endif; ?>


                    <?php if ($is_claimant): ?>

                        <form
                            action="handover.php"
                            method="POST"
                            onsubmit="return confirm(
                                'Accept this handover meeting?'
                            );">

                            <input
                                type="hidden"
                                name="claim_id"
                                value="<?= (int)$claim['claim_id'] ?>">

                            <input
                                type="hidden"
                                name="action"
                                value="accept">

                            <button
                                type="submit"
                                class="btn btn-success action-btn">

                                <i class="fa-solid fa-check me-2"></i>

                                Accept Handover

                            </button>

                        </form>


                    <?php else: ?>

                        <div class="alert alert-warning mb-0">

                            <i class="fa-solid fa-clock me-2"></i>

                            Waiting for the claimant to accept
                            the meeting.

                        </div>

                    <?php endif; ?>


                </div>


            <?php elseif ($handover['status'] === "Confirmed"): ?>


                <div class="handover-box handover-confirmed">

                    <h5 class="section-title">

                        <i class="fa-solid fa-circle-check text-success me-2"></i>

                        Handover Meeting Confirmed

                    </h5>


                    <p class="text-success">

                        The claimant has accepted the meeting.

                    </p>


                    <div class="row">


                        <div class="col-md-6">

                            <div class="info-label">
                                Location
                            </div>

                            <div class="info-value">
                                <?= e($handover['location']) ?>
                            </div>

                        </div>


                        <div class="col-md-6">

                            <div class="info-label">
                                Date
                            </div>

                            <div class="info-value">

                                <?= date(
                                    'd F Y',
                                    strtotime(
                                        $handover['handover_date']
                                    )
                                ) ?>

                            </div>

                        </div>


                        <div class="col-md-6">

                            <div class="info-label">
                                Time
                            </div>

                            <div class="info-value">

                                <?= date(
                                    'h:i A',
                                    strtotime(
                                        $handover['handover_time']
                                    )
                                ) ?>

                            </div>

                        </div>


                    </div>


                    <?php if (!empty($handover['reporter_note'])): ?>

                        <div class="reason-box mb-3">

                            <strong>
                                Reporter Note:
                            </strong>

                            <br>

                            <?= nl2br(
                                e($handover['reporter_note'])
                            ) ?>

                        </div>

                    <?php endif; ?>


                    <?php if ($is_claimant): ?>

                        <div class="alert alert-info">

                            <i class="fa-solid fa-handshake me-2"></i>

                            After you physically receive the item,
                            click the button below to complete
                            the handover.

                        </div>


                        <form
                            action="handover.php"
                            method="POST"
                            onsubmit="return confirm(
                                'Have you received the item from the reporter? This will complete the handover.'
                            );">

                            <input
                                type="hidden"
                                name="claim_id"
                                value="<?= (int)$claim['claim_id'] ?>">

                            <input
                                type="hidden"
                                name="action"
                                value="complete">

                            <button
                                type="submit"
                                class="btn btn-success action-btn">

                                <i class="fa-solid fa-handshake me-2"></i>

                                Mark as Complete

                            </button>

                        </form>


                    <?php else: ?>

                        <div class="alert alert-info mb-0">

                            <i class="fa-solid fa-clock me-2"></i>

                            Waiting for the claimant to receive
                            the item and mark the handover complete.

                        </div>

                    <?php endif; ?>


                </div>


            <?php endif; ?>


        <!-- ==================================================
             COMPLETED
        ================================================== -->

        <?php elseif ($claim['claim_status'] === "Completed"): ?>


            <div class="alert alert-success">

                <h5>

                    <i class="fa-solid fa-circle-check me-2"></i>

                    Handover Completed

                </h5>

                <p class="mb-0">

                    The item has been successfully handed over
                    to the claimant.

                </p>

            </div>


        <!-- ==================================================
             REJECTED
        ================================================== -->

        <?php elseif ($claim['claim_status'] === "Rejected"): ?>


            <div class="alert alert-danger">

                <h5>

                    <i class="fa-solid fa-circle-xmark me-2"></i>

                    Claim Rejected

                </h5>

                <p class="mb-0">

                    This claim was rejected by the reporter.

                </p>

            </div>


        <?php endif; ?>


        <div class="mt-4">

            <a
                href="notifications.php"
                class="btn btn-outline-secondary action-btn">

                <i class="fa-solid fa-arrow-left me-2"></i>

                Back to Notifications

            </a>

        </div>


    </div>

</div>

</section>

</body>

</html>