<?php
session_start();
require_once 'includes/db.php';

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$error = "";
$success = "";

// Process registration form
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = $conn->real_escape_string($_POST['first_name']);
    $last_name = $conn->real_escape_string($_POST['last_name']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $phone = $conn->real_escape_string($_POST['phone']);
    $dob = $conn->real_escape_string($_POST['dob']);
    
    // Handle photo upload
    $photo_path = '';
    $upload_error = false;
    
    if(isset($_FILES['user_photo']) && $_FILES['user_photo']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['user_photo']['name'];
        $file_ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        // Check if extension is allowed
        if(in_array($file_ext, $allowed)) {
            // Create unique filename
            $new_filename = uniqid() . '.' . $file_ext;
            $upload_dir = 'uploads/user_photos/';
            
            // Create directory if it doesn't exist
            if(!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $destination = $upload_dir . $new_filename;
            
            // Move uploaded file
            if(move_uploaded_file($_FILES['user_photo']['tmp_name'], $destination)) {
                $photo_path = $destination;
            } else {
                $upload_error = true;
                $error = "Error uploading photo. Please try again.";
            }
        } else {
            $upload_error = true;
            $error = "Invalid file type. Allowed types: jpg, jpeg, png, gif";
        }
    } else {
        $upload_error = true;
        $error = "Profile photo is required";
    }
    
    // Validate input
    if (empty($first_name) || empty($last_name) || empty($email) || empty($password) || empty($confirm_password) || $upload_error) {
        $error = "Please fill all required fields";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long";
    } else {
        // Check if email already exists
        $sql = "SELECT user_id FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = "Email already exists. Please use a different email or login.";
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert new user
            $sql = "INSERT INTO users (first_name, last_name, email, password, photo_path, phone, dob) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssss", $first_name, $last_name, $email, $hashed_password, $photo_path, $phone, $dob);
            
            if ($stmt->execute()) {
                $success = "Registration successful! You can now login.";
            } else {
                $error = "Error: " . $stmt->error;
            }
        }
        
        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - SkyWay Airlines</title>
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

    <!-- Registration Form -->
    <div class="container">
        <div class="auth-container">
            <div class="auth-logo">
                <i class="fas fa-user-plus"></i>
            </div>
            <h2 class="auth-title">Create Your Account</h2>
            
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($success)): ?>
                <div class="alert alert-success" role="alert">
                    <?php echo $success; ?>
                    <br>
                    <a href="login.php" class="alert-link">Click here to login</a>
                </div>
            <?php else: ?>
            
            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" id="first_name" name="first_name" placeholder="John" required>
                            <label for="first_name">First Name</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" id="last_name" name="last_name" placeholder="Doe" required>
                            <label for="last_name">Last Name</label>
                        </div>
                    </div>
                </div>
                
                <div class="form-floating mb-3">
                    <input type="email" class="form-control" id="email" name="email" placeholder="name@example.com" required>
                    <label for="email">Email address</label>
                </div>
                
                <div class="form-floating mb-3">
                    <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                    <label for="password">Password</label>
                    <small class="text-muted">Password must be at least 6 characters long</small>
                </div>
                
                <div class="form-floating mb-3">
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Confirm Password" required>
                    <label for="confirm_password">Confirm Password</label>
                </div>
                
                <div class="form-floating mb-3">
                    <input type="tel" class="form-control" id="phone" name="phone" placeholder="Phone Number">
                    <label for="phone">Phone Number (optional)</label>
                </div>
                
                <div class="form-floating mb-4">
                    <input type="date" class="form-control" id="dob" name="dob" placeholder="Date of Birth">
                    <label for="dob">Date of Birth</label>
                </div>
                
                <div class="mb-4">
                    <label for="user_photo" class="form-label">Profile Photo (Required)</label>
                    <input type="file" class="form-control" id="user_photo" name="user_photo" accept="image/*" required>
                    <small class="text-muted">Upload a profile photo (JPG, PNG, or GIF)</small>
                </div>
                
                <div class="form-check mb-4">
                    <input class="form-check-input" type="checkbox" id="terms" required>
                    <label class="form-check-label" for="terms">
                        I agree to the <a href="terms.html" target="_blank">Terms of Service</a> and <a href="privacy.html" target="_blank">Privacy Policy</a>
                    </label>
                </div>
                
                <button type="submit" class="btn btn-auth">Create Account</button>
            </form>
            
            <?php endif; ?>
            
            <div class="auth-footer">
                <p>Already have an account? <a href="login.php">Login here</a></p>
            </div>
        </div>
    </div>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Include Footer -->
    <?php include 'includes/footer.php'; ?>
    
    <script>
        // Password confirmation validation
        document.getElementById('confirm_password').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirmPassword = this.value;
            
            if (password !== confirmPassword) {
                this.setCustomValidity('Passwords do not match');
            } else {
                this.setCustomValidity('');
            }
        });
        
        // Password strength validation
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            
            if (password.length < 6) {
                this.setCustomValidity('Password must be at least 6 characters long');
            } else {
                this.setCustomValidity('');
                // Also check confirmation field
                const confirmPassword = document.getElementById('confirm_password');
                if (confirmPassword.value) {
                    if (password !== confirmPassword.value) {
                        confirmPassword.setCustomValidity('Passwords do not match');
                    } else {
                        confirmPassword.setCustomValidity('');
                    }
                }
            }
        });
    </script>
</body>
</html>
