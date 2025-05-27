<?php
session_start();
require_once 'includes/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Check if required parameters are present
if (!isset($_GET['booking_id']) || !isset($_GET['flight_id'])) {
    die("Missing required parameters");
}

$booking_id = $_GET['booking_id'];
$flight_id = $_GET['flight_id'];
$user_id = $_SESSION['user_id'];

// Get booking details
$booking_query = "SELECT * FROM bookings WHERE booking_id = ? AND user_id = ?";
if ($stmt = $conn->prepare($booking_query)) {
    $stmt->bind_param('ii', $booking_id, $user_id);
    $stmt->execute();
    $booking_result = $stmt->get_result();
    $booking = $booking_result->fetch_assoc();
    $stmt->close();
    
    if (!$booking) {
        die("Booking not found");
    }
} else {
    die("Error retrieving booking information");
}

// Get flight details
$flight_query = "SELECT * FROM flights WHERE flight_id = ?";
if ($stmt = $conn->prepare($flight_query)) {
    $stmt->bind_param('i', $flight_id);
    $stmt->execute();
    $flight_result = $stmt->get_result();
    $flight = $flight_result->fetch_assoc();
    $stmt->close();
    
    if (!$flight) {
        die("Flight not found");
    }
} else {
    die("Error retrieving flight information");
}

// Get user details
$user_query = "SELECT * FROM users WHERE user_id = ?";
if ($stmt = $conn->prepare($user_query)) {
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $user_result = $stmt->get_result();
    $user = $user_result->fetch_assoc();
    $stmt->close();
    
    if (!$user) {
        die("User not found");
    }
} else {
    die("Error retrieving user information");
}

// Generate booking reference if not available
$booking_reference = isset($booking['booking_reference']) ? $booking['booking_reference'] : strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 6));

// Generate a random ticket number if not available
$ticket_number = 'TKT' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));

// Format the flight date and time
$flight_date = isset($flight['depart_date']) ? date('l, F j, Y', strtotime($flight['depart_date'])) : 'May 25, 2025';
$flight_time = isset($flight['depart_time']) ? date('h:i A', strtotime($flight['depart_time'])) : '10:30 AM';

// Set flight information
$flight_number = isset($flight['flight_number']) ? $flight['flight_number'] : 'SW101';
$departure_city = isset($flight['from_city']) ? $flight['from_city'] : 'New York';
$arrival_city = isset($flight['to_city']) ? $flight['to_city'] : 'London';
$seat_number = isset($booking['seat_number']) ? $booking['seat_number'] : 'A1';
$seat_class = isset($booking['seat_class']) ? $booking['seat_class'] : 'Economy';
$passenger_name = $user['first_name'] . ' ' . $user['last_name'];
$booking_price = isset($booking['booking_price']) ? '$' . number_format($booking['booking_price'], 2) : '$450.00';

// Set the filename for download
$filename = 'boarding_pass_' . $booking_reference . '.html';

// Set headers to force download
header('Content-Type: text/html');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: max-age=0');

// Output the HTML content
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Boarding Pass - <?php echo $booking_reference; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/ticket.css">
    <!-- All styles moved to ticket.css -->
</head>
<body>
    <div class="ticket-container">
        <div class="ticket-header">
            <div>
                <h2>Boarding Pass</h2>
                <small>Electronic ticket</small>
            </div>
            <div class="airline-logo">
                <i class="fas fa-plane-departure"></i> SkyWay
            </div>
        </div>
        
        <div class="ticket-body">
            <div class="passenger-info">
                <div class="info-column">
                    <div class="info-item">
                        <span class="info-label">Passenger Name</span>
                        <span class="info-value"><?php echo htmlspecialchars($passenger_name); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Flight</span>
                        <span class="info-value"><?php echo htmlspecialchars($flight_number); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Class</span>
                        <span class="info-value"><?php echo htmlspecialchars(ucfirst($seat_class)); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Seat</span>
                        <span class="info-value"><?php echo htmlspecialchars($seat_number); ?></span>
                    </div>
                </div>
                <div class="info-column">
                    <div class="info-item">
                        <span class="info-label">Date</span>
                        <span class="info-value"><?php echo $flight_date; ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Time</span>
                        <span class="info-value"><?php echo $flight_time; ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Booking Reference</span>
                        <span class="info-value"><?php echo $booking_reference; ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Ticket Number</span>
                        <span class="info-value"><?php echo $ticket_number; ?></span>
                    </div>
                </div>
            </div>
            
            <div class="flight-info">
                <div class="flight-route">
                    <div class="flight-point">
                        <div class="city"><?php echo htmlspecialchars($departure_city); ?></div>
                        <div class="airport">International Airport</div>
                    </div>
                    <div class="flight-point">
                        <div class="city"><?php echo htmlspecialchars($arrival_city); ?></div>
                        <div class="airport">International Airport</div>
                    </div>
                </div>
                <div class="flight-icon">✈</div>
            </div>
            
            <div class="barcode">
                <div class="barcode-text"><?php echo $booking_reference . $ticket_number; ?></div>
                <div class="barcode-note">Please present this boarding pass at the gate</div>
            </div>
        </div>
        
        <div class="ticket-footer">
            <div style="display: flex; justify-content: space-between;">
                <div>
                    <i>Please arrive at the airport at least 2 hours before departure time.</i>
                </div>
                <div>
                    <strong>SkyWay Airlines</strong>
                </div>
            </div>
        </div>
    </div>
    
    <div class="print-instructions">
        <p>To print this boarding pass, please use your browser's print function (Ctrl+P or Cmd+P).</p>
    </div>
    
    <!-- Include Footer -->
    <?php include 'includes/footer.php'; ?>
</body>
</html>
