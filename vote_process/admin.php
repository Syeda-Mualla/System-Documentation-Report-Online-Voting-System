<?php
// admin.php - Admin panel for managing candidates
// Only accessible to users with is_admin = 1
// Allows adding and deleting candidates

// Include database connection
require_once 'db.php';

// Start session
session_start();

// SECURITY CHECK 1: Verify user is logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

// SECURITY CHECK 2: Verify user is an admin (is_admin = 1)
// If is_admin is not 1 (i.e., is 0), user is not authorized - redirect to voting page
if ($_SESSION["is_admin"] != 1) {
    header("Location: vote.php");
    exit();
}

// Get admin name from session
$admin_name = $_SESSION["user_name"];

// Fetch all candidates from database
$candidates_query = "SELECT id, name, party, vote_count FROM candidates ORDER BY name ASC";
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
    <title>Admin Panel - Online Voting System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <!-- Navigation bar -->
    <nav>
        <h1>🗳️ Online Voting System</h1>
        <ul>
            <li><a href="results.php">View Results</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </nav>

    <!-- Main container -->
    <div class="container">
        <!-- Welcome card -->
        <div class="card">
            <h2>Admin Panel</h2>
            <p class="text-center mb-3" style="color: #27ae60; font-weight: bold;">
                ✓ Welcome Admin: <?php echo htmlspecialchars($admin_name); ?>
            </p>
            <div class="info">
                <strong>⚠️ Admin Access:</strong> Only administrators can add or remove candidates. Handle with care.
            </div>
        </div>

        <!-- Add candidate form -->
        <div class="card">
            <h3>Add New Candidate</h3>
            
            <!-- Form to add candidate -->
            <form method="POST" action="admin_process.php">
                <!-- Hidden field to specify action -->
                <input type="hidden" name="action" value="add">

                <!-- Candidate name field -->
                <div class="form-group">
                    <label for="name">Candidate Name:</label>
                    <input type="text" id="name" name="name" placeholder="Enter candidate name" required>
                </div>

                <!-- Candidate party field -->
                <div class="form-group">
                    <label for="party">Political Party:</label>
                    <input type="text" id="party" name="party" placeholder="Enter political party" required>
                </div>

                <!-- Submit button -->
                <button type="submit">Add Candidate</button>
            </form>
        </div>

        <!-- Candidates table -->
        <div class="card">
            <h3>All Candidates</h3>

            <!-- Check if any candidates exist -->
            <?php if ($candidates_result->num_rows > 0): ?>
                <!-- Table showing all candidates -->
                <table>
                    <!-- Table header -->
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Party</th>
                            <th>Votes</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <!-- Table body with candidate data -->
                    <tbody>
                        <?php
                        // Loop through each candidate
                        while ($candidate = $candidates_result->fetch_assoc()) {
                            $candidate_id = $candidate["id"];
                            $candidate_name = htmlspecialchars($candidate["name"]);
                            $candidate_party = htmlspecialchars($candidate["party"]);
                            $vote_count = $candidate["vote_count"];
                        ?>
                            <tr>
                                <td><?php echo $candidate_id; ?></td>
                                <td><?php echo $candidate_name; ?></td>
                                <td><?php echo $candidate_party; ?></td>
                                <td><strong><?php echo $vote_count; ?></strong></td>
                                <!-- Delete button for each candidate -->
                                <td>
                                    <!-- Form for delete action -->
                                    <form method="POST" action="admin_process.php" style="display: inline;">
                                        <!-- Hidden fields for delete action -->
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="candidate_id" value="<?php echo $candidate_id; ?>">
                                        
                                        <!-- Delete button with red styling -->
                                        <button type="submit" class="btn-delete" 
                                                onclick="return confirm('Are you sure you want to delete this candidate?');">
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php
                        }
                        ?>
                    </tbody>
                </table>
            <?php else: ?>
                <!-- Message if no candidates exist -->
                <div class="info">
                    No candidates found. Add a candidate to get started.
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
