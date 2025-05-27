<?php
// Check if session is started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get user information if logged in
if (isset($_SESSION['user_id']) && !isset($user)) {
    require_once 'db.php';
    $user_id = $_SESSION['user_id'];
    $user_query = "SELECT * FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($user_query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $user_result = $stmt->get_result();
    $user = $user_result->fetch_assoc();
    $stmt->close();
}
?>

<!-- Navigation Bar -->
<nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container">
        <a class="navbar-brand" href="index.php"><i class="fas fa-plane-departure me-2"></i>SkyWay</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="index.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="flights.php">Flights</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="user_dashboard.php">My Bookings</a>
                </li>
                <?php if(isset($_SESSION['user_id'])): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle me-1"></i> <?php echo isset($user['first_name']) ? htmlspecialchars($user['first_name']) : 'My Account'; ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="user_dashboard.php"><i class="fas fa-user me-2"></i>My Profile</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Sign Out</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link btn-nav" href="register.php">Register</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
