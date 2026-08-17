
<?php

session_start();

if (!isset($_SESSION['user_id'])) {

    header("Location: ../login.php");
    exit();

}

include "../config/database.php";

$user_id = $_SESSION['user_id'];


/* ===========================
   GET USER'S UNIVERSITY
=========================== */

$sql = "SELECT u.name AS university_name,
               u.id AS university_id
        FROM users us
        LEFT JOIN universities u
        ON us.university_ref_id = u.id
        WHERE us.id = ?";

$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $user_id
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$user = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);


if (!$user || empty($user['university_id'])) {

    header("Location: ../message.php?action=invalid_university");
    exit();

}

$university_id = $user['university_id'];

$university_name = $user['university_name'];


/* ===========================
   GET SEARCH FILTERS
=========================== */

$search = trim($_GET['search'] ?? '');

$type = $_GET['type'] ?? 'All';

$category = $_GET['category'] ?? 'All';

$location = $_GET['location'] ?? 'All';


/* ===========================
   BUILD QUERY
=========================== */

$conditions = [];

$params = [];

$types = "";


/* UNIVERSITY FILTER */

$conditions[] = "university_ref_id = ?";

$params[] = $university_id;

$types .= "i";


/* SEARCH */

if ($search !== '') {

    $conditions[] = "item_name LIKE ?";

    $params[] = "%" . $search . "%";

    $types .= "s";

}


/* CATEGORY */

if ($category !== '' && $category !== 'All') {

    $conditions[] = "category = ?";

    $params[] = $category;

    $types .= "s";

}


/* LOCATION */

if ($location !== '' && $location !== 'All') {

    $conditions[] = "location = ?";

    $params[] = $location;

    $types .= "s";

}


/* ===========================
   GET LOST ITEMS
=========================== */

$lostItems = [];

if ($type === 'All' || $type === 'Lost') {

    $lostSql = "
        SELECT
            id,
            item_name,
            category,
            location,
            lost_date AS report_date,
            description,
            image,
            status,
            'Lost' AS item_type
        FROM lost_items
        WHERE " . implode(" AND ", $conditions) . "
        ORDER BY created_at DESC
    ";

    $stmt = mysqli_prepare($conn, $lostSql);

    if (!empty($params)) {

        mysqli_stmt_bind_param(
            $stmt,
            $types,
            ...$params
        );

    }

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    while ($row = mysqli_fetch_assoc($result)) {

        $lostItems[] = $row;

    }

    mysqli_stmt_close($stmt);

}


/* ===========================
   GET FOUND ITEMS
=========================== */

$foundItems = [];

if ($type === 'All' || $type === 'Found') {

    $foundSql = "
        SELECT
            id,
            item_name,
            category,
            location,
            found_date AS report_date,
            description,
            image,
            status,
            'Found' AS item_type
        FROM found_items
        WHERE " . implode(" AND ", $conditions) . "
        ORDER BY created_at DESC
    ";

    $stmt = mysqli_prepare($conn, $foundSql);

    if (!empty($params)) {

        mysqli_stmt_bind_param(
            $stmt,
            $types,
            ...$params
        );

    }

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    while ($row = mysqli_fetch_assoc($result)) {

        $foundItems[] = $row;

    }

    mysqli_stmt_close($stmt);

}


/* ===========================
   COMBINE RESULTS
=========================== */

$items = array_merge(
    $lostItems,
    $foundItems
);


/* SORT NEWEST FIRST */

usort(
    $items,
    function ($a, $b) {

        return strtotime($b['report_date'])
             - strtotime($a['report_date']);

    }
);

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<title>Find Items | FindIt</title>

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

.search-header {

    padding: 120px 20px 60px;

    text-align: center;

    color: white;

    background:
    linear-gradient(
        rgba(15,23,42,.55),
        rgba(15,23,42,.55)
    ),
    url("../assets/images/register-bg.jpg");

    background-size: cover;

    background-position: center;

}


.search-header h1 {

    font-size: 42px;

    font-weight: 700;

}


.search-header p {

    margin-top: 10px;

    font-size: 17px;

}


.university-name {

    display: inline-flex;

    align-items: center;

    gap: 8px;

    margin-top: 15px;

    padding: 8px 18px;

    border-radius: 30px;

    background: rgba(255,255,255,.18);

    border: 1px solid rgba(255,255,255,.3);

    font-weight: 600;

}


.search-area {

    margin-top: -35px;

    position: relative;

    z-index: 2;

}


.search-card {

    background: white;

    border-radius: 22px;

    padding: 28px;

    box-shadow: 0 15px 45px rgba(0,0,0,.10);

}


.search-card label {

    font-weight: 600;

    margin-bottom: 7px;

}


.search-card .form-control,
.search-card .form-select {

    border-radius: 12px;

    padding: 11px 14px;

}


.search-btn {

    border-radius: 12px;

    font-weight: 600;

    padding: 11px 24px;

}


.results-title {

    margin: 45px 0 25px;

    font-size: 28px;

    font-weight: 700;

}


.item-card {

    background: white;

    border-radius: 20px;

    overflow: hidden;

    box-shadow: 0 10px 35px rgba(0,0,0,.08);

    height: 100%;

    transition: .3s;

}


.item-card:hover {

    transform: translateY(-6px);

    box-shadow: 0 18px 45px rgba(0,0,0,.13);

}


.item-image {

    width: 100%;

    height: 210px;

    object-fit: cover;

    background: #f8fafc;

}


.item-body {

    padding: 20px;

}


.item-body h5 {

    font-weight: 700;

    color: #0f172a;

    margin-bottom: 12px;

}


.item-body p {

    color: #64748b;

    margin-bottom: 8px;

}


.item-body p i {

    width: 20px;

    color: #2563eb;

}


.type-badge {

    display: inline-block;

    padding: 6px 12px;

    border-radius: 30px;

    font-size: 12px;

    font-weight: 700;

    margin-bottom: 12px;

}


.type-lost {

    background: #fee2e2;

    color: #dc2626;

}


.type-found {

    background: #dcfce7;

    color: #16a34a;

}


.status-badge {

    padding: 6px 12px;

    border-radius: 30px;

    font-size: 12px;

    font-weight: 600;

}


.view-btn {

    width: 100%;

    border-radius: 30px;

    margin-top: 12px;

    font-weight: 600;

}


.empty-results {

    text-align: center;

    padding: 70px 20px;

    background: white;

    border-radius: 20px;

    box-shadow: 0 10px 35px rgba(0,0,0,.07);

}


.empty-results i {

    font-size: 55px;

    color: #94a3b8;

    margin-bottom: 20px;

}


.empty-results h4 {

    font-weight: 700;

}


.empty-results p {

    color: #64748b;

}


@media(max-width:768px) {

    .search-header {

        padding: 100px 20px 55px;

    }

    .search-header h1 {

        font-size: 32px;

    }

}

</style>

</head>


<body>


<?php include "../includes/student_navbar.php"; ?>


<!-- ===========================
     HEADER
=========================== -->

<section class="search-header">

    <h1>

        <i class="fa-solid fa-magnifying-glass"></i>

        Find an Item

    </h1>

    <p>

        Search lost and found items from your university.

    </p>

    <div class="university-name">

        <i class="fa-solid fa-building-columns"></i>

        <?php echo htmlspecialchars($university_name); ?>

    </div>

</section>


<!-- ===========================
     SEARCH AREA
=========================== -->

<div class="container search-area">

    <div class="search-card">

        <form method="GET"
              action="search.php">

            <div class="row g-3 align-items-end">


                <!-- SEARCH -->

                <div class="col-lg-4">

                    <label>

                        Search Item

                    </label>

                    <input
                        type="text"
                        name="search"
                        class="form-control"
                        placeholder="e.g. Black wallet"
                        value="<?php echo htmlspecialchars($search); ?>">

                </div>


                <!-- TYPE -->

                <div class="col-lg-2 col-md-4">

                    <label>

                        Type

                    </label>

                    <select
                        name="type"
                        class="form-select">

                        <option value="All"
                            <?php if($type == 'All') echo 'selected'; ?>>

                            All

                        </option>

                        <option value="Lost"
                            <?php if($type == 'Lost') echo 'selected'; ?>>

                            Lost

                        </option>

                        <option value="Found"
                            <?php if($type == 'Found') echo 'selected'; ?>>

                            Found

                        </option>

                    </select>

                </div>


                <!-- CATEGORY -->

                <div class="col-lg-2 col-md-4">

                    <label>

                        Category

                    </label>

                    <select
                        name="category"
                        class="form-select">

                        <option value="All">All</option>

                        <option value="Electronics">Electronics</option>

                        <option value="Wallet">Wallet</option>

                        <option value="ID Card">ID Card</option>

                        <option value="Keys">Keys</option>

                        <option value="Books">Books</option>

                        <option value="Bag">Bag</option>

                        <option value="Note">Note</option>

                        <option value="Jewelry">Jewelry</option>

                        <option value="Other">Other</option>

                    </select>

                </div>


                <!-- LOCATION -->

                <div class="col-lg-2 col-md-4">

                    <label>

                        Location

                    </label>

                    <select
                        name="location"
                        class="form-select">

                        <option value="All">All</option>

                        <option>Cafeteria</option>

                        <option>Library</option>

                        <option>Main Building</option>

                        <option>Textile Building</option>

                        <option>Female Prayer Room</option>

                        <option>Male Common Room</option>


                        <option>Female Common Room</option>

                    </select>

                </div>


                <!-- BUTTON -->

                <div class="col-lg-2">

                    <button
                        type="submit"
                        class="btn btn-primary search-btn w-100">

                        <i class="fa-solid fa-magnifying-glass"></i>

                        Search

                    </button>

                </div>

            </div>

        </form>

    </div>


    <!-- ===========================
         RESULTS
    =========================== -->

    <h2 class="results-title">

        Search Results

        <small class="text-muted fs-6">

            (<?php echo count($items); ?> items)

        </small>

    </h2>


    <?php if (count($items) > 0) { ?>

        <div class="row g-4 pb-5">


            <?php foreach ($items as $item) { ?>

                <div class="col-lg-4 col-md-6">

                    <div class="item-card">


                        <?php

                        if (
                            $item['image'] &&
                            $item['image'] != 'default-item.png'
                        ) {

                            if ($item['item_type'] == 'Lost') {

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

                        ?>


                        <img
                            src="<?php echo htmlspecialchars($image_path); ?>"
                            class="item-image"
                            onerror="this.src='../assets/images/default-item.png';">


                        <div class="item-body">


                            <?php if ($item['item_type'] == 'Lost') { ?>

                                <span class="type-badge type-lost">

                                    <i class="fa-solid fa-circle-exclamation"></i>

                                    LOST

                                </span>

                            <?php } else { ?>

                                <span class="type-badge type-found">

                                    <i class="fa-solid fa-hand-holding-heart"></i>

                                    FOUND

                                </span>

                            <?php } ?>


                            <h5>

                                <?php
                                echo htmlspecialchars(
                                    $item['item_name']
                                );
                                ?>

                            </h5>


                            <p>

                                <i class="fa-solid fa-layer-group"></i>

                                <?php
                                echo htmlspecialchars(
                                    $item['category']
                                );
                                ?>

                            </p>


                            <p>

                                <i class="fa-solid fa-location-dot"></i>

                                <?php
                                echo htmlspecialchars(
                                    $item['location']
                                );
                                ?>

                            </p>


                            <p>

                                <i class="fa-solid fa-calendar"></i>

                                <?php
                                echo htmlspecialchars(
                                    $item['report_date']
                                );
                                ?>

                            </p>


                            <span class="status-badge
                                <?php
                                echo $item['status'] == 'Available'
                                    ? 'bg-primary text-white'
                                    : 'bg-secondary text-white';
                                ?>">

                                <?php
                                echo htmlspecialchars(
                                    $item['status']
                                );
                                ?>

                            </span>


                           <a
    href="item_details.php?id=<?php echo $item['id']; ?>&type=<?php echo $item['item_type']; ?>"
    class="btn btn-outline-primary view-btn">

    <i class="fa-solid fa-eye"></i>

    View Details

</a>

                        </div>

                    </div>

                </div>

            <?php } ?>


        </div>


    <?php } else { ?>


        <div class="empty-results mb-5">

            <i class="fa-solid fa-box-open"></i>

            <h4>

                No Items Found

            </h4>

            <p>

                No lost or found reports matched your search.

            </p>

        </div>


    <?php } ?>


</div>


</body>

</html>

