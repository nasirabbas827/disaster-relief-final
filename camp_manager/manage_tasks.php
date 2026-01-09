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
        $title = $_POST['title'];
        $description = $_POST['description'];
        $required_skills = $_POST['required_skills'];

        $stmt = mysqli_prepare($conn, "INSERT INTO volunteer_tasks (camp_id, title, description, required_skills) VALUES (?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "isss", $camp_id, $title, $description, $required_skills);
        if (mysqli_stmt_execute($stmt)) {
            $message = '<div class="alert alert-success">Task added successfully.</div>';
        } else {
            $message = '<div class="alert alert-danger">Failed to add task.</div>';
        }
        mysqli_stmt_close($stmt);
    }

    if ($action == 'update') {
        $task_id = intval($_POST['task_id']);
        $title = $_POST['title'];
        $description = $_POST['description'];
        $required_skills = $_POST['required_skills'];
        $status = $_POST['status'];

        $stmt = mysqli_prepare($conn, "UPDATE volunteer_tasks SET title=?, description=?, required_skills=?, status=?, created_at=NOW() WHERE id=? AND camp_id=?");
        mysqli_stmt_bind_param($stmt, "ssssii", $title, $description, $required_skills, $status, $task_id, $camp_id);
        if (mysqli_stmt_execute($stmt)) {
            $message = '<div class="alert alert-success">Task updated successfully.</div>';
        } else {
            $message = '<div class="alert alert-danger">Failed to update task.</div>';
        }
        mysqli_stmt_close($stmt);
    }

    if ($action == 'delete') {
        $task_id = intval($_POST['task_id']);
        $stmt = mysqli_prepare($conn, "DELETE FROM volunteer_tasks WHERE id=? AND camp_id=?");
        mysqli_stmt_bind_param($stmt, "ii", $task_id, $camp_id);
        if (mysqli_stmt_execute($stmt)) {
            $message = '<div class="alert alert-success">Task deleted successfully.</div>';
        } else {
            $message = '<div class="alert alert-danger">Failed to delete task.</div>';
        }
        mysqli_stmt_close($stmt);
    }
}

/* ================= FETCH TASKS ================= */
$stmt = mysqli_prepare($conn, "SELECT id, title, description, required_skills, status, created_at FROM volunteer_tasks WHERE camp_id=? ORDER BY created_at DESC");
mysqli_stmt_bind_param($stmt, "i", $camp_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
mysqli_stmt_close($stmt);
mysqli_close($conn);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Volunteer Tasks</title>
    <meta charset="utf-8">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include('navbar.php'); ?>

<div class="container mt-5">
    <h3 class="text-center mb-4">Manage Volunteer Tasks</h3>
    <?php echo $message; ?>

    <!-- Add Button -->
    <div class="text-right mb-3">
        <button class="btn btn-success" data-toggle="modal" data-target="#addTaskModal">Add New Task</button>
    </div>

    <!-- Tasks Table -->
    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead class="thead-dark">
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Description</th>
                    <th>Required Skills</th>
                    <th>Status</th>
                    <th>Created At</th>
                    <th width="150">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= htmlspecialchars($row['title']) ?></td>
                    <td><?= htmlspecialchars($row['description']) ?></td>
                    <td><?= htmlspecialchars($row['required_skills']) ?></td>
                    <td><?= ucfirst($row['status']) ?></td>
                    <td><?= $row['created_at'] ?></td>
                    <td>
                        <button class="btn btn-primary btn-sm mb-1" 
                            data-toggle="modal" 
                            data-target="#editTaskModal<?= $row['id'] ?>">Edit</button>
                        <form method="POST" style="display:inline-block;">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="task_id" value="<?= $row['id'] ?>">
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Delete this task?');">Delete</button>
                        </form>
                    </td>
                </tr>

                <!-- Edit Modal -->
                <div class="modal fade" id="editTaskModal<?= $row['id'] ?>" tabindex="-1" role="dialog">
                  <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                      <form method="POST">
                        <div class="modal-header">
                          <h5 class="modal-title">Edit Task</h5>
                          <button type="button" class="close" data-dismiss="modal">&times;</button>
                        </div>
                        <div class="modal-body">
                          <input type="hidden" name="action" value="update">
                          <input type="hidden" name="task_id" value="<?= $row['id'] ?>">

                          <div class="form-row">
                              <div class="form-group col-md-6">
                                  <label>Title</label>
                                  <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($row['title']) ?>" required>
                              </div>
                              <div class="form-group col-md-6">
                                  <label>Status</label>
                                  <select name="status" class="form-control">
                                      <?php
                                      $statuses = ['open','assigned','completed'];
                                      foreach($statuses as $s){
                                          $sel = ($row['status']==$s)?'selected':'';
                                          echo "<option value='$s' $sel>".ucfirst($s)."</option>";
                                      }
                                      ?>
                                  </select>
                              </div>
                          </div>
                          <div class="form-group">
                              <label>Description</label>
                              <textarea name="description" class="form-control" rows="2"><?= htmlspecialchars($row['description']) ?></textarea>
                          </div>
                          <div class="form-group">
                              <label>Required Skills</label>
                              <input type="text" name="required_skills" class="form-control" value="<?= htmlspecialchars($row['required_skills']) ?>">
                          </div>
                        </div>
                        <div class="modal-footer">
                          <button type="submit" class="btn btn-primary">Update Task</button>
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
<div class="modal fade" id="addTaskModal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <form method="POST">
        <div class="modal-header">
          <h5 class="modal-title">Add New Task</h5>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="action" value="add">
          <div class="form-row">
              <div class="form-group col-md-6">
                  <label>Title</label>
                  <input type="text" name="title" class="form-control" required>
              </div>
              <div class="form-group col-md-6">
                  <label>Status</label>
                  <select name="status" class="form-control">
                      <option value="open" selected>Open</option>
                      <option value="assigned">Assigned</option>
                      <option value="completed">Completed</option>
                  </select>
              </div>
          </div>
          <div class="form-group">
              <label>Description</label>
              <textarea name="description" class="form-control" rows="2"></textarea>
          </div>
          <div class="form-group">
              <label>Required Skills</label>
              <input type="text" name="required_skills" class="form-control">
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success">Add Task</button>
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
