<?php
session_start();
include('config.php');

// Authentication Check
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "camp_manager") {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION["id"];
$name    = $_SESSION["name"];

// Get the camp managed by this manager
$camp_query = $conn->prepare("
    SELECT id, camp_name, province, district, location_details, 
           capacity, current_occupancy, facilities 
    FROM relief_camps 
    WHERE manager_id = ?
");
$camp_query->bind_param("i", $user_id);
$camp_query->execute();
$camp_result = $camp_query->get_result();
$camp = $camp_result->fetch_assoc();

// If no camp is assigned → show message
$has_camp = $camp !== null;

if ($has_camp) {
    $camp_id = $camp['id'];

    // 1. Urgent needs count (pending + critical/high)
    $urgent_needs = $conn->query("
        SELECT COUNT(*) AS count 
        FROM camp_needs 
        WHERE camp_id = $camp_id 
          AND status IN ('pending', 'partial')
          AND priority_level IN ('high', 'critical')
    ")->fetch_assoc()['count'] ?? 0;

    // 2. Total families linked to this camp
    $total_families = $conn->query("
        SELECT COUNT(*) AS count 
        FROM family_camp_link 
        WHERE camp_id = $camp_id
    ")->fetch_assoc()['count'] ?? 0;

    // 3. Pending volunteer tasks
    $pending_tasks = $conn->query("
        SELECT COUNT(*) AS count 
        FROM volunteer_tasks 
        WHERE camp_id = $camp_id 
          AND status = 'open'
    ")->fetch_assoc()['count'] ?? 0;

    // 4. Recent needs (last 5)
    $recent_needs = $conn->query("
        SELECT resource_type, quantity_required, unit_type, priority_level, status, updated_at
        FROM camp_needs
        WHERE camp_id = $camp_id
        ORDER BY updated_at DESC
        LIMIT 5
    ");

    // 5. Recent volunteer task assignments (last 5)
    $recent_tasks = $conn->query("
        SELECT vt.title, vt.status, vt.created_at,
               u.name AS volunteer_name,
               ta.completion_status
        FROM volunteer_tasks vt
        LEFT JOIN task_assignments ta ON vt.id = ta.task_id
        LEFT JOIN users u ON ta.volunteer_id = u.id
        WHERE vt.camp_id = $camp_id
        ORDER BY vt.created_at DESC
        LIMIT 5
    ");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Camp Manager Dashboard | Disaster Relief</title>
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
            background: linear-gradient(135deg, #2c7be5, #1e60a9);
            color: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 123, 255, 0.3);
        }

        .stat-card {
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            border: none;
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
            color: var(--dark);
        }

        .stat-value {
            font-size: 2.4rem;
            font-weight: 700;
            color: var(--dark);
        }

        .list-group-item {
            border-left: 4px solid;
        }

        .priority-critical { border-left-color: #dc3545; }
        .priority-high    { border-left-color: #fd7e14; }
        .priority-medium  { border-left-color: #ffc107; }
        .priority-low     { border-left-color: #28a745; }
    </style>
</head>
<body>

<?php include('navbar.php'); ?>

<div class="container-fluid mt-4 pb-5">

    <!-- Welcome Header -->
    <div class="card welcome-card mb-5 shadow-lg">
        <div class="card-body text-center py-5">
            <h1 class="display-5 mb-2 font-weight-bold text-white">
                Camp Manager Dashboard
            </h1>
            <p class="lead mb-0">
                Welcome back, <strong><?= htmlspecialchars($name); ?></strong>!
            </p>
            <?php if ($has_camp): ?>
                <p class="mt-2 mb-0">
                    Managing: <strong><?= htmlspecialchars($camp['camp_name']); ?></strong>
                    <br>
                    <small><?= htmlspecialchars($camp['province'] . ' • ' . $camp['district']); ?></small>
                </p>
            <?php else: ?>
                <p class="mt-3 mb-0 text-warning">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    No relief camp is currently assigned to you.
                </p>
            <?php endif; ?>
        </div>
    </div>

    <?php if (!$has_camp): ?>
        <div class="alert alert-info text-center">
            <h5>Please contact the system administrator to assign you to a relief camp.</h5>
        </div>
    <?php else: ?>

        <!-- Statistics Cards -->
        <div class="row mb-5">
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card stat-card bg-white">
                    <i class="fas fa-exclamation-circle stat-icon"></i>
                    <div class="card-body">
                        <h5 class="text-muted">Urgent Needs</h5>
                        <div class="stat-value text-danger"><?= number_format($urgent_needs) ?></div>
                        <small class="text-muted">High / Critical priority</small>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card stat-card bg-white">
                    <i class="fas fa-users stat-icon"></i>
                    <div class="card-body">
                        <h5 class="text-muted">Affected Families</h5>
                        <div class="stat-value text-primary"><?= number_format($total_families) ?></div>
                        <small class="text-muted">Currently linked to your camp</small>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card stat-card bg-white">
                    <i class="fas fa-tasks stat-icon"></i>
                    <div class="card-body">
                        <h5 class="text-muted">Open Volunteer Tasks</h5>
                        <div class="stat-value text-info"><?= number_format($pending_tasks) ?></div>
                        <small class="text-muted">Tasks awaiting assignment</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Recent Camp Needs -->
            <div class="col-lg-6 mb-4">
                <div class="card shadow">
                    <div class="card-header bg-danger text-white">
                        <h5 class="mb-0">Recent / Urgent Needs</h5>
                    </div>
                    <div class="list-group list-group-flush complaint-list">
                        <?php if ($recent_needs->num_rows > 0): ?>
                            <?php while ($need = $recent_needs->fetch_assoc()): ?>
                                <a href="#" class="list-group-item list-group-item-action priority-<?= $need['priority_level'] ?>">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1">
                                            <?= ucfirst($need['resource_type']) ?>
                                            <span class="badge badge-<?= 
                                                $need['status'] === 'fulfilled' ? 'success' : 
                                                ($need['status'] === 'partial' ? 'warning' : 'danger')
                                            ?> badge-status ml-2">
                                                <?= ucfirst($need['status']) ?>
                                            </span>
                                        </h6>
                                        <small>
                                            <?= date('d M Y H:i', strtotime($need['updated_at'])) ?>
                                        </small>
                                    </div>
                                    <p class="mb-1">
                                        <?= $need['quantity_required'] ?> <?= $need['unit_type'] ?>
                                        <span class="badge badge-<?= 
                                            $need['priority_level'] === 'critical' ? 'danger' : 
                                            ($need['priority_level'] === 'high' ? 'warning' : 'info')
                                        ?> ml-2">
                                            <?= ucfirst($need['priority_level']) ?>
                                        </span>
                                    </p>
                                </a>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="text-center py-4 text-muted">
                                No recent needs recorded.
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="card-footer text-center">
                        <a href="manage_needs.php" class="btn btn-sm btn-outline-danger">Manage All Needs</a>
                    </div>
                </div>
            </div>

            <!-- Recent Volunteer Tasks -->
            <div class="col-lg-6 mb-4">
                <div class="card shadow">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">Recent Volunteer Tasks</h5>
                    </div>
                    <div class="list-group list-group-flush complaint-list">
                        <?php if ($recent_tasks->num_rows > 0): ?>
                            <?php while ($task = $recent_tasks->fetch_assoc()): ?>
                                <div class="list-group-item">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1"><?= htmlspecialchars($task['title']) ?></h6>
                                        <small>
                                            <?= date('d M Y H:i', strtotime($task['created_at'])) ?>
                                        </small>
                                    </div>
                                    <?php if ($task['volunteer_name']): ?>
                                        <p class="mb-1 text-muted">
                                            Assigned to: <strong><?= htmlspecialchars($task['volunteer_name']) ?></strong>
                                            <span class="badge badge-<?= 
                                                $task['completion_status'] === 'completed' ? 'success' : 'warning' 
                                            ?> ml-2">
                                                <?= ucfirst($task['completion_status']) ?>
                                            </span>
                                        </p>
                                    <?php else: ?>
                                        <p class="mb-1 text-muted">Not yet assigned</p>
                                    <?php endif; ?>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="text-center py-4 text-muted">
                                No recent tasks created.
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="card-footer text-center">
                        <a href="manage_tasks.php" class="btn btn-sm btn-outline-info">Manage Tasks</a>
                    </div>
                </div>
            </div>
        </div>

    <?php endif; // end of has_camp check ?>

</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>