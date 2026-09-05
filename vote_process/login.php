<?php
// login.php - User login page
// Allows registered users to log in with email and password

// Include database connection file
require_once 'db.php';

// Initialize error message
$error = "";

// Check if form was submitted using POST method
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form input and remove extra spaces
    $email = trim($_POST["email"]);
    $password = $_POST["password"];
    
    // Validate: Check if email and password are not empty
    if (empty($email) || empty($password)) {
        $error = "Email and password are required!";
    }
    else {
        // SECURITY: Use prepared statement to prevent SQL injection
        $login_query = $conn->prepare("SELECT id, name, email, password, is_admin FROM users WHERE email = ?");
        
        // Bind email parameter (s = string type)
        $login_query->bind_param("s", $email);
        
        // Execute the query
        $login_query->execute();
        
        // Get the result
        $result = $login_query->get_result();
        
        // Check if user exists (result should have exactly 1 row)
        if ($result->num_rows == 1) {
            // Fetch user data from database
            $user = $result->fetch_assoc();
            
            // SECURITY: Verify password using password_verify()
            // This compares the plain text password with the hashed password in database
            // password_verify() returns true if password matches, false otherwise
            if (password_verify($password, $user["password"])) {
                // Password is correct - start a new session for this user
                session_start();
                
                // SECURITY: Store user info in session (server-side, not sent to client)
                $_SESSION["user_id"] = $user["id"];         // User's unique ID
                $_SESSION["user_name"] = $user["name"];     // User's name
                $_SESSION["is_admin"] = $user["is_admin"];  // Is user an admin? (0 = no, 1 = yes)
                
                // Login successful - redirect to voting page
                header("Location: vote.php");
                exit();
            }
            else {
                // Password is incorrect
                $error = "Invalid email or password!";
            }
        }
        else {
            // Email not found in database
            $error = "Invalid email or password!";
        }
        
        // Close the prepared statement
        $login_query->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Online Voting System</title>
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
        <!-- Card with login form -->
        <div class="card">
            <h2>Login to Vote</h2>

            <!-- Display error message if exists -->
            <?php if ($error): ?>
                <div class="error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <!-- Login form -->
            <form method="POST">
                <!-- Email field -->
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" placeholder="Enter your email" required>
                </div>

                <!-- Password field -->
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" placeholder="Enter your password" required>
                </div>

                <!-- Submit button -->
                <button type="submit">Login</button>
            </form>

            <!-- Link to registration page -->
            <p class="text-center mt-2">
                Don't have an account? <a href="register.php" style="color: #3498db; text-decoration: none;">Register here</a>
            </p>
        </div>
    </div>
</body>
</html>
