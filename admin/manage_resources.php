<?php
include('config.php');
session_start();

if (!isset($_SESSION["usertype"]) || $_SESSION["usertype"] !== "admin") {
    header("Location: admin_login.php");
    exit;
}

/* =========================
   ADD / UPDATE
========================= */
if (isset($_POST['save_resource'])) {

    $resource_name = $_POST['resource_name'];
    $category = $_POST['category'];
    $quantity = $_POST['quantity'];
    $unit = $_POST['unit'];
    $location = $_POST['current_location'];
    $camp_id = $_POST['camp_id'];

    if (!empty($_POST['id'])) {
        // UPDATE
        $stmt = $conn->prepare("
            UPDATE resources 
            SET resource_name=?, category=?, quantity=?, unit=?, current_location=?, camp_id=?
            WHERE id=?
        ");
        $stmt->bind_param(
            "ssissii",
            $resource_name, $category, $quantity, $unit, $location, $camp_id, $_POST['id']
        );
    } else {
        // INSERT
        $stmt = $conn->prepare("
            INSERT INTO resources (resource_name, category, quantity, unit, current_location, camp_id)
            VALUES (?,?,?,?,?,?)
        ");
        $stmt->bind_param(
            "ssissi",
            $resource_name, $category, $quantity, $unit, $location, $camp_id
        );
    }

    $stmt->execute();
    $stmt->close();
    header("Location: manage_resources.php");
    exit;
}

/* =========================
   DELETE
========================= */
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM resources WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: manage_resources.php");
    exit;
}

/* =========================
   FETCH DATA
========================= */
$resources = mysqli_query($conn, "
    SELECT r.*, c.camp_name AS camp_name
    FROM resources r
    JOIN relief_camps c ON r.camp_id = c.id
    ORDER BY r.id DESC
");

$camps = mysqli_query($conn, "SELECT id, camp_name FROM relief_camps");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Resources</title>
    <link rel="stylesheet"
          href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">

</head>

<body>

<?php include('admin_navbar.php'); ?>

<div class="container mt-5">

    <div>
        <h3>Manage Resources</h3>
        <button class="btn btn-success" data-toggle="modal" data-target="#resourceModal"
                onclick="clearForm()">Add Resource</button>
    </div>

    <table class="table table-bordered table-striped">
        <thead class="thead-dark">
        <tr>
            <th>ID</th>
            <th>Resource</th>
            <th>Category</th>
            <th>Quantity</th>
            <th>Unit</th>
            <th>Location</th>
            <th>Camp</th>
            <th width="180">Actions</th>
        </tr>
        </thead>

        <tbody>
        <?php while ($row = mysqli_fetch_assoc($resources)): ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['resource_name']) ?></td>
                <td><?= htmlspecialchars($row['category']) ?></td>
                <td><?= $row['quantity'] ?></td>
                <td><?= $row['unit'] ?></td>
                <td><?= htmlspecialchars($row['current_location']) ?></td>
                <td><?= htmlspecialchars($row['camp_name']) ?></td>
                <td>
                    <button class="btn btn-primary btn-sm"
                        onclick='editResource(<?= json_encode($row) ?>)'>
                        Edit
                    </button>

                    <a href="?delete=<?= $row['id'] ?>"
                       onclick="return confirm('Delete this resource?')"
                       class="btn btn-danger btn-sm">
                        Delete
                    </a>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>

</div>

<!-- =========================
     MODAL FORM
========================= -->
<div class="modal fade" id="resourceModal">
    <div class="modal-dialog">
        <form method="post" class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Resource</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>

            <div class="modal-body">

                <input type="hidden" name="id" id="id">

                <div class="form-group">
                    <label>Resource Name</label>
                    <input type="text" name="resource_name" id="resource_name" class="form-control" required>
                </div>

                <div class="form-group">
                    <label>Category</label>
                    <input type="text" name="category" id="category" class="form-control">
                </div>

                <div class="form-row">
                    <div class="form-group col">
                        <label>Quantity</label>
                        <input type="number" name="quantity" id="quantity" class="form-control">
                    </div>

                    <div class="form-group col">
                        <label>Unit</label>
                        <input type="text" name="unit" id="unit" class="form-control">
                    </div>
                </div>

                <div class="form-group">
                    <label>Current Location</label>
                    <input type="text" name="current_location" id="current_location" class="form-control">
                </div>

                <div class="form-group">
                    <label>Camp</label>
                    <select name="camp_id" id="camp_id" class="form-control" required>
                        <option value="">Select Camp</option>
                        <?php while ($c = mysqli_fetch_assoc($camps)): ?>
                            <option value="<?= $c['id'] ?>"><?= $c['camp_name'] ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

            </div>

            <div class="modal-footer">
                <button type="submit" name="save_resource" class="btn btn-primary">Save</button>
            </div>

        </form>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
function editResource(data) {
    document.getElementById('id').value = data.id;
    document.getElementById('resource_name').value = data.resource_name;
    document.getElementById('category').value = data.category;
    document.getElementById('quantity').value = data.quantity;
    document.getElementById('unit').value = data.unit;
    document.getElementById('current_location').value = data.current_location;
    document.getElementById('camp_id').value = data.camp_id;

    $('#resourceModal').modal('show');
}

function clearForm() {
    document.querySelector('form').reset();
    document.getElementById('id').value = '';
}
</script>

</body>
</html>
