<?php
session_start();
include('config.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RescueNet Pakistan - Disaster Relief Coordination Platform</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="./css/style.css">
    <style>
        :root {
            --primary: #007bff;
            --success: #28a745;
            --info: #17a2b8;
            --light: #f8f9fa;
            --dark: #343a40;
            --accent: #dc3545;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        /* Hero Section */
        .hero-section {
            height: 100vh;
            min-height: 700px;
            background: linear-gradient(rgba(0, 0, 0, 0.65), rgba(0, 0, 0, 0.65)), 
                        url('https://images.unsplash.com/photo-1488521787991-ed7bbaae773c?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80') center/cover no-repeat;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            position: relative;
        }

        .hero-section h1 {
            font-size: 4rem;
            font-weight: 800;
            text-shadow: 2px 2px 8px rgba(0,0,0,0.7);
        }

        .hero-section .lead {
            font-size: 1.5rem;
            max-width: 900px;
            margin: 25px auto;
            font-weight: 300;
        }

        .btn-cta {
            padding: 16px 50px;
            font-size: 1.3rem;
            border-radius: 50px;
            background: var(--success);
            border: none;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(40,167,69,0.4);
        }

        .btn-cta:hover {
            background: #218838;
            transform: translateY(-5px);
            box-shadow: 0 12px 30px rgba(40,167,69,0.5);
        }

        /* Sections */
        .section {
            padding: 7rem 0;
            margin-bottom: 3rem; /* Added consistent bottom margin between major sections */
        }

        .section-title {
            font-weight: 700;
            position: relative;
            margin-bottom: 4rem;
        }

        .section-title::after {
            content: '';
            width: 100px;
            height: 5px;
            background: var(--primary);
            position: absolute;
            bottom: -15px;
            left: 50%;
            transform: translateX(-50%);
            border-radius: 3px;
        }

        /* Feature Cards - FIXED & EQUAL HEIGHT */
        .feature-card {
            border: none;
            border-radius: 16px;
            padding: 2.5rem 1.8rem;
            background: white;
            box-shadow: 0 10px 30px rgba(0,0,0,0.09);
            transition: all 0.4s ease;
            height: 100%;
            min-height: 320px;          /* ← Ensures minimum consistent height */
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            text-align: center;
        }

        .feature-card:hover {
            transform: translateY(-12px);
            box-shadow: 0 25px 50px rgba(0,0,0,0.15);
        }

        .feature-icon {
            font-size: 3.2rem;
            margin-bottom: 1.5rem;
            color: var(--primary);
        }

        .feature-card p {
            margin-top: auto;           /* Pushes text to bottom if needed */
            margin-bottom: 0;
        }

        .impact-number {
            font-size: 3.5rem;
            font-weight: 800;
            color: var(--accent);
        }

        footer {
            background: var(--dark);
            color: white;
            padding: 60px 0 30px;
        }

        footer a {
            color: var(--success);
            text-decoration: none;
        }

        footer a:hover {
            color: #218838;
        }

        @media (max-width: 767px) {
            .hero-section h1 {
                font-size: 2.8rem;
            }
            .feature-card {
                min-height: 340px; /* Slightly taller on mobile for better readability */
            }
        }
    </style>
</head>
<body>

<?php include('navbar.php'); ?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <h1 class="display-3 fw-bold text-white">RescueNet Pakistan</h1>
        <p class="lead">Connecting hearts, resources, and hope in times of crisis. Together we stand stronger against disasters.</p>
        
        <div class="mt-5">
            <?php if (!isset($_SESSION['id'])): ?>
                <a href="login.php" class="btn btn-cta btn-lg me-3 mb-3 text-white">
                    <i class="fas fa-heart mr-2"></i> Donate Now
                </a>
                <a href="register.php" class="btn btn-cta btn-lg me-3 mb-3 text-white">
                    <i class="fas fa-hand-holding-heart mr-2"></i> Become a Volunteer
                </a>
            <?php else: ?>
                <a href="dashboard.php" class="btn btn-cta btn-lg me-3">
                    <i class="fas fa-tachometer-alt mr-2"></i> Go to Dashboard
                </a>
                <a href="manage_donations.php" class="btn btn-outline-light btn-lg">
                    <i class="fas fa-donate mr-2"></i> Make a Contribution
                </a>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Key Features -->
<section class="section bg-light">
    <div class="container">
        <h2 class="text-center section-title">How We Make Relief Faster & Fairer</h2>
        <div class="row g-4 align-items-stretch">
            <div class="col-md-4 mb-4">
                <div class="feature-card">
                    <div>
                        <div class="feature-icon"><i class="fas fa-hand-holding-usd"></i></div>
                        <h4>100% Transparent Donations</h4>
                        <p>Track your contribution from receipt to final distribution in real time.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="feature-card">
                    <div>
                        <div class="feature-icon"><i class="fas fa-campground"></i></div>
                        <h4>Live Camp Coordination</h4>
                        <p>Camp managers report urgent needs instantly — volunteers & aid arrive faster.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="feature-card">
                    <div>
                        <div class="feature-icon"><i class="fas fa-users"></i></div>
                        <h4>Families First</h4>
                        <p>Field teams register families, assess needs & prioritize the most vulnerable.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="feature-card">
                    <div>
                        <div class="feature-icon"><i class="fas fa-tasks"></i></div>
                        <h4>Smart Volunteer Matching</h4>
                        <p>Skills + location = perfect task assignments in relief camps nationwide.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="feature-card">
                    <div>
                        <div class="feature-icon"><i class="fas fa-chart-pie"></i></div>
                        <h4>Clear Impact Reports</h4>
                        <p>See exactly how donations & efforts are saving lives across Pakistan.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="feature-card">
                    <div>
                        <div class="feature-icon"><i class="fas fa-mobile-alt"></i></div>
                        <h4>Works on Any Device</h4>
                        <p>Donate, volunteer or update from your phone — even in low-connectivity areas.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Impact Stats -->
<section class="section">
    <div class="container text-center">
        <h2 class="section-title">Lives Touched • Hope Restored</h2>
        <div class="row g-5 mt-4">
            <div class="col-md-4">
                <div class="impact-number">+1.2M</div>
                <p class="fs-5">People Supported Since Launch</p>
            </div>
            <div class="col-md-4">
                <div class="impact-number">PKR 450M+</div>
                <p class="fs-5">In Donations & Resources Distributed</p>
            </div>
            <div class="col-md-4">
                <div class="impact-number">50K+</div>
                <p class="fs-5">Volunteer Hours in Crisis Response</p>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action -->
<section class="section bg-light">
    <div class="container text-center">
        <h2 class="section-title">Be the Change Pakistan Needs</h2>
        <p class="lead col-lg-9 mx-auto mb-5">
            Every donation, every hour volunteered, every family registered — brings us one step closer to a stronger, more resilient nation. Join hands with us today.
        </p>
        <div class="mt-4">
            <?php if (!isset($_SESSION['id'])): ?>
                <a href="register.php" class="btn btn-success btn-lg px-5 me-3">Join as Volunteer</a>
                <a href="login.php" class="btn btn-primary btn-lg px-5">Donate & Support</a>
            <?php else: ?>
                <a href="manage_donations.php" class="btn btn-success btn-lg px-5">Contribute Now</a>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Footer -->
<footer>
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <h5>RescueNet Pakistan</h5>
                <p>Empowering communities • Saving lives • Building resilience together.</p>
            </div>
            <div class="col-md-6 text-md-right">
                <p>© <?= date('Y') ?> RescueNet Pakistan. All Rights Reserved.</p>
                <p>
                    <a href="privacy.php">Privacy Policy</a> | 
                    <a href="terms.php">Terms of Use</a> | 
                    <a href="contact.php">Contact Us</a>
                </p>
            </div>
        </div>
    </div>
</footer>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>