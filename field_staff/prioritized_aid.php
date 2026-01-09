<?php
session_start();
include('../config.php'); 

if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "field_staff") {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION["id"];
$name = $_SESSION["name"];

/* ================= FETCH CAMP NEEDS ================= */
$camp_needs_sql = "
SELECT rc.camp_name, cn.resource_type, cn.quantity_required, cn.unit_type, cn.priority_level, cn.status, cn.updated_at
FROM camp_needs cn
JOIN relief_camps rc ON cn.camp_id = rc.id
ORDER BY 
    FIELD(cn.priority_level, 'critical', 'high', 'medium', 'low'),
    rc.camp_name ASC
";
$camp_needs_res = mysqli_query($conn, $camp_needs_sql);

?>

<!DOCTYPE html>
<html>
<head>
    <title>Prioritized Aid Distribution</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include('navbar.php'); ?>

<div class="container mt-5">
    <h3 class="text-center mb-4">Prioritized Aid Distribution List</h3>

    <?php if(mysqli_num_rows($camp_needs_res) > 0): ?>
        <div class="table-responsive mb-3">
            <table class="table table-bordered table-striped">
                <thead class="thead-dark">
                    <tr>
                        <th>Camp Name</th>
                        <th>Resource Type</th>
                        <th>Quantity Required</th>
                        <th>Unit</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Last Updated</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = mysqli_fetch_assoc($camp_needs_res)): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['camp_name']) ?></td>
                        <td><?= htmlspecialchars(ucfirst($row['resource_type'])) ?></td>
                        <td><?= $row['quantity_required'] ?></td>
                        <td><?= $row['unit_type'] ?></td>
                        <td><?= ucfirst($row['priority_level']) ?></td>
                        <td><?= ucfirst($row['status']) ?></td>
                        <td><?= $row['updated_at'] ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <div class="text-center">
            <button class="btn btn-primary" onclick="window.print()">Print List</button>
        </div>

    <?php else: ?>
        <div class="alert alert-info text-center">No camp needs found.</div>
    <?php endif; ?>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
