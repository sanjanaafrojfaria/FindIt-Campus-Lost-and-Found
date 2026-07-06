<?php

session_start();

if (!isset($_SESSION['user_id'])) {

    header("Location: ../login.php");
    exit();

}


?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Student Dashboard | FindIt</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link rel="stylesheet" href="../assets/css/style.css">
<link rel="stylesheet" href="../assets/css/dashboard.css">

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

</head>

<body>
<?php include "../includes/student_navbar.php"; ?>

<section class="dashboard-header">

    <div class="container text-center">

        <h2>

            Welcome back,

            <span>

                <?php

                $name = explode(" ", $_SESSION['full_name']);

                echo htmlspecialchars($name[0]);

                ?>

            </span>

            👋

        </h2>

        <p>

            Manage your lost and found activities from one place.

        </p>

    </div>

</section>
<section class="quick-actions">

    <div class="container">

        <h2 class="section-title">

            Quick Actions

        </h2>

        <div class="row g-4">

            <div class="col-md-6">

                <div class="action-card lost-card">

                    <i class="fa-solid fa-circle-exclamation action-icon"></i>

                    <h3>Report Lost Item</h3>

                    <p>

                        Lost something on campus? Submit a report and let others help you find it.

                    </p>

                    <a href="report_lost.php" class="btn btn-danger">

                        Report Lost

                    </a>

                </div>

            </div>

            <div class="col-md-6">

                <div class="action-card found-card">

                    <i class="fa-solid fa-hand-holding-heart action-icon"></i>

                    <h3>Report Found Item</h3>

                    <p>

                        Found an item? Report it so it can be returned to its rightful owner.

                    </p>

                    <a href="report_found.php" class="btn btn-success">

                        Report Found

                    </a>

                </div>

            </div>

        </div>

    </div>

</section>

</body>

</html>