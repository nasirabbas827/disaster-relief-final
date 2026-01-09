<?php
include('config.php');
session_start();

if (!isset($_SESSION['usertype']) || $_SESSION['usertype'] !== 'admin') {
    header("Location: admin_login.php");
    exit;
}

if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM relief_camps WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: view_camps.php");
    exit;
}

$result = mysqli_query($conn, "
    SELECT rc.*, u.name AS manager_name
    FROM relief_camps rc
    LEFT JOIN users u ON rc.manager_id = u.id
    ORDER BY rc.id DESC
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Camps</title>
    <meta charset="utf-8">

    <link rel="stylesheet"
          href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>
<?php include('admin_navbar.php'); ?>

<div class="container mt-5">

<div >
    <h3>Relief Camps</h3>
    <a href="add_camp.php" class="btn btn-success">Add Camp</a>
</div>

<div class="table-responsive">
<table class="table table-bordered table-striped">
<thead class="thead-dark">
<tr>
    <th>ID</th>
    <th>Camp Name</th>
    <th>Province</th>
    <th>District</th>
    <th>Capacity</th>
    <th>Current Occupency</th>
    <th>Manager</th>
    <th width="200">Actions</th>
</tr>
</thead>

<tbody>
<?php while ($row = mysqli_fetch_assoc($result)): ?>
<tr>
    <td><?= $row['id'] ?></td>
    <td><?= htmlspecialchars($row['camp_name']) ?></td>
    <td><?= $row['province'] ?></td>
    <td><?= $row['district'] ?></td>
    <td><?= $row['capacity'] ?></td>
    <td><?= $row['current_occupancy'] ?></td>
    <td><?= $row['manager_name'] ?? 'Not Assigned' ?></td>
    <td>
        <a href="camp_details.php?id=<?= $row['id'] ?>" class="btn btn-info btn-sm m-2 ">View Details</a>
        <a href="edit_camp.php?id=<?= $row['id'] ?>" class="btn btn-primary btn-sm">Edit</a>
        <a href="?delete=<?= $row['id'] ?>"
           onclick="return confirm('Delete this camp?');"
           class="btn btn-danger btn-sm">Delete</a>
    </td>
</tr>
<?php endwhile; ?>
</tbody>

</table>
</div>

</div>
</body>
</html>
