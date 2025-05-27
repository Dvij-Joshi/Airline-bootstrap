<?php
session_start();
require_once 'includes/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Save the intended destination for after login
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    header("Location: login.php?message=" . urlencode("Please login to view your dashboard"));
    exit;
}

// Get user information
$user_id = $_SESSION['user_id'];
$user_query = "SELECT * FROM users WHERE user_id = ?";
$stmt = $conn->prepare($user_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();
$user = $user_result->fetch_assoc();

// Get user's bookings
$bookings_query = "SELECT b.*, f.airline, f.flight_number, f.from_city, f.to_city, f.depart_date, f.depart_time, f.arrive_date, f.arrive_time 
                  FROM bookings b 
                  JOIN flights f ON b.flight_id = f.flight_id 
                  WHERE b.user_id = ? 
                  ORDER BY b.booking_date DESC";
$stmt = $conn->prepare($bookings_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$bookings_result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Dashboard - SkyWay Airlines</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body>
    <!-- Include Header -->
    <?php include 'includes/header.php'; ?>

    <!-- Main Content -->
    <div class="container my-5">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-lg-3 mb-4">
                <div class="card user-sidebar">
                    <div class="card-body text-center">
                        <div class="user-avatar mb-3">
                            <?php if(!empty($user['photo_path'])): ?>
                                <img src="<?php echo htmlspecialchars($user['photo_path']); ?>" alt="Profile Photo" class="rounded-circle img-fluid" style="width: 120px; height: 120px; object-fit: cover;">
                            <?php else: ?>
                                <div class="default-avatar">
                                    <i class="fas fa-user-circle fa-5x text-primary"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <h5 class="card-title"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h5>
                        <p class="card-text text-muted"><?php echo htmlspecialchars($user['email']); ?></p>
                    </div>
                    <div class="list-group list-group-flush">
                        <a href="user_dashboard.php" class="list-group-item list-group-item-action active">
                            <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                        </a>
                        <a href="bookings.php" class="list-group-item list-group-item-action">
                            <i class="fas fa-ticket-alt me-2"></i> My Bookings
                        </a>
                        <a href="profile.php" class="list-group-item list-group-item-action">
                            <i class="fas fa-user me-2"></i> Profile Settings
                        </a>
                        <a href="logout.php" class="list-group-item list-group-item-action text-danger">
                            <i class="fas fa-sign-out-alt me-2"></i> Logout
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Main Content Area -->
            <div class="col-lg-9">
                <!-- Welcome Card -->
                <div class="card mb-4 welcome-card">
                    <div class="card-body">
                        <h4>Welcome back, <?php echo htmlspecialchars($user['first_name']); ?>!</h4>
                        <p>Manage your bookings and explore new flight options.</p>
                        <a href="search_results.php" class="btn">Find New Flights</a>
                    </div>
                </div>
                
                <!-- Recent Bookings -->
                <div class="card mb-4 recent-bookings-card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-ticket-alt me-2"></i>Your Recent Bookings</h5>
                    </div>
                    <div class="card-body">
                        <?php if($bookings_result->num_rows > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-hover booking-table">
                                    <thead>
                                        <tr>
                                            <th>Booking ID</th>
                                            <th>Flight</th>
                                            <th>Route</th>
                                            <th>Date</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while($booking = $bookings_result->fetch_assoc()): ?>
                                            <tr>
                                                <td>#<?php echo htmlspecialchars($booking['booking_id']); ?></td>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($booking['airline']); ?></strong><br>
                                                    <small class="text-muted"><?php echo htmlspecialchars($booking['flight_number']); ?></small>
                                                </td>
                                                <td>
                                                    <?php echo htmlspecialchars($booking['from_city']); ?> → 
                                                    <?php echo htmlspecialchars($booking['to_city']); ?>
                                                </td>
                                                <td>
                                                    <?php echo date('d M Y', strtotime($booking['depart_date'])); ?><br>
                                                    <small class="text-muted"><?php echo date('h:i A', strtotime($booking['depart_time'])); ?></small>
                                                </td>
                                                <td>
                                                    <?php 
                                                    $departure_datetime = strtotime($booking['depart_date'] . ' ' . $booking['depart_time']);
                                                    $now = time();
                                                    
                                                    if($departure_datetime < $now) {
                                                        echo '<span class="badge bg-secondary">Completed</span>';
                                                    } else {
                                                        echo '<span class="badge bg-success">Upcoming</span>';
                                                    }
                                                    ?>
                                                </td>
                                                <td class="booking-actions">
                                                    <a href="view_booking.php?id=<?php echo $booking['booking_id']; ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i> View</a>
                                                    <a href="print_ticket.php?id=<?php echo $booking['booking_id']; ?>" class="btn btn-sm btn-outline-secondary"><i class="fas fa-print"></i> Print</a>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php if($bookings_result->num_rows > 5): ?>
                                <div class="text-center mt-3">
                                    <a href="bookings.php" class="btn btn-outline-primary">View All Bookings</a>
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <div class="stat-icon mx-auto mb-3" style="background-color: #f8f9fa;">
                                    <i class="fas fa-ticket-alt"></i>
                                </div>
                                <h5>No Bookings Found</h5>
                                <p class="text-muted">You haven't made any bookings yet.</p>
                                <a href="search_results.php" class="btn btn-primary">Find Flights</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Dashboard Stats -->
                <div class="dashboard-stats mb-4">
                    <div class="row">
                        <div class="col-md-3 mb-4">
                            <div class="stat-card">
                                <div class="stat-icon">
                                    <i class="fas fa-plane"></i>
                                </div>
                                <div class="stat-value"><?php echo $bookings_result->num_rows; ?></div>
                                <div class="stat-label">Total Bookings</div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-4">
                            <div class="stat-card">
                                <div class="stat-icon">
                                    <i class="fas fa-calendar-check"></i>
                                </div>
                                <div class="stat-value">0</div>
                                <div class="stat-label">Upcoming Trips</div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-4">
                            <div class="stat-card">
                                <div class="stat-icon">
                                    <i class="fas fa-history"></i>
                                </div>
                                <div class="stat-value"><?php echo $bookings_result->num_rows; ?></div>
                                <div class="stat-label">Past Trips</div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-4">
                            <div class="stat-card">
                                <div class="stat-icon">
                                    <i class="fas fa-star"></i>
                                </div>
                                <div class="stat-value">0</div>
                                <div class="stat-label">Rewards Points</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Dashboard Cards -->
                <div class="row mb-4">
                    <div class="col-md-4 mb-4">
                        <div class="dashboard-card">
                            <div class="card-body">
                                <i class="fas fa-plane"></i>
                                <h5 class="card-title">My Flights</h5>
                                <p class="card-text">View all your upcoming and past flights.</p>
                                <a href="bookings.php" class="btn btn-outline-primary">View Flights</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4">
                        <div class="dashboard-card">
                            <div class="card-body">
                                <i class="fas fa-user-cog"></i>
                                <h5 class="card-title">Profile Settings</h5>
                                <p class="card-text">Update your personal information and preferences.</p>
                                <a href="profile.php" class="btn btn-outline-primary">Edit Profile</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4">
                        <div class="dashboard-card">
                            <div class="card-body">
                                <i class="fas fa-headset"></i>
                                <h5 class="card-title">Support</h5>
                                <p class="card-text">Need help? Contact our customer support team.</p>
                                <a href="contact.php" class="btn btn-outline-primary">Get Help</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Include Footer -->
    <?php include 'includes/footer.php'; ?>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</body>
</html>
