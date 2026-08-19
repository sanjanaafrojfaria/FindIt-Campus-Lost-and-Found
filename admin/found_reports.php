<?php

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != "Admin") {

    header("Location: ../login.php");
    exit();

}

include "../config/database.php";


/* ===========================
   GET ALL FOUND REPORTS
=========================== */

$sql = "
    SELECT
        found_items.id,
        found_items.item_name,
        found_items.category,
        found_items.location,
        found_items.found_date,
        found_items.description,
        found_items.image,
        found_items.status,
        universities.name AS university_name

    FROM found_items

    LEFT JOIN universities
    ON found_items.university_ref_id = universities.id

    ORDER BY found_items.created_at DESC
";


$result = mysqli_query($conn, $sql);

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>Found Reports | FindIt</title>


<!-- Bootstrap -->

<link
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet">


<!-- Main CSS -->

<link
rel="stylesheet"
href="../assets/css/style.css">


<!-- Dashboard CSS -->

<link
rel="stylesheet"
href="../assets/css/dashboard.css">


<!-- Font Awesome -->

<link
rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">


<style>

/* ===========================
   REPORT CARD
=========================== */

.report-card {

    background: white;

    border-radius: 20px;

    overflow: hidden;

    box-shadow: 0 10px 35px rgba(0,0,0,.08);

    height: 100%;

    transition: .3s;

}


.report-card:hover {

    transform: translateY(-5px);

    box-shadow: 0 18px 45px rgba(0,0,0,.13);

}


/* ===========================
   IMAGE
=========================== */

.report-image {

    width: 100%;

    height: 220px;

    object-fit: cover;

    background: #f8fafc;

}


/* ===========================
   BODY
=========================== */

.report-body {

    padding: 22px;

}


.report-body h4 {

    font-weight: 700;

    color: #0f172a;

    margin-bottom: 15px;

}


.report-info {

    color: #64748b;

    margin-bottom: 9px;

}


.report-info i {

    width: 22px;

    color: #2563eb;

}


.description {

    color: #64748b;

    font-size: 14px;

    margin-top: 15px;

    padding-top: 15px;

    border-top: 1px solid #e5e7eb;

}


.found-badge {

    display: inline-block;

    background: #dcfce7;

    color: #16a34a;

    padding: 6px 13px;

    border-radius: 30px;

    font-size: 12px;

    font-weight: 700;

    margin-bottom: 12px;

}


.status-badge {

    display: inline-block;

    padding: 6px 12px;

    border-radius: 30px;

    font-size: 12px;

    font-weight: 600;

}


/* ===========================
   PAGE HEADER
=========================== */

.page-title {

    padding-top: 125px;

    padding-bottom: 35px;

}


.page-title h2 {

    font-weight: 700;

}


.page-title p {

    color: #64748b;

}


</style>

</head>


<body>


<?php include "navbar.php"; ?>


<!-- ===========================
     PAGE HEADER
=========================== -->

<div class="container page-title">

    <h2>

        <i class="fa-solid fa-hand-holding-heart text-success"></i>

        Found Reports

    </h2>

    <p>

        View all found item reports submitted across FindIt universities.

    </p>

</div>


<!-- ===========================
     REPORTS
=========================== -->

<div class="container pb-5">


    <div class="row g-4">


        <?php if (mysqli_num_rows($result) > 0) { ?>


            <?php while ($item = mysqli_fetch_assoc($result)) { ?>


                <div class="col-lg-4 col-md-6">


                    <div class="report-card">


                        <!-- ===========================
                             IMAGE
                        ============================ -->

                        <?php

                        if (
                            !empty($item['image']) &&
                            $item['image'] != 'default-item.png'
                        ) {

                            $image_path =
                                "../uploads/found_items/" .
                                $item['image'];

                        } else {

                            $image_path =
                                "../assets/images/default-item.png";

                        }

                        ?>


                        <img
                            src="<?php echo htmlspecialchars($image_path); ?>"
                            class="report-image"
                            alt="Found Item"
                            onerror="this.src='../assets/images/default-item.png';">


                        <!-- ===========================
                             BODY
                        ============================ -->

                        <div class="report-body">


                            <span class="found-badge">

                                <i class="fa-solid fa-hand-holding-heart"></i>

                                FOUND

                            </span>


                            <h4>

                                <?php echo htmlspecialchars(
                                    $item['item_name']
                                ); ?>

                            </h4>


                            <!-- CATEGORY -->

                            <p class="report-info">

                                <i class="fa-solid fa-layer-group"></i>

                                <strong>Category:</strong>

                                <?php echo htmlspecialchars(
                                    $item['category']
                                ); ?>

                            </p>


                            <!-- UNIVERSITY -->

                            <p class="report-info">

                                <i class="fa-solid fa-building-columns"></i>

                                <strong>University:</strong>

                                <?php echo htmlspecialchars(
                                    $item['university_name'] ?? 'Unknown'
                                ); ?>

                            </p>


                            <!-- LOCATION -->

                            <p class="report-info">

                                <i class="fa-solid fa-location-dot"></i>

                                <strong>Location:</strong>

                                <?php echo htmlspecialchars(
                                    $item['location']
                                ); ?>

                            </p>


                            <!-- DATE -->

                            <p class="report-info">

                                <i class="fa-solid fa-calendar"></i>

                                <strong>Found Date:</strong>

                                <?php echo htmlspecialchars(
                                    $item['found_date']
                                ); ?>

                            </p>


                            <!-- STATUS -->

                            <p class="report-info">

                                <i class="fa-solid fa-circle-info"></i>

                                <strong>Status:</strong>

                                <?php if ($item['status'] == 'Available') { ?>

                                    <span class="status-badge bg-primary text-white">

                                        Available

                                    </span>

                                <?php } else { ?>

                                    <span class="status-badge bg-secondary text-white">

                                        <?php echo htmlspecialchars(
                                            $item['status']
                                        ); ?>

                                    </span>

                                <?php } ?>

                            </p>


                            <!-- DESCRIPTION -->

                            <?php if (!empty($item['description'])) { ?>

                                <div class="description">

                                    <strong>Description:</strong>

                                    <br>

                                    <?php echo htmlspecialchars(
                                        $item['description']
                                    ); ?>

                                </div>

                            <?php } ?>


                        </div>

                    </div>

                </div>


            <?php } ?>


        <?php } else { ?>


            <div class="col-12">

                <div class="alert alert-info text-center">

                    <i class="fa-solid fa-circle-check"></i>

                    No found reports found.

                </div>

            </div>


        <?php } ?>


    </div>

</div>


</body>

</html>