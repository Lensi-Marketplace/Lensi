<?php
/**
 * Interviews Management Component
 * Handles both AJAX and regular form submissions for interview management
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// For AJAX requests, set JSON content type immediately
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    header('Content-Type: application/json');
}

// Include required controllers
require_once __DIR__ . '/../../Dashboard/controllers/InterviewController.php';
$interviewController = new InterviewController();

// Helper function to check if request is AJAX
function isAjaxRequest() {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $response = [];
    
    try {
        // Ensure user is logged in for all actions
        if (!isset($_SESSION['user'])) {
            throw new Exception('Authentication required. Please log in.');
        }
        
        // Get user email either from form or session
        if (empty($_POST['user_email']) && isset($_SESSION['user']['email'])) {
            $_POST['user_email'] = $_SESSION['user']['email'];
        }
        
        switch ($action) {
            case 'create':
                $response = $interviewController->create();
                break;
                
            case 'update':
                $id = $_POST['id'] ?? 0;
                if (!$id) {
                    throw new Exception('Interview ID is required for update');
                }
                $response = $interviewController->update($id);
                break;
                
            case 'delete':
                $id = $_POST['id'] ?? 0;
                if (!$id) {
                    throw new Exception('Interview ID is required for delete');
                }
                $response = $interviewController->delete($id);
                break;
                
            default:
                throw new Exception('Invalid action');
        }
    } catch (Exception $e) {
        $response = ['success' => false, 'message' => $e->getMessage()];
        
        // Log the error for debugging
        error_log("Interview action error: " . $e->getMessage() . "\nStack trace: " . $e->getTraceAsString());
    }
    
    // Handle the response based on request type
    if (isAjaxRequest()) {
        // Ensure we're sending JSON response
        if (!headers_sent()) {
            header('Content-Type: application/json');
        }
        echo json_encode($response);
        exit;
    } else {
        // For regular form submissions, set session message and redirect
        if ($response['success']) {
            $_SESSION['success_message'] = $response['message'];
        } else {
            $_SESSION['error_message'] = $response['message'];
        }
        // Redirect back to the interviews page
        header('Location: /web/components/Dashboard/index.php?page=interviews');
        exit;
    }
}

// For GET requests, show the interviews page
try {
    // Check if user is logged in
    if (!isset($_SESSION['user'])) {
        throw new Exception('Please log in to view interviews');
    }
    
    $user = $_SESSION['user'];
    $userType = $user['user_type'] ?? 'freelancer';
    
    // Create InterviewModel instance
    require_once __DIR__ . '/../../Dashboard/models/InterviewModel.php';
    $interviewModel = new InterviewModel();
    
    $data = [
        'interviews' => $userType === 'admin' ? 
            $interviewModel->getAllInterviews($user['email']) : 
            $interviewModel->getUserInterviews($user['email']),
        'jobOffers' => $interviewModel->getJobOffers()
    ];
    
    // Include the appropriate view based on user type
    if ($userType === 'admin') {
        include __DIR__ . '/../../Dashboard/views/dashboard/admin_interviews.php';
    } else {
        include __DIR__ . '/../../Dashboard/views/dashboard/user_interviews.php';
    }
} catch (Exception $e) {
    if (isAjaxRequest()) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        exit;
    }
    $error = $e->getMessage();
    include __DIR__ . '/../../Dashboard/views/error.php';
}