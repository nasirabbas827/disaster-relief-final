<?php
session_start();
include('config.php');

if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "field_staff") {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION["id"];
$name    = $_SESSION["name"];

/* ADD / UPDATE */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $family_id = $_POST['family_id'];
    $need_type = $_POST['need_type'];
    $details   = $_POST['details'];

    if (!empty($_POST['edit_id'])) {

        $id = $_POST['edit_id'];
        $stmt = $conn->prepare("
            UPDATE family_special_needs
            SET family_id=?, need_type=?, details=?
            WHERE id=? AND added_by=?
        ");
        $stmt->bind_param("issii", $family_id, $need_type, $details, $id, $user_id);

    } else {

        $stmt = $conn->prepare("
            INSERT INTO family_special_needs
            (family_id, need_type, details, added_by)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->bind_param("issi", $family_id, $need_type, $details, $user_id);
    }

    $stmt->execute();
    $stmt->close();
    header("Location: manage_special_needs.php");
    exit;
}

/* DELETE */
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM family_special_needs WHERE id=$id AND added_by=$user_id");
    header("Location: manage_special_needs.php");
    exit;
}

/* FETCH DATA */
$families = $conn->query("SELECT id, head_name FROM affected_families");

$result = $conn->query("
    SELECT fsn.*, af.head_name
    FROM family_special_needs fsn
    JOIN affected_families af ON af.id = fsn.family_id
    WHERE fsn.added_by = $user_id
    ORDER BY fsn.id DESC
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Special Needs</title>
    <meta charset="utf-8">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<?php include('navbar.php'); ?>

<div class="container mt-4">

    <div  >
        <h4>Family Special Needs</h4>
        <button class="btn btn-primary" data-toggle="modal" data-target="#needModal">
            Add Special Need
        </button>
    </div>

    <table class="table table-bordered table-striped">
        <thead class="thead-dark">
        <tr>
            <th>Family</th>
            <th>Need Type</th>
            <th>Details</th>
            <th width="180">Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['head_name']) ?></td>
                <td><?= ucfirst($row['need_type']) ?></td>
                <td><?= htmlspecialchars($row['details']) ?></td>
                <td>
                    <button class="btn btn-sm btn-info"
                        onclick='editNeed(<?= json_encode($row) ?>)'>Edit</button>

                    <a href="?delete=<?= $row['id'] ?>"
                       onclick="return confirm('Delete this record?')"
                       class="btn btn-sm btn-danger">Delete</a>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>

</div>

<!-- MODAL -->
<div class="modal fade" id="needModal">
    <div class="modal-dialog">
        <form method="post" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Special Need</h5>
                <button class="close" data-dismiss="modal">&times;</button>
            </div>

            <div class="modal-body">

                <input type="hidden" name="edit_id" id="edit_id">

                <div class="form-group">
                    <label>Family</label>
                    <select name="family_id" id="family_id" class="form-control" required>
                        <option value="">Select Family</option>
                        <?php while ($f = $families->fetch_assoc()): ?>
                            <option value="<?= $f['id'] ?>">
                                <?= htmlspecialchars($f['head_name']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Need Type</label>
                    <select name="need_type" id="need_type" class="form-control" required>
                        <option value="">Select</option>
                        <option value="medical">Medical</option>
                        <option value="elderly">Elderly</option>
                        <option value="children">Children</option>
                        <option value="disabled">Disabled</option>
                        <option value="other">Other</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Details</label>
                    <textarea name="details" id="details" class="form-control"></textarea>
                </div>

            </div>

            <div class="modal-footer">
                <button class="btn btn-success">Save</button>
                <button class="btn btn-secondary" data-dismiss="modal" type="button">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<script>
function editNeed(data) {
    $('#edit_id').val(data.id);
    $('#family_id').val(data.family_id);
    $('#need_type').val(data.need_type);
    $('#details').val(data.details);
    $('#needModal').modal('show');
}
</script>

</body>
</html>
