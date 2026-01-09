<?php
session_start();
include('../config.php'); 

if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "camp_manager") {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION["id"];  
$name = $_SESSION["name"];

/* ================= FETCH CAMP ================= */
$sql = "SELECT id, camp_name FROM relief_camps WHERE manager_id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $camp_id, $camp_name);
mysqli_stmt_fetch($stmt);
mysqli_stmt_close($stmt);

/* ================= FETCH FAMILIES ================= */
$families = [];
if ($camp_id) {
    $fam_sql = "
    SELECT af.id AS family_id, af.head_name, af.contact, af.family_size, af.original_location, 
           af.registered_at, u.name AS registered_by, fcl.status AS camp_status
    FROM family_camp_link fcl
    JOIN affected_families af ON fcl.family_id = af.id
    JOIN users u ON af.registered_by = u.id
    WHERE fcl.camp_id = ?
    ORDER BY af.id DESC
    ";
    $stmt = mysqli_prepare($conn, $fam_sql);
    mysqli_stmt_bind_param($stmt, "i", $camp_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($result)) {
        $families[] = $row;
    }
    mysqli_stmt_close($stmt);
}
// Note: mysqli_close($conn) REMOVED from here to keep connection alive for modals
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>My Camp Families</title>
    <meta charset="utf-8">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include('navbar.php'); ?>

<div class="container mt-5">
    <h3 class="text-center mb-4">Families in <?= htmlspecialchars($camp_name ?? 'Your Camp'); ?></h3>

    <?php if ($camp_id && count($families) > 0): ?>
    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead class="thead-dark">
                <tr>
                    <th>Head Name</th>
                    <th>Contact</th>
                    <th>Size</th>
                    <th>Original Location</th>
                    <th>Registered By</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($families as $f): ?>
                <tr>
                    <td><?= htmlspecialchars($f['head_name']) ?></td>
                    <td><?= htmlspecialchars($f['contact']) ?></td>
                    <td><?= $f['family_size'] ?></td>
                    <td><?= htmlspecialchars($f['original_location']) ?></td>
                    <td><?= htmlspecialchars($f['registered_by']) ?></td>
                    <td>
                        <span class="badge badge-info"><?= htmlspecialchars($f['camp_status']) ?></span>
                    </td>
                    <td>
                        <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#familyModal<?= $f['family_id'] ?>">
                            View Details
                        </button>
                    </td>
                </tr>

                <div class="modal fade" id="familyModal<?= $f['family_id'] ?>" tabindex="-1" role="dialog" aria-hidden="true">
                  <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                      <div class="modal-header">
                        <h5 class="modal-title">Family Details: <?= htmlspecialchars($f['head_name']) ?></h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                          <span aria-hidden="true">&times;</span>
                        </button>
                      </div>
                      <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Contact:</strong> <?= htmlspecialchars($f['contact']) ?></p>
                                <p><strong>Family Size:</strong> <?= $f['family_size'] ?></p>
                                <p><strong>Location:</strong> <?= htmlspecialchars($f['original_location']) ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Registered By:</strong> <?= htmlspecialchars($f['registered_by']) ?></p>
                                <p><strong>Date:</strong> <?= $f['registered_at'] ?></p>
                                <p><strong>Status:</strong> <?= $f['camp_status'] ?></p>
                            </div>
                        </div>

                        <hr>
                        <h6 class="text-danger">Family Losses:</h6>
                        <?php
                        // Connection is still open, so this query will now work
                        $f_id = (int)$f['family_id'];
                        $losses_res = mysqli_query($conn, "SELECT * FROM family_losses WHERE family_id = $f_id");
                        if(mysqli_num_rows($losses_res) > 0){
                            while ($l = mysqli_fetch_assoc($losses_res)) {
                                echo "<ul>";
                                echo "<li>House Damage: ".($l['house_damage'] ? "Yes" : "No")."</li>";
                                echo "<li>Livestock Loss: ".($l['livestock_loss'] ? "Yes" : "No")."</li>";
                                echo "<li>Crop Loss: ".($l['crop_loss'] ? "Yes" : "No")."</li>";
                                echo "<li>Notes: ".htmlspecialchars($l['notes'])."</li>";
                                echo "</ul>";
                            }
                        } else {
                            echo "<p class='text-muted'>No losses reported.</p>";
                        }
                        ?>

                        <hr>
                        <h6 class="text-primary">Special Needs:</h6>
                        <?php
                        $needs_res = mysqli_query($conn, "SELECT * FROM family_special_needs WHERE family_id = $f_id");
                        if(mysqli_num_rows($needs_res) > 0){
                            while ($n = mysqli_fetch_assoc($needs_res)) {
                                echo "<strong>".ucfirst($n['need_type']).":</strong> ".htmlspecialchars($n['details'])."<br>";
                            }
                        } else {
                            echo "<p class='text-muted'>No special needs reported.</p>";
                        }
                        ?>
                      </div>
                      <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                      </div>
                    </div>
                  </div>
                </div>

                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php elseif ($camp_id): ?>
        <div class="alert alert-warning text-center">No families assigned to your camp yet.</div>
    <?php else: ?>
        <div class="alert alert-danger text-center">You do not have a camp assigned yet. Please contact the administrator.</div>
    <?php endif; ?>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<?php mysqli_close($conn); // CLOSE CONNECTION AT THE VERY END ?>
</body>
</html>