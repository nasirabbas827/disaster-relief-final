<?php
session_start();
include('../config.php'); 

if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "camp_manager") {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION["id"];  
$name = $_SESSION["name"];

/* ================= FETCH CAMP ================= */
$sql = "SELECT id, camp_name FROM relief_camps WHERE manager_id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $camp_id, $camp_name);
mysqli_stmt_fetch($stmt);
mysqli_stmt_close($stmt);

/* ================= FETCH RESOURCES ================= */
$resources = [];
if ($camp_id) {
    $res_sql = "SELECT resource_name, category, quantity, unit, current_location 
                FROM resources WHERE camp_id = ?";
    $stmt = mysqli_prepare($conn, $res_sql);
    mysqli_stmt_bind_param($stmt, "i", $camp_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($result)) {
        $resources[] = $row;
    }
    mysqli_stmt_close($stmt);
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Camp Resources</title>
    <meta charset="utf-8">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include('navbar.php'); ?>

<div class="container mt-5">
    <h3 class="text-center mb-4">Resources in <?php echo htmlspecialchars($camp_name ?? 'Your Camp'); ?></h3>

    <?php if ($camp_id && count($resources) > 0): ?>
        <table class="table table-bordered table-striped">
            <thead class="thead-dark">
                <tr>
                    <th>Resource Name</th>
                    <th>Category</th>
                    <th>Quantity</th>
                    <th>Unit</th>
                    <th>Current Location</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($resources as $res): ?>
                <tr>
                    <td><?php echo htmlspecialchars($res['resource_name']); ?></td>
                    <td><?php echo htmlspecialchars($res['category']); ?></td>
                    <td><?php echo htmlspecialchars($res['quantity']); ?></td>
                    <td><?php echo htmlspecialchars($res['unit']); ?></td>
                    <td><?php echo htmlspecialchars($res['current_location']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php elseif ($camp_id): ?>
        <div class="alert alert-warning text-center">No resources added yet.</div>
    <?php else: ?>
        <div class="alert alert-warning text-center">You do not have a camp assigned yet.</div>
    <?php endif; ?>
</div>
</body>
</html>
