<?php
session_start();
require_once 'includes/db.php';

// Get redirect URL from session or query parameter
$redirect = 'index.php'; // Default redirect

// Check if there's a redirect in the session
if (isset($_SESSION['redirect_after_login'])) {
    $redirect = $_SESSION['redirect_after_login'];
}

// Check if there's a redirect in the URL parameters (this takes precedence)
if (isset($_GET['redirect'])) {
    $redirect = urldecode($_GET['redirect']);
}

// Check if there's a message in the URL parameters
$message = "";
if (isset($_GET['message'])) {
    $message = urldecode($_GET['message']);
}

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    // Clear the redirect session variable
    if (isset($_SESSION['redirect_after_login'])) {
        unset($_SESSION['redirect_after_login']);
    }
    header("Location: $redirect");
    exit;
}

$error = "";

// Process login form
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];
    
    // Validate input
    if (empty($email) || empty($password)) {
        $error = "Please enter both email and password";
    } else {
        // Check if email exists
        $sql = "SELECT user_id, first_name, last_name, email, password FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            
            // Verify password
            if (password_verify($password, $user['password'])) {
                // Password is correct, create session
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['first_name'] = $user['first_name'];
                $_SESSION['last_name'] = $user['last_name'];
                $_SESSION['email'] = $user['email'];
                
                // Clear the redirect session variable
                if (isset($_SESSION['redirect_after_login'])) {
                    unset($_SESSION['redirect_after_login']);
                }
                
                // Redirect to the appropriate page
                header("Location: $redirect");
                exit;
            } else {
                $error = "Invalid email or password";
            }
        } else {
            $error = "Invalid email or password";
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
    <title>Login - SkyWay Airlines</title>
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
    <!-- Include Header -->
    <?php include 'includes/header.php'; ?>

    <!-- Login Form -->
    <div class="container">
        <div class="auth-container">
            <div class="auth-logo">
                <i class="fas fa-plane-departure"></i>
            </div>
            <h2 class="auth-title">Login to Your Account</h2>
            
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($message)): ?>
                <div class="alert alert-info" role="alert">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>
            
            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . (isset($_GET['redirect']) ? '?redirect=' . urlencode($_GET['redirect']) : ''); ?>">
                <div class="form-floating mb-3">
                    <input type="email" class="form-control" id="email" name="email" placeholder="name@example.com" required>
                    <label for="email">Email address</label>
                </div>
                <div class="form-floating mb-3">
                    <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                    <label for="password">Password</label>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="remember">
                        <label class="form-check-label" for="remember">Remember me</label>
                    </div>
                    <a href="forgot-password.php" class="forgot-password">Forgot password?</a>
                </div>
                <button type="submit" class="btn btn-auth">Login</button>
            </form>
            
            <div class="auth-footer">
                <p>Don't have an account? <a href="register.php">Register now</a></p>
            </div>
        </div>
    </div>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Include Footer -->
    <?php include 'includes/footer.php'; ?>
</body>
</html>
