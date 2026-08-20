<?php

session_start();

if (!isset($_SESSION['user_id'])) {

    header("Location: login.php");
    exit();

}

include "config/database.php";

$user_id = (int) $_SESSION['user_id'];


/* ===========================
   CHECK REQUEST
=========================== */

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    header("Location: index.php");
    exit();

}


/* ===========================
   GET FORM DATA
=========================== */

$lost_item_id = isset($_POST['lost_item_id'])
    ? (int) $_POST['lost_item_id']
    : 0;

$contact_number = trim($_POST['contact_number'] ?? '');
$found_location = trim($_POST['found_location'] ?? '');
$found_date = trim($_POST['found_date'] ?? '');
$found_description = trim($_POST['found_description'] ?? '');


/* ===========================
   VALIDATE REQUIRED DATA
=========================== */

if (
    $lost_item_id <= 0 ||
    empty($contact_number) ||
    empty($found_location) ||
    empty($found_date) ||
    empty($found_description)
) {

    header(
        "Location: student/found_item.php?id="
        . $lost_item_id
        . "&type=Lost&error=missing"
    );

    exit();

}


/* ===========================
   VALIDATE CONTACT NUMBER
=========================== */

if (!preg_match('/^[0-9+\-\s]{7,20}$/', $contact_number)) {

    header(
        "Location: student/found_item.php?id="
        . $lost_item_id
        . "&type=Lost&error=phone"
    );

    exit();

}


/* ===========================
   GET LOST ITEM
=========================== */

$sql = "SELECT
            id,
            user_id,
            university_ref_id,
            item_name,
            status
        FROM lost_items
        WHERE id = ?";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    die("Database error.");
}

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $lost_item_id
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$item = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);


/* ===========================
   ITEM NOT FOUND
=========================== */

if (!$item) {

    header("Location: student/search.php");
    exit();

}


/* ===========================
   PREVENT OWNER FROM RESPONDING
=========================== */

if ((int) $item['user_id'] === $user_id) {

    header(
        "Location: student/item_details.php?id="
        . $lost_item_id
        . "&type=Lost"
    );

    exit();

}


/* ===========================
   CHECK ITEM STATUS
=========================== */

if (
    isset($item['status']) &&
    $item['status'] !== 'Open'
) {

    header(
        "Location: student/item_details.php?id="
        . $lost_item_id
        . "&type=Lost&error=closed"
    );

    exit();

}


/* ===========================
   GET USER UNIVERSITY
=========================== */

$sql = "SELECT university_ref_id
        FROM users
        WHERE id = ?";

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

    header("Location: message.php?action=invalid_university");
    exit();

}


/* ===========================
   UNIVERSITY CHECK
=========================== */

if (
    (int) $user['university_ref_id']
    !==
    (int) $item['university_ref_id']
) {

    header(
        "Location: student/item_details.php?id="
        . $lost_item_id
        . "&type=Lost"
    );

    exit();

}


/* ===========================
   CHECK EXISTING RESPONSE
=========================== */

$sql = "SELECT id
        FROM found_responses
        WHERE lost_item_id = ?
        AND finder_id = ?";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    die("Database error.");
}

mysqli_stmt_bind_param(
    $stmt,
    "ii",
    $lost_item_id,
    $user_id
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$existing_response = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);


if ($existing_response) {

    header(
        "Location: student/item_details.php?id="
        . $lost_item_id
        . "&type=Lost&already=1"
    );

    exit();

}


/* ===========================
   HANDLE PROOF IMAGE
=========================== */

$proof_image = NULL;

if (
    isset($_FILES['proof_image']) &&
    $_FILES['proof_image']['error'] !== UPLOAD_ERR_NO_FILE
) {

    if ($_FILES['proof_image']['error'] !== UPLOAD_ERR_OK) {

        header(
            "Location: student/found_item.php?id="
            . $lost_item_id
            . "&type=Lost&error=image"
        );

        exit();

    }


    /* Allowed extensions */

    $allowed_extensions = [
        'jpg',
        'jpeg',
        'png'
    ];

    $file_name = $_FILES['proof_image']['name'];

    $file_extension = strtolower(
        pathinfo($file_name, PATHINFO_EXTENSION)
    );


    if (!in_array(
        $file_extension,
        $allowed_extensions
    )) {

        header(
            "Location: student/found_item.php?id="
            . $lost_item_id
            . "&type=Lost&error=image_type"
        );

        exit();

    }


    /* Maximum size: 5 MB */

    if (
        $_FILES['proof_image']['size']
        >
        5 * 1024 * 1024
    ) {

        header(
            "Location: student/found_item.php?id="
            . $lost_item_id
            . "&type=Lost&error=image_size"
        );

        exit();

    }


    /* ===========================
       CREATE UPLOAD FOLDER
    =========================== */

    $upload_directory =
        __DIR__
        . "/uploads/found_responses/";


    if (!is_dir($upload_directory)) {

        mkdir(
            $upload_directory,
            0777,
            true
        );

    }


    /* ===========================
       GENERATE UNIQUE FILENAME
    =========================== */

    $proof_image =
        time()
        . "_"
        . bin2hex(random_bytes(8))
        . "."
        . $file_extension;


    $upload_path =
        $upload_directory
        . $proof_image;


    if (!move_uploaded_file(
        $_FILES['proof_image']['tmp_name'],
        $upload_path
    )) {

        header(
            "Location: student/found_item.php?id="
            . $lost_item_id
            . "&type=Lost&error=image_upload"
        );

        exit();

    }

}


/* ===========================
   INSERT FOUND RESPONSE
=========================== */

$sql = "INSERT INTO found_responses
        (
            lost_item_id,
            finder_id,
            contact_number,
            found_location,
            found_date,
            found_description,
            proof_image,
            status
        )
        VALUES (?, ?, ?, ?, ?, ?, ?, 'Pending')";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {

    if (
        $proof_image !== NULL &&
        file_exists(
            __DIR__
            . "/uploads/found_responses/"
            . $proof_image
        )
    ) {

        unlink(
            __DIR__
            . "/uploads/found_responses/"
            . $proof_image
        );

    }

    die("Database error.");
}


mysqli_stmt_bind_param(
    $stmt,
    "iisssss",
    $lost_item_id,
    $user_id,
    $contact_number,
    $found_location,
    $found_date,
    $found_description,
    $proof_image
);


if (!mysqli_stmt_execute($stmt)) {

    mysqli_stmt_close($stmt);


    /* Remove uploaded image if insertion failed */

    if (
        $proof_image !== NULL &&
        file_exists(
            __DIR__
            . "/uploads/found_responses/"
            . $proof_image
        )
    ) {

        unlink(
            __DIR__
            . "/uploads/found_responses/"
            . $proof_image
        );

    }

    die("Something went wrong while submitting your response.");

}


$response_id = mysqli_insert_id($conn);

mysqli_stmt_close($stmt);


/* ===========================
   GET LOST ITEM OWNER
=========================== */

$owner_id = (int) $item['user_id'];


/* ===========================
   CREATE NOTIFICATION
=========================== */

$message =
    "Someone found your lost item \""
    . $item['item_name']
    . "\" and submitted a response.";


/* ===========================
   INSERT NOTIFICATION
=========================== */

$sql = "INSERT INTO notifications
        (
            user_id,
            claim_id,
            found_response_id,
            message,
            is_read,
            created_at
        )
        VALUES (?, NULL, ?, ?, 0, NOW())";

$stmt = mysqli_prepare($conn, $sql);

if ($stmt) {

    mysqli_stmt_bind_param(
        $stmt,
        "iis",
        $owner_id,
        $response_id,
        $message
    );

    mysqli_stmt_execute($stmt);

    mysqli_stmt_close($stmt);

}


/* ===========================
   SUCCESS
=========================== */

/*
 * Do NOT redirect to found_success.php
 * because that file does not exist.
 *
 * Instead, return to the lost item's
 * details page with a success flag.
 */

header(
    "Location: student/item_details.php?id="
    . $lost_item_id
    . "&type=Lost&found=success"
);

exit();

?>