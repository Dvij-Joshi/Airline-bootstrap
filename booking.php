<?php
// booking.php: Flight booking form
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Save the intended destination for after login
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    header("Location: login.php?message=" . urlencode("Please login to book a flight"));
    exit;
}

// Database connection
$db_host = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "airlines";
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get user information
$user_id = $_SESSION['user_id'];
$user_query = "SELECT * FROM users WHERE user_id = ?";
$stmt = $conn->prepare($user_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();
$user = $user_result->fetch_assoc();

// Get the current step (default to 1)
$current_step = isset($_GET['step']) ? intval($_GET['step']) : 1;

// Get the selected class (default to Economy)
$selected_class = isset($_GET['class']) ? $_GET['class'] : 'Economy';
$flight_id = isset($_GET['flight_id']) ? intval($_GET['flight_id']) : 0;
$flight = null;
$seats = [];
if ($flight_id > 0) {
    $flight_res = $conn->query("SELECT * FROM flights WHERE flight_id = $flight_id");
    if ($flight_res && $flight_res->num_rows > 0) {
        $flight = $flight_res->fetch_assoc();
    }
    $seat_res = $conn->query("SELECT seat_number FROM seats WHERE flight_id = $flight_id AND is_booked = 0");
    while ($row = $seat_res->fetch_assoc()) {
        $seats[] = $row['seat_number'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Flight - SkyWay Airlines</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/style.css">
    <!-- Booking specific CSS -->
    <link rel="stylesheet" href="css/booking.css">
</head>
<body>
    <!-- Include Header -->
    <?php include 'includes/header.php'; ?>
    <!-- Main Content -->
    <div class="container my-5">
        <h2 class="mb-4 section-title text-center">Flight Booking</h2>
        
        <!-- Progress Bar -->
        <div class="booking-progress">
            <div class="row">
                <div class="col-4 progress-step <?php echo ($current_step >= 1) ? 'active' : ''; ?>">
                    <div class="step-icon">
                        <i class="fas fa-plane"></i>
                    </div>
                    <div class="step-text">Flight Details</div>
                </div>
                <div class="col-4 progress-step <?php echo ($current_step >= 2) ? 'active' : ''; ?>">
                    <div class="step-icon">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="step-text">Passenger Information</div>
                </div>
                <div class="col-4 progress-step <?php echo ($current_step >= 3) ? 'active' : ''; ?>">
                    <div class="step-icon">
                        <i class="fas fa-credit-card"></i>
                    </div>
                    <div class="step-text">Payment</div>
                </div>
            </div>
        </div>
        
        <?php if ($flight): ?>
        <!-- Flight Details Card -->
        <div class="card flight-details-card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Flight Details</h5>
            </div>
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-2 text-center">
                        <div class="airline-logo mb-2">
                            <i class="fas fa-plane fa-2x text-primary"></i>
                        </div>
                        <div class="airline-name">
                            <strong><?php echo htmlspecialchars($flight['airline']); ?></strong>
                        </div>
                        <div class="flight-number small text-muted">
                            <?php echo htmlspecialchars($flight['flight_number']); ?>
                        </div>
                    </div>
                    <div class="col-md-7">
                        <div class="flight-route d-flex align-items-center">
                            <div class="origin text-center">
                                <div class="city-code fs-4 fw-bold"><?php echo substr($flight['from_city'], 0, 3); ?></div>
                                <div class="city-name"><?php echo htmlspecialchars($flight['from_city']); ?></div>
                                <div class="departure-time small">
                                    <?php echo date('h:i A', strtotime($flight['depart_time'])); ?>
                                </div>
                                <div class="departure-date small text-muted">
                                    <?php echo date('d M Y', strtotime($flight['depart_date'])); ?>
                                </div>
                            </div>
                            <div class="flight-path flex-grow-1 px-3">
                                <div class="flight-line position-relative">
                                    <hr>
                                    <i class="fas fa-plane position-absolute top-50 start-50 translate-middle"></i>
                                </div>
                                <?php 
                                // Calculate flight duration
                                $depart = strtotime($flight['depart_date'] . ' ' . $flight['depart_time']);
                                $arrive = strtotime($flight['arrive_date'] . ' ' . $flight['arrive_time']);
                                $duration = $arrive - $depart;
                                $hours = floor($duration / 3600);
                                $minutes = floor(($duration % 3600) / 60);
                                ?>
                                <div class="flight-duration text-center small text-muted">
                                    <?php echo $hours; ?>h <?php echo $minutes; ?>m
                                </div>
                            </div>
                            <div class="destination text-center">
                                <div class="city-code fs-4 fw-bold"><?php echo substr($flight['to_city'], 0, 3); ?></div>
                                <div class="city-name"><?php echo htmlspecialchars($flight['to_city']); ?></div>
                                <div class="arrival-time small">
                                    <?php echo date('h:i A', strtotime($flight['arrive_time'])); ?>
                                </div>
                                <div class="arrival-date small text-muted">
                                    <?php echo date('d M Y', strtotime($flight['arrive_date'])); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 text-center">
                        <div class="price-tag">
                            <?php 
                            // Get class from URL parameter
                            $class = isset($_GET['class']) ? $_GET['class'] : 'Economy';
                            $price = $flight['price'];
                            
                            // Adjust price based on class
                            if ($class == 'Business') {
                                $price = $price * 1.5; // 50% more for Business class
                            } elseif ($class == 'First') {
                                $price = $price * 2.5; // 150% more for First class
                            }
                            ?>
                            <span class="price-display">$<?php echo number_format($price, 2); ?></span>
                        </div>
                        <div class="class-badge mt-2">
                            <span class="badge bg-info text-dark"><?php echo htmlspecialchars($class); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 1: Flight Details & Seat Selection (Only show if current_step is 1) -->
        <?php if ($current_step == 1): ?>
        <div class="card booking-form-card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-chair me-2"></i>Select Your Seat</h5>
            </div>
            <div class="card-body">
                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="fw-bold mb-0">Select Class:</h5>
                        <div class="btn-group class-selection" role="group">
                            <a href="?flight_id=<?php echo $flight_id; ?>&class=Economy" class="btn <?php echo ($selected_class == 'Economy') ? 'btn-success' : 'btn-outline-success'; ?>"><i class="fas fa-chair me-2"></i>Economy</a>
                            <a href="?flight_id=<?php echo $flight_id; ?>&class=Business" class="btn <?php echo ($selected_class == 'Business') ? 'btn-primary' : 'btn-outline-primary'; ?>"><i class="fas fa-briefcase me-2"></i>Business</a>
                            <a href="?flight_id=<?php echo $flight_id; ?>&class=First" class="btn <?php echo ($selected_class == 'First') ? 'btn-secondary' : 'btn-outline-secondary'; ?>"><i class="fas fa-crown me-2"></i>First</a>
                        </div>
                    </div>
                    
                    <div class="alert alert-info" role="alert">
                        <i class="fas fa-info-circle me-2"></i> Please select a seat to continue with your booking.
                    </div>
                    
                    <div class="alert alert-info">
                        <div class="d-flex align-items-center">
                            <div class="me-3"><i class="fas fa-info-circle"></i></div>
                            <div>
                                <strong>Available Seats:</strong> 
                                <?php 
                                // Count the actual number of seats displayed in the seat map
                                // Define rows and columns based on class
                                $rows = 10; // Default for Economy
                                $cols = 6;  // 3-3 configuration
                                
                                if ($selected_class == 'Business') {
                                    $rows = 5;  // Fewer rows for Business
                                    $cols = 4;  // 2-2 configuration
                                } elseif ($selected_class == 'First') {
                                    $rows = 3;  // Even fewer rows for First
                                    $cols = 4;  // 2-2 configuration
                                }
                                
                                // Calculate total seats based on the actual seat map
                                $total_seats = $rows * $cols;
                                
                                // Get booked seats count for the selected class
                                $booked_seats_query = "SELECT COUNT(*) as booked FROM seats WHERE flight_id = ? AND seat_class = ? AND is_booked = 1";
                                $stmt = $conn->prepare($booked_seats_query);
                                $stmt->bind_param("is", $flight_id, $selected_class);
                                $stmt->execute();
                                $booked_result = $stmt->get_result();
                                $booked_data = $booked_result->fetch_assoc();
                                
                                // Calculate available seats
                                $available_seats = $total_seats - $booked_data['booked'];
                                
                                echo $available_seats . ' of ' . $total_seats . ' seats available';
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <h5 class="mb-3">Select a seat from the diagram below:</h5>
                
                <div class="seat-selection-container">
                    <div class="seat-legend">
                        <div><span class="seat-demo available"></span> Available</div>
                        <div><span class="seat-demo selected"></span> Selected</div>
                        <div><span class="seat-demo booked"></span> Booked</div>
                    </div>
                    
                    <form action="booking.php" method="get" id="seatForm">
                        <input type="hidden" name="flight_id" value="<?php echo $flight_id; ?>">
                        <input type="hidden" name="class" value="<?php echo $selected_class; ?>">
                        <input type="hidden" name="step" value="2">
                        <input type="hidden" name="selected_seat" id="selected_seat_input" value="">
                        
                        <div class="seat-matrix seat-class-<?php echo strtolower($selected_class); ?>">
                            <?php
                            // Get all seats for this flight and class
                            $seats_query = "SELECT seat_number, is_booked FROM seats WHERE flight_id = ? AND seat_class = ? ORDER BY seat_number";
                            $stmt = $conn->prepare($seats_query);
                            $stmt->bind_param("is", $flight_id, $selected_class);
                            $stmt->execute();
                            $seats_result = $stmt->get_result();
                            
                            $seats_data = [];
                            while ($seat = $seats_result->fetch_assoc()) {
                                $seats_data[$seat['seat_number']] = $seat['is_booked'];
                            }
                            
                            // Define rows and columns based on class
                            $rows = 10; // Default for Economy
                            $cols = 6;  // 3-3 configuration
                            
                            if ($selected_class == 'Business') {
                                $rows = 5;  // Fewer rows for Business
                                $cols = 4;  // 2-2 configuration
                            } elseif ($selected_class == 'First') {
                                $rows = 3;  // Even fewer rows for First
                                $cols = 4;  // 2-2 configuration
                            }
                            
                            // Generate seat map
                            $row_letters = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'J', 'K'];
                            
                            for ($row = 0; $row < $rows; $row++) {
                                $row_number = $row + 1;
                                
                                // Row label
                                echo "<div class='row-label'>Row $row_number</div>";
                                
                                for ($col = 0; $col < $cols; $col++) {
                                    // Add aisle
                                    if ($selected_class == 'Economy' && $col == 3) {
                                        echo "<div class='aisle'></div>";
                                    } elseif (($selected_class == 'Business' || $selected_class == 'First') && $col == 2) {
                                        echo "<div class='aisle'></div>";
                                    }
                                    
                                    $seat_letter = $row_letters[$col];
                                    $seat_number = $row_number . $seat_letter;
                                    
                                    $is_booked = isset($seats_data[$seat_number]) && $seats_data[$seat_number] == 1;
                                    $seat_class = $is_booked ? 'booked' : '';
                                    $disabled = $is_booked ? 'disabled' : '';
                                    
                                    // Check if this seat is selected from URL parameter
                                    $selected_seat_param = isset($_GET['selected_seat']) ? $_GET['selected_seat'] : '';
                                    if ($seat_number === $selected_seat_param) {
                                        $seat_class .= ' selected';
                                    }
                                    
                                    echo "<div class='seat $seat_class' data-seat='$seat_number' $disabled>$seat_number</div>";
                                }
                                
                                // Add aisle indicator after every row
                                if ($row < $rows - 1) {
                                    echo "<div class='aisle-indicator'></div>";
                                }
                            }
                            ?>
                        </div>
                        
                        <div class="mt-4 d-flex justify-content-between align-items-center">
                            <a href="search_results.php" class="btn btn-back"><i class="fas fa-arrow-left me-2"></i>Back to Flights</a>
                            <button type="submit" class="btn btn-proceed" id="continueBtn" disabled><i class="fas fa-arrow-right me-2"></i>Continue to Passenger Information</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Step 2: Passenger Information Form (Only show if current_step is 2) -->
        <?php if ($current_step == 2): ?>
        <div class="card booking-form-card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-user me-2"></i>Passenger Information</h5>
            </div>
            <div class="card-body">
                <form action="payment.php" method="post" enctype="multipart/form-data" class="booking-form needs-validation" id="passengerForm" novalidate>
                    <input type="hidden" name="flight_id" value="<?php echo $flight_id; ?>">
                    <input type="hidden" name="class" value="<?php echo htmlspecialchars($class); ?>">
                    <input type="hidden" name="price" value="<?php echo $price; ?>">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="full_name" class="form-control" value="<?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>" pattern="^[A-Za-z\s.'-]{3,50}$" title="Name must be 3-50 characters and contain only letters, spaces, and common punctuation" required>
                    <div class="invalid-feedback">Please enter your full name (3-50 characters).</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" pattern="[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$" title="Please enter a valid email address" required>
                    <div class="invalid-feedback">Please enter a valid email address.</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Phone</label>
                    <input type="tel" name="phone" class="form-control" value="<?php echo htmlspecialchars($user['phone']); ?>" pattern="^[0-9+\s()-]{7,15}$" title="Phone number must be 7-15 digits and may include +, spaces, () or -" required>
                    <div class="invalid-feedback">Please enter a valid phone number.</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Gender</label>
                    <select name="gender" class="form-select" required>
                        <option value="">Select</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Date of Birth</label>
                    <input type="date" name="dob" class="form-control" value="<?php echo htmlspecialchars($user['dob']); ?>" max="<?php echo date('Y-m-d', strtotime('-1 year')); ?>" required>
                    <div class="invalid-feedback">Please enter a valid date of birth (must be in the past).</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Nationality</label>
                    <input type="text" name="nationality" class="form-control" pattern="^[A-Za-z\s-]{2,50}$" title="Nationality must contain only letters, spaces and hyphens" required>
                    <div class="invalid-feedback">Please enter a valid nationality.</div>
                </div>
                <div class="col-md-12">
                    <label class="form-label">Address</label>
                    <input type="text" name="address" class="form-control" required>
                </div>
                <!-- User photo is automatically taken from profile -->
                <div class="col-md-6">
                    <label class="form-label">Passport Number</label>
                    <input type="text" name="passport_no" class="form-control" pattern="^[A-Z0-9]{6,9}$" title="Passport number must be 6-9 characters (letters and numbers only)" required>
                    <div class="invalid-feedback">Please enter a valid passport number (6-9 characters, letters and numbers only).</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Upload Passport</label>
                    <div class="input-group">
                        <input type="file" name="passport_scan" class="form-control">
                        <span class="input-group-text bg-light"><i class="fas fa-passport text-primary"></i></span>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Govt. ID Number (optional)</label>
                    <input type="text" name="govt_id" class="form-control">
                </div>
                <!-- Seat selection is handled by the visual seat map above -->
                <input type="hidden" name="seat_number" id="selected_seat_input" value="<?php echo isset($_GET['selected_seat']) ? htmlspecialchars($_GET['selected_seat']) : ''; ?>">
                
                <div class="col-md-6">
                    <label class="form-label">Meal Preference</label>
                    <select name="meal_preference" class="form-select">
                        <option value="Regular">Regular</option>
                        <option value="Vegetarian">Vegetarian</option>
                        <option value="Vegan">Vegan</option>
                        <option value="Kosher">Kosher</option>
                        <option value="Halal">Halal</option>
                        <option value="Gluten Free">Gluten Free</option>
                    </select>
                </div>
                <div class="col-md-12">
                    <label class="form-label">Special Requests (optional)</label>
                    <textarea name="special_requests" class="form-control" rows="2"></textarea>
                </div>
                <div class="booking-action-buttons">
                    <a href="search_results.php" class="btn btn-back"><i class="fas fa-arrow-left"></i>Back to Flights</a>
                    <button type="submit" class="btn btn-proceed"><i class="fas fa-credit-card"></i>Proceed to Payment</button>
                </div>
            </div>
                </form>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if (!$flight): ?>
            <div class="alert alert-danger">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <i class="fas fa-exclamation-triangle fa-2x"></i>
                    </div>
                    <div>
                        <h5 class="alert-heading mb-1">Flight Not Found</h5>
                        <p class="mb-0">The flight you're looking for could not be found. Please go back and select a valid flight.</p>
                    </div>
                </div>
            </div>
            <div class="text-center mt-4">
                <a href="search_results.php" class="btn btn-primary px-4"><i class="fas fa-search me-2"></i>Search Flights</a>
            </div>
        <?php endif; ?>
    </div>
    <footer class="footer py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4 mb-lg-0">
                    <h5 class="text-white mb-4"><i class="fas fa-plane-departure me-2"></i>SkyWay Airlines</h5>
                    <p class="text-light">Your trusted partner for air travel. We connect people to destinations worldwide with comfort and reliability.</p>
                    <div class="social-links mt-4">
                        <a href="#" class="me-2"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="me-2"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="me-2"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 mb-4 mb-md-0">
                    <h5 class="text-white mb-4">Quick Links</h5>
                    <ul class="footer-links">
                        <li><a href="index.html">Home</a></li>
                        <li><a href="flights.php">Flights</a></li>
                        <li><a href="bookings.php">My Bookings</a></li>
                        <li><a href="about.php">About Us</a></li>
                        <li><a href="contact.php">Contact</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-4 mb-4 mb-md-0">
                    <h5 class="text-white mb-4">Popular Destinations</h5>
                    <ul class="footer-links">
                        <li><a href="search_results.php?toCity=New York">New York</a></li>
                        <li><a href="search_results.php?toCity=London">London</a></li>
                        <li><a href="search_results.php?toCity=Tokyo">Tokyo</a></li>
                        <li><a href="search_results.php?toCity=Paris">Paris</a></li>
                        <li><a href="search_results.php?toCity=Dubai">Dubai</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-4">
                    <h5 class="text-white mb-4">Contact Info</h5>
                    <ul class="footer-contact">
                        <li><i class="fas fa-map-marker-alt"></i> 123 Airport Road, New York, NY 10001</li>
                        <li><i class="fas fa-phone"></i> (123) 456-7890</li>
                        <li><i class="fas fa-envelope"></i> info@skyway.com</li>
                        <li><i class="fas fa-clock"></i> Mon-Fri: 9:00 AM - 8:00 PM</li>
                    </ul>
                </div>
            </div>
            <hr>
            <div class="text-center text-white-50 mt-3">
                &copy; <?php echo date('Y'); ?> SkyWay Airlines. All rights reserved.
            </div>
        </div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <script>
        // Form validation and seat selection functionality
        $(document).ready(function() {
            // Handle seat selection
            $('.seat').not('.booked').click(function() {
                // Remove selected class from all seats
                $('.seat').removeClass('selected');
                
                // Add selected class to clicked seat
                $(this).addClass('selected');
                
                // Update hidden input with selected seat
                var selectedSeat = $(this).data('seat');
                $('#selected_seat_input').val(selectedSeat);
                
                // Enable continue button
                $('#continueBtn').prop('disabled', false);
            });
            
            // Initialize seat selection if there's a selected seat in URL
            const urlParams = new URLSearchParams(window.location.search);
            const selectedSeat = urlParams.get('selected_seat');
            
            if (selectedSeat) {
                $("[data-seat='" + selectedSeat + "']").addClass('selected');
                $('#selected_seat_input').val(selectedSeat);
                $('#continueBtn').prop('disabled', false);
            }
            
            // Form validation
            // Fetch all forms we want to apply custom Bootstrap validation styles to
            const forms = document.querySelectorAll('.needs-validation');
            
            // Passport number validation with custom regex
            const passportInput = document.querySelector('input[name="passport_no"]');
            if (passportInput) {
                passportInput.addEventListener('input', function() {
                    const passportRegex = /^[A-Z0-9]{6,9}$/;
                    if (this.value.trim() !== '' && !passportRegex.test(this.value)) {
                        this.setCustomValidity('Passport number must be 6-9 characters (letters and numbers only)');
                    } else {
                        this.setCustomValidity('');
                    }
                });
            }
            
            // Loop over forms and prevent submission if validation fails
            Array.prototype.slice.call(forms).forEach(function(form) {
                form.addEventListener('submit', function(event) {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                        
                        // Find the first invalid field and focus it
                        const invalidField = form.querySelector(':invalid');
                        if (invalidField) {
                            invalidField.focus();
                            
                            // Scroll to the invalid field with smooth animation
                            invalidField.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        }
                    }
                    
                    form.classList.add('was-validated');
                }, false);
                
                // Real-time validation as user types
                form.querySelectorAll('input, select, textarea').forEach(function(input) {
                    input.addEventListener('blur', function() {
                        // Check validity when user leaves a field
                        if (this.checkValidity()) {
                            this.classList.remove('is-invalid');
                            this.classList.add('is-valid');
                        } else {
                            this.classList.remove('is-valid');
                            this.classList.add('is-invalid');
                        }
                    });
                });
            });
        });
    </script>
    
    <!-- Include Footer -->
    <?php include 'includes/footer.php'; ?>
</body>
</html>
<?php endif; ?>
