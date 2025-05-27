<?php
session_start();

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: index.html");
    exit;
}

// Database connection
$db_host = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "airlines";

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$error = "";
$success = "";

// Process forgot password form
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $conn->real_escape_string($_POST['email']);
    
    // Validate input
    if (empty($email)) {
        $error = "Please enter your email address";
    } else {
        // Check if email exists
        $sql = "SELECT user_id, first_name FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            
            // Generate a unique token
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            // Store token in database (you would need to create a password_resets table)
            // For this example, we'll just simulate the process
            
            // In a real application, you would send an email with a reset link
            // For this example, we'll just show a success message
            
            $success = "A password reset link has been sent to your email address. Please check your inbox.";
        } else {
            // Don't reveal if email exists or not for security
            $success = "If your email address exists in our database, you will receive a password reset link shortly.";
        }
        
        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - SkyWay Airlines</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="index.html">
                <i class="fas fa-plane-departure me-2"></i>SkyWay
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.html">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="flights.php">Flights</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="bookings.php">My Bookings</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="login.php">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link btn-nav" href="register.php">Register</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Forgot Password Form -->
    <div class="container">
        <div class="auth-container">
            <div class="auth-logo">
                <i class="fas fa-key"></i>
            </div>
            <h2 class="auth-title">Forgot Your Password?</h2>
            <p class="text-center mb-4">Enter your email address and we'll send you a link to reset your password.</p>
            
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($success)): ?>
                <div class="alert alert-success" role="alert">
                    <?php echo $success; ?>
                </div>
            <?php else: ?>
            
            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <div class="form-floating mb-4">
                    <input type="email" class="form-control" id="email" name="email" placeholder="name@example.com" required>
                    <label for="email">Email address</label>
                </div>
                
                <button type="submit" class="btn btn-auth">Send Reset Link</button>
            </form>
            
            <?php endif; ?>
            
            <div class="auth-footer">
                <p>Remember your password? <a href="login.php">Back to Login</a></p>
            </div>
        </div>
    </div>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</body>
</html>
