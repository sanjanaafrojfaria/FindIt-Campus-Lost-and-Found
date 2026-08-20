
<?php

session_start();

if (!isset($_SESSION['user_id'])) {

    header("Location: ../login.php");
    exit();

}

include "../config/database.php";

$user_id = (int)$_SESSION['user_id'];


/* =========================================================
   MARK LOST ITEM AS FOUND
   Only the owner can do this
========================================================= */

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST['mark_found']) &&
    isset($_POST['item_id'])
) {

    $found_item_id = (int)$_POST['item_id'];

    if ($found_item_id > 0) {

        $sql = "
            UPDATE lost_items
            SET status = 'Found'
            WHERE id = ?
            AND user_id = ?
            AND status IN ('Open', 'Matched')
        ";

        $stmt = mysqli_prepare($conn, $sql);

        if ($stmt) {

            mysqli_stmt_bind_param(
                $stmt,
                "ii",
                $found_item_id,
                $user_id
            );

            mysqli_stmt_execute($stmt);

            mysqli_stmt_close($stmt);

        }

    }

    header(
        "Location: item_details.php?id="
        . $found_item_id
        . "&type=Lost"
        . "&updated=found"
    );

    exit();

}


/* ===========================
   VALIDATE PARAMETERS
=========================== */

if (
    !isset($_GET['id']) ||
    !is_numeric($_GET['id']) ||
    !isset($_GET['type'])
) {

    header("Location: search.php");
    exit();

}

$item_id = (int)$_GET['id'];
$item_type = $_GET['type'];


/* Only Lost or Found is allowed */

if ($item_type !== "Lost" && $item_type !== "Found") {

    header("Location: search.php");
    exit();

}


/* ===========================
   GET USER'S UNIVERSITY
=========================== */

$sql = "
    SELECT university_ref_id
    FROM users
    WHERE id = ?
";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {

    die("Database error.");

}

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $user_id
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$user = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);


if (!$user || empty($user['university_ref_id'])) {

    header("Location: ../message.php?action=invalid_university");
    exit();

}

$university_id = (int)$user['university_ref_id'];


/* ===========================
   GET ITEM
=========================== */

if ($item_type === "Lost") {

    $sql = "
        SELECT
            l.*,
            u.name AS university_name
        FROM lost_items l
        LEFT JOIN universities u
            ON l.university_ref_id = u.id
        WHERE l.id = ?
        AND l.university_ref_id = ?
    ";

} else {

    $sql = "
        SELECT
            f.*,
            u.name AS university_name
        FROM found_items f
        LEFT JOIN universities u
            ON f.university_ref_id = u.id
        WHERE f.id = ?
        AND f.university_ref_id = ?
    ";

}


$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {

    die("Database error.");

}

mysqli_stmt_bind_param(
    $stmt,
    "ii",
    $item_id,
    $university_id
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$item = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);


/* ===========================
   ITEM NOT FOUND
=========================== */

if (!$item) {

    header("Location: search.php");
    exit();

}


/* ===========================
   CHECK OWNER
=========================== */

$is_owner =
    ((int)$item['user_id'] === $user_id);


/* ===========================
   DATE
=========================== */

if ($item_type === "Lost") {

    $date = $item['lost_date'];

} else {

    $date = $item['found_date'];

}


/* ===========================
   IMAGE
=========================== */

if (
    !empty($item['image']) &&
    $item['image'] !== "default-item.png"
) {

    if ($item_type === "Lost") {

        $image_path =
            "../uploads/lost_items/" .
            $item['image'];

    } else {

        $image_path =
            "../uploads/found_items/" .
            $item['image'];

    }

} else {

    $image_path =
        "../assets/images/default-item.png";

}


/* =========================================================
   SMART MATCHING
   ONLY OWNER OF LOST ITEM CAN SEE THIS
========================================================= */

$possible_matches = [];


if (
    $item_type === "Lost" &&
    $item['status'] === "Open" &&
    $is_owner
) {


    /* ===========================
       GET AVAILABLE FOUND ITEMS
    =========================== */

    $sql = "
        SELECT
            f.*,
            u.name AS university_name
        FROM found_items f
        LEFT JOIN universities u
            ON f.university_ref_id = u.id
        WHERE
            f.university_ref_id = ?
            AND f.status = 'Available'
    ";


    $stmt = mysqli_prepare($conn, $sql);


    if ($stmt) {

        mysqli_stmt_bind_param(
            $stmt,
            "i",
            $university_id
        );

        mysqli_stmt_execute($stmt);

        $result =
            mysqli_stmt_get_result($stmt);


        /* ===========================
           LOST ITEM DATA
        =========================== */

        $lost_name =
            strtolower(
                trim($item['item_name'])
            );

        $lost_category =
            strtolower(
                trim($item['category'])
            );

        $lost_location =
            strtolower(
                trim($item['location'])
            );


        /* ===========================
           COMMON WORDS
        =========================== */

        $stop_words = [

            'the',
            'a',
            'an',
            'my',
            'this',
            'that',
            'item',
            'lost',
            'found'

        ];


        /* ===========================
           CHECK FOUND ITEMS
        =========================== */

        while ($found = mysqli_fetch_assoc($result)) {

            $score = 0;


            /* ===========================
               CATEGORY MATCH
            =========================== */

            $found_category =
                strtolower(
                    trim($found['category'])
                );


            if (
                $lost_category !== "" &&
                $found_category !== "" &&
                $lost_category === $found_category
            ) {

                $score += 30;

            }


            /* ===========================
               LOCATION MATCH
            =========================== */

            $found_location =
                strtolower(
                    trim($found['location'])
                );


            if (
                $lost_location !== "" &&
                $found_location !== "" &&
                $lost_location === $found_location
            ) {

                $score += 20;

            }


            /* ===========================
               ITEM NAME MATCH
            =========================== */

            $found_name =
                strtolower(
                    trim($found['item_name'])
                );


            $lost_words =
                preg_split(
                    '/\s+/',
                    $lost_name
                );


            $found_words =
                preg_split(
                    '/\s+/',
                    $found_name
                );


            $lost_words =
                array_filter(
                    $lost_words,
                    function ($word) use ($stop_words) {

                        return
                            $word !== "" &&
                            !in_array(
                                $word,
                                $stop_words
                            );

                    }
                );


            $found_words =
                array_filter(
                    $found_words,
                    function ($word) use ($stop_words) {

                        return
                            $word !== "" &&
                            !in_array(
                                $word,
                                $stop_words
                            );

                    }
                );


            $name_match_count = 0;


            foreach ($lost_words as $lost_word) {

                foreach ($found_words as $found_word) {

                    if (
                        $lost_word === $found_word ||
                        strpos(
                            $lost_word,
                            $found_word
                        ) !== false ||
                        strpos(
                            $found_word,
                            $lost_word
                        ) !== false
                    ) {

                        $name_match_count++;

                        break;

                    }

                }

            }


            $total_lost_words =
                count($lost_words);


            if ($total_lost_words > 0) {

                $name_percentage =
                    (
                        $name_match_count /
                        $total_lost_words
                    ) * 50;

                $score += round(
                    $name_percentage
                );

            }


            /* ===========================
               ONLY GOOD MATCHES
            =========================== */

            if ($score >= 60) {

                $found['match_score'] =
                    $score;

                $possible_matches[] =
                    $found;

            }

        }


        mysqli_stmt_close($stmt);

    }


    /* ===========================
       BEST MATCH FIRST
    =========================== */

    usort(
        $possible_matches,
        function ($a, $b) {

            return
                $b['match_score']
                <=>
                $a['match_score'];

        }
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

<title>

<?php

echo htmlspecialchars(
    $item['item_name']
);

?>

| FindIt

</title>


<link
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet">


<link
rel="stylesheet"
href="../assets/css/style.css">


<link
rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">


<style>

body {

    background: #f8fafc;

}


.details-section {

    padding: 130px 20px 70px;

}


.details-card {

    max-width: 950px;

    margin: auto;

    background: white;

    border-radius: 24px;

    overflow: hidden;

    box-shadow:
        0 15px 45px rgba(0,0,0,.10);

}


.details-image {

    width: 100%;

    height: 430px;

    object-fit: cover;

    background: #f1f5f9;

}


.details-body {

    padding: 35px;

}


.details-title {

    font-size: 34px;

    font-weight: 700;

    color: #0f172a;

    margin-bottom: 20px;

}


.type-badge {

    display: inline-block;

    padding: 8px 16px;

    border-radius: 30px;

    font-size: 13px;

    font-weight: 700;

    margin-bottom: 15px;

}


.type-lost {

    background: #fee2e2;

    color: #dc2626;

}


.type-found {

    background: #dcfce7;

    color: #16a34a;

}


.details-row {

    display: flex;

    align-items: center;

    gap: 12px;

    margin-bottom: 15px;

    color: #64748b;

}


.details-row i {

    width: 22px;

    color: #2563eb;

}


.details-row strong {

    color: #334155;

}


.details-description {

    margin-top: 28px;

    padding: 22px;

    background: #f8fafc;

    border-radius: 16px;

}


.details-description h5 {

    font-weight: 700;

    color: #0f172a;

    margin-bottom: 12px;

}


.details-description p {

    color: #64748b;

    line-height: 1.7;

    margin-bottom: 0;

}


.back-btn {

    border-radius: 30px;

    padding: 10px 22px;

    font-weight: 600;

}


/* ===========================
   SMART MATCHING
=========================== */

.match-section {

    margin-top: 35px;

    padding: 25px;

    background: #f8fafc;

    border-radius: 18px;

    border: 1px solid #e2e8f0;

}


.match-header {

    display: flex;

    align-items: center;

    gap: 10px;

    margin-bottom: 20px;

}


.match-header h4 {

    margin: 0;

    font-weight: 700;

    color: #0f172a;

}


.match-subtitle {

    color: #64748b;

    margin-bottom: 20px;

}


.match-card {

    background: white;

    border-radius: 16px;

    padding: 18px;

    margin-bottom: 15px;

    border: 1px solid #e2e8f0;

    transition: .2s;

}


.match-card:hover {

    transform: translateY(-2px);

    box-shadow:
        0 8px 25px rgba(0,0,0,.08);

}


.match-image {

    width: 120px;

    height: 100px;

    object-fit: cover;

    border-radius: 12px;

    background: #f1f5f9;

}


.match-name {

    font-size: 18px;

    font-weight: 700;

    color: #0f172a;

}


.match-score {

    display: inline-block;

    background: #dcfce7;

    color: #15803d;

    border-radius: 20px;

    padding: 5px 11px;

    font-size: 13px;

    font-weight: 700;

}


.match-info {

    color: #64748b;

    font-size: 14px;

    margin-bottom: 5px;

}


.no-match {

    text-align: center;

    padding: 20px;

    color: #64748b;

}


/* ===========================
   MARK AS FOUND
=========================== */

.found-box {

    margin-top: 30px;

    padding: 22px;

    background: #ecfdf5;

    border: 1px solid #bbf7d0;

    border-radius: 18px;

}


@media(max-width:768px) {

    .details-section {

        padding: 110px 15px 50px;

    }


    .details-image {

        height: 280px;

    }


    .details-body {

        padding: 25px;

    }


    .details-title {

        font-size: 28px;

    }


    .match-image {

        width: 100%;

        height: 180px;

        margin-bottom: 15px;

    }

}

</style>

</head>


<body>

<?php include "../includes/student_navbar.php"; ?>


<section class="details-section">

<div class="container">

<div class="details-card">


<!-- IMAGE -->

<img

src="<?php echo htmlspecialchars($image_path); ?>"

class="details-image"

onerror="this.src='../assets/images/default-item.png';"

>


<div class="details-body">


<!-- TYPE -->

<?php if ($item_type === "Lost") { ?>

<span class="type-badge type-lost">

<i class="fa-solid fa-circle-exclamation"></i>

LOST ITEM

</span>

<?php } else { ?>

<span class="type-badge type-found">

<i class="fa-solid fa-hand-holding-heart"></i>

FOUND ITEM

</span>

<?php } ?>


<!-- ITEM NAME -->

<h1 class="details-title">

<?php

echo htmlspecialchars(
    $item['item_name']
);

?>

</h1>

<hr>


<!-- CATEGORY -->

<div class="details-row">

<i class="fa-solid fa-layer-group"></i>

<strong>Category:</strong>

<span>

<?php

echo htmlspecialchars(
    $item['category']
);

?>

</span>

</div>


<!-- LOCATION -->

<div class="details-row">

<i class="fa-solid fa-location-dot"></i>

<strong>Location:</strong>

<span>

<?php

echo htmlspecialchars(
    $item['location']
);

?>

</span>

</div>


<!-- DATE -->

<div class="details-row">

<i class="fa-solid fa-calendar"></i>

<strong>

<?php

echo $item_type === "Lost"
    ? "Lost Date:"
    : "Found Date:";

?>

</strong>

<span>

<?php

echo htmlspecialchars($date);

?>

</span>

</div>


<!-- UNIVERSITY -->

<div class="details-row">

<i class="fa-solid fa-building-columns"></i>

<strong>University:</strong>

<span>

<?php

echo htmlspecialchars(
    $item['university_name']
);

?>

</span>

</div>


<!-- STATUS -->

<div class="details-row">

<i class="fa-solid fa-circle-info"></i>

<strong>Status:</strong>

<span>

<?php

echo htmlspecialchars(
    $item['status']
);

?>

</span>

</div>


<!-- DESCRIPTION -->

<div class="details-description">

<h5>

<i class="fa-solid fa-file-lines"></i>

Description

</h5>

<p>

<?php

echo nl2br(
    htmlspecialchars(
        $item['description']
    )
);

?>

</p>

</div>


<!-- ==================================================
     SMART MATCHING
================================================== -->

<?php if (
    $item_type === "Lost" &&
    $item['status'] === "Open" &&
    $is_owner
): ?>

<div class="match-section">

<div class="match-header">

<i
class="fa-solid fa-wand-magic-sparkles text-primary">
</i>

<h4>

Possible Matches

</h4>

</div>


<p class="match-subtitle">

FindIt checks available found items for
similarity with your lost item.

</p>


<?php if (count($possible_matches) > 0): ?>


<?php foreach ($possible_matches as $match): ?>


<?php

if (
    !empty($match['image']) &&
    $match['image'] !== "default-item.png"
) {

    $match_image =
        "../uploads/found_items/"
        . $match['image'];

} else {

    $match_image =
        "../assets/images/default-item.png";

}

?>


<div class="match-card">

<div class="row align-items-center g-3">


<!-- IMAGE -->

<div class="col-md-3">

<img

src="<?= htmlspecialchars($match_image) ?>"

class="match-image"

onerror="this.src='../assets/images/default-item.png';"

alt="Found item"

>

</div>


<!-- INFORMATION -->

<div class="col-md-6">

<div class="match-name">

<?= htmlspecialchars(
    $match['item_name']
) ?>

</div>


<span class="match-score">

<?= (int)$match['match_score'] ?>%

Match

</span>


<div class="match-info mt-2">

<i class="fa-solid fa-layer-group"></i>

<?= htmlspecialchars(
    $match['category']
) ?>

</div>


<div class="match-info">

<i class="fa-solid fa-location-dot"></i>

<?= htmlspecialchars(
    $match['location']
) ?>

</div>


<div class="match-info">

<i class="fa-solid fa-calendar"></i>

<?= htmlspecialchars(
    $match['found_date']
) ?>

</div>

</div>


<!-- BUTTON -->

<div class="col-md-3 text-md-end">

<a

href="item_details.php?id=<?= (int)$match['id'] ?>&type=Found"

class="btn btn-outline-success back-btn"

>

<i class="fa-solid fa-eye me-1"></i>

View Found Item

</a>

</div>


</div>

</div>


<?php endforeach; ?>


<?php else: ?>


<div class="no-match">

<i
class="fa-solid fa-magnifying-glass mb-2"
style="font-size:30px;">
</i>

<p class="mb-0">

No strong matches found yet.

</p>

<small>

FindIt will compare this item with
available found items.

</small>

</div>


<?php endif; ?>


</div>

<?php endif; ?>


<!-- ==================================================
     MARK AS FOUND
     ONLY OWNER OF LOST ITEM
================================================== -->

<?php if (
    $item_type === "Lost" &&
    ($item['status'] === "Open" ||
     $item['status'] === "Matched") &&
    $is_owner
): ?>

<div class="found-box">

<h5 class="fw-bold">

<i
class="fa-solid fa-circle-check text-success me-1">
</i>

Have you found your item?

</h5>


<p class="text-muted mb-3">

If you have recovered this item, mark it as found.

</p>


<form

method="POST"

>

<input

type="hidden"

name="item_id"

value="<?= $item_id ?>"

>


<button

type="submit"

name="mark_found"

value="1"

class="btn btn-success back-btn"

onclick="return confirm('Mark this lost item as found?');"

>

<i class="fa-solid fa-check me-1"></i>

Mark as Found

</button>

</form>

</div>

<?php endif; ?>


<!-- ===========================
     ACTION BUTTONS
=========================== -->

<div class="mt-4 d-flex gap-2 flex-wrap">


<?php

/* LOST ITEM */

if (
    $item_type === "Lost" &&
    $item['status'] === "Open" &&
    !$is_owner
) {

?>

<a

href="found_item.php?id=<?= $item_id ?>&type=Lost"

class="btn btn-danger back-btn"

>

<i class="fa-solid fa-hand-holding-heart me-1"></i>

I Found This Item

</a>

<?php } ?>


<?php

/* FOUND ITEM */

if (
    $item_type === "Found" &&
    $item['status'] === "Available" &&
    !$is_owner
) {

?>

<a

href="claim_item.php?id=<?= $item_id ?>&type=Found"

class="btn btn-success back-btn"

>

<i class="fa-solid fa-hand-holding-heart me-1"></i>

Claim This Item

</a>

<?php } ?>


<a

href="search.php"

class="btn btn-outline-primary back-btn"

>

<i class="fa-solid fa-arrow-left me-1"></i>

Back to Search

</a>


</div>


</div>

</div>

</div>

</section>


</body>

</html>
