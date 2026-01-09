<nav class="navbar navbar-expand-lg navbar-dark bg-danger">
    <a class="navbar-brand" href="dashboard.php">RescueNet Pakistan</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ml-auto">
            <?php
            if (isset($_SESSION["id"]) && !empty($_SESSION["id"])) {
                echo '<li class="nav-item"><a class="nav-link" href="dashboard.php">Home</a></li>';
                echo '<li class="nav-item"><a class="nav-link" href="profile.php">Profile</a></li>';
                echo '<li class="nav-item"><a class="nav-link" href="view_camp.php">Camp</a></li>';
                echo '<li class="nav-item"><a class="nav-link" href="manage_needs.php">Camp Needs</a></li>';
                echo '<li class="nav-item"><a class="nav-link" href="manage_tasks.php">Volunteer Tasks</a></li>';
                echo '<li class="nav-item"><a class="nav-link" href="families.php">Families</a></li>';
                echo '<li class="nav-item"><a class="nav-link" href="view_resouces.php">Resources</a></li>';
                echo '<li class="nav-item"><a class="nav-link" href="manage_aid.php">Aid Distribution</a></li>';
                echo '<li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>';
            } else {
                echo '<li class="nav-item"><a class="nav-link" href="register.php">Register</a></li>';
                echo '<li class="nav-item"><a class="nav-link" href="login.php">Login</a></li>';
                echo '<li class="nav-item"><a class="nav-link" href="./admin/admin_login.php">Admin Login</a></li>';

            }
            ?>
        </ul>
    </div>
</nav>
