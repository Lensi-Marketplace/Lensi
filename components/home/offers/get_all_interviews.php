<?php
/**
 * AJAX endpoint for getting all interviews for the current user
 * Used by the calendar view to display interviews
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Get user data
$user = $_SESSION['user'];
$userEmail = $user['email'];

// Initialize interview model
require_once __DIR__ . '/../../Dashboard/models/InterviewModel.php';
$interviewModel = new InterviewModel();

try {
    // Get user's interviews
    $interviews = $interviewModel->getUserInterviews($userEmail);
    
    // Return interviews as JSON
    header('Content-Type: application/json');
    echo json_encode($interviews);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
    exit;
}