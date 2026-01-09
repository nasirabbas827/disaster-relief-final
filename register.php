<?php
include('config.php');

$name = $email = $password = $phone = $skills = $availability = $donation_preferences = "";
$name_err = $email_err = $password_err = "";
$registration_msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Name
    if (empty(trim($_POST["name"]))) {
        $name_err = "Please enter your full name.";
    } else {
        $name = trim($_POST["name"]);
    }

    // Email
    if (empty(trim($_POST["email"]))) {
        $email_err = "Please enter email.";
    } else {
        $email = trim($_POST["email"]);
        $check = "SELECT id FROM users WHERE email = ?";
        $stmt = mysqli_prepare($conn, $check);
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        if (mysqli_stmt_num_rows($stmt) > 0) {
            $email_err = "Email already registered.";
        }
        mysqli_stmt_close($stmt);
    }

    // Password
    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter password.";
    } elseif (strlen($_POST["password"]) < 4) {
        $password_err = "Password must be at least 4 characters.";
    } else {
        $password = trim($_POST["password"]);
    }

    // Optional
    $phone = !empty($_POST['phone']) ? trim($_POST['phone']) : NULL;
    $skills = !empty($_POST['skills']) ? trim($_POST['skills']) : NULL;
    $availability = !empty($_POST['availability']) ? trim($_POST['availability']) : NULL;
    $donation_preferences = !empty($_POST['donation_preferences']) ? trim($_POST['donation_preferences']) : NULL;

    // Fixed role
    $role = 'supporter';
    $status = 'active';

    if (empty($name_err) && empty($email_err) && empty($password_err)) {

        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $sql = "INSERT INTO users 
                (name, email, password, role, phone, skills, availability, donation_preferences, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param(
            $stmt,
            "sssssssss",
            $name,
            $email,
            $hashed_password,
            $role,
            $phone,
            $skills,
            $availability,
            $donation_preferences,
            $status
        );

        if (mysqli_stmt_execute($stmt)) {
            $registration_msg = '<div class="alert alert-success text-center">Supporter registered successfully.</div>';
            $name = $email = $password = $phone = $skills = $availability = $donation_preferences = "";
        } else {
            $registration_msg = '<div class="alert alert-danger text-center">Registration failed.</div>';
        }

        mysqli_stmt_close($stmt);
    }

    mysqli_close($conn);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Supporter Registration</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="./css/style.css">
</head>
<body>

<?php include('navbar.php'); ?>

<div class="container mt-5">
    <div class="card mx-auto shadow" style="max-width: 800px;">
        <div class="card-body">
            <h3 class="text-center mb-4">Supporter Registration</h3>

            <?php echo $registration_msg; ?>

            <form method="post">

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Full Name</label>
                        <input type="text" name="name" class="form-control <?php echo (!empty($name_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $name; ?>">
                        <div class="invalid-feedback"><?php echo $name_err; ?></div>
                    </div>

                    <div class="form-group col-md-6">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $email; ?>">
                        <div class="invalid-feedback"><?php echo $email_err; ?></div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Password</label>
                        <input type="password" name="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>">
                        <div class="invalid-feedback"><?php echo $password_err; ?></div>
                    </div>

                    <div class="form-group col-md-6">
                        <label>Phone</label>
                        <input type="text" name="phone" class="form-control" value="<?php echo $phone; ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Skills</label>
                        <input type="text" name="skills" class="form-control" value="<?php echo $skills; ?>">
                    </div>

                    <div class="form-group col-md-6">
                        <label>Availability</label>
                        <input type="text" name="availability" class="form-control" value="<?php echo $availability; ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label>Donation Preferences</label>
                    <textarea name="donation_preferences" class="form-control" rows="2"><?php echo $donation_preferences; ?></textarea>
                </div>

                <div class="text-center mt-4">
                    <button type="submit" class="btn btn-primary px-5">Register as Supporter</button>
                </div>

            </form>
        </div>
    </div>
</div>

</body>
</html>
