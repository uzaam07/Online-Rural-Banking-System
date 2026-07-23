<?php
session_start();

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
$success = '';

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
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = isset($_POST['role']) ? $_POST['role'] : 'customer';

    // Validate input
    if (empty($username_input) || empty($name) || empty($email) || empty($phone) || empty($password) || empty($confirm_password)) {
        $error = "All fields are required";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match";
    } elseif (strlen($password) < 8) {
        $error = "Password must be at least 8 characters long";
    } else {
        // Check if username or email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        if (!$stmt) {
            $error = "An error occurred while processing your registration. Please try again later.";
        } else {
            $stmt->bind_param("ss", $username_input, $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $error = "Username or Email already registered";
            } else {
                // Hash password and insert user
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO users (username, name, email, phone, password, role) VALUES (?, ?, ?, ?, ?, ?)");
                if (!$stmt) {
                    $error = "An error occurred while processing your registration. Please try again later.";
                } else {
                    $stmt->bind_param("ssssss", $username_input, $name, $email, $phone, $hashed_password, $role);

                    if ($stmt->execute()) {
                        // Registration successful, redirect to login with success message
                        header("Location: login.php?registered=1");
                        exit();
                    } else {
                        $error = "Registration failed: " . $stmt->error;
                    }
                }
            }
        }
    }

    mysqli_close($conn);
}

$page_title = "Register - Banking System";
include 'includes/header.php';
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-lg" style="background-color: #1a1a2e; color: #fff;">
                <div class="card-body p-5">
                    <h2 class="text-center mb-4" style="color: #ffff80;">Register</h2>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger" role="alert">
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($success): ?>
                        <div class="alert alert-success" role="alert">
                            <?php echo htmlspecialchars($success); ?>
                            <div class="mt-2">
                                <a href="login.php" class="btn btn-primary">Go to Login</a>
                            </div>
                        </div>
                    <?php else: ?>
                        <form method="POST" action="" id="register-form">
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username" name="username" required>
                            </div>
                            <div class="mb-3">
                                <label for="name" class="form-label">Full Name</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email address</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <div class="mb-3">
                                <label for="phone" class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" id="phone" name="phone" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                                <div class="form-text text-muted">Password must be at least 8 characters long</div>
                            </div>
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Confirm Password</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            </div>
                            <div class="mb-3">
                                <label for="role" class="form-label">Register As</label>
                                <select class="form-control" id="role" name="role" required>
                                    <option value="customer" selected>Customer</option>
                                    <option value="collector">Collector</option>
                                    <option value="admin">Admin</option>
                                </select>
                            </div>
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">Register</button>
                            </div>
                        </form>

                        <div class="text-center mt-4">
                            <p class="mb-0">Already have an account? <a href="login.php" class="text-warning">Login here</a></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

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

.form-text {
    color: #aaa !important;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    if (window.location.hash === '#register-form') {
        var form = document.getElementById('register-form');
        if (form) {
            form.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }
});
</script>

<?php include 'includes/footer.php'; ?> 
