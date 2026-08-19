<?php

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != "Admin") {

    header("Location: ../login.php");
    exit();

}

include "../config/database.php";


/* ===========================
   LOST REPORTS
=========================== */

$result = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total FROM lost_items"
);

$lost = mysqli_fetch_assoc($result)['total'];


/* ===========================
   FOUND REPORTS
=========================== */

$result = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total FROM found_items"
);

$found = mysqli_fetch_assoc($result)['total'];


/* ===========================
   PENDING USERS
=========================== */

$result = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM users
     WHERE approval_status = 'Pending'"
);

$pending = mysqli_fetch_assoc($result)['total'];


/* ===========================
   APPROVED USERS
   EXCLUDE ADMIN
=========================== */

$result = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM users
     WHERE approval_status = 'Approved'
     AND role != 'Admin'"
);

$approved = mysqli_fetch_assoc($result)['total'];

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>Admin Dashboard | FindIt</title>


<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
      rel="stylesheet">


<link rel="stylesheet"
      href="../assets/css/style.css">

<link rel="stylesheet"
      href="../assets/css/dashboard.css">


<link rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">


<style>

.dashboard-link {

    text-decoration: none;

    color: inherit;

    display: block;

}


.dashboard-link:hover {

    color: inherit;

}


.dashboard-link .stat-card {

    cursor: pointer;

    transition: transform 0.2s ease,
                box-shadow 0.2s ease;

}


.dashboard-link .stat-card:hover {

    transform: translateY(-5px);

    box-shadow: 0 8px 25px rgba(0,0,0,0.12);

}

</style>

</head>


<body>


<?php include "navbar.php"; ?>


<!-- ===========================
     ADMIN HEADER
=========================== -->

<section class="admin-header">

    <div class="container">

        <div class="admin-header-content">

            <div class="admin-icon">

                <i class="fa-solid fa-user-shield"></i>

            </div>


            <div>

                <h2>

                    Welcome back,

                    <span>

                        <?php echo htmlspecialchars(
                            $_SESSION['full_name']
                        ); ?>

                    </span>

                </h2>


                <p>

                    Manage users, approvals and monitor
                    the FindIt platform.

                </p>

            </div>

        </div>

    </div>

</section>



<!-- ===========================
     DASHBOARD STATISTICS
=========================== -->

<section class="dashboard-stats">

    <div class="container">

        <div class="row g-4">


            <!-- ===========================
                 PENDING APPROVALS
            ============================ -->

            <div class="col-lg-3 col-md-6">

                <a href="users.php"
                   class="dashboard-link">

                    <div class="stat-card">

                        <i class="fa-solid fa-user-clock stat-icon notify"></i>

                        <h2>

                            <?php echo $pending; ?>

                        </h2>

                        <p>

                            Pending Approvals

                        </p>

                    </div>

                </a>

            </div>



            <!-- ===========================
                 APPROVED USERS
            ============================ -->

            <div class="col-lg-3 col-md-6">

                <a href="approved_users.php"
                   class="dashboard-link">

                    <div class="stat-card">

                        <i class="fa-solid fa-users stat-icon found"></i>

                        <h2>

                            <?php echo $approved; ?>

                        </h2>

                        <p>

                            Approved Users

                        </p>

                    </div>

                </a>

            </div>



            <!-- ===========================
                 LOST REPORTS
            ============================ -->

            <div class="col-lg-3 col-md-6">

                <a href="lost_reports.php"
                   class="dashboard-link">

                    <div class="stat-card">

                        <i class="fa-solid fa-circle-exclamation stat-icon lost"></i>

                        <h2>

                            <?php echo $lost; ?>

                        </h2>

                        <p>

                            Lost Reports

                        </p>

                    </div>

                </a>

            </div>



            <!-- ===========================
                 FOUND REPORTS
            ============================ -->

            <div class="col-lg-3 col-md-6">

                <a href="found_reports.php"
                   class="dashboard-link">

                    <div class="stat-card">

                        <i class="fa-solid fa-hand-holding-heart stat-icon returned"></i>

                        <h2>

                            <?php echo $found; ?>

                        </h2>

                        <p>

                            Found Reports

                        </p>

                    </div>

                </a>

            </div>


        </div>

    </div>

</section>


</body>

</html>