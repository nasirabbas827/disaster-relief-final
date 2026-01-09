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

    $family_id      = $_POST['family_id'];
    $house_damage   = isset($_POST['house_damage']) ? 1 : 0;
    $livestock_loss = isset($_POST['livestock_loss']) ? 1 : 0;
    $crop_loss      = isset($_POST['crop_loss']) ? 1 : 0;
    $notes          = $_POST['notes'];

    if (!empty($_POST['edit_id'])) {

        $id = $_POST['edit_id'];
        $stmt = $conn->prepare("
            UPDATE family_losses SET 
            family_id=?, house_damage=?, livestock_loss=?, crop_loss=?, notes=?
            WHERE id=? AND added_by=?
        ");
        $stmt->bind_param(
            "sssssss",
            $family_id, $house_damage, $livestock_loss, $crop_loss, $notes,
            $id, $user_id
        );
    } else {

        $stmt = $conn->prepare("
            INSERT INTO family_losses
            (family_id, house_damage, livestock_loss, crop_loss, notes, added_by)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param(
            "ssssss",
            $family_id, $house_damage, $livestock_loss, $crop_loss, $notes, $user_id
        );
    }

    $stmt->execute();
    $stmt->close();
    header("Location: manage_family_losses.php");
    exit;
}

/* DELETE */
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM family_losses WHERE id=$id AND added_by=$user_id");
    header("Location: manage_family_losses.php");
    exit;
}

/* FETCH DATA */
$families = $conn->query("SELECT id, head_name FROM affected_families");
$result = $conn->query("
    SELECT fl.*, af.head_name
    FROM family_losses fl
    JOIN affected_families af ON af.id = fl.family_id
    WHERE fl.added_by = $user_id
    ORDER BY fl.id DESC
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Family Losses</title>
    <meta charset="utf-8">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<?php include('navbar.php'); ?>

<div class="container mt-4">

    <div >
        <h4>Family Loss Records</h4>
        <button class="btn btn-primary" data-toggle="modal" data-target="#lossModal">
            Add Loss Record
        </button>
    </div>

    <table class="table table-bordered table-striped">
        <thead class="thead-dark">
        <tr>
            <th>Family</th>
            <th>House</th>
            <th>Livestock</th>
            <th>Crop</th>
            <th>Notes</th>
            <th width="180">Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['head_name']) ?></td>
                <td><?= $row['house_damage'] ? 'Yes' : 'No' ?></td>
                <td><?= $row['livestock_loss'] ? 'Yes' : 'No' ?></td>
                <td><?= $row['crop_loss'] ? 'Yes' : 'No' ?></td>
                <td><?= htmlspecialchars($row['notes']) ?></td>
                <td>
                    <button class="btn btn-sm btn-info"
                        onclick='editLoss(<?= json_encode($row) ?>)'>Edit</button>

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
<div class="modal fade" id="lossModal">
    <div class="modal-dialog">
        <form method="post" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Family Loss</h5>
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

                <div class="form-row">
                    <div class="col-md-4">
                        <input type="checkbox" name="house_damage" id="house_damage"> House Damage
                    </div>
                    <div class="col-md-4">
                        <input type="checkbox" name="livestock_loss" id="livestock_loss"> Livestock Loss
                    </div>
                    <div class="col-md-4">
                        <input type="checkbox" name="crop_loss" id="crop_loss"> Crop Loss
                    </div>
                </div>

                <div class="form-group mt-2">
                    <label>Notes</label>
                    <textarea name="notes" id="notes" class="form-control"></textarea>
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
function editLoss(data) {
    $('#edit_id').val(data.id);
    $('#family_id').val(data.family_id);
    $('#house_damage').prop('checked', data.house_damage == 1);
    $('#livestock_loss').prop('checked', data.livestock_loss == 1);
    $('#crop_loss').prop('checked', data.crop_loss == 1);
    $('#notes').val(data.notes);
    $('#lossModal').modal('show');
}
</script>

</body>
</html>
