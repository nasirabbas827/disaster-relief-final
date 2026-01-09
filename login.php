<?php
include('config.php');
session_start();

$email = $password = $role = "";
$email_err = $password_err = $login_msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (empty($_POST["email"])) {
        $email_err = "Email required";
    } else {
        $email = trim($_POST["email"]);
    }

    if (empty($_POST["password"])) {
        $password_err = "Password required";
    } else {
        $password = trim($_POST["password"]);
    }

    $role = $_POST['role'];

    if (empty($email_err) && empty($password_err)) {

        $sql = "SELECT id, name, email, password, role, status 
                FROM users 
                WHERE email = ? AND role = ?";

        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ss", $email, $role);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);

        if (mysqli_stmt_num_rows($stmt) == 1) {

            mysqli_stmt_bind_result($stmt, $id, $name, $email, $hashed_password, $db_role, $status);
            mysqli_stmt_fetch($stmt);

            if ($status != 'active') {
                $login_msg = '<div class="alert alert-warning text-center">Account inactive.</div>';
            } elseif (password_verify($password, $hashed_password)) {

                $_SESSION['id'] = $id;
                $_SESSION['name'] = $name;
                $_SESSION['role'] = $db_role;

                if ($db_role == 'camp_manager') {
                    header("Location: camp_manager/dashboard.php");
                } elseif ($db_role == 'supporter') {
                    header("Location: supporter/dashboard.php");
                } elseif ($db_role == 'field_staff') {
                    header("Location: field_staff/dashboard.php");
                }
                exit();

            } else {
                $password_err = "Incorrect password";
            }

        } else {
            $email_err = "No account found";
        }

        mysqli_stmt_close($stmt);
    }

    mysqli_close($conn);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="./css/style.css">
</head>
<body>

<?php include('navbar.php'); ?>

<div class="container mt-5">
    <div class="card mx-auto" style="max-width:500px;">
        <div class="card-body">

            <h4 class="text-center mb-4">User Login</h4>
            <?php echo $login_msg; ?>

            <form method="post">

                <div class="form-row">
                    <div class="form-group col-md-12">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control <?php echo $email_err?'is-invalid':''; ?>">
                        <div class="invalid-feedback"><?php echo $email_err; ?></div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-12">
                        <label>Password</label>
                        <input type="password" name="password" class="form-control <?php echo $password_err?'is-invalid':''; ?>">
                        <div class="invalid-feedback"><?php echo $password_err; ?></div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-12">
                        <label>Login As</label>
                        <select name="role" class="form-control" required>
                            <option value="">Select Role</option>
                            <option value="camp_manager">Camp Manager</option>
                            <option value="supporter">Supporter</option>
                            <option value="field_staff">Field Staff</option>
                        </select>
                    </div>
                </div>

                <div class="text-center">
                    <button class="btn btn-primary px-4">Login</button>
                </div>

            </form>

        </div>
    </div>
</div>

</body>
</html>
