<?php
session_start();
include('../config.php');

if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "camp_manager") {
    header("Location: ../login.php");
    exit;
}

$manager_id = $_SESSION["id"];
$message = "";

/* ================= GET CAMP ID ================= */
$stmt = mysqli_prepare($conn, "SELECT id FROM relief_camps WHERE manager_id=?");
mysqli_stmt_bind_param($stmt, "i", $manager_id);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $camp_id);
mysqli_stmt_fetch($stmt);
mysqli_stmt_close($stmt);

if (!$camp_id) {
    die('<div class="alert alert-warning text-center">You do not have an assigned camp yet.</div>');
}

/* ================= HANDLE CRUD ================= */
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST['action'];

    if ($action == 'add') {
        $resource_type = $_POST['resource_type'];
        $quantity_required = intval($_POST['quantity_required']);
        $unit_type = $_POST['unit_type'];
        $priority_level = $_POST['priority_level'];

        $stmt = mysqli_prepare($conn, "INSERT INTO camp_needs (camp_id, resource_type, quantity_required, unit_type, priority_level) VALUES (?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "isiss", $camp_id, $resource_type, $quantity_required, $unit_type, $priority_level);
        if (mysqli_stmt_execute($stmt)) {
            $message = '<div class="alert alert-success">Resource need added successfully.</div>';
        } else {
            $message = '<div class="alert alert-danger">Failed to add need.</div>';
        }
        mysqli_stmt_close($stmt);
    }

    if ($action == 'update') {
        $need_id = intval($_POST['need_id']);
        $resource_type = $_POST['resource_type'];
        $quantity_required = intval($_POST['quantity_required']);
        $unit_type = $_POST['unit_type'];
        $priority_level = $_POST['priority_level'];
        $status = $_POST['status'];

        $stmt = mysqli_prepare($conn, "UPDATE camp_needs SET resource_type=?, quantity_required=?, unit_type=?, priority_level=?, status=?, updated_at=NOW() WHERE id=? AND camp_id=?");
        mysqli_stmt_bind_param($stmt, "sisisii", $resource_type, $quantity_required, $unit_type, $priority_level, $status, $need_id, $camp_id);
        if (mysqli_stmt_execute($stmt)) {
            $message = '<div class="alert alert-success">Resource need updated successfully.</div>';
        } else {
            $message = '<div class="alert alert-danger">Failed to update need.</div>';
        }
        mysqli_stmt_close($stmt);
    }

    if ($action == 'delete') {
        $need_id = intval($_POST['need_id']);
        $stmt = mysqli_prepare($conn, "DELETE FROM camp_needs WHERE id=? AND camp_id=?");
        mysqli_stmt_bind_param($stmt, "ii", $need_id, $camp_id);
        if (mysqli_stmt_execute($stmt)) {
            $message = '<div class="alert alert-success">Resource need deleted successfully.</div>';
        } else {
            $message = '<div class="alert alert-danger">Failed to delete need.</div>';
        }
        mysqli_stmt_close($stmt);
    }
}

/* ================= FETCH NEEDS ================= */
$stmt = mysqli_prepare($conn, "SELECT id, resource_type, quantity_required, unit_type, priority_level, status, updated_at FROM camp_needs WHERE camp_id=? ORDER BY updated_at DESC");
mysqli_stmt_bind_param($stmt, "i", $camp_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
mysqli_stmt_close($stmt);
mysqli_close($conn);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Camp Needs</title>
    <meta charset="utf-8">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include('navbar.php'); ?>

<div class="container mt-5">
    <h3 class="text-center mb-4">Manage Your Camp Needs</h3>
    <?php echo $message; ?>

    <!-- Button to trigger Add Modal -->
    <div class="text-right mb-3">
        <button class="btn btn-success" data-toggle="modal" data-target="#addNeedModal">Add New Need</button>
    </div>

    <!-- ================== NEEDS TABLE ================== -->
    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead class="thead-dark">
                <tr>
                    <th>ID</th>
                    <th>Resource</th>
                    <th>Quantity</th>
                    <th>Unit</th>
                    <th>Priority</th>
                    <th>Status</th>
                    <th>Last Updated</th>
                    <th width="150">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= ucfirst($row['resource_type']) ?></td>
                    <td><?= $row['quantity_required'] ?></td>
                    <td><?= $row['unit_type'] ?></td>
                    <td><?= ucfirst($row['priority_level']) ?></td>
                    <td><?= ucfirst($row['status']) ?></td>
                    <td><?= $row['updated_at'] ?></td>
                    <td>
                        <button class="btn btn-primary btn-sm mb-1" 
                            data-toggle="modal" 
                            data-target="#editNeedModal<?= $row['id'] ?>">Edit</button>
                        <form method="POST" style="display:inline-block;">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="need_id" value="<?= $row['id'] ?>">
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Delete this need?');">Delete</button>
                        </form>
                    </td>
                </tr>

                <!-- Edit Modal -->
                <div class="modal fade" id="editNeedModal<?= $row['id'] ?>" tabindex="-1" role="dialog">
                  <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                      <form method="POST">
                        <div class="modal-header">
                          <h5 class="modal-title">Edit Resource Need</h5>
                          <button type="button" class="close" data-dismiss="modal">&times;</button>
                        </div>
                        <div class="modal-body">
                          <input type="hidden" name="action" value="update">
                          <input type="hidden" name="need_id" value="<?= $row['id'] ?>">

                          <div class="form-row">
                              <div class="form-group col-md-4">
                                  <label>Resource Type</label>
                                  <select name="resource_type" class="form-control">
                                      <?php 
                                      $types = ['food','water','medicine','shelter','clothing','other'];
                                      foreach ($types as $t) {
                                          $sel = ($row['resource_type']==$t)?'selected':'';
                                          echo "<option value='$t' $sel>".ucfirst($t)."</option>";
                                      }
                                      ?>
                                  </select>
                              </div>
                              <div class="form-group col-md-2">
                                  <label>Quantity</label>
                                  <input type="number" name="quantity_required" value="<?= $row['quantity_required'] ?>" class="form-control" min="1">
                              </div>
                              <div class="form-group col-md-2">
                                  <label>Unit</label>
                                  <select name="unit_type" class="form-control">
                                      <?php
                                      $units = ['kg','litre','pcs','other'];
                                      foreach($units as $u){
                                          $sel = ($row['unit_type']==$u)?'selected':'';
                                          echo "<option value='$u' $sel>$u</option>";
                                      }
                                      ?>
                                  </select>
                              </div>
                              <div class="form-group col-md-2">
                                  <label>Priority</label>
                                  <select name="priority_level" class="form-control">
                                      <?php
                                      $levels = ['low','medium','high','critical'];
                                      foreach($levels as $l){
                                          $sel = ($row['priority_level']==$l)?'selected':'';
                                          echo "<option value='$l' $sel>".ucfirst($l)."</option>";
                                      }
                                      ?>
                                  </select>
                              </div>
                              <div class="form-group col-md-2">
                                  <label>Status</label>
                                  <select name="status" class="form-control">
                                      <?php
                                      $statuses = ['pending','partial','fulfilled'];
                                      foreach($statuses as $s){
                                          $sel = ($row['status']==$s)?'selected':'';
                                          echo "<option value='$s' $sel>".ucfirst($s)."</option>";
                                      }
                                      ?>
                                  </select>
                              </div>
                          </div>
                        </div>
                        <div class="modal-footer">
                          <button type="submit" class="btn btn-primary">Update Need</button>
                          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        </div>
                      </form>
                    </div>
                  </div>
                </div>

                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addNeedModal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <form method="POST">
        <div class="modal-header">
          <h5 class="modal-title">Add New Resource Need</h5>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="action" value="add">
          <div class="form-row">
              <div class="form-group col-md-3">
                  <label>Resource Type</label>
                  <select name="resource_type" class="form-control">
                      <option value="food">Food</option>
                      <option value="water">Water</option>
                      <option value="medicine">Medicine</option>
                      <option value="shelter">Shelter</option>
                      <option value="clothing">Clothing</option>
                      <option value="other">Other</option>
                  </select>
              </div>
              <div class="form-group col-md-2">
                  <label>Quantity</label>
                  <input type="number" name="quantity_required" class="form-control" min="1" required>
              </div>
              <div class="form-group col-md-2">
                  <label>Unit</label>
                  <select name="unit_type" class="form-control">
                      <option value="kg">kg</option>
                      <option value="litre">litre</option>
                      <option value="pcs" selected>pcs</option>
                      <option value="other">Other</option>
                  </select>
              </div>
              <div class="form-group col-md-3">
                  <label>Priority Level</label>
                  <select name="priority_level" class="form-control">
                      <option value="low">Low</option>
                      <option value="medium">Medium</option>
                      <option value="high">High</option>
                      <option value="critical">Critical</option>
                  </select>
              </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success">Add Need</button>
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
