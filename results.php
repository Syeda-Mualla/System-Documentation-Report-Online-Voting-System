<?php
// results.php - Display voting results
// Shows all candidates with vote counts and progress bars
// Shows percentage of votes each candidate received

// Include database connection
require_once 'db.php';

// Start session to check if user is logged in
session_start();

// SECURITY CHECK: Verify user is logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

// Get user name from session
$user_name = $_SESSION["user_name"];

// Fetch all candidates with their vote counts
// Ordered by vote_count in descending order (most votes first)
$candidates_query = "SELECT id, name, party, vote_count FROM candidates ORDER BY vote_count DESC";
$candidates_result = $conn->query($candidates_query);

// Check if query was successful
if (!$candidates_result) {
    die("Error fetching candidates: " . $conn->error);
}

// Calculate total votes across all candidates
$total_votes_query = "SELECT SUM(vote_count) as total FROM candidates";
$total_result = $conn->query($total_votes_query);
$total_row = $total_result->fetch_assoc();
$total_votes = $total_row["total"] ?? 0;  // ?? returns 0 if total is null

// Handle messages (from vote_process.php redirects)
$message = "";
if (isset($_GET["message"])) {
    if ($_GET["message"] == "vote_success") {
        $message = "✓ Your vote has been recorded successfully!";
    }
    elseif ($_GET["message"] == "already_voted") {
        $message = "ℹ️ You have already voted. You can view the results below.";
    }
}

// Check if user has voted
$check_voted = $conn->prepare("SELECT has_voted FROM users WHERE id = ?");
$check_voted->bind_param("i", $_SESSION["user_id"]);
$check_voted->execute();
$result = $check_voted->get_result();
$user = $result->fetch_assoc();
$has_voted = $user["has_voted"] == 1;
$check_voted->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Results - Online Voting System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <!-- Navigation bar -->
    <nav>
        <h1>🗳️ Online Voting System</h1>
        <ul>
            <?php if (!$has_voted): ?>
                <li><a href="vote.php">Vote</a></li>
            <?php endif; ?>
            <li><a href="logout.php">Logout</a></li>
            <?php if ($_SESSION["is_admin"] == 1): ?>
                <li><a href="admin.php">Admin Panel</a></li>
            <?php endif; ?>
        </ul>
    </nav>

    <!-- Main container -->
    <div class="container">
        <!-- Welcome card -->
        <div class="card">
            <h2>Voting Results</h2>
            <p class="text-center mb-3">Welcome, <strong><?php echo htmlspecialchars($user_name); ?></strong></p>

            <!-- Display message if exists -->
            <?php if ($message): ?>
                <div class="success mb-3"><?php echo $message; ?></div>
            <?php endif; ?>
        </div>

        <!-- Results card -->
        <div class="card">
            <!-- Total votes -->
            <div style="background-color: #f0f8ff; padding: 1rem; border-radius: 4px; margin-bottom: 1.5rem; text-align: center;">
                <h3 style="margin: 0;">Total Votes Cast</h3>
                <p style="font-size: 2rem; font-weight: bold; color: #3498db; margin: 0.5rem 0;">
                    <?php echo $total_votes; ?>
                </p>
            </div>

            <!-- Results heading -->
            <h3>Candidate Rankings</h3>

            <!-- Loop through all candidates and display results -->
            <?php
            // Reset results pointer to beginning
            $candidates_result->data_seek(0);
            
            // Loop counter for ranking position
            $rank = 1;
            
            while ($candidate = $candidates_result->fetch_assoc()) {
                $candidate_id = $candidate["id"];
                $candidate_name = htmlspecialchars($candidate["name"]);
                $candidate_party = htmlspecialchars($candidate["party"]);
                $vote_count = $candidate["vote_count"];
                
                // Calculate percentage of total votes (avoid division by zero)
                if ($total_votes > 0) {
                    $percentage = ($vote_count / $total_votes) * 100;
                } else {
                    $percentage = 0;
                }
            ?>
                <!-- Candidate result card -->
                <div class="candidate-card">
                    <!-- Rank and name -->
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <div style="font-size: 1.1rem; color: #7f8c8d; margin-bottom: 0.25rem;">
                                Rank: #<?php echo $rank; ?>
                            </div>
                            <div class="candidate-name"><?php echo $candidate_name; ?></div>
                            <div class="candidate-party">Party: <?php echo $candidate_party; ?></div>
                        </div>
                        <div class="vote-count"><?php echo $vote_count; ?> votes</div>
                    </div>

                    <!-- Progress bar -->
                    <div class="progress-bar-container">
                        <div class="progress-bar" style="width: <?php echo round($percentage, 1); ?>%;">
                            <?php echo round($percentage, 1); ?>%
                        </div>
                    </div>
                </div>

            <?php
                $rank++;  // Increment rank for next candidate
            }
            ?>
        </div>

        <!-- Info box -->
        <div class="card">
            <div class="info">
                <strong>ℹ️ About Results:</strong> Results are updated in real-time as votes are cast. 
                <?php if (!$has_voted): ?>
                    You can still vote by clicking the "Vote" button in the navigation.
                <?php else: ?>
                    You have already voted. Your vote has been recorded.
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
