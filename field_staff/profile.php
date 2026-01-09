<?php
include('config.php');
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['id'];
$role    = $_SESSION['role'];

$message = "";

/* ================= FETCH USER ================= */
$sql = "SELECT name, email, phone, skills, availability, donation_preferences 
        FROM users WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $name, $email, $phone, $skills, $availability, $donation_preferences);
mysqli_stmt_fetch($stmt);
mysqli_stmt_close($stmt);

/* ================= UPDATE PROFILE ================= */
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name  = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $skills = trim($_POST['skills']);
    $availability = trim($_POST['availability']);
    $donation_preferences = trim($_POST['donation_preferences']);

    $password_sql = "";
    $params = [$name, $phone, $skills, $availability, $donation_preferences, $user_id];

    if (!empty($_POST['password'])) {
        $hashed_password = password_hash(trim($_POST['password']), PASSWORD_DEFAULT);
        $password_sql = ", password=?";
        array_splice($params, -1, 0, $hashed_password); // insert before user_id
    }

    $update_sql = "UPDATE users 
                   SET name=?, phone=?, skills=?, availability=?, donation_preferences=? $password_sql
                   WHERE id=?";

    $stmt = mysqli_prepare($conn, $update_sql);

    // Dynamically bind params based on password
    if (!empty($_POST['password'])) {
        mysqli_stmt_bind_param($stmt, "ssssssi", ...$params);
    } else {
        mysqli_stmt_bind_param($stmt, "sssssi", ...$params);
    }

    if (mysqli_stmt_execute($stmt)) {
        $message = '<div class="alert alert-success">Profile updated successfully</div>';
    } else {
        $message = '<div class="alert alert-danger">Update failed</div>';
    }

    mysqli_stmt_close($stmt);
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Profile</title>
    <meta charset="utf-8">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<?php include('navbar.php'); ?>

<div class="container mt-5">
    <div class="card mx-auto" style="max-width:700px;">
        <div class="card-body">

            <h4 class="text-center mb-4">My Profile</h4>
            <?php echo $message; ?>

            <form method="POST">

                <div class="form-row">

                    <div class="form-group col-md-6">
                        <label>Name</label>
                        <input type="text" name="name" value="<?php echo htmlspecialchars($name); ?>" class="form-control" required>
                    </div>

                    <div class="form-group col-md-6">
                        <label>Email (Read Only)</label>
                        <input type="email" value="<?php echo htmlspecialchars($email); ?>" class="form-control" readonly>
                    </div>

                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Phone</label>
                        <input type="text" name="phone" value="<?php echo htmlspecialchars($phone); ?>" class="form-control">
                    </div>

                    <div class="form-group col-md-6">
                        <label>Password (Optional)</label>
                        <input type="password" name="password" class="form-control" placeholder="Enter new password to change">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Skills</label>
                        <textarea name="skills" class="form-control" rows="2"><?php echo htmlspecialchars($skills); ?></textarea>
                    </div>

                    <div class="form-group col-md-6">
                        <label>Availability</label>
                        <input type="text" name="availability" value="<?php echo htmlspecialchars($availability); ?>" class="form-control">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-12">
                        <label>Donation Preferences</label>
                        <textarea name="donation_preferences" class="form-control" rows="2"><?php echo htmlspecialchars($donation_preferences); ?></textarea>
                    </div>
                </div>

                <div class="text-center mt-3">
                    <button class="btn btn-primary px-4">Update Profile</button>
                </div>

            </form>

        </div>
    </div>
</div>

</body>
</html>
