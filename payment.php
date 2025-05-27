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

// Get flight details
$flight_query = "SELECT * FROM flights WHERE flight_id = ?";
$stmt = $conn->prepare($flight_query);
$stmt->bind_param('i', $flight_id);
$stmt->execute();
$flight = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$flight) {
    $_SESSION['error'] = "Flight not found.";
    header('Location: index.php');
    exit();
}

// Calculate taxes and fees (typically 10-15% of base fare)
$tax_rate = 0.12; // 12% tax
$taxes = round($price * $tax_rate, 2);
$service_fee = 25.00; // Fixed service fee
$total_price = $price + $taxes + $service_fee;

// Current step in booking process
$current_step = 3; // Payment step
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment - SkyWay Airlines</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/payment.css">
</head>
<body>
    <!-- Include Header -->
    <?php include 'includes/header.php'; ?>
    
    <!-- Main Content -->
    <main class="container my-5">
        <!-- Progress Indicator -->
        <div class="booking-progress mb-5">
            <div class="row">
                <div class="col-md-4 progress-step">
                    <div class="step-icon">
                        <i class="fas fa-plane"></i>
                    </div>
                    <div class="step-text">Flight Details</div>
                </div>
                <div class="col-md-4 progress-step">
                    <div class="step-icon">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="step-text">Passenger Information</div>
                </div>
                <div class="col-md-4 progress-step active">
                    <div class="step-icon">
                        <i class="fas fa-credit-card"></i>
                    </div>
                    <div class="step-text">Payment</div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <!-- Payment Form -->
            <div class="col-lg-8">
                <div class="payment-card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-credit-card me-2"></i> Payment Details</h5>
                    </div>
                    <div class="card-body">
                        <form action="confirmation.php" method="post" id="payment-form" class="needs-validation" novalidate>
                            <!-- Hidden fields to pass data -->
                            <input type="hidden" name="flight_id" value="<?php echo htmlspecialchars($flight_id); ?>">
                            <input type="hidden" name="class" value="<?php echo htmlspecialchars($class); ?>">
                            <input type="hidden" name="price" value="<?php echo htmlspecialchars($price); ?>">
                            <input type="hidden" name="full_name" value="<?php echo htmlspecialchars($full_name); ?>">
                            <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">
                            <input type="hidden" name="phone" value="<?php echo htmlspecialchars($phone); ?>">
                            <input type="hidden" name="gender" value="<?php echo htmlspecialchars($gender); ?>">
                            <input type="hidden" name="dob" value="<?php echo htmlspecialchars($dob); ?>">
                            <input type="hidden" name="nationality" value="<?php echo htmlspecialchars($nationality); ?>">
                            <input type="hidden" name="address" value="<?php echo htmlspecialchars($address); ?>">
                            <input type="hidden" name="passport_no" value="<?php echo htmlspecialchars($passport_no); ?>">
                            <input type="hidden" name="seat_number" value="<?php echo htmlspecialchars($seat_number); ?>">
                            <input type="hidden" name="meal_preference" value="<?php echo htmlspecialchars($meal_preference); ?>">
                            <input type="hidden" name="special_requests" value="<?php echo htmlspecialchars($special_requests); ?>">
                            <input type="hidden" name="total_price" value="<?php echo htmlspecialchars($total_price); ?>">
                            
                            <!-- Payment Method Selection -->
                            <div style="background-color: white; border-radius: 10px; padding: 20px; margin-bottom: 25px; box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1); border: 1px solid #e9ecef;">
                                <h5 class="mb-4">Select Payment Method</h5>
                                
                                <div class="payment-methods mb-4">
                                <div class="payment-method selected" data-method="credit-card">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="payment_method" id="credit-card" value="credit-card" checked>
                                        <label class="form-check-label d-flex align-items-center" for="credit-card">
                                            <span class="me-2">Credit/Debit Card</span>
                                            <div class="ms-auto">
                                                <img src="images/visa.png" alt="Visa" class="me-1">
                                                <img src="images/mastercard.png" alt="Mastercard" class="me-1">
                                                <img src="images/amex.png" alt="American Express">
                                            </div>
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="payment-method" data-method="paypal">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="payment_method" id="paypal" value="paypal">
                                        <label class="form-check-label d-flex align-items-center" for="paypal">
                                            <span class="me-2">PayPal</span>
                                            <div class="ms-auto">
                                                <img src="images/paypal.png" alt="PayPal">
                                            </div>
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="payment-method" data-method="apple-pay">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="payment_method" id="apple-pay" value="apple-pay">
                                        <label class="form-check-label d-flex align-items-center" for="apple-pay">
                                            <span class="me-2">Apple Pay</span>
                                            <div class="ms-auto">
                                                <img src="images/apple-pay.png" alt="Apple Pay">
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            
                            <div id="credit-card-form">
                                <div class="row g-3">
                                    <div class="col-md-12">
                                        <label class="form-label">Card Holder Name</label>
                                        <input type="text" class="form-control" name="card_holder" required pattern="^[A-Za-z\s.'-]{3,50}$" title="Please enter a valid name">
                                        <div class="invalid-feedback">Please enter the cardholder's name</div>
                                    </div>
                                    
                                    <div class="col-md-12">
                                        <label class="form-label">Card Number</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control" name="card_number" required pattern="^[0-9]{16}$" title="Please enter a valid 16-digit card number">
                                            <span class="input-group-text bg-light"><i class="fas fa-credit-card text-primary"></i></span>
                                        </div>
                                        <div class="invalid-feedback">Please enter a valid 16-digit card number</div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label class="form-label">Expiration Date</label>
                                        <input type="text" class="form-control" name="expiry_date" placeholder="MM/YY" required pattern="^(0[1-9]|1[0-2])\/([0-9]{2})$" title="Please enter a valid expiration date (MM/YY)">
                                        <div class="invalid-feedback">Please enter a valid expiration date (MM/YY)</div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label class="form-label">CVV</label>
                                        <input type="text" class="form-control" name="cvv" required pattern="^[0-9]{3,4}$" title="Please enter a valid 3 or 4 digit CVV">
                                        <div class="invalid-feedback">Please enter a valid 3 or 4 digit CVV</div>
                                    </div>
                                </div>
                            </div>
                            
                            <div id="paypal-form" style="display: none;">
                                <!-- PayPal form content will be handled by external provider -->
                            </div>
                            
                            <div id="apple-pay-form" style="display: none;">
                                <!-- Apple Pay form content will be handled by external provider -->
                            </div>
                            
                            <div class="form-check mt-4">
                                <input class="form-check-input" type="checkbox" id="terms" name="terms" required>
                                <label class="form-check-label" for="terms">
                                    I agree to the <a href="#" data-bs-toggle="modal" data-bs-target="#termsModal">Terms and Conditions</a> and <a href="#" data-bs-toggle="modal" data-bs-target="#privacyModal">Privacy Policy</a>
                                </label>
                                <div class="invalid-feedback">
                                    You must agree to the terms and conditions
                                </div>
                                </div>
                                
                                <!-- Apple Pay Message -->
                                <div class="alert alert-info mt-3" id="apple-pay-message" style="display: none;">
                                    <i class="fas fa-info-circle me-2"></i> You will be redirected to Apple Pay to complete your payment.
                                </div>
                                
                                <!-- PayPal Message -->
                                <div class="alert alert-info mt-3" id="paypal-message" style="display: none;">
                                    <i class="fas fa-info-circle me-2"></i> You will be redirected to PayPal to complete your payment.
                                </div>
                            </div><!-- End of payment method container -->
                            
                            <div class="booking-action-buttons">
                                <a href="booking.php" class="btn btn-back">
                                    <i class="fas fa-arrow-left me-2"></i> Back
                                </a>
                                <button type="submit" class="btn btn-proceed" id="complete-booking-btn">
                                    Complete Booking <i class="fas fa-check ms-2"></i>
                                </button>
                                <noscript>
                                    <!-- Fallback for when JavaScript is disabled -->
                                    <a href="confirmation.php" class="btn btn-proceed"><i class="fas fa-check-circle"></i>Complete Booking (No JS)</a>
                                </noscript>
                            </div>
                        </form>
                        
                        <div class="security-badge mt-4">
                            <i class="fas fa-lock me-2"></i>
                            All transactions are secure and encrypted. Your personal information is protected.
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Price Summary -->
            <div class="col-lg-4">
                <div class="payment-card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i> Booking Summary</h5>
                    </div>
                    <div class="card-body">
                        <div class="flight-details">
                            <h6><i class="fas fa-plane me-2"></i> Flight Details</h6>
                            <p><span>Flight:</span> <span><?php echo !empty($flight['flight_number']) ? htmlspecialchars($flight['flight_number']) : 'JF110'; ?></span></p>
                            <p><span>From:</span> <span><?php echo !empty($flight['departure_city']) ? htmlspecialchars($flight['departure_city']) : (!empty($flight['from_city']) ? htmlspecialchars($flight['from_city']) : 'New York'); ?></span></p>
                            <p><span>To:</span> <span><?php echo !empty($flight['arrival_city']) ? htmlspecialchars($flight['arrival_city']) : (!empty($flight['to_city']) ? htmlspecialchars($flight['to_city']) : 'London'); ?></span></p>
                            <p><span>Date:</span> <span><?php echo !empty($flight['departure_date']) ? date('M d, Y', strtotime($flight['departure_date'])) : (!empty($flight['depart_date']) ? htmlspecialchars($flight['depart_date']) : date('M d, Y')); ?></span></p>
                            <p><span>Time:</span> <span><?php echo !empty($flight['departure_time']) ? date('h:i A', strtotime($flight['departure_time'])) : (!empty($flight['depart_time']) ? htmlspecialchars($flight['depart_time']) : '10:00 AM'); ?></span></p>
                            <p><span>Class:</span> <span><?php echo htmlspecialchars(ucfirst($class)); ?></span></p>
                            <p><span>Seat:</span> <span><?php echo htmlspecialchars($seat_number); ?></span></p>
                        </div>
                        
                        <div class="price-breakdown">
                            <div class="price-item">
                                <span>Base Fare:</span>
                                <span>$<?php echo number_format($price, 2); ?></span>
                            </div>
                            <div class="price-item">
                                <span>Taxes & Fees:</span>
                                <span>$<?php echo number_format($taxes, 2); ?></span>
                            </div>
                            <div class="price-item">
                                <span>Service Fee:</span>
                                <span>$<?php echo number_format($service_fee, 2); ?></span>
                            </div>
                            <div class="price-total">
                                <span>Total:</span>
                                <span>$<?php echo number_format($total_price, 2); ?></span>
                            </div>
                        </div>
                        
                        </div>
                        
                        <div class="alert alert-info mt-4">
                            <i class="fas fa-shield-alt me-2"></i> Your payment is secured with SSL encryption.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <!-- Terms Modal -->
    <div class="modal fade" id="termsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Terms and Conditions</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <h6>1. Booking and Payment</h6>
                    <p>By completing this booking, you agree to pay the total amount shown. All payments are processed securely. Fares are subject to availability and may change without notice until ticketed.</p>
                    
                    <h6>2. Cancellation Policy</h6>
                    <p>Cancellations made more than 24 hours before departure may be eligible for a partial refund or credit. Cancellations within 24 hours of departure are non-refundable.</p>
                    
                    <h6>3. Baggage Policy</h6>
                    <p>Baggage allowances vary by fare type and class. Additional fees may apply for excess baggage. SkyWay Airlines is not responsible for damage to fragile or perishable items.</p>
                    
                    <h6>4. Check-in Requirements</h6>
                    <p>Passengers must check in at least 2 hours before domestic flights and 3 hours before international flights. Valid identification is required for all passengers.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">I Understand</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Privacy Modal -->
    <div class="modal fade" id="privacyModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Privacy Policy</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <h6>1. Information Collection</h6>
                    <p>We collect personal information necessary for booking and security purposes, including name, contact details, payment information, and identification documents.</p>
                    
                    <h6>2. Use of Information</h6>
                    <p>Your information is used to process bookings, provide customer service, comply with legal requirements, and improve our services.</p>
                    
                    <h6>3. Data Security</h6>
                    <p>We implement appropriate security measures to protect your personal information from unauthorized access, alteration, or disclosure.</p>
                    
                    <h6>4. Third-Party Sharing</h6>
                    <p>We may share your information with third parties only as necessary to complete your booking, such as with airports, immigration authorities, and payment processors.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">I Understand</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Footer -->
    <footer class="footer bg-dark text-white py-5 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-3 col-md-4">
                    <h5 class="text-white mb-4">SkyWay Airlines</h5>
                    <p>Your journey, our passion. Fly with confidence and comfort.</p>
                    <div class="social-icons mt-3">
                        <a href="#" class="text-white me-3"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="text-white me-3"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-white me-3"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="text-white"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>
                <div class="col-lg-3 col-md-4">
                    <h5 class="text-white mb-4">Quick Links</h5>
                    <ul class="footer-links">
                        <li><a href="index.php">Home</a></li>
                        <li><a href="flights.php">Flights</a></li>
                        <li><a href="about.php">About Us</a></li>
                        <li><a href="contact.php">Contact</a></li>
                        <li><a href="terms.php">Terms & Conditions</a></li>
                        <li><a href="privacy.php">Privacy Policy</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-4">
                    <h5 class="text-white mb-4">Services</h5>
                    <ul class="footer-links">
                        <li><a href="#">Flight Booking</a></li>
                        <li><a href="#">Vacation Packages</a></li>
                        <li><a href="#">Hotel Reservations</a></li>
                        <li><a href="#">Car Rentals</a></li>
                        <li><a href="#">Travel Insurance</a></li>
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
        $(document).ready(function() {
            // Payment method selection
            $('.payment-method').click(function() {
                $('.payment-method').removeClass('selected');
                $(this).addClass('selected');
                $(this).find('input[type="radio"]').prop('checked', true);
                
                const method = $(this).data('method');
                
                // Hide all forms and messages first
                $('#credit-card-form, #paypal-form, #apple-pay-form').hide();
                $('#apple-pay-message, #paypal-message').hide();
                
                // Show the selected form
                $(`#${method}-form`).show();
                
                // Show appropriate message
                if (method === 'apple-pay') {
                    $('#apple-pay-message').show();
                } else if (method === 'paypal') {
                    $('#paypal-message').show();
                }
            });
            
            // Form validation - modified to ensure form submission
            const forms = document.querySelectorAll('.needs-validation');
            
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
                    } else {
                        // Form is valid, make sure it submits
                        console.log('Form is valid, submitting...');
                        // Ensure the form submits by returning true
                        return true;
                    }
                    
                    form.classList.add('was-validated');
                }, false);
            });
            
            // Direct submission option
            $('#complete-booking-btn').on('click', function(e) {
                // Check if terms checkbox is checked
                if ($('#terms').is(':checked')) {
                    // If using PayPal or Apple Pay, we don't need card details
                    const selectedMethod = $('input[name="payment_method"]:checked').val();
                    if (selectedMethod === 'paypal' || selectedMethod === 'apple-pay') {
                        // For these methods, bypass validation and submit directly
                        $('#payment-form').removeClass('needs-validation');
                        $('#payment-form').submit();
                        return true;
                    }
                }
            });
            
            // Format credit card number with spaces
            $('input[name="card_number"]').on('input', function() {
                // Remove all non-digits
                let value = $(this).val().replace(/\D/g, '');
                
                // Limit to 16 digits
                value = value.substring(0, 16);
                
                // Update the input value
                $(this).val(value);
            });
            
            // Format expiry date
            $('input[name="expiry_date"]').on('input', function() {
                let value = $(this).val().replace(/\D/g, '');
                
                if (value.length > 2) {
                    value = value.substring(0, 2) + '/' + value.substring(2, 4);
                }
                
                $(this).val(value);
            });
            
            // Format CVV
            $('input[name="cvv"]').on('input', function() {
                let value = $(this).val().replace(/\D/g, '');
                value = value.substring(0, 4);
                $(this).val(value);
            });
        });
    </script>
    
    <!-- Include Footer -->
    <?php include 'includes/footer.php'; ?>
</body>
</html>
