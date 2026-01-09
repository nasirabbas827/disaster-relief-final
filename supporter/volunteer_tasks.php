<?php
session_start();
include('../config.php');

if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "supporter") {
    header("Location: ../login.php");
    exit;
}

$volunteer_id = $_SESSION["id"];
$message = "";

/* ================= HANDLE TASK ACCEPT / COMPLETE ================= */
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST['action'];
    $task_id = intval($_POST['task_id']);

    if ($action == 'accept') {
        // Check if already assigned
        $stmt = mysqli_prepare($conn, "SELECT id FROM task_assignments WHERE task_id=? AND volunteer_id=?");
        mysqli_stmt_bind_param($stmt, "ii", $task_id, $volunteer_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        if (mysqli_stmt_num_rows($stmt) == 0) {
            $stmt2 = mysqli_prepare($conn, "INSERT INTO task_assignments (task_id, volunteer_id) VALUES (?, ?)");
            mysqli_stmt_bind_param($stmt2, "ii", $task_id, $volunteer_id);
            mysqli_stmt_execute($stmt2);
            mysqli_stmt_close($stmt2);

            // Update task status to assigned
            $stmt3 = mysqli_prepare($conn, "UPDATE volunteer_tasks SET status='assigned' WHERE id=?");
            mysqli_stmt_bind_param($stmt3, "i", $task_id);
            mysqli_stmt_execute($stmt3);
            mysqli_stmt_close($stmt3);

            $message = '<div class="alert alert-success">Task accepted successfully.</div>';
        } else {
            $message = '<div class="alert alert-warning">You already accepted this task.</div>';
        }
        mysqli_stmt_close($stmt);
    }

    if ($action == 'complete') {
        $stmt = mysqli_prepare($conn, "UPDATE task_assignments SET completion_status='completed' WHERE task_id=? AND volunteer_id=?");
        mysqli_stmt_bind_param($stmt, "ii", $task_id, $volunteer_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        // If all assignments completed, mark task as completed
        $stmt_check = mysqli_prepare($conn, "SELECT COUNT(*) FROM task_assignments WHERE task_id=? AND completion_status='pending'");
        mysqli_stmt_bind_param($stmt_check, "i", $task_id);
        mysqli_stmt_execute($stmt_check);
        mysqli_stmt_bind_result($stmt_check, $pending_count);
        mysqli_stmt_fetch($stmt_check);
        mysqli_stmt_close($stmt_check);

        if ($pending_count == 0) {
            $stmt_update = mysqli_prepare($conn, "UPDATE volunteer_tasks SET status='completed' WHERE id=?");
            mysqli_stmt_bind_param($stmt_update, "i", $task_id);
            mysqli_stmt_execute($stmt_update);
            mysqli_stmt_close($stmt_update);
        }

        $message = '<div class="alert alert-success">Task marked as completed.</div>';
    }
}

/* ================= FETCH TASKS ================= */
$search = isset($_GET['search']) ? "%".trim($_GET['search'])."%" : "%";
$stmt = mysqli_prepare($conn, "
    SELECT vt.id, vt.camp_id, vt.title, vt.description, vt.required_skills, vt.status, vt.created_at,
           ta.completion_status
    FROM volunteer_tasks vt
    LEFT JOIN task_assignments ta ON vt.id=ta.task_id AND ta.volunteer_id=?
    WHERE vt.title LIKE ? OR vt.required_skills LIKE ?
    ORDER BY vt.created_at DESC
");
mysqli_stmt_bind_param($stmt, "iss", $volunteer_id, $search, $search);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
mysqli_stmt_close($stmt);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Volunteer Tasks</title>
    <meta charset="utf-8">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include('navbar.php'); ?>

<div class="container mt-5">
    <h3 class="text-center mb-4">Available Volunteer Tasks</h3>
    <?php echo $message; ?>

    <form method="GET" class="form-inline mb-3">
        <input type="text" name="search" class="form-control mr-2" placeholder="Search tasks by title or skills">
        <button type="submit" class="btn btn-primary">Search</button>
    </form>

    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead class="thead-dark">
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Description</th>
                    <th>Skills</th>
                    <th>Status</th>
                    <th>Your Status</th>
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
                    <td><?= isset($row['completion_status']) ? ucfirst($row['completion_status']) : '-' ?></td>
                    <td><?= $row['created_at'] ?></td>
                    <td>
                        <?php if ($row['status'] == 'open'): ?>
                            <form method="POST" style="display:inline-block;">
                                <input type="hidden" name="action" value="accept">
                                <input type="hidden" name="task_id" value="<?= $row['id'] ?>">
                                <button type="submit" class="btn btn-success btn-sm">Accept</button>
                            </form>
                        <?php elseif (isset($row['completion_status']) && $row['completion_status'] == 'pending'): ?>
                            <form method="POST" style="display:inline-block;">
                                <input type="hidden" name="action" value="complete">
                                <input type="hidden" name="task_id" value="<?= $row['id'] ?>">
                                <button type="submit" class="btn btn-primary btn-sm">Mark Complete</button>
                            </form>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
