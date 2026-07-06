<?php

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != "Admin") {

    header("Location: ../login.php");
    exit();

}

include "../config/database.php";

$sql = "SELECT * FROM users WHERE approval_status='Pending'";

$result = mysqli_query($conn, $sql);

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Pending Users | FindIt</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link rel="stylesheet" href="../assets/css/style.css">

<link rel="stylesheet" href="../assets/css/dashboard.css">

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

</head>

<body>

<?php include "navbar.php"; ?>

<div class="container" style="padding-top:130px; padding-bottom:60px;">

    <h2 class="text-center mb-5">

        Pending Student Accounts

    </h2>

    <div class="row g-4">

        <?php if(mysqli_num_rows($result) > 0){ ?>

            <?php while($user = mysqli_fetch_assoc($result)){ ?>

                <div class="col-lg-6">

                    <div class="user-card">

                        <h4>

                            <i class="fa-solid fa-user text-primary"></i>

                            <?php echo htmlspecialchars($user['full_name']); ?>

                        </h4>

                        <p>

                            <strong>ID:</strong>

                            <?php echo htmlspecialchars($user['university_id']); ?>

                        </p>

                        <p>

                            <strong>Email:</strong>

                            <?php echo htmlspecialchars($user['email']); ?>

                        </p>

                        <p>

                            <strong>Department:</strong>

                            <?php echo htmlspecialchars($user['department']); ?>

                        </p>

                        <div class="mt-4">

                            <a href="approve.php?id=<?php echo $user['id']; ?>"
                               class="btn btn-success">

                                <i class="fa-solid fa-check"></i>

                                Approve

                            </a>

                            <a href="reject.php?id=<?php echo $user['id']; ?>"
                               class="btn btn-danger">

                                <i class="fa-solid fa-xmark"></i>

                                Reject

                            </a>

                        </div>

                    </div>

                </div>

            <?php } ?>

        <?php } else { ?>

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