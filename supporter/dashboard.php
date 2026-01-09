<?php
session_start();
include('config.php');

// Authentication Check
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "supporter") {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION["id"];
$name    = $_SESSION["name"];

// === Statistics for Supporter ===

// 1. Total donations made by this user (cash + count of in-kind)
$total_donation_count = $conn->query("
    SELECT COUNT(*) AS count 
    FROM donations 
    WHERE supporter_id = $user_id
")->fetch_assoc()['count'] ?? 0;

$total_cash_donated = $conn->query("
    SELECT COALESCE(SUM(amount), 0) AS total 
    FROM donations 
    WHERE supporter_id = $user_id AND donation_type = 'cash'
")->fetch_assoc()['total'] ?? 0;

// 2. Current status breakdown of their donations
$donation_status = $conn->query("
    SELECT status, COUNT(*) AS count 
    FROM donations 
    WHERE supporter_id = $user_id 
    GROUP BY status
");

// 3. Active / Accepted volunteer tasks
$active_tasks = $conn->query("
    SELECT COUNT(*) AS count 
    FROM task_assignments ta
    JOIN volunteer_tasks vt ON ta.task_id = vt.id
    WHERE ta.volunteer_id = $user_id 
      AND ta.completion_status = 'pending'
")->fetch_assoc()['count'] ?? 0;

// 4. Recent donations by this user
$recent_donations = $conn->query("
    SELECT id, donation_type, amount, description, status, donated_at 
    FROM donations 
    WHERE supporter_id = $user_id 
    ORDER BY donated_at DESC 
    LIMIT 5
");

// 5. Recent assigned tasks
$recent_tasks = $conn->query("
    SELECT vt.title, vt.description, vt.camp_id, vt.status, 
           ta.assigned_at, ta.completion_status
    FROM task_assignments ta
    JOIN volunteer_tasks vt ON ta.task_id = vt.id
    WHERE ta.volunteer_id = $user_id 
    ORDER BY ta.assigned_at DESC 
    LIMIT 5
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supporter Dashboard | Disaster Relief</title>
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
            background: linear-gradient(135deg, #28a745, #1e7e34);
            color: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(40, 167, 69, 0.3);
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

        .status-received  { background-color: #d4edda; }
        .status-stored    { background-color: #fff3cd; }
        .status-allocated { background-color: #cce5ff; }
        .status-distributed { background-color: #28a74533; }
    </style>
</head>
<body>

<?php include('navbar.php'); ?>

<div class="container-fluid mt-4 pb-5">

    <!-- Welcome Header -->
    <div class="card welcome-card mb-5 shadow-lg">
        <div class="card-body text-center py-5">
            <h1 class="display-5 mb-2 font-weight-bold text-white">Supporter Dashboard</h1>
            <p class="lead mb-0">
                Welcome back, <strong><?= htmlspecialchars($name); ?></strong>!
            </p>
            <p class="mt-2">Thank you for supporting disaster relief efforts</p>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mb-5 justify-content-center text-center">
        <div class="col-6 col-sm-4 col-md-3 col-lg-2 mb-3">
            <a href="manage_donations.php" class="btn btn-success btn-block quick-action-btn">
                <i class="fas fa-donate mr-2"></i> Make Donation
            </a>
        </div>
        <div class="col-6 col-sm-4 col-md-3 col-lg-2 mb-3">
            <a href="volunteer_tasks.php" class="btn btn-primary btn-block quick-action-btn">
                <i class="fas fa-hands-helping mr-2"></i> Volunteer Tasks
            </a>
        </div>
        <div class="col-6 col-sm-4 col-md-3 col-lg-2 mb-3">
            <a href="transparency_reports.php" class="btn btn-info btn-block quick-action-btn">
                <i class="fas fa-chart-bar mr-2"></i> Reports
            </a>
        </div>
        <div class="col-6 col-sm-4 col-md-3 col-lg-2 mb-3">
            <a href="notifications.php" class="btn btn-warning btn-block quick-action-btn">
                <i class="fas fa-bell mr-2"></i> Notifications
            </a>
        </div>
    </div>

    <!-- Statistics Overview -->
    <div class="row mb-5">
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card stat-card bg-white">
                <i class="fas fa-hand-holding-heart stat-icon"></i>
                <div class="card-body text-center">
                    <h5 class="text-muted mb-1">Total Donations</h5>
                    <div class="stat-value text-success"><?= number_format($total_donation_count) ?></div>
                    <small class="text-muted">Contributions made</small>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card stat-card bg-white">
                <i class="fas fa-rupee-sign stat-icon"></i>
                <div class="card-body text-center">
                    <h5 class="text-muted mb-1">Cash Donated</h5>
                    <div class="stat-value text-primary">PKR <?= number_format($total_cash_donated) ?></div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card stat-card bg-white">
                <i class="fas fa-tasks stat-icon"></i>
                <div class="card-body text-center">
                    <h5 class="text-muted mb-1">Active Tasks</h5>
                    <div class="stat-value text-info"><?= number_format($active_tasks) ?></div>
                    <small class="text-muted">Assigned & pending</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Recent Donations -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">Your Recent Donations</h5>
                </div>
                <div class="list-group list-group-flush">
                    <?php if ($recent_donations->num_rows > 0): ?>
                        <?php while ($don = $recent_donations->fetch_assoc()): ?>
                            <div class="list-group-item status-<?= $don['status'] ?>">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <strong>
                                            <?= $don['donation_type'] === 'cash' 
                                                ? 'PKR ' . number_format($don['amount']) 
                                                : ucfirst($don['donation_type']) ?>
                                        </strong>
                                        <small class="d-block text-muted">
                                            <?= htmlspecialchars(substr($don['description'] ?? 'No description', 0, 60)) ?>...
                                        </small>
                                    </div>
                                    <div class="text-right">
                                        <span class="badge badge-<?= 
                                            $don['status'] === 'distributed' ? 'success' : 
                                            ($don['status'] === 'received' ? 'warning' : 'info')
                                        ?>">
                                            <?= ucfirst($don['status']) ?>
                                        </span>
                                        <small class="d-block mt-1">
                                            <?= date('d M Y', strtotime($don['donated_at'])) ?>
                                        </small>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-gift fa-3x mb-3 d-block opacity-50"></i>
                            No donations yet
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Recent Tasks -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Your Recent Volunteer Tasks</h5>
                </div>
                <div class="list-group list-group-flush">
                    <?php if ($recent_tasks->num_rows > 0): ?>
                        <?php while ($task = $recent_tasks->fetch_assoc()): ?>
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <strong><?= htmlspecialchars($task['title']) ?></strong>
                                        <small class="d-block text-muted">
                                            <?= htmlspecialchars(substr($task['description'] ?? '', 0, 60)) ?>...
                                        </small>
                                    </div>
                                    <div class="text-right">
                                        <span class="badge badge-<?= 
                                            $task['completion_status'] === 'completed' ? 'success' : 'warning'
                                        ?>">
                                            <?= ucfirst($task['completion_status']) ?>
                                        </span>
                                        <small class="d-block mt-1">
                                            <?= date('d M Y', strtotime($task['assigned_at'])) ?>
                                        </small>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-hands-helping fa-3x mb-3 d-block opacity-50"></i>
                            No tasks assigned yet
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