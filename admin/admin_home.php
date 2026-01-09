<?php
include('config.php');
session_start();

if (!isset($_SESSION["usertype"]) || $_SESSION["usertype"] !== "admin") {
    header("Location: admin_login.php");
    exit;
}

// === Fetch statistics for dashboard ===
// 1. Total relief camps
$total_camps = $conn->query("SELECT COUNT(*) AS count FROM relief_camps")->fetch_assoc()['count'];

// 2. Camps with critical needs
$critical_needs = $conn->query("
    SELECT COUNT(DISTINCT camp_id) AS count 
    FROM camp_needs 
    WHERE priority_level = 'critical' AND status != 'fulfilled'
")->fetch_assoc()['count'];

// 3. Total donations received (cash + in-kind value approximation)
$total_donations = $conn->query("
    SELECT SUM(amount) AS total_cash 
    FROM donations 
    WHERE donation_type = 'cash' AND status IN ('received','stored','allocated','distributed')
")->fetch_assoc()['total_cash'] ?? 0;

// For simplicity we only count cash here; you can extend for in-kind estimation

// 4. Total registered affected families
$total_families = $conn->query("SELECT COUNT(*) AS count FROM affected_families")->fetch_assoc()['count'];

// === Data for charts ===
// A. Camp occupancy (simple: occupied vs available capacity)
$occupancy_data = $conn->query("
    SELECT 
        SUM(current_occupancy) AS occupied,
        SUM(capacity) AS total_capacity
    FROM relief_camps
")->fetch_assoc();

$occupied = $occupancy_data['occupied'] ?? 0;
$total_capacity = $occupancy_data['total_capacity'] ?? 0;
$available = max(0, $total_capacity - $occupied);

// B. Donation status distribution
$donation_status = $conn->query("
    SELECT status, COUNT(*) AS count 
    FROM donations 
    GROUP BY status
");
$donation_labels = [];
$donation_counts = [];
while ($row = $donation_status->fetch_assoc()) {
    $donation_labels[] = ucfirst($row['status']);
    $donation_counts[] = $row['count'];
}

// C. Critical needs by resource type
$critical_needs_by_type = $conn->query("
    SELECT resource_type, COUNT(*) AS count 
    FROM camp_needs 
    WHERE priority_level = 'critical' AND status != 'fulfilled'
    GROUP BY resource_type
    ORDER BY count DESC
    LIMIT 5
");
$needs_labels = [];
$needs_counts = [];
while ($row = $critical_needs_by_type->fetch_assoc()) {
    $needs_labels[] = ucfirst($row['resource_type']);
    $needs_counts[] = $row['count'];
}

// === Recent activity ===
// Recent camps (last 5)
$recent_camps = $conn->query("
    SELECT id, camp_name, province, district, current_occupancy, capacity, created_at 
    FROM relief_camps 
    ORDER BY created_at DESC 
    LIMIT 5
");

// Recent donations (last 5)
$recent_donations = $conn->query("
    SELECT d.id, d.donation_type, d.amount, d.status, d.donated_at, u.name AS supporter 
    FROM donations d 
    JOIN users u ON d.supporter_id = u.id 
    ORDER BY d.donated_at DESC 
    LIMIT 5
");

// Recent family registrations (last 5)
$recent_families = $conn->query("
    SELECT f.id, f.head_name, f.family_size, f.original_location, f.registered_at, u.name AS registered_by 
    FROM affected_families f 
    JOIN users u ON f.registered_by = u.id 
    ORDER BY f.registered_at DESC 
    LIMIT 5
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Disaster Relief Resource Management</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <style>
        :root {
            --primary: #007bff;
            --success: #28a745;
            --warning: #ffc107;
            --danger: #dc3545;
            --info: #17a2b8;
            --secondary: #6c757d;
            --light: #f8f9fa;
            --dark: #343a40;
        }

        body { background-color: var(--light); font-family: 'Segoe UI', sans-serif; }
        
        .welcome-card {
            background: linear-gradient(135deg, var(--primary), #0056b3);
            color: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 123, 255, 0.3);
        }

        .card-stats {
            transition: all 0.3s ease;
            border-radius: 12px;
            border: none;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            position: relative;
            overflow: hidden;
        }
        .card-stats:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
        }

        .card-header-icon {
            padding: 1.2rem;
            border-radius: 12px;
            position: absolute;
            top: -18px;
            right: 18px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
            color: white;
        }

        .stats-value {
            font-size: 2.4rem;
            font-weight: 700;
            color: #2c3e50;
        }

        .notification-list {
            max-height: 380px;
            overflow-y: auto;
        }

        .chart-container {
            position: relative;
            height: 300px;
        }
    </style>
</head>
<body>
<?php include('admin_navbar.php'); ?>

<div class="container-fluid mt-4 pb-5">
    <!-- Welcome Section -->
    <div class="card welcome-card mb-5 shadow-lg">
        <div class="card-body text-center py-5">
            <h1 class="mb-2 font-weight-bold text-white">Disaster Relief Admin Dashboard</h1>
            <p class="lead mb-0">Welcome back, <strong><?php echo htmlspecialchars($_SESSION["username"] ?? 'Admin'); ?>!</strong></p>
            <p>Monitor camps, donations, needs and support coordination.</p>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-6 col-lg-3 mb-4">
            <div class="card card-stats">
                <div class="card-header-icon" style="background: linear-gradient(45deg, #28a745, #218838);"><i class="fas fa-campground fa-2x"></i></div>
                <div class="card-body pt-4">
                    <h5 class="text-muted">Active Relief Camps</h5>
                    <h3 class="stats-value"><?php echo number_format($total_camps); ?></h3>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3 mb-4">
            <div class="card card-stats">
                <div class="card-header-icon" style="background: linear-gradient(45deg, #dc3545, #c82333);"><i class="fas fa-exclamation-triangle fa-2x"></i></div>
                <div class="card-body pt-4">
                    <h5 class="text-muted">Critical Needs</h5>
                    <h3 class="stats-value"><?php echo number_format($critical_needs); ?></h3>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3 mb-4">
            <div class="card card-stats">
                <div class="card-header-icon" style="background: linear-gradient(45deg, #007bff, #0056b3);"><i class="fas fa-hand-holding-usd fa-2x"></i></div>
                <div class="card-body pt-4">
                    <h5 class="text-muted">Total Donations (PKR)</h5>
                    <h3 class="stats-value"><?php echo number_format($total_donations, 0); ?></h3>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3 mb-4">
            <div class="card card-stats">
                <div class="card-header-icon" style="background: linear-gradient(45deg, #17a2b8, #138496);"><i class="fas fa-users fa-2x"></i></div>
                <div class="card-body pt-4">
                    <h5 class="text-muted">Affected Families</h5>
                    <h3 class="stats-value"><?php echo number_format($total_families); ?></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row mb-4">
        <div class="col-lg-4 mb-4">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Camp Occupancy</h5>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="occupancyChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 mb-4">
            <div class="card shadow">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">Donation Status</h5>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="donationStatusChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 mb-4">
            <div class="card shadow">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0">Critical Needs by Type</h5>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="needsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="row">
        <div class="col-lg-4 mb-4">
            <div class="card shadow h-100">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">Recent Camps</h5>
                </div>
                <div class="card-body notification-list">
                    <?php if ($recent_camps->num_rows > 0): ?>
                        <ul class="list-unstyled">
                            <?php while ($camp = $recent_camps->fetch_assoc()): ?>
                                <li class="media alert alert-light mb-3 p-3 rounded">
                                    <i class="fas fa-campground fa-3x mr-3 text-success"></i>
                                    <div class="media-body">
                                        <h6 class="mt-0 mb-1"><strong><?php echo htmlspecialchars($camp['camp_name']); ?></strong></h6>
                                        <small class="text-muted">
                                            <?php echo htmlspecialchars($camp['province'] . ' - ' . $camp['district']); ?><br>
                                            Occupancy: <?php echo $camp['current_occupancy'] . ' / ' . $camp['capacity']; ?><br>
                                            Added: <?php echo date('M d, Y', strtotime($camp['created_at'])); ?>
                                        </small>
                                    </div>
                                </li>
                            <?php endwhile; ?>
                        </ul>
                    <?php else: ?>
                        <div class="text-center text-muted py-4">No recent camps.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-4 mb-4">
            <div class="card shadow h-100">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Recent Donations</h5>
                </div>
                <div class="card-body notification-list">
                    <?php if ($recent_donations->num_rows > 0): ?>
                        <ul class="list-unstyled">
                            <?php while ($don = $recent_donations->fetch_assoc()): ?>
                                <li class="alert alert-light mb-3 border-left-primary">
                                    <h6><strong><?php echo htmlspecialchars($don['supporter']); ?></strong></h6>
                                    <small>
                                        Type: <?php echo ucfirst($don['donation_type']); ?> 
                                        <?php if ($don['donation_type'] === 'cash') echo ' - PKR ' . number_format($don['amount']); ?><br>
                                        Status: <span class="badge badge-<?php echo $don['status'] === 'distributed' ? 'success' : ($don['status'] === 'received' ? 'warning' : 'info'); ?>">
                                            <?php echo ucfirst($don['status']); ?>
                                        </span><br>
                                        Date: <?php echo date('M d, Y H:i', strtotime($don['donated_at'])); ?>
                                    </small>
                                </li>
                            <?php endwhile; ?>
                        </ul>
                    <?php else: ?>
                        <div class="text-center text-muted py-4">No recent donations.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-4 mb-4">
            <div class="card shadow h-100">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">Recent Family Registrations</h5>
                </div>
                <div class="card-body notification-list">
                    <?php if ($recent_families->num_rows > 0): ?>
                        <ul class="list-unstyled">
                            <?php while ($fam = $recent_families->fetch_assoc()): ?>
                                <li class="alert alert-light mb-3 border-left-info">
                                    <h6><strong><?php echo htmlspecialchars($fam['head_name']); ?></strong></h6>
                                    <small>
                                        Family Size: <?php echo $fam['family_size']; ?><br>
                                        Location: <?php echo htmlspecialchars($fam['original_location']); ?><br>
                                        By: <?php echo htmlspecialchars($fam['registered_by']); ?><br>
                                        Date: <?php echo date('M d, Y', strtotime($fam['registered_at'])); ?>
                                    </small>
                                </li>
                            <?php endwhile; ?>
                        </ul>
                    <?php else: ?>
                        <div class="text-center text-muted py-4">No recent registrations.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Pass PHP data to JavaScript
const occupancyData = {
    labels: ['Occupied', 'Available'],
    datasets: [{
        data: [<?php echo $occupied; ?>, <?php echo $available; ?>],
        backgroundColor: ['#28a745', '#6c757d'],
        borderWidth: 2,
        borderColor: '#fff'
    }]
};

const donationLabels = <?php echo json_encode($donation_labels); ?>;
const donationData = <?php echo json_encode($donation_counts); ?>;

const needsLabels = <?php echo json_encode($needs_labels); ?>;
const needsData = <?php echo json_encode($needs_counts); ?>;

// Occupancy Chart
new Chart(document.getElementById('occupancyChart'), {
    type: 'doughnut',
    data: occupancyData,
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { position: 'bottom' } }
    }
});

// Donation Status Chart
new Chart(document.getElementById('donationStatusChart'), {
    type: 'pie',
    data: {
        labels: donationLabels,
        datasets: [{
            data: donationData,
            backgroundColor: ['#28a745', '#ffc107', '#17a2b8', '#dc3545'],
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { position: 'bottom' } }
    }
});

// Critical Needs Chart
new Chart(document.getElementById('needsChart'), {
    type: 'bar',
    data: {
        labels: needsLabels,
        datasets: [{
            label: 'Critical Count',
            data: needsData,
            backgroundColor: '#dc3545',
            borderColor: '#c82333',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: { beginAtZero: true, ticks: { stepSize: 1 } }
        }
    }
});
</script>
</body>
</html>