<?php

session_start();

if (!isset($_SESSION['user_id'])) {

    header("Location: ../login.php");
    exit();

}

include "../config/database.php";

$user_id = (int)$_SESSION['user_id'];

$id = isset($_GET['id'])
    ? (int)$_GET['id']
    : 0;

if ($id <= 0) {

    header("Location: my_reports.php");
    exit();

}


/* ===========================
   GET FOUND ITEM
=========================== */

$sql = "
    SELECT *
    FROM found_items
    WHERE id = ?
    AND user_id = ?
    LIMIT 1
";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {

    die("Database error.");

}

mysqli_stmt_bind_param(
    $stmt,
    "ii",
    $id,
    $user_id
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$item = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);


if (!$item) {

    die("Found item not found or access denied.");

}


/* ===========================
   UPDATE
=========================== */

if ($_SERVER["REQUEST_METHOD"] === "POST") {


    $item_name = trim($_POST['item_name'] ?? "");
    $category = trim($_POST['category'] ?? "");
    $location = trim($_POST['location'] ?? "");
    $found_date = trim($_POST['found_date'] ?? "");
    $description = trim($_POST['description'] ?? "");


    if (
        $item_name === "" ||
        $category === "" ||
        $location === "" ||
        $found_date === "" ||
        $description === ""
    ) {

        $error =
            "Please fill in all required fields.";

    } else {


        $image_name =
            $item['image'];


        /* ===========================
           NEW IMAGE
        =========================== */

        if (
            isset($_FILES['image']) &&
            $_FILES['image']['error'] === UPLOAD_ERR_OK
        ) {


            $allowed_types = [

                'image/jpeg',
                'image/png'

            ];


            if (
                !in_array(
                    $_FILES['image']['type'],
                    $allowed_types,
                    true
                )
            ) {

                $error =
                    "Only JPG and PNG images are allowed.";

            } else {


                $extension =
                    strtolower(
                        pathinfo(
                            $_FILES['image']['name'],
                            PATHINFO_EXTENSION
                        )
                    );


                $new_image =
                    time() . "_" .
                    bin2hex(random_bytes(5)) .
                    "." .
                    $extension;


                $upload_dir =
                    "../uploads/found_items/";


                if (
                    !is_dir($upload_dir)
                ) {

                    mkdir(
                        $upload_dir,
                        0755,
                        true
                    );

                }


                if (
                    move_uploaded_file(
                        $_FILES['image']['tmp_name'],
                        $upload_dir . $new_image
                    )
                ) {


                    if (
                        !empty($item['image']) &&
                        $item['image'] !== 'default-item.png'
                    ) {

                        $old_image =
                            $upload_dir .
                            $item['image'];


                        if (
                            file_exists($old_image)
                        ) {

                            unlink($old_image);

                        }

                    }


                    $image_name =
                        $new_image;

                } else {

                    $error =
                        "Could not upload the new image.";

                }

            }

        }


        /* ===========================
           UPDATE DATABASE
        =========================== */

        if (!isset($error)) {


            $sql = "
                UPDATE found_items

                SET
                    item_name = ?,
                    category = ?,
                    location = ?,
                    found_date = ?,
                    description = ?,
                    image = ?

                WHERE
                    id = ?
                    AND user_id = ?
            ";


            $stmt =
                mysqli_prepare(
                    $conn,
                    $sql
                );


            if (!$stmt) {

                $error =
                    "Could not prepare update.";

            } else {


                mysqli_stmt_bind_param(
                    $stmt,
                    "ssssssii",
                    $item_name,
                    $category,
                    $location,
                    $found_date,
                    $description,
                    $image_name,
                    $id,
                    $user_id
                );


                if (
                    mysqli_stmt_execute($stmt)
                ) {

                    mysqli_stmt_close($stmt);


                    header(
                        "Location: my_reports.php"
                    );

                    exit();

                } else {

                    $error =
                        "Could not update report.";

                    mysqli_stmt_close($stmt);

                }

            }

        }

    }

}


function e($value)
{

    return htmlspecialchars(
        $value ?? "",
        ENT_QUOTES,
        'UTF-8'
    );

}

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta
name="viewport"
content="width=device-width, initial-scale=1.0">

<title>Edit Found Report | FindIt</title>

<link
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet">

<link
rel="stylesheet"
href="../assets/css/style.css">

<link
rel="stylesheet"
href="../assets/css/report.css">

<link
rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

</head>

<body>

<?php include "../includes/student_navbar.php"; ?>


<section class="report-section">

<div class="container">

<div class="report-card">


<h2 class="report-title">

<i class="fa-solid fa-pen-to-square text-success"></i>

Edit Found Report

</h2>


<p class="report-subtitle">

Update the information about the found item.

</p>


<?php if (isset($error)): ?>

<div class="alert alert-danger">

<?= e($error) ?>

</div>

<?php endif; ?>


<form
method="POST"
enctype="multipart/form-data">


<div class="row">


<div class="col-md-6 mb-3">

<label class="form-label">

<i class="fa-solid fa-tag me-2"></i>

Item Name

</label>

<input
type="text"
class="form-control"
name="item_name"
value="<?= e($item['item_name']) ?>"
required>

</div>


<div class="col-md-6 mb-3">

<label class="form-label">

<i class="fa-solid fa-layer-group me-2"></i>

Category

</label>

<select
class="form-select"
name="category"
required>

<option value="">
Select Category
</option>

<?php

$categories = [

    "Electronics",
    "Wallet",
    "ID Card",
    "Keys",
    "Books",
    "Bag",
    "Note",
    "Jewelry",
    "Other"

];

foreach ($categories as $category):

?>

<option
value="<?= e($category) ?>"
<?= $item['category'] === $category
    ? 'selected'
    : '' ?>>

<?= e($category) ?>

</option>

<?php endforeach; ?>

</select>

</div>

</div>


<div class="row">


<div class="col-md-6 mb-3">

<label class="form-label">

<i class="fa-solid fa-location-dot me-2"></i>

Location Found

</label>

<select
class="form-select"
name="location"
required>

<option value="">
Select Location
</option>

<?php

$locations = [

    "Cafeteria",
    "Library",
    "Main Building",
    "Textile Building",
    "Female Prayer Room",
    "Male Common Room",
    "Female Common Room"

];

foreach ($locations as $location):

?>

<option
value="<?= e($location) ?>"
<?= $item['location'] === $location
    ? 'selected'
    : '' ?>>

<?= e($location) ?>

</option>

<?php endforeach; ?>

</select>

</div>


<div class="col-md-6 mb-3">

<label class="form-label">

<i class="fa-solid fa-calendar me-2"></i>

Date Found

</label>

<input
type="date"
class="form-control"
name="found_date"
value="<?= e($item['found_date']) ?>"
max="<?= date('Y-m-d') ?>"
required>

</div>

</div>


<div class="mb-3">

<label class="form-label">

<i class="fa-solid fa-image me-2"></i>

Replace Image

</label>

<input
type="file"
class="form-control"
name="image"
accept=".jpg,.jpeg,.png">

<small class="text-muted">

Leave empty to keep the current image.

</small>

</div>


<?php if (
    !empty($item['image']) &&
    $item['image'] !== 'default-item.png'
): ?>

<div class="mb-3">

<label class="form-label">

Current Image

</label>

<br>

<img
src="../uploads/found_items/<?= e($item['image']) ?>"
onerror="this.src='../assets/images/default-item.png'"
style="
width:150px;
height:150px;
object-fit:cover;
border-radius:12px;
">

</div>

<?php endif; ?>


<div class="mb-4">

<label class="form-label">

<i class="fa-solid fa-file-lines me-2"></i>

Description

</label>

<textarea
class="form-control"
rows="4"
name="description"
required><?= e($item['description']) ?></textarea>

</div>


<div class="d-flex gap-2 flex-wrap">


<button
type="submit"
class="btn btn-success btn-submit">

<i class="fa-solid fa-floppy-disk me-2"></i>

Save Changes

</button>


<a
href="my_reports.php"
class="btn btn-outline-secondary btn-submit">

<i class="fa-solid fa-arrow-left me-2"></i>

Cancel

</a>


</div>


</form>


</div>

</div>

</section>

</body>

</html>