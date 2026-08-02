
<?php

session_start();

if (!isset($_SESSION['user_id'])) {

    header("Location: ../login.php");
    exit();

}
include "../config/database.php";

$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("
    SELECT COUNT(*) AS total
    FROM lost_items
    WHERE user_id = ?
");

$stmt->bind_param("i", $user_id);

$stmt->execute();

$result = $stmt->get_result();

$lost_count = $result->fetch_assoc()['total'];


/* ==========================
   FOUND REPORTS
========================== */

$stmt = $conn->prepare("
    SELECT COUNT(*) AS total
    FROM found_items
    WHERE user_id = ?
");

$stmt->bind_param("i", $user_id);

$stmt->execute();

$result = $stmt->get_result();

$found_count = $result->fetch_assoc()['total'];


/* ==========================
   RETURNED ITEMS
========================== */

$stmt = $conn->prepare("
    SELECT
        (
            SELECT COUNT(*)
            FROM lost_items
            WHERE user_id = ?
            AND status = 'Returned'
        )
        +
        (
            SELECT COUNT(*)
            FROM found_items
            WHERE user_id = ?
            AND status = 'Returned'
        ) AS total
");

$stmt->bind_param("ii", $user_id, $user_id);

$stmt->execute();

$result = $stmt->get_result();

$returned_count = $result->fetch_assoc()['total'];


/* ==========================
   NOTIFICATIONS
========================== */

$notification_count = 0;


$name = explode(" ", $_SESSION['full_name']);
$firstName = htmlspecialchars($name[0]);

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


<!-- ==========================
     WELCOME HERO
========================== -->

<section class="dashboard-hero">

    <div class="container">

        <div class="dashboard-hero-content">

    <div class="hero-illustration">

        <div class="illustration-circle">
            <i class="fa-solid fa-magnifying-glass"></i>
        </div>

        <div class="floating-icon icon-one">
            <i class="fa-solid fa-circle-exclamation"></i>
        </div>

        <div class="floating-icon icon-two">
            <i class="fa-solid fa-hand-holding-heart"></i>
        </div>

        <div class="floating-icon icon-three">
            <i class="fa-solid fa-box-open"></i>
        </div>

    </div>


    <div>

        <span class="welcome-label">
            <i class="fa-solid fa-sparkles"></i>
            Student Dashboard
        </span>

        <h1>
            Welcome back,
            <span>
                <?php
                $name = explode(" ", $_SESSION['full_name']);
                echo htmlspecialchars($name[0]);
                ?>
            </span>
            👋
        </h1>

        <p>
            Keep track of your lost and found items,
            manage your reports, and help reunite
            belongings with their owners.
        </p>

        <div class="dashboard-hero-buttons">

    <a href="my_reports.php" class="btn btn-primary">
        <i class="fa-solid fa-file-lines"></i>
        View My Reports
    </a>

    <a href="found_items.php" class="btn btn-outline-primary">
        <i class="fa-solid fa-magnifying-glass"></i>
        Browse Found Items
    </a>

</div>

    </div>

</div>
    </div>

</section>



<!-- ==========================
     STATISTICS
========================== -->

<section class="dashboard-stats">

    <div class="container">

        <div class="row g-4">

            <div class="col-md-6 col-lg-3">

                <div class="stat-card">

                    <div class="stat-icon lost">

                        <i class="fa-solid fa-circle-exclamation"></i>

                    </div>

                    <h2><?php echo $lost_count; ?></h2>

                    <p>Lost Reports</p>

                </div>

            </div>


            <div class="col-md-6 col-lg-3">

                <div class="stat-card">

                    <div class="stat-icon found">

                        <i class="fa-solid fa-hand-holding-heart"></i>

                    </div>

                    <h2><?php echo $found_count; ?></h2>

                    <p>Found Reports</p>

                </div>

            </div>


            <div class="col-md-6 col-lg-3">

                <div class="stat-card">

                    <div class="stat-icon returned">

                        <i class="fa-solid fa-box-open"></i>

                    </div>

                    <h2><?php echo $returned_count; ?></h2>

                    <p>Items Returned</p>

                </div>

            </div>


            <div class="col-md-6 col-lg-3">

                <div class="stat-card">

                    <div class="stat-icon notify">

                        <i class="fa-solid fa-bell"></i>

                    </div>

                    <h2><?php echo $notification_count; ?></h2>

                    <p>Notifications</p>

                </div>

            </div>

        </div>

    </div>

</section>



<!-- ==========================
     QUICK ACTIONS
========================== -->

<section class="quick-actions">

    <div class="container">

        <div class="section-heading">

            <div>

                <span class="section-label">

                    GET STARTED

                </span>

                <h2>What would you like to do?</h2>

            </div>

            <p>

                Choose an action to get started with FindIt.

            </p>

        </div>


        <div class="row g-4">

            <div class="col-md-6">

                <div class="action-card lost-card">

                    <div class="action-icon-wrapper">

                        <i class="fa-solid fa-circle-exclamation"></i>

                    </div>

                    <div class="action-content">

                        <h3>Report a Lost Item</h3>

                        <p>

                            Lost something on campus?

                            Create a report with details about your item

                            and let the FindIt community help you.

                        </p>

                        <a href="report_lost.php" class="action-link lost-link">

                            Report Lost Item

                            <i class="fa-solid fa-arrow-right"></i>

                        </a>

                    </div>

                </div>

            </div>


            <div class="col-md-6">

                <div class="action-card found-card">

                    <div class="action-icon-wrapper">

                        <i class="fa-solid fa-hand-holding-heart"></i>

                    </div>

                    <div class="action-content">

                        <h3>Report a Found Item</h3>

                        <p>

                            Found something that belongs to someone?

                            Report it and help return it to its rightful owner.

                        </p>

                        <a href="report_found.php" class="action-link found-link">

                            Report Found Item

                            <i class="fa-solid fa-arrow-right"></i>

                        </a>

                    </div>

                </div>

            </div>

        </div>

    </div>

</section>



<!-- ==========================
     RECENT ACTIVITY
========================== -->

<section class="recent-activity">

    <div class="container">

        <div class="section-heading">

            <div>

                <span class="section-label">

                    YOUR ACTIVITY

                </span>

                <h2>Recent Reports</h2>

            </div>

            <a href="my_reports.php" class="view-all">

                View All

                <i class="fa-solid fa-arrow-right"></i>

            </a>

        </div>


        <div class="empty-activity">

            <div class="empty-icon">

                <i class="fa-solid fa-folder-open"></i>

            </div>

            <h3>No reports yet</h3>

            <p>

                Your lost and found reports will appear here.

            </p>

            <a href="report_lost.php" class="btn btn-primary">

                Create Your First Report

            </a>

        </div>

    </div>

</section>



<!-- ==========================
     HELP SECTION
========================== -->

<section class="dashboard-help">

    <div class="container">

        <div class="help-card">

            <div class="help-icon">

                <i class="fa-solid fa-circle-question"></i>

            </div>

            <div>

                <h3>Need help finding something?</h3>

                <p>

                    Browse recently reported items and see if someone

                    has already found what you're looking for.

                </p>

            </div>

            <a href="../index.php#found-items" class="btn browse-found-btn">
    <i class="fa-solid fa-magnifying-glass"></i>
    Browse Found Items
</a>

        </div>

    </div>

</section>


</body>

</html>
