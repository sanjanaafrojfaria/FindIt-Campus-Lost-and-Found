<?php
include 'config/database.php';
include 'includes/navbar.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Register | FindIt</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

    <!-- Main CSS -->
    <link rel="stylesheet"
          href="assets/css/style.css">

    <!-- Register CSS -->
    <link rel="stylesheet"
          href="assets/css/register.css">

</head>

<body>

<div class="overlay">

    <div class="register-card">

        <h2>Create Account</h2>

        <p class="subtitle">
            Join FindIt and help reconnect lost belongings with their rightful owners.
        </p>


        <form action="register_process.php"
              method="POST"
              enctype="multipart/form-data">


            <!-- ==========================
                 FULL NAME + UNIVERSITY
            =========================== -->

            <div class="row">

                <!-- FULL NAME -->
                <div class="col-md-6 mb-3">

                    <label for="full_name">Full Name</label>

                    <input type="text"
                           id="full_name"
                           class="form-control"
                           name="full_name"
                           placeholder="Enter your full name"
                           required>

                </div>


                <!-- UNIVERSITY -->
                <div class="col-md-6 mb-3">

                    <label for="university">University</label>

                    <select class="form-select"
                            id="university"
                            name="university_ref_id"
                            required>

                        <option value="">Select University</option>

                        <?php

                        $universityQuery = mysqli_query(
                            $conn,
                            "SELECT id, name
                             FROM universities
                             ORDER BY name ASC"
                        );

                        while ($university = mysqli_fetch_assoc($universityQuery)) {

                        ?>

                            <option value="<?php echo $university['id']; ?>">

                                <?php echo htmlspecialchars($university['name']); ?>

                            </option>

                        <?php

                        }

                        ?>

                    </select>

                </div>


                <!-- ==========================
                     UNIVERSITY ID + EMAIL
                =========================== -->

                <!-- UNIVERSITY ID -->
                <div class="col-md-6 mb-3">

                    <label for="university_id">University ID</label>

                    <input type="text"
                           id="university_id"
                           class="form-control"
                           name="university_id"
                           placeholder="e.g. 221-35-xxxx"
                           required>

                </div>


                <!-- EMAIL -->
                <div class="col-md-6 mb-3">

                    <label for="email">Email</label>

                    <input type="email"
                           id="email"
                           class="form-control"
                           name="email"
                           placeholder="Enter your email"
                           required>

                </div>


                <!-- ==========================
                     PHONE + DEPARTMENT
                =========================== -->

                <!-- PHONE -->
                <div class="col-md-6 mb-3">

                    <label for="phone">Phone Number</label>

                    <input type="tel"
       id="phone"
       class="form-control"
       name="phone"
       placeholder="01XXXXXXXXX"
       maxlength="11"
       minlength="11"
       pattern="[0-9]{11}"
       inputmode="numeric"
       required>

                </div>


                <!-- DEPARTMENT -->
                <div class="col-md-6 mb-3">

                    <label for="department">Department</label>

                    <select class="form-select"
                            id="department"
                            name="department"
                            required>

                        <option value="">Select Department</option>

                        <option value="CSE">CSE</option>
                        <option value="EEE">EEE</option>
                        <option value="CE">CE</option>
                        <option value="Business Administration">
                            Business Administration
                        </option>
                        <option value="English">English</option>
                        <option value="Law">Law</option>
                        <option value="Pharmacy">Pharmacy</option>
                        <option value="Architecture">Architecture</option>
                        <option value="Other">Other</option>

                    </select>

                </div>


                <!-- ==========================
                     ID CARD
                =========================== -->

                <div class="col-12 mb-3">

                    <label for="profile_image">
                        University ID Card
                    </label>

                    <input type="file"
                           id="profile_image"
                           class="form-control"
                           name="profile_image"
                           accept=".jpg,.jpeg,.png"
                           required>

                    <small class="text-muted">

                        Upload a clear photo of your university ID card.
                        This image will be used as your profile picture
                        and verified by an administrator.

                    </small>

                </div>


                <!-- ==========================
                     PASSWORD ROW
                =========================== -->

                <div class="col-md-6 mb-4">

                    <label for="password">Password</label>

                    <input type="password"
                           id="password"
                           class="form-control"
                           name="password"
                           placeholder="Create password"
                           required>

                </div>


                <!-- CONFIRM PASSWORD -->
                <div class="col-md-6 mb-4">

                    <label for="confirm_password">
                        Confirm Password
                    </label>

                    <input type="password"
                           id="confirm_password"
                           class="form-control"
                           name="confirm_password"
                           placeholder="Confirm password"
                           required>

                </div>


                <!-- ==========================
                     SUBMIT
                =========================== -->

                <div class="col-12">

                    <button type="submit"
                            class="btn btn-primary btn-lg">

                        Create Account

                    </button>

                </div>

            </div>

        </form>


        <!-- LOGIN LINK -->

        <div class="login-link">

            Already have an account?

            <a href="login.php">
                Sign In
            </a>

        </div>

    </div>

</div>


<script src="assets/js/register.js"></script>

</body>

</html>