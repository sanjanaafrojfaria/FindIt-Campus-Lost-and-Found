<?php

session_start();

include "config/database.php";


/* ===========================
   CHECK LOGIN
=========================== */

if (!isset($_SESSION['user_id'])) {

    header("Location: login.php");
    exit();

}


/* ===========================
   CHECK REQUEST
=========================== */

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    header("Location: student/search.php");
    exit();

}


$user_id = $_SESSION['user_id'];


/* ===========================
   GET FORM DATA
=========================== */

$item_id = isset($_POST['item_id'])
    ? (int) $_POST['item_id']
    : 0;

$item_type = $_POST['item_type'] ?? '';

$claim_reason = trim(
    $_POST['claim_reason'] ?? ''
);


/* ===========================
   VALIDATION
=========================== */

if (
    $item_id <= 0 ||
    $item_type !== "Found" ||
    empty($claim_reason)
) {

    header("Location: message.php?action=claim_invalid");
    exit();

}


/* ===========================
   GET USER UNIVERSITY
=========================== */

$sql = "
    SELECT university_ref_id
    FROM users
    WHERE id = ?
";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {

    die("Database error: " . mysqli_error($conn));

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


if (
    !$user ||
    empty($user['university_ref_id'])
) {

    header("Location: message.php?action=invalid_university");
    exit();

}

$university_id = $user['university_ref_id'];


/* ===========================
   GET FOUND ITEM
=========================== */

$sql = "
    SELECT
        id,
        user_id,
        item_name,
        status,
        university_ref_id
    FROM found_items
    WHERE
        id = ?
        AND university_ref_id = ?
";

$stmt = mysqli_prepare($conn, $sql);

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


if (!$item) {

    header("Location: message.php?action=item_not_found");
    exit();

}


/* ===========================
   OWNER CANNOT CLAIM
=========================== */

if (
    (int)$item['user_id'] ===
    (int)$user_id
) {

    header("Location: message.php?action=own_item");
    exit();

}


/* ===========================
   ITEM MUST BE AVAILABLE
=========================== */

if ($item['status'] !== "Available") {

    header("Location: message.php?action=item_unavailable");
    exit();

}


/* ===========================
   CHECK EXISTING ACTIVE CLAIM
=========================== */

$sql = "
    SELECT id
    FROM claims
    WHERE
        item_id = ?
        AND item_type = 'Found'
        AND user_id = ?
        AND status IN ('Pending', 'Approved')
";

$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "ii",
    $item_id,
    $user_id
);

mysqli_stmt_execute($stmt);

mysqli_stmt_store_result($stmt);

if (mysqli_stmt_num_rows($stmt) > 0) {

    mysqli_stmt_close($stmt);

    header("Location: message.php?action=claim_exists");
    exit();

}

mysqli_stmt_close($stmt);


/* ===========================
   PROOF IMAGE
=========================== */

$proof_image = null;


if (
    isset($_FILES['proof_image']) &&
    $_FILES['proof_image']['error'] === UPLOAD_ERR_OK
) {

    $file_name =
        $_FILES['proof_image']['name'];

    $file_tmp =
        $_FILES['proof_image']['tmp_name'];

    $file_size =
        $_FILES['proof_image']['size'];

    $file_ext =
        strtolower(
            pathinfo(
                $file_name,
                PATHINFO_EXTENSION
            )
        );


    $allowed_extensions = [
        "jpg",
        "jpeg",
        "png"
    ];


    if (
        !in_array(
            $file_ext,
            $allowed_extensions
        )
    ) {

        header(
            "Location: message.php?action=invalid_proof"
        );

        exit();

    }


    if (
        $file_size >
        5 * 1024 * 1024
    ) {

        header(
            "Location: message.php?action=proof_too_large"
        );

        exit();

    }


    $new_file_name =
        time() .
        "_" .
        uniqid() .
        "." .
        $file_ext;


    $upload_dir =
        "uploads/claim_proofs/";


    if (!is_dir($upload_dir)) {

        mkdir(
            $upload_dir,
            0755,
            true
        );

    }


    $destination =
        $upload_dir .
        $new_file_name;


    if (
        !move_uploaded_file(
            $file_tmp,
            $destination
        )
    ) {

        header(
            "Location: message.php?action=proof_upload_failed"
        );

        exit();

    }


    $proof_image =
        $new_file_name;

}


/* ===========================
   INSERT CLAIM
=========================== */

$sql = "
    INSERT INTO claims
    (
        item_id,
        item_type,
        user_id,
        claim_reason,
        proof_image,
        status
    )
    VALUES
    (?, ?, ?, ?, ?, 'Pending')
";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {

    die(
        "Claim prepare error: " .
        mysqli_error($conn)
    );

}

mysqli_stmt_bind_param(
    $stmt,
    "isiss",
    $item_id,
    $item_type,
    $user_id,
    $claim_reason,
    $proof_image
);


if (!mysqli_stmt_execute($stmt)) {

    mysqli_stmt_close($stmt);

    header(
        "Location: message.php?action=claim_failed"
    );

    exit();

}


/* ===========================
   GET NEW CLAIM ID
=========================== */

$claim_id =
    mysqli_insert_id($conn);

mysqli_stmt_close($stmt);


/* ===========================
   CREATE REPORTER NOTIFICATION
=========================== */

$reporter_id =
    (int)$item['user_id'];

$message =
    'Someone has submitted a claim for your found item "' .
    $item['item_name'] .
    '".';


$sql = "
    INSERT INTO notifications
    (
        user_id,
        claim_id,
        message,
        is_read
    )
    VALUES
    (?, ?, ?, 0)
";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {

    die(
        "Notification prepare error: " .
        mysqli_error($conn)
    );

}

mysqli_stmt_bind_param(
    $stmt,
    "iis",
    $reporter_id,
    $claim_id,
    $message
);


if (!mysqli_stmt_execute($stmt)) {

    mysqli_stmt_close($stmt);

    header(
        "Location: message.php?action=claim_failed"
    );

    exit();

}

mysqli_stmt_close($stmt);


/* ===========================
   SUCCESS
=========================== */

header(
    "Location: message.php?action=claim_success"
);

exit();

?>