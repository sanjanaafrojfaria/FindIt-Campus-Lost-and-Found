<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>FindIt - Campus Lost & Found Platform</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

<link rel="stylesheet"
href="assets/css/style.css">
<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
</head>

<body>

<?php include('includes/navbar.php'); ?>

<!-- HERO -->

<section class="hero" id="home">

    <div class="container">

        <div class="row align-items-center">

            <div class="col-lg-7 hero-content">

                <div class="hero-badge">

                    <i class="fa-solid fa-shield-halved"></i>

                    Verified Campus Members Only

                </div>

                <h1>

                    Lost Something
                    <br>

                    <span>on Campus?</span>

                </h1>

                <p>

                    FindIt is a secure platform exclusively for
                    university students and staff to report,
                    search, and recover lost belongings quickly
                    through verified accounts and smart matching.

                </p>

                <div class="hero-buttons">

                    <a href="register.php"
                       class="btn btn-hero">

                        Get Started

                    </a>

                    <a href="login.php"
                       class="btn btn-outline-hero">

                        Login

                    </a>

                </div>

            </div>

        </div>

    </div>

    <div class="scroll-indicator">

        <span>Scroll</span>

        <i class="fa-solid fa-chevron-down"></i>

    </div>

</section>
<!-- LOST SOMETHING SECTION -->

<section class="lost-section">

    <div class="container">

        <div class="section-title">

            <h2 class="main-title">
                Lost Something?
            </h2>

            <div class="or-text">
                or
            </div>

            <h2 class="main-title found-title">
                Found Something?
            </h2>

            <p class="section-description">

                Every report brings an item one step closer to its owner.
                Report what you've lost or help someone by reporting what you've found.

            </p>

            <div class="action-buttons">

                <a href="#" class="btn-lost">

                    <i class="fa-solid fa-circle-exclamation"></i>

                    Report Lost Item

                </a>

                <a href="#" class="btn-found">

                    <i class="fa-solid fa-hand-holding-heart"></i>

                    Report Found Item

                </a>

            </div>

        </div>

    </div>

</section>


<!-- MOVING ITEMS -->

<!-- LATEST LOST ITEMS -->

<section class="recent-items">

    <div class="container">

        <h3 class="carousel-title">

            🔴 Latest Lost Items

        </h3>

    </div>

    <div class="slider">

        <div class="slide-track">

            <!-- Lost Card -->

            <div class="item-card">

                <span class="status lost-status">

                    LOST

                </span>

                <h4>🎒 Black Backpack</h4>

                <p><i class="fa-solid fa-location-dot"></i> Engineering Building</p>

                <small>

                    <i class="fa-regular fa-clock"></i>

                    2 hours ago

                </small>

            </div>

            <div class="item-card">

                <span class="status lost-status">

                    LOST

                </span>

                <h4>📱 Samsung Phone</h4>

                <p><i class="fa-solid fa-location-dot"></i> Library</p>

                <small>

                    <i class="fa-regular fa-clock"></i>

                    Today

                </small>

            </div>

            <div class="item-card">

                <span class="status lost-status">

                    LOST

                </span>

                <h4>🪪 Student ID</h4>

                <p><i class="fa-solid fa-location-dot"></i> CSE Building</p>

                <small>

                    <i class="fa-regular fa-clock"></i>

                    Yesterday

                </small>

            </div>

            <!-- Duplicate -->

            <div class="item-card">

                <span class="status lost-status">

                    LOST

                </span>

                <h4>🎒 Black Backpack</h4>

                <p><i class="fa-solid fa-location-dot"></i> Engineering Building</p>

                <small>

                    <i class="fa-regular fa-clock"></i>

                    2 hours ago

                </small>

            </div>

        </div>

    </div>

</section>


<!-- LATEST FOUND ITEMS -->

<section class="recent-items">

    <div class="container">

        <h3 class="carousel-title found">

            🟢 Latest Found Items

        </h3>

    </div>

    <div class="slider reverse">

        <div class="slide-track reverse-track">

            <div class="item-card">

                <span class="status found-status">

                    FOUND

                </span>

                <h4>⌚ Smart Watch</h4>

                <p><i class="fa-solid fa-location-dot"></i> Cafeteria</p>

                <small>

                    <i class="fa-regular fa-clock"></i>

                    30 mins ago

                </small>

            </div>

            <div class="item-card">

                <span class="status found-status">

                    FOUND

                </span>

                <h4>🔑 Keys</h4>

                <p><i class="fa-solid fa-location-dot"></i> Main Gate</p>

                <small>

                    <i class="fa-regular fa-clock"></i>

                    Today

                </small>

            </div>

            <div class="item-card">

                <span class="status found-status">

                    FOUND

                </span>

                <h4>💧 Water Bottle</h4>

                <p><i class="fa-solid fa-location-dot"></i> Auditorium</p>

                <small>

                    <i class="fa-regular fa-clock"></i>

                    Yesterday

                </small>

            </div>

            <div class="item-card">

                <span class="status found-status">

                    FOUND

                </span>

                <h4>⌚ Smart Watch</h4>

                <p><i class="fa-solid fa-location-dot"></i> Cafeteria</p>

                <small>

                    <i class="fa-regular fa-clock"></i>

                    30 mins ago

                </small>

            </div>

        </div>

    </div>

</section>
<!-- STATS -->

<section class="py-5 bg-light">

<div class="container">

<div class="row text-center g-4">

<div class="col-md-3">

<div class="card stat-card shadow-sm">

<div class="card-body">

<h2 class="text-primary fw-bold">
125+
</h2>

<p>Verified Users</p>

</div>

</div>

</div>

<div class="col-md-3">

<div class="card stat-card shadow-sm">

<div class="card-body">

<h2 class="text-primary fw-bold">
48+
</h2>

<p>Lost Items</p>

</div>

</div>

</div>

<div class="col-md-3">

<div class="card stat-card shadow-sm">

<div class="card-body">

<h2 class="text-primary fw-bold">
31+
</h2>

<p>Found Items</p>

</div>

</div>

</div>

<div class="col-md-3">

<div class="card stat-card shadow-sm">

<div class="card-body">

<h2 class="text-primary fw-bold">
22+
</h2>

<p>Successful Returns</p>

</div>

</div>

</div>

</div>

</div>

</section>

<!-- FEATURES -->

<section class="py-5" id="features">

<div class="container">

<div class="text-center mb-5">

<h2 class="fw-bold">
Key Features
</h2>

<p class="text-muted">
Making item recovery easier and safer.
</p>

</div>

<div class="row g-4">

<div class="col-md-4">

<div class="card feature-card shadow-sm h-100">

<div class="card-body text-center">

<i class="fa-solid fa-user-shield fa-3x text-primary mb-3"></i>

<h5>
Verified Users
</h5>

<p>
Only approved university members can use the platform.
</p>

</div>

</div>

</div>

<div class="col-md-4">

<div class="card feature-card shadow-sm h-100">

<div class="card-body text-center">

<i class="fa-solid fa-link fa-3x text-primary mb-3"></i>

<h5>
Smart Matching
</h5>

<p>
Suggests potential matches between lost and found items.
</p>

</div>

</div>

</div>

<div class="col-md-4">

<div class="card feature-card shadow-sm h-100">

<div class="card-body text-center">

<i class="fa-solid fa-qrcode fa-3x text-primary mb-3"></i>

<h5>
QR Support
</h5>

<p>
Generate QR codes for easier item recovery.
</p>

</div>

</div>

</div>

</div>

</div>

</section>

<!-- HOW IT WORKS -->

<section class="bg-light py-5" id="how-it-works">

<div class="container">

<h2 class="text-center fw-bold mb-5">
How FindIt Works
</h2>

<div class="row text-center">

<div class="col-md-3">

<div class="step-card">

<div class="step-icon">
1️⃣
</div>

<h5>
Report Item
</h5>

<p>
Create a lost or found item post.
</p>

</div>

</div>

<div class="col-md-3">

<div class="step-card">

<div class="step-icon">
2️⃣
</div>

<h5>
Search
</h5>

<p>
Browse matching posts.
</p>

</div>

</div>

<div class="col-md-3">

<div class="step-card">

<div class="step-icon">
3️⃣
</div>

<h5>
Claim
</h5>

<p>
Submit ownership proof.
</p>

</div>

</div>

<div class="col-md-3">

<div class="step-card">

<div class="step-icon">
4️⃣
</div>

<h5>
Recover
</h5>

<p>
Receive your item safely.
</p>

</div>

</div>

</div>

</div>

</section>

<!-- CTA -->

<section class="cta-section py-5 text-center">

<div class="container">

<h2 class="fw-bold">
Ready to Recover Lost Items?
</h2>

<p class="mb-4">
Join the FindIt community today.
</p>

<a href="register.php"
class="btn btn-light btn-lg">
Register Now
</a>

</div>

</section>

<?php include('includes/footer.php'); ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/script.js"></script>
</body>
</html>