<?php
session_start();
include('../config.php');

if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "camp_manager") {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION["id"];
$camp_id = null;

// Get manager's camp
$stmt = mysqli_prepare($conn, "SELECT id FROM relief_camps WHERE manager_id=?");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $camp_id);
mysqli_stmt_fetch($stmt);
mysqli_stmt_close($stmt);

if (!$camp_id) {
    die("<div class='alert alert-warning text-center'>You do not have a camp assigned yet.</div>");
}

// Handle Add/Edit
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['action'])) {
    $family_id = intval($_POST['family_id']);
    $resource_id = intval($_POST['resource_id']);
    $quantity = intval($_POST['quantity_given']);
    $action = $_POST['action'];

    if ($action === 'add') {
        $stmt = mysqli_prepare($conn, "INSERT INTO aid_distribution (family_id, resource_id, quantity_given, distributed_by) VALUES (?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "iiii", $family_id, $resource_id, $quantity, $user_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    } elseif ($action === 'edit' && isset($_POST['id'])) {
        $id = intval($_POST['id']);
        $stmt = mysqli_prepare($conn, "UPDATE aid_distribution SET family_id=?, resource_id=?, quantity_given=? WHERE id=? AND distributed_by=?");
        mysqli_stmt_bind_param($stmt, "iiiii", $family_id, $resource_id, $quantity, $id, $user_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
    header("Location: ".$_SERVER['PHP_SELF']);
    exit;
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = mysqli_prepare($conn, "DELETE FROM aid_distribution WHERE id=? AND distributed_by=?");
    mysqli_stmt_bind_param($stmt, "ii", $id, $user_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    header("Location: ".$_SERVER['PHP_SELF']);
    exit;
}

// Fetch Aid Distribution Records
$query = "SELECT ad.id, af.head_name, r.resource_name, ad.quantity_given, ad.distribution_date
          FROM aid_distribution ad
          JOIN affected_families af ON ad.family_id = af.id
          JOIN resources r ON ad.resource_id = r.id
          WHERE af.id IN (SELECT family_id FROM family_camp_link WHERE camp_id=?)";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $camp_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$records = mysqli_fetch_all($result, MYSQLI_ASSOC);
mysqli_stmt_close($stmt);

// Fetch families & resources for dropdowns
$families = mysqli_query($conn, "SELECT af.id, af.head_name FROM affected_families af 
                                 JOIN family_camp_link fcl ON af.id=fcl.family_id 
                                 WHERE fcl.camp_id=$camp_id");
$resources = mysqli_query($conn, "SELECT * FROM resources WHERE camp_id=$camp_id");

mysqli_close($conn);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Aid Distribution Management</title>
    <meta charset="utf-8">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">

</head>
<body>
<?php include('navbar.php'); ?>

<div class="container mt-5">
    <h3 class="text-center mb-4">Aid Distribution Records</h3>

    <div class="text-right mb-3">
        <button class="btn btn-success" data-toggle="modal" data-target="#aidModal" onclick="openAddModal()">Add Distribution</button>
    </div>

    <table class="table table-bordered table-striped">
        <thead class="thead-dark">
            <tr>
                <th>Family Head</th>
                <th>Resource</th>
                <th>Quantity Given</th>
                <th>Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($records as $row): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['head_name']); ?></td>
                <td><?php echo htmlspecialchars($row['resource_name']); ?></td>
                <td><?php echo htmlspecialchars($row['quantity_given']); ?></td>
                <td><?php echo htmlspecialchars($row['distribution_date']); ?></td>
                <td>
                    <button class="btn btn-primary btn-sm" 
                            data-toggle="modal" data-target="#aidModal"
                            onclick="openEditModal(<?php echo $row['id']; ?>, <?php echo $row['family_id'] ?? 0; ?>, <?php echo $row['resource_id'] ?? 0; ?>, <?php echo $row['quantity_given']; ?>)">
                        Edit
                    </button>
                    <a href="?delete=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this record?')">Delete</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Modal for Add/Edit -->
<div class="modal fade" id="aidModal" tabindex="-1" role="dialog" aria-labelledby="aidModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <form method="POST" class="modal-content">
        <input type="hidden" name="id" id="aid_id">
        <input type="hidden" name="action" id="aid_action" value="add">
        <div class="modal-header">
            <h5 class="modal-title" id="aidModalLabel">Add Aid Distribution</h5>
            <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label>Family</label>
                <select name="family_id" id="family_id" class="form-control" required>
                    <option value="">Select Family</option>
                    <?php while($f = mysqli_fetch_assoc($families)): ?>
                        <option value="<?php echo $f['id']; ?>"><?php echo htmlspecialchars($f['head_name']); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Resource</label>
                <select name="resource_id" id="resource_id" class="form-control" required>
                    <option value="">Select Resource</option>
                    <?php while($r = mysqli_fetch_assoc($resources)): ?>
                        <option value="<?php echo $r['id']; ?>"><?php echo htmlspecialchars($r['resource_name']); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Quantity Given</label>
                <input type="number" name="quantity_given" id="quantity_given" class="form-control" min="1" required>
            </div>
        </div>
        <div class="modal-footer">
            <button type="submit" class="btn btn-success">Save</button>
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
        </div>
    </form>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
function openAddModal() {
    document.getElementById('aidModalLabel').innerText = 'Add Aid Distribution';
    document.getElementById('aid_action').value = 'add';
    document.getElementById('aid_id').value = '';
    document.getElementById('family_id').value = '';
    document.getElementById('resource_id').value = '';
    document.getElementById('quantity_given').value = '';
}

function openEditModal(id, family_id, resource_id, quantity) {
    document.getElementById('aidModalLabel').innerText = 'Edit Aid Distribution';
    document.getElementById('aid_action').value = 'edit';
    document.getElementById('aid_id').value = id;
    document.getElementById('family_id').value = family_id;
    document.getElementById('resource_id').value = resource_id;
    document.getElementById('quantity_given').value = quantity;
}
</script>

</body>
</html>
