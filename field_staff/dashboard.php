<?php
session_start();
include('config.php');

// Authentication Check
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "field_staff") {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION["id"];
$name    = $_SESSION["name"];

// === Key Statistics for Field Staff ===

// 1. Families registered by this staff
$total_registered = $conn->query("
    SELECT COUNT(*) AS count 
    FROM affected_families 
    WHERE registered_by = $user_id
")->fetch_assoc()['count'] ?? 0;

// 2. Families needing follow-up (no status update in last 7 days)
$needs_followup = $conn->query("
    SELECT COUNT(DISTINCT f.id) AS count
    FROM affected_families f
    LEFT JOIN family_camp_link l ON f.id = l.family_id
    WHERE f.registered_by = $user_id
      AND (l.last_updated IS NULL OR l.last_updated < DATE_SUB(NOW(), INTERVAL 7 DAY))
")->fetch_assoc()['count'] ?? 0;

// 3. Total critical needs across all camps (context for field work)
$critical_needs_count = $conn->query("
    SELECT COUNT(*) AS count 
    FROM camp_needs 
    WHERE priority_level = 'critical' AND status != 'fulfilled'
")->fetch_assoc()['count'] ?? 0;

// 4. Recent families registered by this staff member
$recent_families = $conn->query("
    SELECT f.id, f.head_name, f.family_size, f.original_location, 
           f.registered_at, l.status AS camp_status
    FROM affected_families f
    LEFT JOIN family_camp_link l ON f.id = l.family_id
    WHERE f.registered_by = $user_id
    ORDER BY f.registered_at DESC
    LIMIT 5
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Field Staff Dashboard | Disaster Relief</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        :root {
            --primary: #007bff;
            --success: #28a745;
            --warning: #ffc107;
            --danger: #dc3545;
            --info: #17a2b8;
            --dark: #343a40;
            --light: #f8f9fa;
        }

        body {
            background-color: var(--light);
            font-family: 'Segoe UI', sans-serif;
        }

        .welcome-card {
            background: linear-gradient(135deg, #17a2b8, #0c5460);
            color: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(23, 162, 184, 0.3);
        }

        .stat-card {
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .stat-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
        }

        .stat-icon {
            font-size: 4rem;
            opacity: 0.12;
            position: absolute;
            right: 20px;
            top: 20px;
        }

        .stat-value {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--dark);
        }

        .quick-action-btn {
            font-size: 1.1rem;
            padding: 12px;
        }

        .list-group-item {
            border-left: 5px solid transparent;
            transition: all 0.2s;
        }

        .list-group-item:hover {
            background-color: #f1f9ff;
        }
    </style>
</head>
<body>

<?php include('navbar.php'); ?>

<div class="container-fluid mt-4 pb-5">

    <!-- Welcome Header -->
    <div class="card welcome-card mb-5 shadow-lg">
        <div class="card-body text-center py-5">
            <h1 class="display-5 mb-2 font-weight-bold text-white">Field Staff Dashboard</h1>
            <p class="lead mb-0">
                Welcome back, <strong><?= htmlspecialchars($name); ?></strong>!
            </p>
            <p class="mt-2">Field coordination • Family registration • Needs assessment</p>
        </div>
    </div>

    <!-- Quick Action Buttons (matching your navbar) -->
    <div class="row mb-5 justify-content-center text-center">
        <div class="col-6 col-sm-4 col-md-3 col-lg-2 mb-3">
            <a href="manage_families.php" class="btn btn-dark btn-block quick-action-btn">
                <i class="fas fa-user-plus mr-2"></i> Families
            </a>
        </div>
        <div class="col-6 col-sm-4 col-md-3 col-lg-2 mb-3">
            <a href="manage_family_losses.php" class="btn btn-warning btn-block quick-action-btn">
                <i class="fas fa-home mr-2"></i> Family Loss
            </a>
        </div>
        <div class="col-6 col-sm-4 col-md-3 col-lg-2 mb-3">
            <a href="manage_special_needs.php" class="btn btn-info btn-block quick-action-btn">
                <i class="fas fa-heartbeat mr-2"></i> Family Needs
            </a>
        </div>
        <div class="col-6 col-sm-4 col-md-3 col-lg-2 mb-3">
            <a href="manage_family_camp.php" class="btn btn-success btn-block quick-action-btn">
                <i class="fas fa-link mr-2"></i> Link Family
            </a>
        </div>
        <div class="col-6 col-sm-4 col-md-3 col-lg-2 mb-3">
            <a href="prioritized_aid.php" class="btn btn-danger btn-block quick-action-btn">
                <i class="fas fa-list-ol mr-2"></i> Prioritized Aid
            </a>
        </div>
    </div>

    <!-- Statistics Overview -->
    <div class="row mb-5">
        <div class="col-md-4 mb-4">
            <div class="card stat-card bg-white">
                <i class="fas fa-users stat-icon"></i>
                <div class="card-body text-center">
                    <h5 class="text-muted mb-1">Families Registered</h5>
                    <div class="stat-value text-primary"><?= number_format($total_registered) ?></div>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-4">
            <div class="card stat-card bg-white">
                <i class="fas fa-clock stat-icon"></i>
                <div class="card-body text-center">
                    <h5 class="text-muted mb-1">Needing Follow-up</h5>
                    <div class="stat-value text-warning"><?= number_format($needs_followup) ?></div>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-4">
            <div class="card stat-card bg-white">
                <i class="fas fa-exclamation-triangle stat-icon"></i>
                <div class="card-body text-center">
                    <h5 class="text-muted mb-1">Critical Needs</h5>
                    <div class="stat-value text-danger"><?= number_format($critical_needs_count) ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">Recently Registered Families (Last 5)</h5>
                </div>
                <div class="list-group list-group-flush">
                    <?php if ($recent_families->num_rows > 0): ?>
                        <?php while ($fam = $recent_families->fetch_assoc()): ?>
                            <a href="family_details.php?id=<?= $fam['id'] ?>" class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong><?= htmlspecialchars($fam['head_name']) ?></strong>
                                        <small class="d-block text-muted">
                                            Family Size: <?= $fam['family_size'] ?> • 
                                            <?= htmlspecialchars($fam['original_location']) ?>
                                        </small>
                                    </div>
                                    <div class="text-right">
                                        <small class="d-block"><?= date('d M Y', strtotime($fam['registered_at'])) ?></small>
                                        <span class="badge badge-<?= 
                                            $fam['camp_status'] === 'aid_received' ? 'success' : 
                                            ($fam['camp_status'] === 'medical_required' ? 'danger' : 'secondary')
                                        ?>">
                                            <?= ucfirst($fam['camp_status'] ?? 'Not Linked') ?>
                                        </span>
                                    </div>
                                </div>
                            </a>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-users fa-3x mb-3 d-block opacity-50"></i>
                            No families registered yet
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>