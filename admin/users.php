<?php

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != "Admin") {

    header("Location: ../login.php");
    exit();

}

include "../config/database.php";


/* ===========================
   GET PENDING USERS
=========================== */

$sql = "SELECT users.*, universities.name AS university_name
        FROM users
        INNER JOIN universities
        ON users.university_ref_id = universities.id
        WHERE users.approval_status = 'Pending'
        ORDER BY users.id DESC";

$result = mysqli_query($conn, $sql);

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Pending Users | FindIt</title>


<!-- Bootstrap -->

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
      rel="stylesheet">


<!-- Main CSS -->

<link rel="stylesheet"
      href="../assets/css/style.css">


<!-- Dashboard CSS -->

<link rel="stylesheet"
      href="../assets/css/dashboard.css">


<!-- Font Awesome -->

<link rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">


<style>

/* ===========================
   ID CARD SECTION
=========================== */

.id-card-section {

    margin-top: 20px;

    padding-top: 15px;

    border-top: 1px solid #ddd;

}


.id-card-section h6 {

    font-weight: 600;

    margin-bottom: 12px;

}


/* ===========================
   ID CARD IMAGE
=========================== */

.id-card-image {

    width: 100%;

    max-width: 400px;

    max-height: 250px;

    object-fit: contain;

    border-radius: 10px;

    border: 1px solid #ddd;

    background: #f8f9fa;

    padding: 5px;

    cursor: pointer;

    transition: 0.2s;

}


.id-card-image:hover {

    transform: scale(1.02);

    box-shadow: 0 5px 20px rgba(0,0,0,0.15);

}


/* ===========================
   MISSING IMAGE
=========================== */

.no-id-card {

    color: #dc3545;

    font-size: 14px;

}


/* ===========================
   UNIVERSITY
=========================== */

.university-name {

    color: #272c34;

    font-weight: 600;

}

</style>

</head>


<body>


<?php include "navbar.php"; ?>


<div class="container"
     style="padding-top:130px; padding-bottom:60px;">


    <!-- PAGE TITLE -->

    <h2 class="text-center mb-5">

        Pending Student Accounts

    </h2>


    <div class="row g-4">


        <?php if (mysqli_num_rows($result) > 0) { ?>


            <?php while ($user = mysqli_fetch_assoc($result)) { ?>


                <div class="col-lg-6">


                    <div class="user-card">


                        <!-- ===========================
                             STUDENT NAME
                        ============================ -->

                        <h4>

                            <i class="fa-solid fa-user text-primary"></i>

                            <?php echo htmlspecialchars($user['full_name']); ?>

                        </h4>


                        <!-- ===========================
                             UNIVERSITY
                        ============================ -->

                        <p>

                            <strong>

                                
                                University:

                            </strong>

                            <span class="university-name">

                                <?php echo htmlspecialchars(
                                    $user['university_name']
                                ); ?>

                            </span>

                        </p>


                        <!-- ===========================
                             UNIVERSITY ID
                        ============================ -->

                        <p>

                            <strong>ID:</strong>

                            <?php echo htmlspecialchars(
                                $user['university_id']
                            ); ?>

                        </p>


                        <!-- ===========================
                             EMAIL
                        ============================ -->

                        <p>

                            <strong>Email:</strong>

                            <?php echo htmlspecialchars(
                                $user['email']
                            ); ?>

                        </p>


                        <!-- ===========================
                             DEPARTMENT
                        ============================ -->

                        <p>

                            <strong>Department:</strong>

                            <?php echo htmlspecialchars(
                                $user['department']
                            ); ?>

                        </p>


                        <!-- ===========================
                             UNIVERSITY ID CARD
                        ============================ -->

                        <div class="id-card-section">


                            <h6>

                                <i class="fa-solid fa-id-card text-primary"></i>

                                University ID Card

                            </h6>


                            <?php

                            if (
                                !empty($user['profile_image']) &&
                                file_exists(
                                    "../uploads/profile/" .
                                    $user['profile_image']
                                )
                            ) {

                            ?>


                                <!-- CLICKABLE ID CARD -->

                                <a href="../uploads/profile/<?php
                                    echo htmlspecialchars(
                                        $user['profile_image']
                                    );
                                ?>"
                                   target="_blank">


                                    <img src="../uploads/profile/<?php
                                        echo htmlspecialchars(
                                            $user['profile_image']
                                        );
                                    ?>"
                                         alt="University ID Card"
                                         class="id-card-image">


                                </a>


                                <p class="text-muted mt-2 mb-0">

                                    <small>

                                        Click the image to view it in
                                        full size.

                                    </small>

                                </p>


                            <?php

                            } else {

                            ?>


                                <p class="no-id-card">

                                    <i class="fa-solid fa-triangle-exclamation"></i>

                                    ID card image not available.

                                </p>


                            <?php

                            }

                            ?>


                        </div>


                        <!-- ===========================
                             ACTION BUTTONS
                        ============================ -->

                        <div class="mt-4">


                            <!-- APPROVE -->

                            <a href="approve.php?id=<?php
                                echo $user['id'];
                            ?>"
                               class="btn btn-success">


                                <i class="fa-solid fa-check"></i>

                                Approve


                            </a>


                            <!-- REJECT -->

                            <a href="reject.php?id=<?php
                                echo $user['id'];
                            ?>"
                               class="btn btn-danger">


                                <i class="fa-solid fa-xmark"></i>

                                Reject


                            </a>


                        </div>


                    </div>


                </div>


            <?php } ?>


        <?php } else { ?>


            <!-- NO PENDING USERS -->

            <div class="col-12">


                <div class="alert alert-success text-center">


                    <i class="fa-solid fa-circle-check"></i>

                    No pending registrations.


                </div>


            </div>


        <?php } ?>


    </div>

</div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>


</body>

</html>