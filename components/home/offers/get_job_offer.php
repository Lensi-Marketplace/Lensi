<?php
/**
 * AJAX endpoint for getting job offer details
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

// Get the job offer ID from query parameters
$id = $_GET['id'] ?? 0;

if (!$id) {
    http_response_code(400);
    echo json_encode(['error' => 'Job offer ID is required']);
    exit;
}

// Initialize job offer controller and get job offer details
require_once __DIR__ . '/../../Dashboard/controllers/JobOfferController.php';
$jobOfferController = new JobOfferController();
$result = $jobOfferController->getJobOffer($id);

// Set JSON content type header
header('Content-Type: application/json');

// Return the result
if ($result['success']) {
    echo json_encode($result['data']);
} else {
    http_response_code(404);
    echo json_encode(['error' => $result['message']]);
}