<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

?>

<nav class="navbar navbar-expand-lg fixed-top custom-navbar">

    <div class="container">

        <!-- LOGO -->

        <a class="navbar-brand logo" href="../index.php">

            <div class="logo-icon">
                <i class="fa-solid fa-magnifying-glass"></i>
            </div>

            <div class="logo-text">
                <h3>Find<span>It</span></h3>
                <small>Admin Panel</small>
            </div>

        </a>


        <!-- MOBILE MENU BUTTON -->

        <button
            class="navbar-toggler text-white"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#navbarNav"
            aria-controls="navbarNav"
            aria-expanded="false"
            aria-label="Toggle navigation">

            <i class="fa-solid fa-bars"></i>

        </button>


        <!-- NAVIGATION -->

        <div class="collapse navbar-collapse" id="navbarNav">

            <ul class="navbar-nav ms-auto align-items-center">


                <!-- DASHBOARD -->

                <li class="nav-item">

                    <a
                        class="nav-link"
                        href="dashboard.php">

                        Dashboard

                    </a>

                </li>


                <!-- USERS -->

                <li class="nav-item">

                    <a
                        class="nav-link"
                        href="users.php">

                        Pending Users

                    </a>

                </li>


                <!-- MANAGE CLAIMS -->

                <li class="nav-item">

                    <a
                        class="nav-link"
                        href="approved_users.php">

                        Approved Users

                    </a>

                </li>
                <li class="nav-item">

                    <a
                        class="nav-link"
                        href="lost_reports.php">

                        Lost Reports

                    </a>

                </li>
                <li class="nav-item">

                    <a
                        class="nav-link"
                        href="found_reports.php">

                        Found Reports

                    </a>

                </li>


                <!-- ADMIN NAME -->

                <li class="nav-item">

                    <span class="nav-link">

                        <i class="fa-solid fa-user-shield"></i>

                        <?php

                        echo htmlspecialchars(
                            $_SESSION['full_name'] ?? 'Admin'
                        );

                        ?>

                    </span>

                </li>


                <!-- LOGOUT -->

                <li class="nav-item ms-lg-3">

                    <a
                        href="../logout.php"
                        class="btn btn-login">

                        Logout

                    </a>

                </li>


            </ul>

        </div>

    </div>

</nav>