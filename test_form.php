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
    <title>Test Form</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
</head>
<body>
    <div class="container my-5">
        <h2>Test Flight Search Form</h2>
        
        <div class="card p-4 my-4">
            <form action="search_results.php" method="get" id="testForm">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">From</label>
                        <select class="form-control" name="fromCity" id="fromCity" required>
                            <option value="">Select departure city</option>
                            <?php foreach ($cities as $city): ?>
                                <option value="<?php echo $city; ?>"><?php echo $city; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">To</label>
                        <select class="form-control" name="toCity" id="toCity" required>
                            <option value="">Select destination city</option>
                            <?php foreach ($cities as $city): ?>
                                <option value="<?php echo $city; ?>"><?php echo $city; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Departure Date</label>
                        <input type="date" class="form-control" name="departDate" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Class</label>
                        <select class="form-control" name="class">
                            <option value="Economy">Economy</option>
                            <option value="Business">Business</option>
                            <option value="First">First Class</option>
                        </select>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Search Flights</button>
            </form>
        </div>
        
        <div id="debug" class="mt-4"></div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Simple form validation
            $('#testForm').submit(function(e) {
                const fromCity = $('#fromCity').val();
                const toCity = $('#toCity').val();
                
                $('#debug').html(`<p>From: ${fromCity}, To: ${toCity}</p>`);
                
                if (fromCity === toCity && fromCity !== '') {
                    e.preventDefault();
                    alert('Origin and destination cannot be the same.');
                    return false;
                }
                
                return true;
            });
        });
    </script>
</body>
</html>
