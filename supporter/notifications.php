<?php
session_start();
include('../config.php');

if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "supporter") {
    header("Location: ../login.php");
    exit;
}

// Fetch latest urgent camp needs
$needs_sql = "
SELECT cn.id, rc.camp_name, cn.resource_type, cn.quantity_required, cn.unit_type, cn.priority_level, cn.status, cn.updated_at
FROM camp_needs cn
JOIN relief_camps rc ON cn.camp_id = rc.id
WHERE cn.priority_level IN ('high','critical') AND cn.status='pending'
ORDER BY cn.updated_at DESC
";
$needs_result = mysqli_query($conn, $needs_sql);

?>

<!DOCTYPE html>
<html>
<head>
    <title>Supporter Notifications</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include('navbar.php'); ?>

<div class="container mt-5">
    <h3 class="text-center mb-4">Urgent Needs & Notifications</h3>

    <?php if(mysqli_num_rows($needs_result) > 0): ?>
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="thead-dark">
                    <tr>
                        <th>ID</th>
                        <th>Camp Name</th>
                        <th>Resource Type</th>
                        <th>Quantity Required</th>
                        <th>Unit</th>
                        <th>Priority Level</th>
                        <th>Status</th>
                        <th>Last Updated</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($n = mysqli_fetch_assoc($needs_result)): ?>
                    <tr>
                        <td><?= $n['id'] ?></td>
                        <td><?= htmlspecialchars($n['camp_name']) ?></td>
                        <td><?= ucfirst($n['resource_type']) ?></td>
                        <td><?= $n['quantity_required'] ?></td>
                        <td><?= $n['unit_type'] ?></td>
                        <td><?= ucfirst($n['priority_level']) ?></td>
                        <td><?= ucfirst($n['status']) ?></td>
                        <td><?= $n['updated_at'] ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="alert alert-info text-center">No urgent needs at the moment.</div>
    <?php endif; ?>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
