<?php
include('config.php');
session_start();

if (!isset($_SESSION['usertype']) || $_SESSION['usertype'] !== 'admin') {
    header("Location: admin_login.php");
    exit;
}

if (!isset($_GET['id'])) {
    die("Camp ID is required.");
}

$camp_id = intval($_GET['id']);

// Fetch camp info
$stmt = $conn->prepare("SELECT rc.*, u.name AS manager_name FROM relief_camps rc LEFT JOIN users u ON rc.manager_id = u.id WHERE rc.id=?");
$stmt->bind_param("i", $camp_id);
$stmt->execute();
$camp = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$camp) die("Camp not found.");

// Registered Families
$fam_sql = $conn->prepare("
    SELECT af.*, fcl.status
    FROM affected_families af
    JOIN family_camp_link fcl ON af.id=fcl.family_id
    WHERE fcl.camp_id=?
");
$fam_sql->bind_param("i", $camp_id);
$fam_sql->execute();
$families = $fam_sql->get_result();
$fam_sql->close();

// Aid Distribution
$aid_sql = $conn->prepare("
    SELECT ad.*, af.head_name, r.resource_name, u.name AS distributed_by
    FROM aid_distribution ad
    JOIN affected_families af ON ad.family_id = af.id
    JOIN resources r ON ad.resource_id = r.id
    JOIN users u ON ad.distributed_by = u.id
    WHERE r.camp_id=?
");
$aid_sql->bind_param("i", $camp_id);
$aid_sql->execute();
$aid_records = $aid_sql->get_result();
$aid_sql->close();

// Volunteer Tasks
$task_sql = $conn->prepare("SELECT * FROM volunteer_tasks WHERE camp_id=?");
$task_sql->bind_param("i", $camp_id);
$task_sql->execute();
$tasks = $task_sql->get_result();
$task_sql->close();

// Camp Needs
$needs_sql = $conn->prepare("SELECT * FROM camp_needs WHERE camp_id=?");
$needs_sql->bind_param("i", $camp_id);
$needs_sql->execute();
$needs = $needs_sql->get_result();
$needs_sql->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Camp Details - <?= htmlspecialchars($camp['camp_name']) ?></title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">

</head>
<body>
<?php include('admin_navbar.php'); ?>

<div class="container mt-5">
    <h3>Camp Details: <?= htmlspecialchars($camp['camp_name']) ?></h3>
    <p><strong>Province:</strong> <?= $camp['province'] ?> | <strong>District:</strong> <?= $camp['district'] ?> | <strong>Manager:</strong> <?= $camp['manager_name'] ?? 'Not Assigned' ?></p>

    <!-- Registered Families -->
    <h5 class="mt-4">Registered Families</h5>
    <table class="table table-bordered table-striped">
        <thead class="thead-dark">
            <tr>
                <th>Head Name</th>
                <th>Contact</th>
                <th>Family Size</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php while($f = $families->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($f['head_name']) ?></td>
                <td><?= $f['contact'] ?></td>
                <td><?= $f['family_size'] ?></td>
                <td><?= $f['status'] ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <!-- Aid Distribution -->
    <h5 class="mt-4">Aid Distribution</h5>
    <table class="table table-bordered table-striped">
        <thead class="thead-dark">
            <tr>
                <th>Family Head</th>
                <th>Resource</th>
                <th>Quantity Given</th>
                <th>Distributed By</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            <?php while($a = $aid_records->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($a['head_name']) ?></td>
                <td><?= htmlspecialchars($a['resource_name']) ?></td>
                <td><?= $a['quantity_given'] ?></td>
                <td><?= htmlspecialchars($a['distributed_by']) ?></td>
                <td><?= $a['distribution_date'] ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <!-- Volunteer Tasks -->
    <h5 class="mt-4">Volunteer Tasks</h5>
    <table class="table table-bordered table-striped">
        <thead class="thead-dark">
            <tr>
                <th>Title</th>
                <th>Description</th>
                <th>Required Skills</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php while($t = $tasks->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($t['title']) ?></td>
                <td><?= htmlspecialchars($t['description']) ?></td>
                <td><?= htmlspecialchars($t['required_skills']) ?></td>
                <td><?= $t['status'] ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <!-- Camp Needs -->
    <h5 class="mt-4">Camp Needs</h5>
    <table class="table table-bordered table-striped">
        <thead class="thead-dark">
            <tr>
                <th>Resource Type</th>
                <th>Quantity Required</th>
                <th>Unit</th>
                <th>Priority Level</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php while($n = $needs->fetch_assoc()): ?>
            <tr>
                <td><?= $n['resource_type'] ?></td>
                <td><?= $n['quantity_required'] ?></td>
                <td><?= $n['unit_type'] ?></td>
                <td><?= $n['priority_level'] ?></td>
                <td><?= $n['status'] ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

</div>
</body>
</html>
