<nav class="navbar navbar-expand-lg fixed-top custom-navbar">

    <div class="container">

        <a class="navbar-brand logo" href="../index.php">

            <div class="logo-icon">
                <i class="fa-solid fa-magnifying-glass"></i>
            </div>

            <div class="logo-text">
                <h3>Find<span>It</span></h3>
                <small>Campus Lost & Found</small>
            </div>

        </a>

        <button class="navbar-toggler text-white"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#navbarNav">

            <i class="fa-solid fa-bars"></i>

        </button>

        <div class="collapse navbar-collapse" id="navbarNav">

            <ul class="navbar-nav ms-auto align-items-center">

                <li class="nav-item">
                    <a class="nav-link" href="dashboard.php">Dashboard</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="report_lost.php">Report Lost</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="report_found.php">Report Found</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="my_items.php">My Items</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="notifications.php">Notifications</a>
                </li>

                <li class="nav-item ms-lg-3">
                    <span class="nav-link">
                        <i class="fa-solid fa-user"></i>
                        <?php
                        $name = explode(" ", $_SESSION['full_name']);
                        echo htmlspecialchars($name[0]);
                        ?>
                    </span>
                </li>

                <li class="nav-item ms-lg-2">
                    <a href="../logout.php" class="btn btn-login">
                        <i class="fa-solid fa-right-from-bracket"></i>
                        Logout
                    </a>
                </li>

            </ul>

        </div>

    </div>

</nav>