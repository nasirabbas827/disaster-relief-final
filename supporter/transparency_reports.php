<?php
session_start();
include('../config.php');

if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "supporter") {
    header("Location: ../login.php");
    exit;
}

$supporter_id = $_SESSION['id'];

/* ================= FETCH DONATIONS ================= */
$donations_sql = "
SELECT id, donation_type, amount, description, status, donated_at
FROM donations
WHERE supporter_id = ?
ORDER BY donated_at DESC
";
$stmt = mysqli_prepare($conn, $donations_sql);
mysqli_stmt_bind_param($stmt, "i", $supporter_id);
mysqli_stmt_execute($stmt);
$donations_res = mysqli_stmt_get_result($stmt);
mysqli_stmt_close($stmt);

/* ================= FETCH AID DISTRIBUTION ================= */
$aid_sql = "
SELECT ad.id AS aid_id, af.head_name, r.resource_name, ad.quantity_given, u.name AS distributed_by, ad.distribution_date
FROM aid_distribution ad
JOIN affected_families af ON ad.family_id = af.id
JOIN resources r ON ad.resource_id = r.id
JOIN users u ON ad.distributed_by = u.id
ORDER BY ad.distribution_date DESC
";
$aid_res = mysqli_query($conn, $aid_sql);

?>

<!DOCTYPE html>
<html>
<head>
    <title>Transparency Reports</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include('navbar.php'); ?>

<div class="container mt-5">
    <h3 class="text-center mb-4">Transparency Reports</h3>

    <!-- DONATIONS -->
    <h5>Your Donations</h5>
    <?php if(mysqli_num_rows($donations_res) > 0): ?>
        <div class="table-responsive mb-4">
            <table class="table table-bordered table-striped">
                <thead class="thead-dark">
                    <tr>
                        <th>ID</th>
                        <th>Donation Type</th>
                        <th>Amount</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th>Donated At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($d = mysqli_fetch_assoc($donations_res)): ?>
                    <tr>
                        <td><?= $d['id'] ?></td>
                        <td><?= ucfirst($d['donation_type']) ?></td>
                        <td><?= $d['amount'] ?></td>
                        <td><?= htmlspecialchars($d['description']) ?></td>
                        <td><?= ucfirst($d['status']) ?></td>
                        <td><?= $d['donated_at'] ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="alert alert-info">You haven't made any donations yet.</div>
    <?php endif; ?>

    <!-- AID DISTRIBUTION -->
    <h5>Aid / Resources Distributed</h5>
    <?php if(mysqli_num_rows($aid_res) > 0): ?>
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="thead-dark">
                    <tr>
                        <th>ID</th>
                        <th>Family Head</th>
                        <th>Resource Name</th>
                        <th>Quantity Given</th>
                        <th>Distributed By</th>
                        <th>Distribution Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($a = mysqli_fetch_assoc($aid_res)): ?>
                    <tr>
                        <td><?= $a['aid_id'] ?></td>
                        <td><?= htmlspecialchars($a['head_name']) ?></td>
                        <td><?= htmlspecialchars($a['resource_name']) ?></td>
                        <td><?= $a['quantity_given'] ?></td>
                        <td><?= htmlspecialchars($a['distributed_by']) ?></td>
                        <td><?= $a['distribution_date'] ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="alert alert-info">No aid has been distributed yet.</div>
    <?php endif; ?>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
