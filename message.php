<?php

include 'includes/navbar.php';

$type = $_GET['type'] ?? 'info';
$action = $_GET['action'] ?? '';

$icon = "fa-circle-info";
$color = "#2563eb";
$title = "Information";
$message = "Something happened.";
$buttonText = "Back to Home";
$buttonLink = "index.php";

switch ($action) {

    case "register_success":

        $icon = "fa-circle-check";
        $color = "#10b981";
        $title = "Registration Successful!";
        $message = "Your account has been created successfully. It is now waiting for administrator approval. You will be able to log in after an admin approves your account.";
        $buttonText = "Back to Home";
        $buttonLink = "index.php";

        break;
    case "empty_fields":

        $icon = "fa-circle-exclamation";
        $color = "#f59e0b";
        $title = "Missing Information";
        $message = "Please fill in all required fields before submitting the form.";
        $buttonText = "Back to Registration";
        $buttonLink = "register.php";

    break;

    case "invalid_email":

        $icon = "fa-circle-xmark";
        $color = "#ef4444";
        $title = "Invalid Email";
        $message = "Please enter a valid email address.";
        $buttonText = "Back to Registration";
        $buttonLink = "register.php";

    break;

    case "password_mismatch":

        $icon = "fa-lock";
        $color = "#ef4444";
        $title = "Passwords Do Not Match";
        $message = "The password and confirm password fields must be identical.";
        $buttonText = "Back to Registration";
        $buttonLink = "register.php";

    break;
    case "email_exists":

    $icon = "fa-envelope-circle-xmark";
    $color = "#ef4444";
    $title = "Email Already Registered";
    $message = "An account with this email already exists. Please sign in or use a different email address.";
    $buttonText = "Back to Registration";
    $buttonLink = "register.php";

    break;
    case "id_exists":

    $icon = "fa-id-card";
    $color = "#ef4444";
    $title = "University ID Already Registered";
    $message = "This University ID is already associated with an account. If it's your account, please sign in instead.";
    $buttonText = "Back to Registration";
    $buttonLink = "register.php";

    break;

}
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>FindIt</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

<link rel="stylesheet" href="assets/css/style.css">

<style>

body{

    min-height:100vh;

    position:relative;

    overflow-x:hidden;

}
body::before{

    content:"";

    position:fixed;

    top:0;

    left:0;

    width:100%;

    height:100%;

    background:url("assets/images/register-bg.jpg") center center/cover no-repeat;

    filter:blur(3px);

    transform:scale(1.05);

    z-index:-2;

}
body::after{

    content:"";

    position:fixed;

    inset:0;

    background:rgba(255,255,255,.15);

    z-index:-1;

}


.message-card{

    max-width:700px;

    margin:160px auto 80px;

    padding:50px;

    border-radius:24px;

    background:rgba(255,255,255,.88);

    backdrop-filter:blur(14px);

    -webkit-backdrop-filter:blur(14px);

    border:1px solid rgba(255,255,255,.35);

    box-shadow:0 20px 45px rgba(0,0,0,.15);
      text-align:center;  

}

.message-icon{

    font-size:70px;

    margin-bottom:20px;

}

.message-card h2{

    font-weight:700;

    margin-bottom:20px;

}

.message-card p{

    color:#64748b;

    line-height:1.8;

    margin-bottom:35px;

}

.btn-main{

    background:#2563eb;

    color:white;

    border-radius:30px;

    padding:12px 28px;

    text-decoration:none;

    transition:.3s;

}

.btn-main:hover{

    background:#1d4ed8;

    color:white;

}

</style>

</head>

<body>

<div class="message-card">

    <div class="message-icon" style="color:<?= $color ?>;">

        <i class="fa-solid <?= $icon ?>"></i>

    </div>

    <h2><?= $title ?></h2>

    <p><?= $message ?></p>

    <a href="<?= $buttonLink ?>" class="btn-main">

        <?= $buttonText ?>

    </a>

</div>

</body>

</html>