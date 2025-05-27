<?php
session_start();
require_once 'includes/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Check if form data is submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit();
}

// Get user data
$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM users WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Get form data
$flight_id = isset($_POST['flight_id']) ? $_POST['flight_id'] : '';
$class = isset($_POST['class']) ? $_POST['class'] : '';
$price = isset($_POST['price']) ? $_POST['price'] : 0;
$full_name = isset($_POST['full_name']) ? $_POST['full_name'] : '';
$email = isset($_POST['email']) ? $_POST['email'] : '';
$phone = isset($_POST['phone']) ? $_POST['phone'] : '';
$gender = isset($_POST['gender']) ? $_POST['gender'] : '';
$dob = isset($_POST['dob']) ? $_POST['dob'] : '';
$nationality = isset($_POST['nationality']) ? $_POST['nationality'] : '';
$address = isset($_POST['address']) ? $_POST['address'] : '';
$passport_no = isset($_POST['passport_no']) ? $_POST['passport_no'] : '';
$seat_number = isset($_POST['seat_number']) ? $_POST['seat_number'] : '';
$meal_preference = isset($_POST['meal_preference']) ? $_POST['meal_preference'] : 'Regular';
$special_requests = isset($_POST['special_requests']) ? $_POST['special_requests'] : '';
$total_price = isset($_POST['total_price']) ? $_POST['total_price'] : 0;
$payment_method = isset($_POST['payment_method']) ? $_POST['payment_method'] : '';

// Get flight details
$flight_query = "SELECT * FROM flights WHERE flight_id = ?";
if ($stmt = $conn->prepare($flight_query)) {
    $stmt->bind_param('i', $flight_id);
    $stmt->execute();
    $flight = $stmt->get_result()->fetch_assoc();
    $stmt->close();
} else {
    // Handle error if prepare statement fails
    $flight = null;
    error_log("Database error retrieving flight: " . $conn->error);
}

// Generate booking reference (alphanumeric, 6 characters)
$booking_reference = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 6));

// Insert booking into database
$booking_query = "INSERT INTO bookings (user_id, flight_id, seat_number, seat_class, booking_price, booking_date, payment_status) 
                 VALUES (?, ?, ?, ?, ?, NOW(), 'Completed')";

if ($stmt = $conn->prepare($booking_query)) {
    $stmt->bind_param('iissd', 
        $user_id, 
        $flight_id, 
        $seat_number, 
        $class, 
        $total_price
    );
    
    $booking_successful = $stmt->execute();
    $booking_id = $stmt->insert_id;
    $stmt->close();
} else {
    // Handle error if prepare statement fails
    $booking_successful = false;
    error_log("Database error: " . $conn->error);
}

// Update seat as booked
if ($booking_successful && !empty($seat_number)) {
    $update_seat = "UPDATE seats SET is_booked = 1 WHERE flight_id = ? AND seat_number = ?";
    if ($stmt = $conn->prepare($update_seat)) {
        $stmt->bind_param('is', $flight_id, $seat_number);
        $stmt->execute();
        $stmt->close();
    } else {
        // Handle error if prepare statement fails
        error_log("Database error updating seat: " . $conn->error);
    }
}

// Generate a random ticket number
$ticket_number = 'TKT' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));

// Format the flight date and time
$flight_date = isset($flight['depart_date']) ? date('l, F j, Y', strtotime($flight['depart_date'])) : 'May 25, 2025';
$flight_time = isset($flight['depart_time']) ? date('h:i A', strtotime($flight['depart_time'])) : '10:30 AM';

// Set default values if flight data is not available
$flight_number = isset($flight['flight_number']) ? $flight['flight_number'] : 'SW101';
$departure_city = isset($flight['from_city']) ? $flight['from_city'] : 'New York';
$arrival_city = isset($flight['to_city']) ? $flight['to_city'] : 'London';
$departure_airport = 'International Airport';
$arrival_airport = 'International Airport';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmation - SkyWay Airlines</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/confirmation.css">
                <!-- box-shadow: none;
                margin: 0;
                border: 1px solid #dee2e6;
            }
        } -->
    </style>
</head>
<body>
    <!-- Include Header -->
    <?php include 'includes/header.php'; ?>
    
    <!-- Main Content -->
    <main class="container my-5">
        <div class="confirmation-container">
            <?php if ($booking_successful): ?>
                <div class="text-center mb-4">
                    <div class="success-icon">
                        <i class="fas fa-check"></i>
                    </div>
                    <h2>Booking Confirmed!</h2>
                    <p class="lead">Your booking has been successfully confirmed. Your booking reference is <strong><?php echo $booking_reference; ?></strong>.</p>
                    <p>A confirmation email has been sent to <strong><?php echo htmlspecialchars($email); ?></strong>.</p>
                </div>
                
                <!-- Ticket -->
                <div class="ticket-container" id="ticket">
                    <div class="ticket-header d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-0">Boarding Pass</h4>
                            <small>Electronic ticket</small>
                        </div>
                        <div class="airline-logo">
                            <i class="fas fa-plane-departure"></i> SkyWay
                        </div>
                    </div>
                    
                    <div class="ticket-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="info-row">
                                    <div class="info-label">Passenger Name:</div>
                                    <div class="info-value"><?php echo htmlspecialchars($full_name); ?></div>
                                </div>
                                <div class="info-row">
                                    <div class="info-label">Flight:</div>
                                    <div class="info-value"><?php echo htmlspecialchars($flight_number); ?></div>
                                </div>
                                <div class="info-row">
                                    <div class="info-label">Class:</div>
                                    <div class="info-value"><?php echo htmlspecialchars(ucfirst($class)); ?></div>
                                </div>
                                <div class="info-row">
                                    <div class="info-label">Seat:</div>
                                    <div class="info-value"><?php echo htmlspecialchars($seat_number); ?></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-row">
                                    <div class="info-label">Date:</div>
                                    <div class="info-value"><?php echo $flight_date; ?></div>
                                </div>
                                <div class="info-row">
                                    <div class="info-label">Time:</div>
                                    <div class="info-value"><?php echo $flight_time; ?></div>
                                </div>
                                <div class="info-row">
                                    <div class="info-label">Booking Ref:</div>
                                    <div class="info-value"><?php echo $booking_reference; ?></div>
                                </div>
                                <div class="info-row">
                                    <div class="info-label">Ticket Number:</div>
                                    <div class="info-value"><?php echo $ticket_number; ?></div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="flight-info position-relative">
                            <div class="flight-route">
                                <div class="flight-point">
                                    <div class="city"><?php echo htmlspecialchars($departure_city); ?></div>
                                    <div class="airport"><?php echo htmlspecialchars($departure_airport); ?></div>
                                </div>
                                <div class="flight-icon">
                                    <i class="fas fa-plane"></i>
                                </div>
                                <div class="flight-point">
                                    <div class="city"><?php echo htmlspecialchars($arrival_city); ?></div>
                                    <div class="airport"><?php echo htmlspecialchars($arrival_airport); ?></div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="barcode">
                            <img src="https://barcode.tec-it.com/barcode.ashx?data=<?php echo $booking_reference . $ticket_number; ?>&code=Code128&multiplebarcodes=false&translate-esc=false&unit=Fit&dpi=96&imagetype=Gif&rotation=0&color=%23000000&bgcolor=%23ffffff&codepage=&qunit=Mm&quiet=0" alt="Barcode">
                            <div class="small text-muted mt-2">Please present this boarding pass at the gate</div>
                        </div>
                    </div>
                    
                    <div class="ticket-footer">
                        <div class="row">
                            <div class="col-md-8">
                                <small class="text-muted">
                                    <i class="fas fa-info-circle me-1"></i> Please arrive at the airport at least 2 hours before departure time.
                                </small>
                            </div>
                            <div class="col-md-4 text-end">
                                <small class="text-muted">SkyWay Airlines</small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="text-center mt-4 no-print">
                    <a href="ticket_pdf.php?booking_id=<?php echo $booking_id; ?>&flight_id=<?php echo $flight_id; ?>" class="btn download-btn">
                        <i class="fas fa-download"></i> Download Ticket
                    </a>
                </div>
            <?php else: ?>
                <div class="text-center">
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i> There was an error processing your booking. Please try again.
                    </div>
                    <a href="index.php" class="btn btn-primary">Return to Homepage</a>
                </div>
            <?php endif; ?>
        </div>
    </main>
    
    <!-- Include Footer -->
    <?php include 'includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function printTicket() {
            // Create a new window for printing
            const printWindow = window.open('', '_blank');
            
            // Get the ticket HTML
            const ticketContent = document.getElementById('ticket').outerHTML;
            
            // Create the print document
            printWindow.document.write(`
                <!DOCTYPE html>
                <html>
                <head>
                    <title>Boarding Pass - ${<?php echo json_encode($booking_reference); ?>}</title>
                    <meta charset="UTF-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
                    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
                    <style>
                        body {
                            font-family: 'Arial', sans-serif;
                            padding: 20px;
                        }
                        
                        .ticket-container {
                            background-color: white;
                            border-radius: 8px;
                            overflow: hidden;
                            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
                            margin: 0 auto;
                            position: relative;
                            max-width: 800px;
                        }
                        
                        .ticket-header {
                            background-color: #0077c0;
                            color: white;
                            padding: 20px;
                            position: relative;
                        }
                        
                        .ticket-body {
                            padding: 20px;
                            position: relative;
                        }
                        
                        .ticket-footer {
                            background-color: #f8f9fa;
                            padding: 15px 20px;
                            border-top: 1px dashed #dee2e6;
                        }
                        
                        .flight-info {
                            display: flex;
                            align-items: center;
                            margin: 20px 0;
                        }
                        
                        .flight-route {
                            flex-grow: 1;
                            display: flex;
                            align-items: center;
                            justify-content: space-between;
                            padding: 0 30px;
                        }
                        
                        .flight-route::before {
                            content: '';
                            position: absolute;
                            left: 50%;
                            top: 50%;
                            width: 70%;
                            height: 1px;
                            background: linear-gradient(90deg, transparent, #dee2e6 20%, #dee2e6 80%, transparent);
                            transform: translate(-50%, -50%);
                            z-index: 1;
                        }
                        
                        .flight-point {
                            text-align: center;
                            position: relative;
                            z-index: 2;
                            background-color: white;
                            padding: 0 15px;
                        }
                        
                        .flight-icon {
                            position: absolute;
                            left: 50%;
                            top: 50%;
                            transform: translate(-50%, -50%);
                            color: #0077c0;
                            font-size: 24px;
                            z-index: 2;
                            background-color: white;
                            padding: 0 10px;
                        }
                        
                        .info-row {
                            display: flex;
                            margin-bottom: 10px;
                        }
                        
                        .info-label {
                            width: 150px;
                            font-weight: 500;
                            color: #6c757d;
                        }
                        
                        .info-value {
                            flex-grow: 1;
                            font-weight: 500;
                        }
                        
                        .barcode {
                            text-align: center;
                            margin-top: 20px;
                        }
                        
                        .barcode img {
                            max-width: 80%;
                            height: 60px;
                        }
                        
                        .airline-logo {
                            height: 40px;
                        }
                    </style>
                </head>
                <body>
                    ${ticketContent}
                    <script>
                        window.onload = function() {
                            window.print();
                            setTimeout(function() {
                                window.close();
                            }, 500);
                        };
                    </script>
                </body>
                </html>
            `);
            
            printWindow.document.close();
        }
    </script>
</body>
</html>
