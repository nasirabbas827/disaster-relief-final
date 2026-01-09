<?php
include('config.php');
session_start();

if (!isset($_SESSION["usertype"]) || $_SESSION["usertype"] !== "admin") {
    header("Location: admin_login.php");
    exit;
}

$id = intval($_GET['id']);
$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name   = $_POST['name'];
    $email  = $_POST['email'];
    $role   = $_POST['role'];
    $status = $_POST['status'];

    $stmt = $conn->prepare("
        UPDATE users
        SET name = ?, email = ?, role = ?, status = ?
        WHERE id = ?
    ");
    $stmt->bind_param("ssssi", $name, $email, $role, $status, $id);
    $stmt->execute();
    $stmt->close();

    $message = "User updated successfully.";
}

$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit User</title>
    <meta charset="utf-8">

    <link rel="stylesheet"
          href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>

<?php include('admin_navbar.php'); ?>

<div class="container mt-5">
    <div class="card mx-auto" style="max-width:750px;">
        <div class="card-body">

            <h3 class="text-center mb-4">Edit User</h3>

            <?php if ($message): ?>
                <div class="alert alert-success"><?= $message ?></div>
            <?php endif; ?>

            <form method="POST">

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>Name</label>
                        <input type="text" name="name" class="form-control"
                               value="<?= htmlspecialchars($user['name']) ?>" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control"
                               value="<?= htmlspecialchars($user['email']) ?>" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>Role</label>
                        <select name="role" class="form-control">
                            <option value="camp_manager" <?= $user['role']=='camp_manager'?'selected':'' ?>>Camp Manager</option>
                            <option value="supporter" <?= $user['role']=='supporter'?'selected':'' ?>>Supporter</option>
                            <option value="field_staff" <?= $user['role']=='field_staff'?'selected':'' ?>>Field Staff</option>
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>Status</label>
                        <select name="status" class="form-control">
                            <option value="active" <?= $user['status']=='active'?'selected':'' ?>>Active</option>
                            <option value="inactive" <?= $user['status']=='inactive'?'selected':'' ?>>Inactive</option>
                        </select>
                    </div>
                </div>

                <div class="text-center mt-4">
                    <button class="btn btn-primary">Update</button>
                    <a href="view_users.php" class="btn btn-secondary">Back</a>
                </div>

            </form>

        </div>
    </div>
</div>

</body>
</html>
