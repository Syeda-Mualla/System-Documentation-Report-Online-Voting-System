<?php
// admin_process.php - Process admin actions
// Handles adding and deleting candidates
// Only accessible to admin users (is_admin = 1)

// Include database connection
require_once 'db.php';

// Start session
session_start();

// SECURITY CHECK 1: Verify user is logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

// SECURITY CHECK 2: Verify user is admin
// This prevents non-admin users from accessing this file directly via URL
if ($_SESSION["is_admin"] != 1) {
    header("Location: vote.php");
    exit();
}

// Check if form was submitted via POST
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: admin.php");
    exit();
}

// Check if action parameter exists and is not empty
if (!isset($_POST["action"]) || empty($_POST["action"])) {
    header("Location: admin.php");
    exit();
}

// Get the action (add or delete)
$action = $_POST["action"];

// ============================================================
// HANDLE ADD CANDIDATE ACTION
// ============================================================
if ($action == "add") {
    // Get form inputs and remove extra spaces
    $name = trim($_POST["name"]);
    $party = trim($_POST["party"]);
    
    // Validate: Check if name is not empty
    if (empty($name)) {
        header("Location: admin.php?error=empty_name");
        exit();
    }
    
    // Validate: Check if party is not empty
    if (empty($party)) {
        header("Location: admin.php?error=empty_party");
        exit();
    }
    
    // SECURITY: Use prepared statement to prevent SQL injection
    // Even admin input should be protected against SQL injection
    $add_candidate = $conn->prepare("INSERT INTO candidates (name, party, vote_count) VALUES (?, ?, 0)");
    
    // Bind parameters (ss = string, string)
    $add_candidate->bind_param("ss", $name, $party);
    
    // Execute the insert
    if ($add_candidate->execute()) {
        // Candidate added successfully - redirect to admin panel
        header("Location: admin.php?success=candidate_added");
    }
    else {
        // Error occurred during insertion
        header("Location: admin.php?error=add_failed");
    }
    
    // Close prepared statement
    $add_candidate->close();
}

// ============================================================
// HANDLE DELETE CANDIDATE ACTION
// ============================================================
elseif ($action == "delete") {
    // Get candidate ID from form
    $candidate_id = intval($_POST["candidate_id"]);  // intval() converts to integer safely
    
    // Validate: Check if candidate_id is provided and is a positive number
    if (empty($candidate_id) || $candidate_id <= 0) {
        header("Location: admin.php?error=invalid_candidate");
        exit();
    }
    
    // SECURITY: Use prepared statement to prevent SQL injection
    $delete_candidate = $conn->prepare("DELETE FROM candidates WHERE id = ?");
    
    // Bind parameter (i = integer type)
    $delete_candidate->bind_param("i", $candidate_id);
    
    // Execute the delete
    if ($delete_candidate->execute()) {
        // Check if any row was actually deleted (candidate existed)
        if ($delete_candidate->affected_rows > 0) {
            // Candidate deleted successfully
            header("Location: admin.php?success=candidate_deleted");
        }
        else {
            // Candidate ID not found
            header("Location: admin.php?error=candidate_not_found");
        }
    }
    else {
        // Error occurred during deletion
        header("Location: admin.php?error=delete_failed");
    }
    
    // Close prepared statement
    $delete_candidate->close();
}
else {
    // Unknown action - redirect to admin panel
    header("Location: admin.php?error=invalid_action");
}
?>
