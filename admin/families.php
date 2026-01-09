<?php
include('config.php');
session_start();

// Admin access check
if (!isset($_SESSION['usertype']) || $_SESSION['usertype'] !== 'admin') {
    header("Location: admin_login.php");
    exit;
}

// Fetch all families with related data
$query = "
SELECT 
    af.id AS family_id,
    af.head_name,
    af.contact,
    af.family_size,
    af.original_location,
    af.registered_at,
    u.name AS registered_by,
    fcl.camp_id,
    rc.camp_name,
    fcl.status AS camp_status
FROM affected_families af
JOIN users u ON af.registered_by = u.id
LEFT JOIN family_camp_link fcl ON af.id = fcl.family_id
LEFT JOIN relief_camps rc ON fcl.camp_id = rc.id
ORDER BY af.id DESC
";

$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Registered Families</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include('admin_navbar.php'); ?>

<div class="container mt-5">
    <h3>All Registered Families</h3>

    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead class="thead-dark">
                <tr>
                    <th>ID</th>
                    <th>Head Name</th>
                    <th>Contact</th>
                    <th>Family Size</th>
                    <th>Original Location</th>
                    <th>Registered By</th>
                    <th>Camp Assigned</th>
                    <th>Camp Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($f = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?= $f['family_id'] ?></td>
                    <td><?= htmlspecialchars($f['head_name']) ?></td>
                    <td><?= $f['contact'] ?></td>
                    <td><?= $f['family_size'] ?></td>
                    <td><?= htmlspecialchars($f['original_location']) ?></td>
                    <td><?= htmlspecialchars($f['registered_by']) ?></td>
                    <td><?= htmlspecialchars($f['camp_name'] ?? 'Not Assigned') ?></td>
                    <td><?= $f['camp_status'] ?? '-' ?></td>
                    <td>
                        <!-- Button to trigger modal -->
                        <button type="button" class="btn btn-info btn-sm" data-toggle="modal" data-target="#familyModal<?= $f['family_id'] ?>">
                            View Details
                        </button>
                    </td>
                </tr>

                <!-- Modal -->
                <div class="modal fade" id="familyModal<?= $f['family_id'] ?>" tabindex="-1" role="dialog" aria-labelledby="familyModalLabel<?= $f['family_id'] ?>" aria-hidden="true">
                  <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                      <div class="modal-header">
                        <h5 class="modal-title" id="familyModalLabel<?= $f['family_id'] ?>">Family Details: <?= htmlspecialchars($f['head_name']) ?></h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                          <span aria-hidden="true">&times;</span>
                        </button>
                      </div>
                      <div class="modal-body">
                        <p><strong>Contact:</strong> <?= $f['contact'] ?></p>
                        <p><strong>Family Size:</strong> <?= $f['family_size'] ?></p>
                        <p><strong>Original Location:</strong> <?= htmlspecialchars($f['original_location']) ?></p>
                        <p><strong>Registered By:</strong> <?= htmlspecialchars($f['registered_by']) ?></p>
                        <p><strong>Registered At:</strong> <?= $f['registered_at'] ?></p>
                        <p><strong>Camp Assigned:</strong> <?= htmlspecialchars($f['camp_name'] ?? 'Not Assigned') ?></p>
                        <p><strong>Camp Status:</strong> <?= $f['camp_status'] ?? '-' ?></p>

                        <hr>
                        <h6>Family Losses:</h6>
                        <?php
                        $losses_sql = "SELECT * FROM family_losses WHERE family_id=".$f['family_id'];
                        $losses_res = mysqli_query($conn, $losses_sql);
                        if(mysqli_num_rows($losses_res) > 0){
                            while ($l = mysqli_fetch_assoc($losses_res)) {
                                echo "House Damage: ".($l['house_damage'] ? "Yes" : "No")."<br>";
                                echo "Livestock Loss: ".($l['livestock_loss'] ? "Yes" : "No")."<br>";
                                echo "Crop Loss: ".($l['crop_loss'] ? "Yes" : "No")."<br>";
                                echo "Notes: ".$l['notes']."<hr>";
                            }
                        } else {
                            echo "<p>No losses reported.</p>";
                        }
                        ?>

                        <h6>Special Needs:</h6>
                        <?php
                        $needs_sql = "SELECT * FROM family_special_needs WHERE family_id=".$f['family_id'];
                        $needs_res = mysqli_query($conn, $needs_sql);
                        if(mysqli_num_rows($needs_res) > 0){
                            while ($n = mysqli_fetch_assoc($needs_res)) {
                                echo ucfirst($n['need_type']).": ".$n['details']."<hr>";
                            }
                        } else {
                            echo "<p>No special needs reported.</p>";
                        }
                        ?>
                      </div>
                      <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                      </div>
                    </div>
                  </div>
                </div>

                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>
