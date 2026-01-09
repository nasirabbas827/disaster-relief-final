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
    $camp_id   = $_POST['camp_id'];
    $status    = $_POST['status'];

    if (!empty($_POST['edit_id'])) {

        $id = $_POST['edit_id'];
        $stmt = $conn->prepare("
            UPDATE family_camp_link
            SET family_id=?, camp_id=?, status=?, added_by=?
            WHERE id=? AND added_by=?
        ");
        $stmt->bind_param(
            "iissii",
            $family_id, $camp_id, $status,
            $user_id, $id, $user_id
        );

    } else {

        $stmt = $conn->prepare("
            INSERT INTO family_camp_link
            (family_id, camp_id, status, added_by)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->bind_param("iisi", $family_id, $camp_id, $status, $user_id);
    }

    $stmt->execute();
    $stmt->close();
    header("Location: manage_family_camp.php");
    exit;
}

/* DELETE */
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM family_camp_link WHERE id=$id AND added_by=$user_id");
    header("Location: manage_family_camp.php");
    exit;
}

/* FETCH DATA */
$families = $conn->query("SELECT id, head_name FROM affected_families");
$camps    = $conn->query("SELECT id, camp_name FROM relief_camps");

$result = $conn->query("
    SELECT fcl.*, af.head_name, rc.camp_name
    FROM family_camp_link fcl
    JOIN affected_families af ON af.id = fcl.family_id
    JOIN relief_camps rc ON rc.id = fcl.camp_id
    WHERE fcl.added_by = $user_id
    ORDER BY fcl.id DESC
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Family Camp Linking</title>
    <meta charset="utf-8">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<?php include('navbar.php'); ?>

<div class="container mt-4">

    <div>
        <h4>Family ↔ Camp Status</h4>
        <button class="btn btn-primary" data-toggle="modal" data-target="#linkModal">
            Link Family to Camp
        </button>
    </div>

    <table class="table table-bordered table-striped">
        <thead class="thead-dark">
        <tr>
            <th>Family Head</th>
            <th>Camp</th>
            <th>Status</th>
            <th>Last Updated</th>
            <th width="180">Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['head_name']) ?></td>
                <td><?= htmlspecialchars($row['camp_name']) ?></td>
                <td><?= ucfirst(str_replace('_',' ', $row['status'])) ?></td>
                <td><?= $row['last_updated'] ?></td>
                <td>
                    <button class="btn btn-sm btn-info"
                        onclick='editLink(<?= json_encode($row) ?>)'>Edit</button>

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
<div class="modal fade" id="linkModal">
    <div class="modal-dialog">
        <form method="post" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Family Camp Link</h5>
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
                    <label>Relief Camp</label>
                    <select name="camp_id" id="camp_id" class="form-control" required>
                        <option value="">Select Camp</option>
                        <?php while ($c = $camps->fetch_assoc()): ?>
                            <option value="<?= $c['id'] ?>">
                                <?= htmlspecialchars($c['camp_name']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Status</label>
                    <select name="status" id="status" class="form-control">
                        <option value="registered">Registered</option>
                        <option value="relocated">Relocated to Camp</option>
                        <option value="aid_received">Aid Received</option>
                        <option value="medical_required">Medical Assistance Required</option>
                    </select>
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
function editLink(data) {
    $('#edit_id').val(data.id);
    $('#family_id').val(data.family_id);
    $('#camp_id').val(data.camp_id);
    $('#status').val(data.status);
    $('#linkModal').modal('show');
}
</script>

</body>
</html>
