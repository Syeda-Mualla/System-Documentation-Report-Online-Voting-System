<?php
// vote.php - Voting page
// Displays all candidates as radio buttons for user to vote
// Only shows if user is logged in and hasn't voted yet

// Include database connection
require_once 'db.php';

// Start session
session_start();

// SECURITY CHECK: Verify user is logged in
// If no user_id in session, user is not authenticated - redirect to login
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

// Get user ID from session
$user_id = $_SESSION["user_id"];

// SECURITY CHECK: Check if user has already voted
// Query database to see if has_voted flag is set to 1 for this user
$check_voted = $conn->prepare("SELECT has_voted FROM users WHERE id = ?");
$check_voted->bind_param("i", $user_id);  // i = integer type
$check_voted->execute();
$result = $check_voted->get_result();
$user = $result->fetch_assoc();

// If user has already voted, redirect to results page
if ($user["has_voted"] == 1) {
    header("Location: results.php?message=already_voted");
    exit();
}

$check_voted->close();

// Fetch all candidates from database to show in radio buttons
$candidates_query = "SELECT id, name, party FROM candidates ORDER BY name ASC";
$candidates_result = $conn->query($candidates_query);

// Check if query was successful
if (!$candidates_result) {
    die("Error fetching candidates: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vote - Online Voting System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <!-- Navigation bar -->
    <nav>
        <h1>🗳️ Online Voting System</h1>
        <ul>
            <li><a href="logout.php">Logout</a></li>
            <?php if ($_SESSION["is_admin"] == 1): ?>
                <li><a href="admin.php">Admin Panel</a></li>
            <?php endif; ?>
        </ul>
    </nav>

    <!-- Main container -->
    <div class="container">
        <!-- Voting card -->
        <div class="card">
            <h2>Cast Your Vote</h2>
            <p class="text-center mb-3" style="color: #7f8c8d;">Select a candidate and submit your vote</p>

            <!-- Voting form -->
            <form method="POST" action="vote_process.php">
                <!-- Radio button group for each candidate -->
                <div class="form-group">
                    <?php
                    // Loop through each candidate and create a radio button
                    while ($candidate = $candidates_result->fetch_assoc()) {
                        $candidate_id = $candidate["id"];
                        $candidate_name = htmlspecialchars($candidate["name"]);
                        $candidate_party = htmlspecialchars($candidate["party"]);
                    ?>
                        <!-- Candidate radio button with label -->
                        <div class="radio-group" style="margin-bottom: 1rem; padding: 1rem; background-color: #f9f9f9; border-radius: 4px;">
                            <input type="radio" id="candidate_<?php echo $candidate_id; ?>" 
                                   name="candidate_id" value="<?php echo $candidate_id; ?>" required>
                            <label for="candidate_<?php echo $candidate_id; ?>" style="font-weight: bold;">
                                <?php echo $candidate_name; ?> (<?php echo $candidate_party; ?>)
                            </label>
                        </div>
                    <?php
                    }
                    ?>
                </div>

                <!-- Submit button -->
                <button type="submit">Submit Vote</button>
            </form>

            <!-- Info box with voting instructions -->
            <div class="info" style="margin-top: 2rem;">
                <strong>ℹ️ Important:</strong> You can only vote once. Please select your candidate carefully before submitting.
            </div>
        </div>
    </div>
</body>
</html>
