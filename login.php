<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
$page_title = 'Login - Banking System';

// If user is already logged in, redirect to appropriate dashboard
if (isset($_SESSION['user_id'])) {
    switch ($_SESSION['role']) {
        case 'admin':
            header("Location: admin_dashboard.php");
            break;
        case 'collector':
            header("Location: collector_dashboard.php");
            break;
        case 'customer':
            header("Location: customer_dashboard.php");
            break;
        default:
            header("Location: index.php");
    }
    exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "bank";

    $conn = mysqli_connect($servername, $username, $password, $dbname);
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }

    $username_input = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, name, username, password, role FROM users WHERE username = ?");
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("s", $username_input);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            // Redirect based on role
            switch ($user['role']) {
                case 'admin':
                    header("Location: admin_dashboard.php");
                    break;
                case 'collector':
                    header("Location: collector_dashboard.php");
                    break;
                case 'customer':
                    header("Location: customer_dashboard.php");
                    break;
                default:
                    header("Location: index.php");
            }
            exit();
        } else {
            $error = "Invalid password";
        }
    } else {
        $error = "User not found";
    }

    mysqli_close($conn);
}

include 'includes/header.php';
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-lg" style="background-color: #1a1a2e; color: #fff;">
                <div class="card-body p-5">
                    <h2 class="text-center mb-4" style="color: #ffff80;">Login</h2>
                    <?php if (isset($_GET['registered']) && $_GET['registered'] == 1): ?>
                        <div class="alert alert-success" role="alert">
                            Registration successful! Please login.
                        </div>
                    <?php endif; ?>
                    <?php if ($error): ?>
                        <div class="alert alert-danger" role="alert">
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="" id="login-form">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Login</button>
                        </div>
                    </form>

                    <div class="text-center mt-4">
                        <p class="mb-0">Don't have an account? <a href="register.php" class="text-warning">Register here</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Scroll to login form if hash is present
if (window.location.hash === '#login-form') {
    document.addEventListener('DOMContentLoaded', function() {
        var form = document.getElementById('login-form');
        if (form) {
            form.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    });
}
</script>

<style>
.card {
    border: none;
    border-radius: 15px;
}

.form-control {
    background-color: #22223b;
    border: 1px solid #2a2a3c;
    color: #fff;
}

.form-control:focus {
    background-color: #22223b;
    border-color: #e94560;
    color: #fff;
    box-shadow: 0 0 0 0.25rem rgba(233, 69, 96, 0.25);
}

.btn-primary {
    background: #e94560;
    border: none;
    padding: 0.8rem;
    font-weight: 500;
    transition: all 0.3s ease;
}

.btn-primary:hover {
    background: #d13a52;
    transform: translateY(-2px);
}

.alert {
    background-color: rgba(220, 53, 69, 0.1);
    border: 1px solid rgba(220, 53, 69, 0.2);
    color: #dc3545;
}

.alert-success {
    background-color: rgba(25, 135, 84, 0.1);
    border: 1px solid rgba(25, 135, 84, 0.2);
    color: #198754;
}
</style>

<?php include 'includes/footer.php'; ?> 
