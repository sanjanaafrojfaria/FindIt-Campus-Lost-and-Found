<?php

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

include "../config/database.php";

$user_id = $_SESSION['user_id'];


/* ===========================
   GET USER INFORMATION
=========================== */

$sql = "
    SELECT
        u.full_name,
        u.university_id,
        u.email,
        u.phone,
        u.department,
        u.profile_image,
        u.trust_score,
        u.created_at,
        un.name AS university_name
    FROM users u
    LEFT JOIN universities un
        ON u.university_ref_id = un.id
    WHERE u.id = ?
";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    die("Database Error: " . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt, "i", $user_id);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$user = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);


/* ===========================
   CHECK USER
=========================== */

if (!$user) {
    die("User not found.");
}


/* ===========================
   PROFILE IMAGE
=========================== */

$profile_image = !empty($user['profile_image'])
    ? "../uploads/profile/" . htmlspecialchars($user['profile_image'])
    : "../uploads/profile/default.png";


/* ===========================
   TRUST SCORE
=========================== */

$trust_score = (int) $user['trust_score'];

if ($trust_score >= 90) {
    $score_label = "Excellent";
} elseif ($trust_score >= 75) {
    $score_label = "Good";
} elseif ($trust_score >= 50) {
    $score_label = "Average";
} else {
    $score_label = "Low";
}


/* ===========================
   MEMBER SINCE
=========================== */

$member_since = date(
    "F Y",
    strtotime($user['created_at'])
);

?>


<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>My Profile | FindIt</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <!-- FindIt CSS -->
    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >

    <!-- Font Awesome -->
    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
    >


    <style>

        body {
            background: #f5f7fb;
            font-family: Arial, sans-serif;
        }

        .profile-container {
    max-width: 900px;
    margin: 110px auto 40px;
    padding: 0 20px;
}

        .profile-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }

        .profile-header {
            background: linear-gradient(135deg, #6a5acd, #8a63d2);
            color: white;
            padding: 35px;
            text-align: center;
        }

        .profile-image {
            width: 180px;
            height: 115px;
            object-fit: cover;
            border-radius: 10px;
            border: 4px solid white;
            background: white;
            margin-bottom: 15px;
        }

        .profile-header h2 {
            margin: 5px 0;
            font-weight: 700;
        }

        .profile-header p {
            margin: 0;
            opacity: 0.9;
        }

        .profile-body {
            padding: 35px;
        }

        .info-box {
            background: #f8f9fc;
            border-radius: 12px;
            padding: 18px 20px;
            margin-bottom: 18px;
        }

        .info-label {
            font-size: 13px;
            color: #777;
            margin-bottom: 5px;
        }

        .info-value {
            font-size: 16px;
            font-weight: 600;
            color: #222;
        }

        .trust-score {
            text-align: center;
            background: #f8f9fc;
            border-radius: 15px;
            padding: 25px;
            margin-top: 10px;
        }

        .score-number {
            font-size: 42px;
            font-weight: 700;
            color: #6a5acd;
        }

        .score-label {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 15px;
        }

        .progress {
            height: 10px;
            border-radius: 10px;
            background: #e5e5e5;
        }

        .progress-bar {
            background: #6a5acd;
            border-radius: 10px;
        }

        .id-card-note {
            font-size: 12px;
            color: #777;
            margin-top: 10px;
        }

    </style>

</head>


<body>


<?php include "../includes/student_navbar.php"; ?>



<div class="profile-container">

    <div class="profile-card">


        <!-- PROFILE HEADER -->

        <div class="profile-header">

            <img
                src="<?= $profile_image ?>"
                alt="University ID Card"
                class="profile-image"
            >

            <h2>
                <?= htmlspecialchars($user['full_name']) ?>
            </h2>

            <p>
                FindIt Member
            </p>

        </div>


        <!-- PROFILE INFORMATION -->

        <div class="profile-body">

            <div class="row">


                <!-- FULL NAME -->

                <div class="col-md-6">

                    <div class="info-box">

                        <div class="info-label">
                            Full Name
                        </div>

                        <div class="info-value">
                            <?= htmlspecialchars($user['full_name']) ?>
                        </div>

                    </div>

                </div>


                <!-- UNIVERSITY ID -->

                <div class="col-md-6">

                    <div class="info-box">

                        <div class="info-label">
                            University ID
                        </div>

                        <div class="info-value">
                            <?= htmlspecialchars($user['university_id']) ?>
                        </div>

                    </div>

                </div>


                <!-- UNIVERSITY -->

                <div class="col-md-6">

                    <div class="info-box">

                        <div class="info-label">
                            University
                        </div>

                        <div class="info-value">

                            <?= !empty($user['university_name'])
                                ? htmlspecialchars($user['university_name'])
                                : "Not available"
                            ?>

                        </div>

                    </div>

                </div>


                <!-- DEPARTMENT -->

                <div class="col-md-6">

                    <div class="info-box">

                        <div class="info-label">
                            Department
                        </div>

                        <div class="info-value">
                            <?= htmlspecialchars($user['department']) ?>
                        </div>

                    </div>

                </div>


                <!-- EMAIL -->

                <div class="col-md-6">

                    <div class="info-box">

                        <div class="info-label">
                            Email
                        </div>

                        <div class="info-value">
                            <?= htmlspecialchars($user['email']) ?>
                        </div>

                    </div>

                </div>


                <!-- PHONE -->

                <div class="col-md-6">

                    <div class="info-box">

                        <div class="info-label">
                            Phone
                        </div>

                        <div class="info-value">

                            <?= !empty($user['phone'])
                                ? htmlspecialchars($user['phone'])
                                : "Not provided"
                            ?>

                        </div>

                    </div>

                </div>


                <!-- MEMBER SINCE -->

                <div class="col-md-6">

                    <div class="info-box">

                        <div class="info-label">
                            Member Since
                        </div>

                        <div class="info-value">
                            <?= htmlspecialchars($member_since) ?>
                        </div>

                    </div>

                </div>

            </div>


            <!-- TRUST SCORE -->

            <div class="trust-score">

                <div class="info-label">
                    Trust Score
                </div>

                <div class="score-number">
                    <?= $trust_score ?>/100
                </div>

                <div class="score-label">
                    <?= htmlspecialchars($score_label) ?>
                </div>

                <div class="progress">

                    <div
                        class="progress-bar"
                        role="progressbar"
                        style="width: <?= min($trust_score, 100) ?>%;"
                        aria-valuenow="<?= $trust_score ?>"
                        aria-valuemin="0"
                        aria-valuemax="100"
                    ></div>

                </div>

            </div>


            <div class="text-center id-card-note">

                Your university ID card is used for account verification.

            </div>


        </div>

    </div>

</div>


</body>

</html>