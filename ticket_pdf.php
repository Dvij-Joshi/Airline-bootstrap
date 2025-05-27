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

// Create a simple HTML ticket that will be converted to PDF using browser's print-to-PDF functionality
header('Content-Type: text/html');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Boarding Pass - <?php echo $booking_reference; ?></title>
    <style>
        @page {
            size: 8.5in 11in;
            margin: 0.5in;
        }
        body {
            font-family: 'Arial', 'Helvetica', sans-serif;
            margin: 0;
            padding: 0;
            background-color: white;
            color: #333;
        }
        .ticket-container {
            width: 100%;
            max-width: 750px;
            margin: 0 auto;
            background-color: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .ticket-header {
            background: linear-gradient(135deg, #0077c0, #005fa3);
            color: white;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .logo {
            font-size: 24px;
            font-weight: bold;
            letter-spacing: 0.5px;
            display: flex;
            align-items: center;
        }
        .logo:before {
            content: '✈';
            margin-right: 8px;
            font-size: 20px;
        }
        .ticket-type {
            font-size: 16px;
            opacity: 0.9;
            margin-top: 5px;
        }
        .flight-details {
            text-align: right;
            font-size: 15px;
        }
        .ticket-body {
            padding: 25px;
            position: relative;
        }
        .passenger-info {
            display: flex;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }
        .info-column {
            flex: 1;
            min-width: 200px;
            margin-bottom: 20px;
            padding-right: 15px;
        }
        .info-item {
            margin-bottom: 20px;
            position: relative;
        }
        .info-item:after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 0;
            width: 40px;
            height: 2px;
            background-color: #e0e0e0;
        }
        .info-label {
            font-size: 12px;
            color: #666;
            margin-bottom: 5px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
        }
        .info-value {
            font-size: 16px;
            font-weight: 600;
            color: #333;
        }
        .flight-route {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin: 30px 0;
            position: relative;
            padding: 0 20px;
        }
        .flight-route:before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50px;
            right: 50px;
            height: 1px;
            background: linear-gradient(90deg, transparent, #ddd 15%, #ddd 85%, transparent);
            transform: translateY(-50%);
            z-index: 1;
        }
        .flight-point {
            text-align: center;
            background: white;
            padding: 0 20px;
            position: relative;
            z-index: 2;
        }
        .city {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 5px;
            color: #222;
        }
        .airport {
            font-size: 14px;
            color: #777;
            font-weight: 500;
        }
        .flight-icon {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 22px;
            color: #0077c0;
            background: white;
            padding: 0 15px;
            z-index: 2;
        }
        .barcode-section {
            text-align: center;
            margin-top: 30px;
            padding: 15px;
            background-color: #f9f9f9;
            border-radius: 6px;
            border: 1px solid #eee;
        }
        .barcode {
            font-family: 'Courier New', monospace;
            font-size: 16px;
            letter-spacing: 3px;
            margin-bottom: 10px;
            font-weight: bold;
            padding: 10px;
            background: white;
            border: 1px solid #eee;
            display: inline-block;
            min-width: 60%;
        }
        .ticket-footer {
            background-color: #f8f9fa;
            padding: 15px 25px;
            border-top: 1px dashed #ddd;
            font-size: 12px;
            color: #666;
            display: flex;
            justify-content: space-between;
        }
        .print-instructions {
            text-align: center;
            margin: 30px auto;
            padding: 20px;
            background-color: white;
            border-radius: 8px;
            font-size: 14px;
            color: #666;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            max-width: 750px;
            border: 1px solid #eee;
        }
        .instruction-box {
            margin: 20px 0;
            text-align: left;
        }
        .instruction-step {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }
        .step-number {
            width: 28px;
            height: 28px;
            background-color: #0077c0;
            border-radius: 50%;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 15px;
            font-size: 14px;
        }
        .step-text {
            flex: 1;
            text-align: left;
        }
        .print-button {
            display: inline-block;
            background: linear-gradient(135deg, #0077c0, #005fa3);
            color: white;
            padding: 12px 30px;
            border-radius: 50px;
            text-decoration: none;
            margin-top: 15px;
            cursor: pointer;
            font-weight: 600;
            border: none;
            box-shadow: 0 4px 10px rgba(0,119,192,0.3);
            transition: all 0.3s ease;
        }
        .print-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(0,119,192,0.4);
        }
        @media print {
            .print-instructions {
                display: none;
            }
            .ticket-container {
                box-shadow: none;
                margin: 0 auto;
            }
        }
    </style>
</head>
<body>
    <div class="ticket-container">
        <div class="ticket-header">
            <div>
                <div class="logo">SkyWay Airlines</div>
                <div class="ticket-type">Boarding Pass</div>
            </div>
            <div>
                <div class="flight-details">
                    <div style="font-weight: bold;">Flight <?php echo htmlspecialchars($flight_number); ?></div>
                    <div><?php echo $flight_date; ?></div>
                </div>
            </div>
        </div>
        
        <div class="ticket-body">
            <div class="passenger-info">
                <div class="info-column">
                    <div class="info-item">
                        <div class="info-label">Passenger Name</div>
                        <div class="info-value"><?php echo htmlspecialchars($passenger_name); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Class</div>
                        <div class="info-value"><?php echo htmlspecialchars(ucfirst($seat_class)); ?></div>
                    </div>
                </div>
                <div class="info-column">
                    <div class="info-item">
                        <div class="info-label">Seat</div>
                        <div class="info-value"><?php echo htmlspecialchars($seat_number); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Boarding Time</div>
                        <div class="info-value"><?php echo $flight_time; ?></div>
                    </div>
                </div>
                <div class="info-column">
                    <div class="info-item">
                        <div class="info-label">Booking Reference</div>
                        <div class="info-value"><?php echo $booking_reference; ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Ticket Number</div>
                        <div class="info-value"><?php echo $ticket_number; ?></div>
                    </div>
                </div>
            </div>
            
            <div class="flight-route">
                <div class="flight-point">
                    <div class="city"><?php echo htmlspecialchars($departure_city); ?></div>
                    <div class="airport">International Airport</div>
                </div>
                <div class="flight-icon">✈</div>
                <div class="flight-point">
                    <div class="city"><?php echo htmlspecialchars($arrival_city); ?></div>
                    <div class="airport">International Airport</div>
                </div>
            </div>
            
            <div class="barcode-section">
                <div class="barcode"><?php echo $booking_reference . ' ' . $ticket_number; ?></div>
                <div style="font-size: 12px; color: #666; margin-top: 8px;">Please present this boarding pass at the gate</div>
            </div>
        </div>
        
        <div class="ticket-footer">
            <div>Please arrive at the airport at least 2 hours before departure time.</div>
            <div>SkyWay Airlines &copy; <?php echo date('Y'); ?></div>
        </div>
    </div>
    
    <div class="print-instructions">
        <p>To save this boarding pass as a PDF:</p>
        <div class="instruction-box">
            <div class="instruction-step">
                <div class="step-number">1</div>
                <div class="step-text">Click the "Save as PDF" button below</div>
            </div>
            <div class="instruction-step">
                <div class="step-number">2</div>
                <div class="step-text">In the print dialog, select "Save as PDF" as the destination</div>
            </div>
            <div class="instruction-step">
                <div class="step-number">3</div>
                <div class="step-text">Click "Save" and choose where to save your PDF ticket</div>
            </div>
        </div>
        <button onclick="window.print();" class="print-button">Save as PDF</button>
    </div>
    
    <script>
        // Auto-trigger print dialog when the page loads
        window.onload = function() {
            // Short delay to ensure the page is fully loaded
            setTimeout(function() {
                window.print();
            }, 1000);
        };
    </script>
</body>
</html>
