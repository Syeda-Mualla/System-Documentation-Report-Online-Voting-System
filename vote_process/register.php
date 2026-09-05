<?php
// register.php - User registration page
// Allows new users to create an account with name, email, dob, and password

// Include database connection file
require_once 'db.php';

// Initialize error and success messages
$error = "";
$success = "";

// Check if form was submitted using POST method
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form input and remove extra spaces
    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);
    $dob = $_POST["dob"];
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];
    
    // Validate: Check if name is not empty
    if (empty($name)) {
        $error = "Name is required!";
    }
    // Validate: Check if email is not empty and is valid format
    else if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Valid email is required!";
    }
    // Validate: Check if date of birth is provided
    else if (empty($dob)) {
        $error = "Date of birth is required!";
    }
    else {
        // AGE VERIFICATION: Calculate age from date of birth
        // DateTime class calculates the difference between two dates
        $birthDate = new DateTime($dob);
        $today = new DateTime();
        $age = $today->diff($birthDate)->y; // ->y gives us the years difference

        // Block registration if user is under 18
        // This mirrors real electoral laws in Pakistan (Article 51 of the Constitution)
        if ($age < 18) {
            $error = "You must be 18 or older to register as a voter. You are currently " . $age . " years old.";
        }
        // Validate: Check if password is not empty and matches confirmation
        else if (empty($password) || $password != $confirm_password) {
            $error = "Passwords do not match or are empty!";
        }
        // Validate: Check if password is at least 6 characters
        else if (strlen($password) < 6) {
            $error = "Password must be at least 6 characters!";
        }
        else {
            // SECURITY: Check if email already exists in database
            // Using prepared statement to prevent SQL injection
            $check_email = $conn->prepare("SELECT id FROM users WHERE email = ?");
            
            // Bind the email parameter (s = string type)
            $check_email->bind_param("s", $email);
            
            // Execute the query
            $check_email->execute();
            
            // Store the result to check number of rows
            $check_email->store_result();
            
            // If email exists, show error
            if ($check_email->num_rows > 0) {
                $error = "Email already registered!";
            }
            else {
                // SECURITY: Hash the password using bcrypt algorithm
                // password_hash() creates a one-way encryption that cannot be reversed
                // This ensures even if database is stolen, passwords are safe
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Insert new user with dob and age stored in database
                // Using prepared statement to prevent SQL injection
                $insert_user = $conn->prepare("INSERT INTO users (name, email, dob, age, password, has_voted, is_admin) VALUES (?, ?, ?, ?, ?, 0, 0)");
                
                // Bind parameters (s=string, s=string, s=date, i=integer, s=string)
                $insert_user->bind_param("sssis", $name, $email, $dob, $age, $hashed_password);
                
                // Execute the insert query
                if ($insert_user->execute()) {
                    // Registration successful
                    $success = "Registration successful! You are verified as " . $age . " years old. Redirecting to login...";
                    
                    // Redirect to login page after 2 seconds
                    header("refresh:2;url=login.php");
                }
                else {
                    // If insert fails, show error
                    $error = "Error creating account. Please try again.";
                }
                
                // Close the prepared statement
                $insert_user->close();
            }
            
            // Close the check email prepared statement
            $check_email->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Online Voting System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <!-- Navigation bar -->
    <nav>
        <h1>🗳️ Online Voting System</h1>
        <ul>
            <li><a href="login.php">Login</a></li>
            <li><a href="register.php">Register</a></li>
        </ul>
    </nav>

    <!-- Main container -->
    <div class="container">
        <!-- Card with registration form -->
        <div class="card">
            <h2>Register Account</h2>

            <!-- Age eligibility notice -->
            <div class="info-box">
                ℹ️ <strong>Voter Eligibility:</strong> You must be <strong>18 years or older</strong> to register and vote.
            </div>

            <!-- Display error message if exists -->
            <?php if ($error): ?>
                <div class="error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <!-- Display success message if exists -->
            <?php if ($success): ?>
                <div class="success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <!-- Registration form -->
            <form method="POST">
                <!-- Name field -->
                <div class="form-group">
                    <label for="name">Full Name:</label>
                    <input type="text" id="name" name="name" placeholder="Enter your full name" required>
                </div>

                <!-- Email field -->
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" placeholder="Enter your email" required>
                </div>

                <!-- Date of Birth field -->
                <div class="form-group">
                    <label for="dob">Date of Birth:</label>
                    <input type="date" id="dob" name="dob" required
                        max="<?php echo date('Y-m-d', strtotime('-18 years')); ?>"
                        title="You must be at least 18 years old">
                    <small style="color: #888; font-size: 12px;">You must be 18 or older to vote.</small>
                </div>

                <!-- Password field -->
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" placeholder="Enter password (min 6 characters)" required>
                </div>

                <!-- Confirm password field -->
                <div class="form-group">
                    <label for="confirm_password">Confirm Password:</label>
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Re-enter password" required>
                </div>

                <!-- Submit button -->
                <button type="submit">Register</button>
            </form>

            <!-- Link to login page -->
            <p class="text-center mt-2">
                Already have an account? <a href="login.php" style="color: #3498db; text-decoration: none;">Login here</a>
            </p>
        </div>
    </div>

    <script>
    // JavaScript age check - gives instant feedback before form submits
    document.getElementById('dob').addEventListener('change', function() {
        var dob = new Date(this.value);
        var today = new Date();
        var age = today.getFullYear() - dob.getFullYear();
        var monthDiff = today.getMonth() - dob.getMonth();

        // Adjust age if birthday hasn't occurred yet this year
        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < dob.getDate())) {
            age--;
        }

        // Show instant message below the date field
        var msg = document.getElementById('age-msg');
        if (!msg) {
            msg = document.createElement('small');
            msg.id = 'age-msg';
            this.parentNode.appendChild(msg);
        }

        if (age < 18) {
            msg.style.color = 'red';
            msg.innerText = '❌ You are ' + age + ' years old. You must be 18+ to register.';
        } else {
            msg.style.color = 'green';
            msg.innerText = '✅ Age verified: ' + age + ' years old. You are eligible to vote.';
        }
    });
    </script>

</body>
</html>