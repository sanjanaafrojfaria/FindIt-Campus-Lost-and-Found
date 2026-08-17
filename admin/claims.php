<?php

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

include "../config/database.php";

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
   GET CLAIMS
=========================== */

$sql = "SELECT
            c.id AS claim_id,
            c.item_id,
            c.item_type,
            c.claim_reason,
            c.proof_image,
            c.status AS claim_status,
            c.created_at,

            u.full_name AS claimant_name,
            u.email AS claimant_email,
            u.university_ref_id,

            un.name AS university_name,

            f.item_name,
            f.category,
            f.location,
            f.found_date,
            f.image AS item_image,
            f.status AS item_status

        FROM claims c

        INNER JOIN users u
            ON c.user_id = u.id

        LEFT JOIN universities un
            ON u.university_ref_id = un.id

        INNER JOIN found_items f
            ON c.item_id = f.id

        WHERE c.item_type = 'Found'

        ORDER BY
            CASE
                WHEN c.status = 'Pending' THEN 1
                WHEN c.status = 'Approved' THEN 2
                ELSE 3
            END,
            c.created_at DESC";

$result = mysqli_query($conn, $sql);

$claims = [];

while ($row = mysqli_fetch_assoc($result)) {
    $claims[] = $row;
}

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>Manage Claims | FindIt</title>

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

.claims-section {

    padding: 130px 20px 70px;

}

.page-title {

    font-size: 38px;

    font-weight: 700;

    color: #0f172a;

}

.page-subtitle {

    color: #64748b;

    margin-bottom: 35px;

}

.claim-card {
    background: white;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 10px 35px rgba(0,0,0,.08);
    margin: 0 auto 25px;
    max-width: 950px;
}

.claim-header {

    padding: 20px 25px;

    border-bottom: 1px solid #e2e8f0;

    display: flex;

    justify-content: space-between;

    align-items: center;

    gap: 15px;

    flex-wrap: wrap;

}

.claim-item-name {

    font-size: 21px;

    font-weight: 700;

    color: #0f172a;

}

.claim-body {

    padding: 25px;

}

.info-label {

    font-size: 13px;

    font-weight: 600;

    color: #64748b;

    margin-bottom: 4px;

}

.info-value {

    color: #0f172a;

    font-weight: 500;

    margin-bottom: 18px;

}

.claim-reason {

    background: #f8fafc;

    padding: 18px;

    border-radius: 14px;

    color: #475569;

    line-height: 1.7;

}

.proof-image {

    width: 100%;

    height: 180px;

    object-fit: cover;

    border-radius: 14px;

    border: 1px solid #e2e8f0;

    cursor: pointer;

    transition: .3s;
}

.proof-image:hover {

    transform: scale(1.02);

}

.status-badge {

    padding: 7px 14px;

    border-radius: 30px;

    font-size: 12px;

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

.action-btn {

    border-radius: 30px;

    padding: 9px 20px;

    font-weight: 600;

}

.empty-claims {

    background: white;

    border-radius: 20px;

    padding: 70px 20px;

    text-align: center;

    box-shadow: 0 10px 35px rgba(0,0,0,.07);

}

.empty-claims i {

    font-size: 55px;

    color: #94a3b8;

    margin-bottom: 20px;

}
.claims-section .page-title,
.claims-section .page-subtitle {
    text-align: center;
}

</style>

</head>

<body>

<?php include "navbar.php"; ?>

<section class="claims-section">

<div class="container">

    <h1 class="page-title">

        <i class="fa-solid fa-hand-holding-heart text-primary"></i>

        Manage Claims

    </h1>

    <p class="page-subtitle">

        Review student claims and verify ownership of found items.

    </p>


    <?php if (count($claims) > 0) { ?>

        <?php foreach ($claims as $claim) { ?>

            <div class="claim-card">

                <!-- HEADER -->

                <div class="claim-header">

                    <div>

                        <div class="claim-item-name">

                            <i class="fa-solid fa-box-open me-2"></i>

                            <?php
                            echo htmlspecialchars(
                                $claim['item_name']
                            );
                            ?>

                        </div>

                        <small class="text-muted">

                            Claim #<?php
                            echo $claim['claim_id'];
                            ?>

                        </small>

                    </div>


                    <?php

                    if ($claim['claim_status'] === "Pending") {

                        $status_class = "status-pending";

                    } elseif (
                        $claim['claim_status'] === "Approved"
                    ) {

                        $status_class = "status-approved";

                    } else {

                        $status_class = "status-rejected";

                    }

                    ?>

                    <span class="status-badge <?php
                        echo $status_class;
                    ?>">

                        <?php
                        echo htmlspecialchars(
                            $claim['claim_status']
                        );
                        ?>

                    </span>

                </div>


                <!-- BODY -->

                <div class="claim-body">

                    <div class="row g-4">

                        <!-- CLAIMANT -->

                        <div class="col-lg-6">

                            <h6 class="fw-bold mb-3">

                                <i class="fa-solid fa-user me-2"></i>

                                Claimant Information

                            </h6>


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


                            <div class="info-label">
                                University
                            </div>

                            <div class="info-value">

                                <?php
                                echo htmlspecialchars(
                                    $claim['university_name']
                                );
                                ?>

                            </div>

                        </div>


                        <!-- ITEM -->

                        <div class="col-lg-6">

                            <h6 class="fw-bold mb-3">

                                <i class="fa-solid fa-box me-2"></i>

                                Found Item

                            </h6>


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


                        <!-- CLAIM REASON -->

                        <div class="col-lg-7">

                            <h6 class="fw-bold mb-3">

                                <i class="fa-solid fa-file-lines me-2"></i>

                                Claim Reason

                            </h6>

                            <div class="claim-reason">

                                <?php
                                echo nl2br(
                                    htmlspecialchars(
                                        $claim['claim_reason']
                                    )
                                );
                                ?>

                            </div>

                        </div>


                       <!-- ITEM & PROOF IMAGES -->

<div class="col-lg-5">

    <h6 class="fw-bold mb-3">

        <i class="fa-solid fa-images me-2"></i>

        Item & Proof Images

    </h6>


    <div class="row g-3">

        <!-- FOUND ITEM IMAGE -->

        <div class="col-6">

            <small class="text-muted d-block mb-2">
                Found Item
            </small>

            <?php

            if (
                !empty($claim['item_image']) &&
                $claim['item_image'] !== 'default-item.png'
            ) {

                $item_image_path =
                    "../uploads/found_items/" .
                    $claim['item_image'];

            } else {

                $item_image_path =
                    "../assets/images/default-item.png";

            }

            ?>

            <a
                href="<?php echo htmlspecialchars($item_image_path); ?>"
                target="_blank">

                <img
                    src="<?php echo htmlspecialchars($item_image_path); ?>"
                    class="proof-image"
                    alt="Found item"
                    onerror="this.src='../assets/images/default-item.png';">

            </a>

        </div>


        <!-- CLAIM PROOF IMAGE -->

        <div class="col-6">

            <small class="text-muted d-block mb-2">
                Claimant Proof
            </small>

            <?php

            if (!empty($claim['proof_image'])) {

                $proof_image_path =
                    "../uploads/claim_proofs/" .
                    $claim['proof_image'];

            } else {

                $proof_image_path = "";

            }

            ?>


            <?php if ($proof_image_path) { ?>

                <a
                    href="<?php echo htmlspecialchars($proof_image_path); ?>"
                    target="_blank">

                    <img
                        src="<?php echo htmlspecialchars($proof_image_path); ?>"
                        class="proof-image"
                        alt="Claim proof"
                        onerror="this.style.display='none';
                        this.nextElementSibling.style.display='block';">

                </a>

                <div
                    class="text-muted"
                    style="display:none;">

                    <i class="fa-solid fa-image-slash"></i>

                    Proof image not found.

                </div>

            <?php } else { ?>

                <div class="text-muted">

                    <i class="fa-solid fa-image-slash"></i>

                    No proof image uploaded.

                </div>

            <?php } ?>

        </div>

    </div>

</div>
                        

                    


                    <!-- ACTIONS -->

                    <?php if (
                        $claim['claim_status'] === "Pending"
                    ) { ?>

                        <hr class="my-4">

                        <div class="d-flex gap-2 flex-wrap">

                            <form
                            action="claim_action.php"
                            method="POST"
                            onsubmit="return confirm(
                                'Are you sure you want to approve this claim?'
                            );">

                                <input
                                type="hidden"
                                name="claim_id"
                                value="<?php
                                    echo $claim['claim_id'];
                                ?>">

                                <input
                                type="hidden"
                                name="action"
                                value="approve">

                                <button
                                type="submit"
                                class="btn btn-success action-btn">

                                    <i class="fa-solid fa-check"></i>

                                    Approve Claim

                                </button>

                            </form>


                            <form
                            action="claim_action.php"
                            method="POST"
                            onsubmit="return confirm(
                                'Are you sure you want to reject this claim?'
                            );">

                                <input
                                type="hidden"
                                name="claim_id"
                                value="<?php
                                    echo $claim['claim_id'];
                                ?>">

                                <input
                                type="hidden"
                                name="action"
                                value="reject">

                                <button
                                type="submit"
                                class="btn btn-danger action-btn">

                                    <i class="fa-solid fa-xmark"></i>

                                    Reject Claim

                                </button>

                            </form>

                        </div>

                    <?php } ?>

                </div>

            </div>

        <?php } ?>

    <?php } else { ?>

        <div class="empty-claims">

            <i class="fa-solid fa-inbox"></i>

            <h4>

                No Claims Yet

            </h4>

            <p class="text-muted">

                Student claims will appear here when they are submitted.

            </p>

        </div>

    <?php } ?>

</div>

</section>

</body>

</html>