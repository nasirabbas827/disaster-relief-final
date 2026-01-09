<?php
include('config.php');
session_start();

if (!isset($_SESSION["usertype"]) || $_SESSION["usertype"] !== "admin") {
    header("Location: admin_login.php");
    exit;
}

/*
Optional: delete donation
*/
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM donations WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    header("Location: view_donations.php");
    exit;
}

/*
Fetch donations with supporter info
*/
$sql = "
    SELECT 
        d.id,
        u.name AS supporter_name,
        u.email AS supporter_email,
        d.donation_type,
        d.amount,
        d.description,
        d.status,
        d.donated_at
    FROM donations d
    JOIN users u ON d.supporter_id = u.id
    ORDER BY d.id DESC
";

$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Donations</title>
    <meta charset="utf-8">

    <link rel="stylesheet"
          href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>

<?php include('admin_navbar.php'); ?>

<div class="container mt-5">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>All Donations</h3>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead class="thead-dark">
            <tr>
                <th>ID</th>
                <th>Supporter</th>
                <th>Email</th>
                <th>Type</th>
                <th>Amount</th>
                <th>Description</th>
                <th>Status</th>
                <th>Donated At</th>
                <th width="150">Actions</th>
            </tr>
            </thead>

            <tbody>
            <?php if (mysqli_num_rows($result) > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= htmlspecialchars($row['supporter_name']) ?></td>
                        <td><?= htmlspecialchars($row['supporter_email']) ?></td>
                        <td><?= ucfirst($row['donation_type']) ?></td>
                        <td>
                            <?= $row['donation_type'] === 'cash'
                                ? number_format($row['amount'], 2)
                                : '-' ?>
                        </td>
                        <td><?= htmlspecialchars($row['description'] ?? '-') ?></td>
                        <td><?= ucfirst($row['status']) ?></td>
                        <td><?= date('d M Y, h:i A', strtotime($row['donated_at'])) ?></td>

                        <td>
                            <a href="?delete=<?= $row['id'] ?>"
                               onclick="return confirm('Are you sure you want to delete this donation?');"
                               class="btn btn-danger btn-sm">
                                Delete
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="9" class="text-center">No donations found</td>
                </tr>
            <?php endif; ?>
            </tbody>

        </table>
    </div>

</div>

</body>
</html>
