<?php
/**
 * AJAX endpoint for getting interview details
 */

// Ensure we return JSON for all responses
header('Content-Type: application/json');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

// Get the interview ID from query parameters
$id = $_GET['id'] ?? 0;

if (!$id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Interview ID is required']);
    exit;
}

try {
    // Initialize interview controller and get interview details
    require_once __DIR__ . '/../../Dashboard/controllers/InterviewController.php';
    $interviewController = new InterviewController();
    $result = $interviewController->getInterview($id);

    if (!$result['success']) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => $result['message']]);
        exit;
    }

    // Return interview data as JSON
    echo json_encode($result['data']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    exit;
}