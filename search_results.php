<?php
// search_results.php: Show available flights based on user search
session_start();
require_once 'includes/db.php';

// Get search parameters from GET
$from_city = isset($_GET['fromCity']) ? $conn->real_escape_string($_GET['fromCity']) : '';
$to_city = isset($_GET['toCity']) ? $conn->real_escape_string($_GET['toCity']) : '';
$depart_date = isset($_GET['departDate']) ? $conn->real_escape_string($_GET['departDate']) : '';
$class = isset($_GET['class']) ? $conn->real_escape_string($_GET['class']) : 'Economy';

// Check if cities are the same
$error_message = '';
if (!empty($from_city) && !empty($to_city) && $from_city === $to_city) {
    $error_message = 'Origin and destination cannot be the same.';
    // Redirect back to index.php with error message
    header("Location: index.php?error=" . urlencode($error_message));
    exit;
}

// Debug information (can be removed in production)
$debug = [];
$debug['params'] = ['from' => $from_city, 'to' => $to_city, 'date' => $depart_date, 'class' => $class];

// Get all unique cities from the database for the dropdowns
$cities_query = "SELECT DISTINCT from_city FROM flights UNION SELECT DISTINCT to_city FROM flights ORDER BY from_city ASC";
$cities_result = $conn->query($cities_query);
$cities = [];
if ($cities_result && $cities_result->num_rows > 0) {
    while ($row = $cities_result->fetch_assoc()) {
        $cities[] = $row['from_city'];
    }
}

// Check if we're showing all flights (no parameters) or filtered results
$show_all = empty($from_city) && empty($to_city) && empty($depart_date);

// Build base query
$sql = "SELECT * FROM flights WHERE 1=1";

// Add conditions only if parameters are provided and we're not showing all flights
if (!empty($from_city)) {
    $sql .= " AND from_city LIKE '%$from_city%'";
}

if (!empty($to_city)) {
    $sql .= " AND to_city LIKE '%$to_city%'";
}

if (!empty($depart_date)) {
    $sql .= " AND depart_date = '$depart_date'";
}

// Order by departure time and date
$sql .= " ORDER BY depart_date ASC, depart_time ASC";

$debug['sql'] = $sql;

// Execute query
$result = $conn->query($sql);
$debug['result_status'] = ($result) ? 'Query executed successfully' : 'Query failed: ' . $conn->error;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Flight Search Results</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Using external CSS file -->
</head>
<body style="background: var(--light-color);">
    <!-- Include Header -->
    <?php include 'includes/header.php'; ?>

    <div class="container my-5">
        <h2 class="mb-4">Available Flights</h2>
        
        <div class="search-results-container p-3" style="background-color: #1e2a38; border-radius: 8px;">
            <div class="mb-3">
                <i class="fas fa-search me-2"></i> Search Flights
            </div>
            <form action="search_results.php" method="GET" class="search-form">
                <div class="px-4 py-2">
                    <div class="d-flex align-items-center gap-2">
                        <select class="form-select me-2" id="fromCity" name="fromCity" style="flex: 1;">
                            <option value="">Select City</option>
                            <?php foreach ($cities as $city): ?>
                                <option value="<?php echo htmlspecialchars($city); ?>" <?php echo ($from_city == $city) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($city); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <select class="form-select me-2" id="toCity" name="toCity" style="flex: 1;">
                            <option value="">Select City</option>
                            <?php foreach ($cities as $city): ?>
                                <option value="<?php echo htmlspecialchars($city); ?>" <?php echo ($to_city == $city) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($city); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <input type="date" class="form-control me-2" id="departDate" name="departDate" value="<?php echo htmlspecialchars($depart_date); ?>" style="flex: 1;">
                        <select class="form-select me-2" id="class" name="class" style="flex: 1;">
                            <option value="Economy" <?php echo ($class == 'Economy') ? 'selected' : ''; ?>>Economy</option>
                            <option value="Business" <?php echo ($class == 'Business') ? 'selected' : ''; ?>>Business</option>
                            <option value="First" <?php echo ($class == 'First') ? 'selected' : ''; ?>>First</option>
                        </select>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i></button>
                    </div>
                </div>
                <div class="d-flex justify-content-between">
                    <button type="button" class="btn btn-sm btn-outline-light rounded-pill" onclick="window.location.href='search_results.php'">
                        Show All Flights
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-light rounded-pill" onclick="window.location.href='index.php'">
                        Back to Home
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Search parameters summary -->
        <!-- Search results summary -->
        <div class="alert alert-info mb-4">
            <div class="d-flex align-items-center">
                <div class="me-2">
                    <i class="fas fa-info-circle"></i>
                </div>
                <div>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <p class="mb-0">Found <?php echo $result->num_rows; ?> <?php echo ($result->num_rows == 1) ? 'flight' : 'flights'; ?> matching your criteria.</p>
                    <?php elseif ($show_all): ?>
                        <p class="mb-0">Showing all available flights. Use the search form above to filter results by specific criteria.</p>
                    <?php else: ?>
                        <p class="mb-0">No flights found matching your criteria. Try different search parameters.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Search parameters display -->
        <?php if (!$show_all && $from_city && $to_city): ?>
        <div class="mb-4">
            <div class="d-flex align-items-center">
                <div class="me-3">
                    <span class="badge rounded-pill bg-primary p-2"><i class="fas fa-plane"></i></span>
                </div>
                <div>
                    <h5 class="mb-0">
                        <span class="text-primary"><?php echo htmlspecialchars($from_city); ?></span>
                        <i class="fas fa-long-arrow-alt-right mx-2"></i>
                        <span class="text-primary"><?php echo htmlspecialchars($to_city); ?></span>
                        <?php if ($depart_date): ?>
                            <span class="text-muted ms-2"><i class="far fa-calendar-alt me-1"></i><?php echo htmlspecialchars($depart_date); ?></span>
                        <?php endif; ?>
                        <?php if ($class): ?>
                            <span class="badge bg-info text-dark ms-2"><?php echo htmlspecialchars($class); ?></span>
                        <?php endif; ?>
                    </h5>
                </div>
            </div>
        </div>
        <?php endif; ?>
        <?php if ($result && $result->num_rows > 0): ?>
            <div class="table-responsive mt-4">
                <table class="table table-hover flights-table">
                    <thead>
                        <tr>
                            <th>Airline</th>
                            <th>Flight No.</th>
                            <th>From</th>
                            <th>To</th>
                            <th>Departure</th>
                            <th>Arrival</th>
                            <th class="text-end">Price ($)</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['airline']); ?></td>
                            <td><?php echo htmlspecialchars($row['flight_number']); ?></td>
                            <td><?php echo htmlspecialchars($row['from_city']); ?></td>
                            <td><?php echo htmlspecialchars($row['to_city']); ?></td>
                            <td><?php echo htmlspecialchars($row['depart_date'] . ' ' . $row['depart_time']); ?></td>
                            <td><?php echo htmlspecialchars($row['arrive_date'] . ' ' . $row['arrive_time']); ?></td>
                            <td class="price-column">
                                <?php 
                                $price = $row['price'];
                                if ($class == 'Business') {
                                    $price = $price * 1.5;
                                } elseif ($class == 'First') {
                                    $price = $price * 2.5;
                                }
                                ?>
                                $<?php echo number_format($price, 2); ?>
                            </td>
                            <td class="text-center">
                                <a href="booking.php?flight_id=<?php echo $row['flight_id']; ?>&class=<?php echo urlencode($class); ?>" class="btn btn-select">Select</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-warning">No flights found for your search.</div>
            <?php if (isset($debug) && !empty($debug)): ?>
            <!-- Debug information (remove in production) -->
            <div class="card mt-3 border-info">
                <div class="card-header bg-info text-white">Debug Information</div>
                <div class="card-body">
                    <h5>Search Parameters:</h5>
                    <pre><?php print_r($debug['params']); ?></pre>
                    
                    <h5>SQL Query:</h5>
                    <pre><?php echo $debug['sql']; ?></pre>
                    
                    <h5>Query Result:</h5>
                    <pre><?php echo $debug['result_status']; ?></pre>
                </div>
            </div>
            <?php endif; ?>
        <?php endif; ?>
        <?php if ($result && $result->num_rows > 0): ?>
        <div class="flights-info-alert">
            <i class="fas fa-info-circle text-primary me-2"></i> Found <strong><?php echo $result->num_rows; ?></strong> flights matching your criteria.
        </div>
        <?php endif; ?>
    </div>

    <!-- Footer (copied from index.php for consistency) -->
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
</body>
</html>
<?php $conn->close(); ?>
