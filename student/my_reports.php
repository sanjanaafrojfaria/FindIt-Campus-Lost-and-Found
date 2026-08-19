<?php

session_start();

if (!isset($_SESSION['user_id'])) {

    header("Location: ../login.php");
    exit();

}

include "../config/database.php";

$user_id = $_SESSION['user_id'];


/* ===========================
   GET LOST REPORTS
=========================== */

$lostQuery = mysqli_query(
    $conn,
    "SELECT *
     FROM lost_items
     WHERE user_id='$user_id'
     ORDER BY created_at DESC"
);


/* ===========================
   GET FOUND REPORTS
=========================== */

$foundQuery = mysqli_query(
    $conn,
    "SELECT *
     FROM found_items
     WHERE user_id='$user_id'
     ORDER BY created_at DESC"
);

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>My Reports | FindIt</title>

<link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
    rel="stylesheet">

<link
    rel="stylesheet"
    href="../assets/css/style.css">

<link
    rel="stylesheet"
    href="../assets/css/my_reports.css">

<link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

</head>


<body>


<?php include "../includes/student_navbar.php"; ?>


<!-- ===========================
     PAGE HEADER
=========================== -->

<section class="reports-header">

<div class="container text-center">

<h2>

<i class="fa-solid fa-folder-open"></i>

My Reports

</h2>

<p>

View all of your Lost and Found reports in one place.

</p>

</div>

</section>



<div class="container py-5">


<!-- ===========================
     LOST REPORTS
=========================== -->

<h3 class="section-title text-danger">

<i class="fa-solid fa-circle-exclamation"></i>

Lost Reports

</h3>


<div class="row g-4 mb-5">


<?php

if (mysqli_num_rows($lostQuery) > 0) {

    while ($row = mysqli_fetch_assoc($lostQuery)) {

?>


<div class="col-lg-4 col-md-6">

<div class="report-card">


<!-- IMAGE -->

<img
    src="../uploads/lost_items/<?php echo htmlspecialchars($row['image']); ?>"
    onerror="this.src='../assets/images/default-item.png'"
    class="report-image"
>


<div class="report-body">


<!-- TOP SECTION -->

<div class="report-top">

<div>

<h5>

<?php echo htmlspecialchars($row['item_name']); ?>

</h5>


<span class="badge bg-danger">

<?php echo htmlspecialchars($row['status']); ?>

</span>

</div>


<!-- ACTION BUTTONS -->

<div class="report-actions">


<a
    href="edit_lost.php?id=<?php echo $row['id']; ?>"
    class="edit-btn"
    title="Edit"
>

<i class="fa-solid fa-pen"></i>

</a>


<a
    href="../delete_lost.php?id=<?php echo $row['id']; ?>"
    class="delete-btn"
    onclick="return confirm('Delete this report?');"
    title="Delete"
>

<i class="fa-solid fa-trash"></i>

</a>


</div>

</div>



<!-- LOCATION -->

<p>

<i class="fa-solid fa-location-dot"></i>

<?php echo htmlspecialchars($row['location']); ?>

</p>



<!-- DATE -->

<p>

<i class="fa-solid fa-calendar"></i>

<?php echo htmlspecialchars($row['lost_date']); ?>

</p>



<!-- VIEW -->

<a
    href="item_details.php?id=<?php echo $row['id']; ?>&type=Lost"
    class="btn btn-outline-danger btn-sm w-100"
>

View

</a>


</div>

</div>

</div>


<?php

    }

} else {

?>


<div class="col-12">

<div class="empty-box">

<i class="fa-solid fa-box-open"></i>

<h5>

No Lost Reports Yet

</h5>

<p>

You haven't reported any lost items.

</p>

</div>

</div>


<?php

}

?>


</div>



<!-- ===========================
     FOUND REPORTS
=========================== -->

<h3 class="section-title text-success">

<i class="fa-solid fa-hand-holding-heart"></i>

Found Reports

</h3>


<div class="row g-4">


<?php

if (mysqli_num_rows($foundQuery) > 0) {

    while ($row = mysqli_fetch_assoc($foundQuery)) {

?>


<div class="col-lg-4 col-md-6">

<div class="report-card">


<!-- IMAGE -->

<img
    src="../uploads/found_items/<?php echo htmlspecialchars($row['image']); ?>"
    onerror="this.src='../assets/images/default-item.png'"
    class="report-image"
>


<div class="report-body">


<!-- TOP SECTION -->

<div class="report-top">

<div>

<h5>

<?php echo htmlspecialchars($row['item_name']); ?>

</h5>


<span class="badge bg-success">

<?php echo htmlspecialchars($row['status']); ?>

</span>

</div>


<!-- ACTION BUTTONS -->

<div class="report-actions">


<a
    href="edit_found.php?id=<?php echo $row['id']; ?>"
    class="edit-btn"
    title="Edit"
>

<i class="fa-solid fa-pen"></i>

</a>


<a
    href="../delete_found.php?id=<?php echo $row['id']; ?>"
    class="delete-btn"
    title="Delete"
    onclick="return confirm('Delete this report?');"
>

<i class="fa-solid fa-trash"></i>

</a>


</div>

</div>



<!-- LOCATION -->

<p>

<i class="fa-solid fa-location-dot"></i>

<?php echo htmlspecialchars($row['location']); ?>

</p>



<!-- DATE -->

<p>

<i class="fa-solid fa-calendar"></i>

<?php echo htmlspecialchars($row['found_date']); ?>

</p>



<!-- VIEW -->

<a
    href="item_details.php?id=<?php echo $row['id']; ?>&type=Found"
    class="btn btn-outline-success btn-sm w-100"
>

View

</a>


</div>

</div>

</div>


<?php

    }

} else {

?>


<div class="col-12">

<div class="empty-box">

<i class="fa-solid fa-box-open"></i>

<h5>

No Found Reports Yet

</h5>

<p>

You haven't reported any found items.

</p>

</div>

</div>


<?php

}

?>


</div>

</div>


</body>

</html>