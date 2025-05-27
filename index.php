<?php
session_start();
require_once 'includes/db.php';

// Fetch unique cities from the database
$cities = [];
if (!$conn->connect_error) {
    $city_sql = "SELECT DISTINCT from_city FROM flights UNION SELECT DISTINCT to_city FROM flights";
    $city_result = $conn->query($city_sql);
    if ($city_result) {
        while ($row = $city_result->fetch_assoc()) {
            $cities[] = htmlspecialchars($row['from_city']);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SkyWay Airlines - Book Your Flight</title>
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

    <!-- Hero Section -->
    <header class="hero-section">
        <div class="overlay"></div>
        <div class="container position-relative">
            <div class="row justify-content-center">
                <div class="col-lg-8 text-center text-white">
                    <h1 class="mb-3 fw-bold">Find and Book Your Flight</h1>
                    <p class="lead mb-4">Search for flights, select your seats, and book your tickets online.</p>
                </div>
            </div>
        </div>
    </header>

    <!-- Flight Search Form -->
    <section class="search-section">
        <div class="container">
            <?php if(isset($_GET['error'])): ?>
            <div class="alert alert-danger mb-3" role="alert">
                <?php echo htmlspecialchars($_GET['error']); ?>
            </div>
            <?php endif; ?>
            <div class="search-container">
                <form action="search_results.php" method="get" id="flightSearchForm">
                    <div class="row mb-4">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">From</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-plane-departure"></i></span>
                                <select class="form-control" name="fromCity" id="fromCity" required>
                                    <option value="">Enter departure city</option>
                                    <?php foreach ($cities as $city): ?>
                                        <option value="<?php echo $city; ?>"><?php echo $city; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">To</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-plane-arrival"></i></span>
                                <select class="form-control" name="toCity" id="toCity" required>
                                    <option value="">Enter destination city</option>
                                    <?php foreach ($cities as $city): ?>
                                        <option value="<?php echo $city; ?>"><?php echo $city; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row mb-4">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Departure Date</label>
                            <input type="date" class="form-control" name="departDate" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Return Date</label>
                            <input type="date" class="form-control" name="returnDate" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Class</label>
                            <select class="form-select" name="class" required>
                                <option value="Economy">Economy</option>
                                <option value="Business">Business</option>
                                <option value="First">First Class</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">Search Flights</button>
                        </div>
                    </div>
                    <input type="hidden" name="trip_type" value="round">
                </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features-section py-5">
        <div class="container">
            <h2 class="section-title text-center mb-5">Why Choose SkyWay?</h2>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="feature-box">
                        <div class="feature-icon">
                            <i class="fas fa-tag"></i>
                        </div>
                        <h3>Best Prices</h3>
                        <p>We offer competitive prices on all flights with no hidden fees.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-box">
                        <div class="feature-icon">
                            <i class="fas fa-headset"></i>
                        </div>
                        <h3>24/7 Support</h3>
                        <p>Our customer service team is available around the clock to assist you.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-box">
                        <div class="feature-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <h3>Secure Booking</h3>
                        <p>Book with confidence knowing your information is protected.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Popular Destinations -->
    <section class="destinations-section py-5">
        <div class="container">
            <h2 class="section-title text-center mb-5">Popular Destinations</h2>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="destination-card">
                        <div class="destination-img">
                            <img src="images/newyork.jpg" alt="New York">
                            <div class="destination-overlay">
                                <a href="search_results.php?toCity=New York" class="btn btn-light">View Flights</a>
                            </div>
                        </div>
                        <div class="destination-info">
                            <h3>New York</h3>
                            <p>Flights from $299</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="destination-card">
                        <div class="destination-img">
                            <img src="images/london.jpg" alt="London">
                            <div class="destination-overlay">
                                <a href="search_results.php?toCity=London" class="btn btn-light">View Flights</a>
                            </div>
                        </div>
                        <div class="destination-info">
                            <h3>London</h3>
                            <p>Flights from $399</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="destination-card">
                        <div class="destination-img">
                            <img src="images/tokoyo.jpg" alt="Tokyo">
                            <div class="destination-overlay">
                                <a href="search_results.php?toCity=Tokyo" class="btn btn-light">View Flights</a>
                            </div>
                        </div>
                        <div class="destination-info">
                            <h3>Tokyo</h3>
                            <p>Flights from $599</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Call to Action -->
    <section class="cta-section py-5">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-8 text-center text-lg-start">
                    <h2 class="text-white mb-3">Ready to start your journey?</h2>
                    <p class="text-white mb-0">Sign up now and get exclusive offers on your first booking!</p>
                </div>
                <div class="col-lg-4 text-center text-lg-end mt-4 mt-lg-0">
                    <a href="register.php" class="btn btn-light btn-lg px-4">Register Now</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
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
                    <li><i class="fas fa-map-marker-alt"></i> 123 Airport Road, Vadodara, India</li>
                    <li><i class="fas fa-phone"></i> +91 9876543210</li>
                        <li><i class="fas fa-envelope"></i> info@skyway.com</li>
                        <li><i class="fas fa-clock"></i> Mon-Fri: 9:00 AM - 8:00 PM</li>
                    </ul>
                </div>
            </div>
            <hr class="mt-4 mb-3 bg-secondary">
            <div class="row">
                <div class="col-md-6 text-center text-md-start">
                    <p class="text-light mb-0">&copy; 2025 SkyWay Airlines. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <p class="text-light mb-0">
                        <a href="#" class="text-light me-3">Privacy Policy</a>
                        <a href="#" class="text-light">Terms of Service</a>
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Custom JS -->
    <script src="js/script.js"></script>
    
    <!-- Custom validation is handled in script.js -->
</body>
</html>
