<?php
include('config.php');
session_start();

// Admin access check
if (!isset($_SESSION['usertype']) || $_SESSION['usertype'] !== 'admin') {
    header("Location: admin_login.php");
    exit;
}

// ============================
// 1. Relief Operations Report
// ============================
$relief_ops_sql = "
SELECT rc.id AS camp_id, rc.camp_name, COUNT(fcl.family_id) AS total_families,
       SUM(af.family_size) AS total_people
FROM relief_camps rc
LEFT JOIN family_camp_link fcl ON rc.id = fcl.camp_id
LEFT JOIN affected_families af ON fcl.family_id = af.id
GROUP BY rc.id
ORDER BY rc.id
";
$relief_ops_result = mysqli_query($conn, $relief_ops_sql);

// ============================
// 2. Donations Report
// ============================
$donations_sql = "
SELECT d.id, u.name AS supporter, d.donation_type, d.amount, d.description, d.status, d.donated_at
FROM donations d
JOIN users u ON d.supporter_id = u.id
ORDER BY d.donated_at DESC
";
$donations_result = mysqli_query($conn, $donations_sql);

// ============================
// 3. Resource Distribution Report
// ============================
$resource_dist_sql = "
SELECT 
    ad.id,
    af.head_name AS family_head,
    rc.resource_name,
    ad.quantity_given,
    r.id AS camp_id,
    r.camp_name AS camp_name,
    u.name AS distributed_by,
    ad.distribution_date
FROM aid_distribution ad
JOIN affected_families af ON ad.family_id = af.id
JOIN resources rc ON ad.resource_id = rc.id
JOIN relief_camps r ON rc.camp_id = r.id
JOIN users u ON ad.distributed_by = u.id
ORDER BY ad.distribution_date DESC
";
$resource_dist_result = mysqli_query($conn, $resource_dist_sql);

?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Reports</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .report-section { display: none; }
    </style>
</head>
<body>
<?php include('admin_navbar.php'); ?>

<div class="container mt-5">

    <h3>Generate Reports</h3>
    <div class="form-group">
        <label for="reportSelect">Select Report:</label>
        <select class="form-control" id="reportSelect">
            <option value="">-- Choose Report --</option>
            <option value="relief_ops">Relief Operations</option>
            <option value="donations">Donations</option>
            <option value="resource_distribution">Resource Distribution</option>
        </select>
    </div>

    <!-- Relief Operations Report -->
    <div id="relief_ops" class="report-section mt-4">
        <div >
            <h4>Relief Operations Report</h4>
            <button class="btn btn-dark" onclick="printReport('relief_ops')">Print</button>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="thead-dark">
                    <tr>
                        <th>Camp ID</th>
                        <th>Camp Name</th>
                        <th>Total Families</th>
                        <th>Total People</th>
                    </tr>
                </thead>
                <tbody>
                    <?php mysqli_data_seek($relief_ops_result, 0); while ($row = mysqli_fetch_assoc($relief_ops_result)): ?>
                    <tr>
                        <td><?= $row['camp_id'] ?></td>
                        <td><?= htmlspecialchars($row['camp_name']) ?></td>
                        <td><?= $row['total_families'] ?></td>
                        <td><?= $row['total_people'] ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Donations Report -->
    <div id="donations" class="report-section mt-4">
        <div  >
            <h4>Donations Report</h4>
            <button class="btn btn-dark" onclick="printReport('donations')">Print</button>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="thead-dark">
                    <tr>
                        <th>ID</th>
                        <th>Supporter</th>
                        <th>Donation Type</th>
                        <th>Amount</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th>Donated At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php mysqli_data_seek($donations_result, 0); while ($row = mysqli_fetch_assoc($donations_result)): ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= htmlspecialchars($row['supporter']) ?></td>
                        <td><?= $row['donation_type'] ?></td>
                        <td><?= $row['amount'] ?></td>
                        <td><?= htmlspecialchars($row['description']) ?></td>
                        <td><?= $row['status'] ?></td>
                        <td><?= $row['donated_at'] ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Resource Distribution Report -->
    <div id="resource_distribution" class="report-section mt-4">
        <div class="">
            <h4>Resource Distribution Report</h4>
            <button class="btn btn-dark" onclick="printReport('resource_distribution')">Print</button>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="thead-dark">
                    <tr>
                        <th>ID</th>
                        <th>Family Head</th>
                        <th>Resource</th>
                        <th>Quantity Given</th>
                        <th>Camp ID</th>
                        <th>Camp Name</th>
                        <th>Distributed By</th>
                        <th>Distribution Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php mysqli_data_seek($resource_dist_result, 0); while ($row = mysqli_fetch_assoc($resource_dist_result)): ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= htmlspecialchars($row['family_head']) ?></td>
                        <td><?= htmlspecialchars($row['resource_name']) ?></td>
                        <td><?= $row['quantity_given'] ?></td>
                        <td><?= $row['camp_id'] ?></td>
                        <td><?= htmlspecialchars($row['camp_name']) ?></td>
                        <td><?= htmlspecialchars($row['distributed_by']) ?></td>
                        <td><?= $row['distribution_date'] ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<script>
    // Show selected report
    $('#reportSelect').on('change', function() {
        $('.report-section').hide();
        var selected = $(this).val();
        if(selected) {
            $('#' + selected).show();
        }
    });

    // Print specific report
    function printReport(reportId) {
        var printContents = document.getElementById(reportId).innerHTML;
        var originalContents = document.body.innerHTML;
        document.body.innerHTML = printContents;
        window.print();
        document.body.innerHTML = originalContents;
        location.reload();
    }
</script>
</body>
</html>
