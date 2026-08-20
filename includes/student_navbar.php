<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once __DIR__ . '/../config/database.php';


/* ===========================
   GET LOGGED-IN USER
=========================== */

$navbar_user_id = $_SESSION['user_id'] ?? 0;

$notification_count = 0;


/* ===========================
   COUNT UNREAD NOTIFICATIONS
=========================== */

if ($navbar_user_id > 0) {

    $sql = "
        SELECT COUNT(*) AS total
        FROM notifications
        WHERE user_id = ?
        AND is_read = 0
    ";

    $stmt = mysqli_prepare($conn, $sql);

    if ($stmt) {

        mysqli_stmt_bind_param(
            $stmt,
            "i",
            $navbar_user_id
        );

        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);

        $row = mysqli_fetch_assoc($result);

        $notification_count = (int)($row['total'] ?? 0);

        mysqli_stmt_close($stmt);
    }
}

?>


<style>

.notification-badge {

    position: absolute;

    top: 0;

    right: -5px;

    background: #dc2626;

    color: white;

    font-size: 10px;

    font-weight: 700;

    min-width: 18px;

    height: 18px;

    padding: 2px 5px;

    border-radius: 50%;

    display: flex;

    align-items: center;

    justify-content: center;

}

</style>


<nav class="navbar navbar-expand-lg fixed-top custom-navbar">

    <div class="container">


        <!-- ===========================
             LOGO
        ============================ -->

        <a class="navbar-brand logo" href="../index.php">

            <div class="logo-icon">

                <i class="fa-solid fa-magnifying-glass"></i>

            </div>

            <div class="logo-text">

                <h3>
                    Find<span>It</span>
                </h3>

                <small>
                    Campus Lost & Found
                </small>

            </div>

        </a>


        <!-- ===========================
             MOBILE MENU
        ============================ -->

        <button
            class="navbar-toggler text-white"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#navbarNav">

            <i class="fa-solid fa-bars"></i>

        </button>


        <div
            class="collapse navbar-collapse"
            id="navbarNav">


            <ul class="navbar-nav ms-auto align-items-center">


                <!-- HOME -->

                <li class="nav-item">

                    <a
                        class="nav-link"
                        href="../index.php">

                        Home

                    </a>

                </li>


                <!-- DASHBOARD -->

                <li class="nav-item">

                    <a
                        class="nav-link"
                        href="dashboard.php">

                        Dashboard

                    </a>

                </li>


                <!-- SEARCH -->

                <li class="nav-item">

                    <a
                        class="nav-link"
                        href="../student/search.php">

                        Search

                    </a>

                </li>


                <!-- REPORT LOST -->

                <li class="nav-item">

                    <a
                        class="nav-link"
                        href="report_lost.php">

                        Report Lost

                    </a>

                </li>


                <!-- REPORT FOUND -->

                <li class="nav-item">

                    <a
                        class="nav-link"
                        href="report_found.php">

                        Report Found

                    </a>

                </li>


                <!-- MY REPORTS -->

                <li class="nav-item">

                    <a
                        class="nav-link"
                        href="../student/my_reports.php">

                        My Reports

                    </a>

                </li>


                <!-- ===========================
                     NOTIFICATIONS
                ============================ -->

                <li class="nav-item">

                    <a
                        class="nav-link position-relative"
                        href="notifications.php">

                        <i class="fa-solid fa-bell"></i>

                        Notifications


                        <?php if ($notification_count > 0) { ?>

                            <span class="notification-badge">

                                <?php
                                echo $notification_count;
                                ?>

                            </span>

                        <?php } ?>


                    </a>

                </li>


                <!-- ===========================
                     PROFILE
                ============================ -->

                <li class="nav-item ms-lg-3">

                    <a
                        class="nav-link"
                        href="profile.php">

                        <i class="fa-solid fa-user"></i>

                        <?php

                        $full_name =
                            $_SESSION['full_name'] ?? 'User';

                        $name =
                            explode(" ", $full_name);

                        echo htmlspecialchars(
                            $name[0]
                        );

                        ?>

                    </a>

                </li>


                <!-- ===========================
                     LOGOUT
                ============================ -->

                <li class="nav-item ms-lg-2">

                    <a
                        href="../logout.php"
                        class="btn btn-login">

                        <i
                            class="fa-solid fa-right-from-bracket">
                        </i>

                        Logout

                    </a>

                </li>


            </ul>

        </div>

    </div>

</nav>