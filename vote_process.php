<?php
// vote_process.php - Process user vote
// Handles vote submission, prevents duplicate votes, updates database

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

// Check if form was submitted using POST
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    // If not POST, redirect to vote page
    header("Location: vote.php");
    exit();
}

// Get user ID from session
$user_id = $_SESSION["user_id"];

// Validate: Check if candidate_id was submitted
if (!isset($_POST["candidate_id"]) || empty($_POST["candidate_id"])) {
    header("Location: vote.php?error=no_candidate");
    exit();
}

// Get candidate ID from form and validate it's a number
$candidate_id = intval($_POST["candidate_id"]);

// ============================================================
// DUPLICATE VOTE PREVENTION - CRITICAL SECURITY FEATURE
// ============================================================
// Before processing vote, check if user has already voted
// This is the PRIMARY defense against duplicate voting
// We check has_voted flag which was set to 0 when user registered
$check_voted = $conn->prepare("SELECT has_voted FROM users WHERE id = ?");
$check_voted->bind_param("i", $user_id);
$check_voted->execute();
$result = $check_voted->get_result();
$user = $result->fetch_assoc();

// If has_voted is already 1, user has voted before - BLOCK THIS VOTE
if ($user["has_voted"] == 1) {
    // User has already voted - this is a duplicate attempt
    // Log this for security purposes (optional: store in database)
    header("Location: results.php?error=already_voted");
    exit();
}

$check_voted->close();

// ============================================================
// VOTE PROCESSING - Using transactions for data integrity
// ============================================================

// Start transaction (all or nothing - if any step fails, rollback all changes)
$conn->begin_transaction();

try {
    // STEP 1: Insert vote record into votes table
    // SECURITY: Use prepared statement to prevent SQL injection
    $insert_vote = $conn->prepare("INSERT INTO votes (user_id, candidate_id, voted_at) VALUES (?, ?, NOW())");
    $insert_vote->bind_param("ii", $user_id, $candidate_id);
    
    if (!$insert_vote->execute()) {
        throw new Exception("Failed to insert vote");
    }
    $insert_vote->close();
    
    // STEP 2: Update candidate vote count by incrementing it by 1
    // SECURITY: Use prepared statement to prevent SQL injection
    $update_votes = $conn->prepare("UPDATE candidates SET vote_count = vote_count + 1 WHERE id = ?");
    $update_votes->bind_param("i", $candidate_id);
    
    if (!$update_votes->execute()) {
        throw new Exception("Failed to update vote count");
    }
    $update_votes->close();
    
    // STEP 3: Mark user as having voted by setting has_voted = 1
    // This is CRITICAL for preventing duplicate votes
    // SECURITY: Use prepared statement to prevent SQL injection
    $mark_voted = $conn->prepare("UPDATE users SET has_voted = 1 WHERE id = ?");
    $mark_voted->bind_param("i", $user_id);
    
    if (!$mark_voted->execute()) {
        throw new Exception("Failed to mark user as voted");
    }
    $mark_voted->close();
    
    // All three steps successful - commit the transaction
    $conn->commit();
    
    // Vote was processed successfully - redirect to results
    header("Location: results.php?message=vote_success");
    exit();
    
} catch (Exception $e) {
    // If any error occurred, rollback all changes (undo everything)
    $conn->rollback();
    
    // Redirect back to vote page with error
    header("Location: vote.php?error=vote_failed");
    exit();
}
?>
