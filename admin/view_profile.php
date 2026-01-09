<?php
include('config.php');
session_start();

if (!isset($_SESSION["usertype"]) || $_SESSION["usertype"] !== "admin") {
    header("Location: admin_login.php");
    exit;
}

$id = intval($_GET['id']);
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user) {
    die("User not found");
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Profile</title>
    <meta charset="utf-8">

    <link rel="stylesheet"
          href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>

<?php include('admin_navbar.php'); ?>

<div class="container mt-5">
    <div class="card mx-auto" style="max-width:700px;">
        <div class="card-body">

            <h3 class="text-center mb-4">User Profile</h3>

            <div class="row">
                <div class="col-md-6"><strong>Name:</strong> <?= htmlspecialchars($user['name']) ?></div>
                <div class="col-md-6"><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></div>
            </div>

            <hr>

            <div class="row">
                <div class="col-md-6"><strong>Phone:</strong> <?= $user['phone'] ?? '-' ?></div>
                <div class="col-md-6"><strong>Role:</strong> <?= ucfirst(str_replace('_',' ', $user['role'])) ?></div>
            </div>

            <hr>

            <div class="row">
                <div class="col-md-6"><strong>Status:</strong> <?= ucfirst($user['status']) ?></div>
                <div class="col-md-6"><strong>Availability:</strong> <?= $user['availability'] ?? '-' ?></div>
            </div>

            <hr>

            <div>
                <strong>Skills:</strong>
                <p><?= $user['skills'] ?? '-' ?></p>
            </div>

            <div>
                <strong>Donation Preferences:</strong>
                <p><?= $user['donation_preferences'] ?? '-' ?></p>
            </div>

            <div class="text-center mt-4">
                <a href="view_users.php" class="btn btn-secondary">Back</a>
            </div>

        </div>
    </div>
</div>

</body>
</html>
