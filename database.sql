-- =====================================================================
-- ONLINE VOTING SYSTEM - DATABASE SCHEMA
-- =====================================================================
-- This SQL file creates the complete database structure for the voting system
-- Import this file in phpMyAdmin or MySQL command line

-- =====================================================================
-- CREATE DATABASE
-- =====================================================================
-- Create the voting_db database if it doesn't exist
CREATE DATABASE IF NOT EXISTS voting_db;

-- Select the database to use
USE voting_db;

-- =====================================================================
-- TABLE 1: USERS
-- =====================================================================
-- Stores registered user information
-- Purpose: Store voter data and prevent duplicate voting
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,           -- Unique user ID, auto-increments
    name VARCHAR(100) NOT NULL,                  -- Full name of voter
    email VARCHAR(100) NOT NULL UNIQUE,          -- Email (must be unique - no duplicate registrations)
    password VARCHAR(255) NOT NULL,              -- Password (stored as bcrypt hash, never plain text)
    has_voted INT DEFAULT 0,                     -- 0 = hasn't voted, 1 = has voted (PREVENTS DUPLICATE VOTING)
    is_admin INT DEFAULT 0,                      -- 0 = regular user, 1 = admin user (can manage candidates)
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP  -- When account was created
);

-- =====================================================================
-- TABLE 2: CANDIDATES
-- =====================================================================
-- Stores candidate information
-- Purpose: List all candidates available for voting
CREATE TABLE candidates (
    id INT PRIMARY KEY AUTO_INCREMENT,           -- Unique candidate ID, auto-increments
    name VARCHAR(100) NOT NULL,                  -- Candidate's name
    party VARCHAR(100) NOT NULL,                 -- Political party name
    vote_count INT DEFAULT 0,                    -- Total votes received (starts at 0, incremented when voted)
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP  -- When candidate was added
);

-- =====================================================================
-- TABLE 3: VOTES
-- =====================================================================
-- Stores vote records
-- Purpose: Track each vote for audit trail and verification
CREATE TABLE votes (
    id INT PRIMARY KEY AUTO_INCREMENT,           -- Unique vote ID
    user_id INT NOT NULL,                        -- User who cast the vote (foreign key to users table)
    candidate_id INT NOT NULL,                   -- Candidate who received the vote (foreign key to candidates table)
    voted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, -- When the vote was cast
    
    -- Foreign key constraints - ensure data integrity
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,         -- If user deleted, delete their votes
    FOREIGN KEY (candidate_id) REFERENCES candidates(id) ON DELETE CASCADE -- If candidate deleted, delete their votes
);

-- =====================================================================
-- CREATE INDEXES FOR PERFORMANCE
-- =====================================================================
-- These indexes speed up queries used in the application

-- Index on email for faster login lookups
CREATE INDEX idx_user_email ON users(email);

-- Index on has_voted for faster duplicate vote checking
CREATE INDEX idx_user_has_voted ON users(has_voted);

-- Index on user_id in votes table for audit trail
CREATE INDEX idx_vote_user ON votes(user_id);

-- Index on candidate_id for looking up votes per candidate
CREATE INDEX idx_vote_candidate ON votes(candidate_id);

-- =====================================================================
-- INSERT SAMPLE DATA (OPTIONAL)
-- =====================================================================
-- Uncomment this section to populate sample data for testing

-- Insert sample users
-- Note: Passwords are NOT hashed here - in real app, always use password_hash()
-- Sample users (password would normally be: password123)
/*
INSERT INTO users (name, email, password, has_voted, is_admin) VALUES
('Admin User', 'admin@voting.com', '$2y$10$WYmGHQQBX9VYA8K7TsZ.uuQxIYi4xRKh7KSEKSg7BG7S5L4VbqFbe', 0, 1),
('John Smith', 'john@voting.com', '$2y$10$0K5e5VnKHvAW7QUU8uE7POGBxh3xqSZhLmQl5fS5PJ4F5QYz.3OGq', 0, 0),
('Jane Doe', 'jane@voting.com', '$2y$10$r7TRXZgP5w9mVQPjCPQPMuRh5v5u7Q9Kz3L2M5N1O6P5Q9R2S5T4U', 0, 0);

-- Insert sample candidates
INSERT INTO candidates (name, party, vote_count) VALUES
('Ali Hassan', 'Democratic Party', 0),
('Fatima Khan', 'Socialist Party', 0),
('Muhammad Ahmed', 'People Party', 0),
('Ayesha Malik', 'Green Party', 0);
*/

-- =====================================================================
-- DATABASE SETUP COMPLETE
-- =====================================================================
-- The database is now ready to use!
-- To test: Go to registration page and create an account
