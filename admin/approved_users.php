<?php

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != "Admin") {

    header("Location: ../login.php");
    exit();

}

include "../config/database.php";


/* ===========================
   GET APPROVED USERS
   EXCLUDE ADMIN
=========================== */

$sql = "SELECT users.*,
               universities.name AS university_name
        FROM users
        INNER JOIN universities
        ON users.university_ref_id = universities.id
        WHERE users.approval_status = 'Approved'
        AND users.role != 'Admin'
        ORDER BY users.id DESC";

$result = mysqli_query($conn, $sql);

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>Approved Users | FindIt</title>


<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
      rel="stylesheet">


<link rel="stylesheet"
      href="../assets/css/style.css">

<link rel="stylesheet"
      href="../assets/css/dashboard.css">


<link rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">


<style>

.user-card {

    height: 100%;

}


.profile-image {

    width: 110px;

    height: 110px;

    object-fit: cover;

    border-radius: 50%;

    border: 3px solid #eee;

    margin-bottom: 15px;

}


.no-image {

    width: 110px;

    height: 110px;

    border-radius: 50%;

    background: #f1f3f5;

    display: flex;

    align-items: center;

    justify-content: center;

    margin-bottom: 15px;

    color: #777;

    font-size: 40px;

}

</style>

</head>


<body>


<?php include "navbar.php"; ?>


<div class="container"
     style="padding-top:130px; padding-bottom:60px;">


    <h2 class="text-center mb-5">

        Approved Users

    </h2>


    <div class="row g-4">


        <?php if (mysqli_num_rows($result) > 0) { ?>


            <?php while ($user = mysqli_fetch_assoc($result)) { ?>


                <div class="col-lg-6">


                    <div class="user-card">


                        <!-- PROFILE IMAGE -->

                        <?php

                        if (
                            !empty($user['profile_image']) &&
                            file_exists(
                                "../uploads/profile/" .
                                $user['profile_image']
                            )
                        ) {

                        ?>

                            <img src="../uploads/profile/<?php
                                echo htmlspecialchars(
                                    $user['profile_image']
                                );
                            ?>"
                                 alt="Profile Picture"
                                 class="profile-image">

                        <?php

                        } else {

                        ?>

                            <div class="no-image">

                                <i class="fa-solid fa-user"></i>

                            </div>

                        <?php

                        }

                        ?>


                        <!-- NAME -->

                        <h4>

                            <i class="fa-solid fa-user text-primary"></i>

                            <?php echo htmlspecialchars(
                                $user['full_name']
                            ); ?>

                        </h4>


                        <!-- UNIVERSITY -->

                        <p>

                            <strong>

                                <i class="fa-solid fa-building-columns"></i>

                                University:

                            </strong>

                            <?php echo htmlspecialchars(
                                $user['university_name']
                            ); ?>

                        </p>


                        <!-- UNIVERSITY ID -->

                        <p>

                            <strong>ID:</strong>

                            <?php echo htmlspecialchars(
                                $user['university_id']
                            ); ?>

                        </p>


                        <!-- EMAIL -->

                        <p>

                            <strong>Email:</strong>

                            <?php echo htmlspecialchars(
                                $user['email']
                            ); ?>

                        </p>


                        <!-- PHONE -->

                        <p>

                            <strong>Phone:</strong>

                            <?php echo htmlspecialchars(
                                $user['phone']
                            ); ?>

                        </p>


                        <!-- DEPARTMENT -->

                        <p>

                            <strong>Department:</strong>

                            <?php echo htmlspecialchars(
                                $user['department']
                            ); ?>

                        </p>


                        <div class="mt-3">

                            <span class="badge bg-success">

                                <i class="fa-solid fa-circle-check"></i>

                                Approved

                            </span>

                        </div>


                    </div>

                </div>


            <?php } ?>


        <?php } else { ?>


            <div class="col-12">

                <div class="alert alert-info text-center">

                    No approved users found.

                </div>

            </div>


        <?php } ?>


    </div>

</div>


</body>

</html>