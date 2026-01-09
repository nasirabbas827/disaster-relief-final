<?php
session_start();
include('../config.php');

if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "supporter") {
    header("Location: ../login.php");
    exit;
}

$supporter_id = $_SESSION["id"];
$message = "";

/* ================= HANDLE CRUD ================= */
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST['action'];

    if ($action == 'add') {
        $donation_type = $_POST['donation_type'];
        $amount = $_POST['amount'];
        $description = $_POST['description'];

        $stmt = mysqli_prepare($conn, "INSERT INTO donations (supporter_id, donation_type, amount, description) VALUES (?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "isds", $supporter_id, $donation_type, $amount, $description);
        if (mysqli_stmt_execute($stmt)) {
            $message = '<div class="alert alert-success">Donation added successfully.</div>';
        } else {
            $message = '<div class="alert alert-danger">Failed to add donation.</div>';
        }
        mysqli_stmt_close($stmt);
    }

    }

/* ================= FETCH DONATIONS ================= */
$stmt = mysqli_prepare($conn, "SELECT id, donation_type, amount, description, status, donated_at FROM donations WHERE supporter_id=? ORDER BY donated_at DESC");
mysqli_stmt_bind_param($stmt, "i", $supporter_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
mysqli_stmt_close($stmt);
mysqli_close($conn);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Donations</title>
    <meta charset="utf-8">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include('navbar.php'); ?>

<div class="container mt-5">
    <h3 class="text-center mb-4">My Donations</h3>
    <?php echo $message; ?>

    <div class="text-right mb-3">
        <button class="btn btn-success" data-toggle="modal" data-target="#addDonationModal">Add Donation</button>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead class="thead-dark">
                <tr>
                    <th>ID</th>
                    <th>Type</th>
                    <th>Amount</th>
                    <th>Description</th>
                    <th>Status</th>
                    <th>Donated At</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= ucfirst($row['donation_type']) ?></td>
                    <td><?= $row['amount'] ?></td>
                    <td><?= htmlspecialchars($row['description']) ?></td>
                    <td><?= ucfirst($row['status']) ?></td>
                    <td><?= $row['donated_at'] ?></td>
                    
                </tr>

               <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addDonationModal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <form method="POST">
        <div class="modal-header">
          <h5 class="modal-title">Add New Donation</h5>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="action" value="add">

          <div class="form-row">
              <div class="form-group col-md-6">
                  <label>Donation Type</label>
                  <select name="donation_type" class="form-control" required>
                      <option value="cash">Cash</option>
                      <option value="in_kind">In-Kind</option>
                  </select>
              </div>
              <div class="form-group col-md-6">
                  <label>Amount</label>
                  <input type="number" step="0.01" name="amount" class="form-control" required>
              </div>
          </div>

          <div class="form-group">
              <label>Description</label>
              <textarea name="description" class="form-control" rows="2"></textarea>
          </div>

        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success">Add Donation</button>
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
