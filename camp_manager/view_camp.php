<?php
session_start();
include('../config.php'); 

if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "camp_manager") {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION["id"];  
$name = $_SESSION["name"];
$message = "";

/* ================= FETCH CAMP ================= */
$sql = "SELECT id, camp_name, province, district, location_details, capacity, current_occupancy, facilities 
        FROM relief_camps WHERE manager_id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $camp_id, $camp_name, $province, $district, $location_details, $capacity, $current_occupancy, $facilities);
mysqli_stmt_fetch($stmt);
mysqli_stmt_close($stmt);

/* ================= UPDATE CAMP ================= */
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $camp_name = trim($_POST['camp_name']);
    $province = trim($_POST['province']);
    $district = trim($_POST['district']);
    $location_details = trim($_POST['location_details']);
    $capacity = intval($_POST['capacity']);
    $current_occupancy = intval($_POST['current_occupancy']);
    $facilities = trim($_POST['facilities']);

    $update_sql = "UPDATE relief_camps 
                   SET camp_name=?, province=?, district=?, location_details=?, capacity=?, current_occupancy=?, facilities=? 
                   WHERE id=? AND manager_id=?";

    $stmt = mysqli_prepare($conn, $update_sql);
    mysqli_stmt_bind_param(
        $stmt,
        "sssssssss",
        $camp_name,
        $province,
        $district,
        $location_details,
        $capacity,
        $current_occupancy,
        $facilities,
        $camp_id,
        $user_id
    );

    if (mysqli_stmt_execute($stmt)) {
        $message = '<div class="alert alert-success">Camp details updated successfully</div>';
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
    <title>My Camp Details</title>
    <meta charset="utf-8">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<?php include('navbar.php'); ?>

<div class="container mt-5">
    <div class="card mx-auto" style="max-width:800px;">
        <div class="card-body">

            <h4 class="text-center mb-4">My Camp Details</h4>
            <?php echo $message; ?>

            <?php if ($camp_id): ?>
            <form method="POST">

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Camp Name</label>
                        <input type="text" name="camp_name" value="<?php echo htmlspecialchars($camp_name); ?>" class="form-control" required>
                    </div>

                    <div class="form-group col-md-6">
                        <label>Province</label>
                        <input type="text" name="province" value="<?php echo htmlspecialchars($province); ?>" class="form-control">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>District</label>
                        <input type="text" name="district" value="<?php echo htmlspecialchars($district); ?>" class="form-control">
                    </div>

                    <div class="form-group col-md-6">
                        <label>Location Details</label>
                        <textarea name="location_details" class="form-control" rows="2"><?php echo htmlspecialchars($location_details); ?></textarea>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Capacity</label>
                        <input type="number" name="capacity" value="<?php echo htmlspecialchars($capacity); ?>" class="form-control" min="0">
                    </div>

                    <div class="form-group col-md-6">
                        <label>Current Occupancy</label>
                        <input type="number" name="current_occupancy" value="<?php echo htmlspecialchars($current_occupancy); ?>" class="form-control" min="0">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-12">
                        <label>Facilities</label>
                        <textarea name="facilities" class="form-control" rows="3"><?php echo htmlspecialchars($facilities); ?></textarea>
                    </div>
                </div>

                <div class="text-center mt-3">
                    <button class="btn btn-primary px-4">Update Camp Details</button>
                </div>

            </form>
            <?php else: ?>
                <div class="alert alert-warning text-center">
                    You do not have a camp assigned yet.
                </div>
            <?php endif; ?>

        </div>
    </div>
</div>

</body>
</html>
