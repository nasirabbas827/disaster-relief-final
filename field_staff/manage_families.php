<?php
session_start();
include('../config.php');

if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "field_staff") {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION["id"];
$name = $_SESSION["name"];
$message = "";

/* ================= ADD / EDIT ================= */
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $head_name = $_POST['head_name'];
    $contact = $_POST['contact'];
    $family_size = intval($_POST['family_size']);
    $original_location = $_POST['original_location'];

    if ($_POST['action'] === 'add') {
        $stmt = $conn->prepare("
            INSERT INTO affected_families 
            (head_name, contact, family_size, original_location, registered_by) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("ssisi", $head_name, $contact, $family_size, $original_location, $user_id);
        $stmt->execute();
        $stmt->close();
        $message = "<div class='alert alert-success'>Family registered successfully.</div>";
    }

    if ($_POST['action'] === 'edit') {
        $family_id = intval($_POST['family_id']);
        $stmt = $conn->prepare("
            UPDATE affected_families 
            SET head_name=?, contact=?, family_size=?, original_location=? 
            WHERE id=? AND registered_by=?
        ");
        $stmt->bind_param("ssiiii", $head_name, $contact, $family_size, $original_location, $family_id, $user_id);
        $stmt->execute();
        $stmt->close();
        $message = "<div class='alert alert-success'>Family updated successfully.</div>";
    }
}

/* ================= DELETE ================= */
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM affected_families WHERE id=$id AND registered_by=$user_id");
    $message = "<div class='alert alert-success'>Family deleted successfully.</div>";
}

/* ================= FETCH ================= */
$result = $conn->query("
    SELECT * FROM affected_families 
    WHERE registered_by = $user_id 
    ORDER BY registered_at DESC
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Affected Families</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">

</head>

<body>
<?php include('navbar.php'); ?>

<div class="container mt-4">

    <h3 class="mb-3">Affected Families – Field Staff</h3>
    <?= $message ?>

    <button class="btn btn-success mb-3" data-toggle="modal" data-target="#addModal">
        Add Family
    </button>

    <table class="table table-bordered table-striped">
        <thead class="thead-dark">
            <tr>
                <th>#</th>
                <th>Head Name</th>
                <th>Contact</th>
                <th>Family Size</th>
                <th>Original Location</th>
                <th>Registered At</th>
                <th width="160">Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($row = $result->fetch_assoc()) { ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['head_name']) ?></td>
                <td><?= htmlspecialchars($row['contact']) ?></td>
                <td><?= $row['family_size'] ?></td>
                <td><?= htmlspecialchars($row['original_location']) ?></td>
                <td><?= $row['registered_at'] ?></td>
                <td>
                    <button class="btn btn-primary btn-sm m-2" data-toggle="modal"
                        data-target="#edit<?= $row['id'] ?>">Edit</button>
                    <a href="?delete=<?= $row['id'] ?>" 
                       class="btn btn-danger btn-sm"
                       onclick="return confirm('Delete this record?')">Delete</a>
                </td>
            </tr>

            <!-- EDIT MODAL -->
            <div class="modal fade" id="edit<?= $row['id'] ?>">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form method="POST">
                            <div class="modal-header">
                                <h5>Edit Family</h5>
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                            </div>
                            <div class="modal-body">
                                <input type="hidden" name="action" value="edit">
                                <input type="hidden" name="family_id" value="<?= $row['id'] ?>">

                                <div class="form-row">
                                    <div class="col-md-6 form-group">
                                        <label>Head Name</label>
                                        <input type="text" name="head_name" class="form-control" value="<?= $row['head_name'] ?>" required>
                                    </div>
                                    <div class="col-md-6 form-group">
                                        <label>Contact</label>
                                        <input type="text" name="contact" class="form-control" value="<?= $row['contact'] ?>">
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="col-md-6 form-group">
                                        <label>Family Size</label>
                                        <input type="number" name="family_size" class="form-control" value="<?= $row['family_size'] ?>" required>
                                    </div>
                                    <div class="col-md-6 form-group">
                                        <label>Original Location</label>
                                        <input type="text" name="original_location" class="form-control" value="<?= $row['original_location'] ?>">
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button class="btn btn-primary">Update</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        <?php } ?>
        </tbody>
    </table>
</div>

<!-- ADD MODAL -->
<div class="modal fade" id="addModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5>Add Family</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">

                    <div class="form-row">
                        <div class="col-md-6 form-group">
                            <label>Head Name</label>
                            <input type="text" name="head_name" class="form-control" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Contact</label>
                            <input type="text" name="contact" class="form-control">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="col-md-6 form-group">
                            <label>Family Size</label>
                            <input type="number" name="family_size" class="form-control" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Original Location</label>
                            <input type="text" name="original_location" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-success">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
