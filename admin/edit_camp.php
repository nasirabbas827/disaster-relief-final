<?php
include('config.php');
session_start();

if (!isset($_SESSION['usertype']) || $_SESSION['usertype'] !== 'admin') {
    header("Location: admin_login.php");
    exit;
}

$id = intval($_GET['id']);
$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $camp_name = $_POST['camp_name'];
    $province  = $_POST['province'];
    $district  = $_POST['district'];
    $location  = $_POST['location_details'];
    $capacity  = $_POST['capacity'];
    $facilities = $_POST['facilities'];
    $manager_id = $_POST['manager_id'] ?: null;

    $stmt = $conn->prepare("
        UPDATE relief_camps
        SET camp_name=?, province=?, district=?, location_details=?,
            capacity=?, facilities=?, manager_id=?
        WHERE id=?
    ");

    $stmt->bind_param(
        "ssssissi",
        $camp_name, $province, $district, $location,
        $capacity, $facilities, $manager_id, $id
    );

    $stmt->execute();
    $stmt->close();

    $message = "Camp updated successfully.";
}

$camp = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT * FROM relief_camps WHERE id=$id")
);

$managers = mysqli_query($conn,
    "SELECT id, name FROM users WHERE role='camp_manager' AND status='active'"
);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Camp</title>
    <meta charset="utf-8">

    <link rel="stylesheet"
          href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>
<?php include('admin_navbar.php'); ?>

<div class="container mt-5">
<div class="card mx-auto" style="max-width:800px;">
<div class="card-body">

<h3 class="text-center mb-4">Edit Relief Camp</h3>

<?php if ($message): ?>
<div class="alert alert-success"><?= $message ?></div>
<?php endif; ?>

<form method="POST">

<div class="row">
    <div class="col-md-6 mb-3">
        <label>Camp Name</label>
        <input type="text" name="camp_name" class="form-control"
               value="<?= htmlspecialchars($camp['camp_name']) ?>" required>
    </div>

    <div class="col-md-6 mb-3">
        <label>Province</label>
        <input type="text" name="province" class="form-control"
               value="<?= $camp['province'] ?>">
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label>District</label>
        <input type="text" name="district" class="form-control"
               value="<?= $camp['district'] ?>">
    </div>

    <div class="col-md-6 mb-3">
        <label>Capacity</label>
        <input type="number" name="capacity" class="form-control"
               value="<?= $camp['capacity'] ?>">
    </div>
</div>

<div class="row">
    <div class="col-md-12 mb-3">
        <label>Location Details</label>
        <textarea name="location_details" class="form-control"><?= $camp['location_details'] ?></textarea>
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label>Facilities</label>
        <textarea name="facilities" class="form-control"><?= $camp['facilities'] ?></textarea>
    </div>

    <div class="col-md-6 mb-3">
        <label>Camp Manager</label>
        <select name="manager_id" class="form-control">
            <option value="">None</option>
            <?php while ($m = mysqli_fetch_assoc($managers)): ?>
                <option value="<?= $m['id'] ?>"
                    <?= $camp['manager_id']==$m['id']?'selected':'' ?>>
                    <?= $m['name'] ?>
                </option>
            <?php endwhile; ?>
        </select>
    </div>
</div>

<div class="text-center mt-4">
    <button class="btn btn-primary">Update</button>
    <a href="view_camps.php" class="btn btn-secondary">Back</a>
</div>

</form>

</div>
</div>
</div>
</body>
</html>
