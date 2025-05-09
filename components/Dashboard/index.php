<?php
/**
 * Dashboard Main Controller
 * This file serves as the entry point for the dashboard and implements MVC architecture
 */

// Initialize session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in, if not redirect to login page
if (!isset($_SESSION['user'])) {
    header('Location: ../Login/login.php');
    exit;
}

// Get user data from session
$userName = $_SESSION['user']['name'] ?? '';
$userType = $_SESSION['user']['user_type'] ?? 'freelancer';
$user = $_SESSION['user'] ?? [];

// Get theme preference from cookies or system preference
$savedTheme = isset($_COOKIE['theme']) ? $_COOKIE['theme'] : null;
$systemTheme = isset($_SERVER['HTTP_SEC_CH_PREFERS_COLOR_SCHEME']) ? $_SERVER['HTTP_SEC_CH_PREFERS_COLOR_SCHEME'] : null;
$initialTheme = $savedTheme ?: ($systemTheme ?: 'light');

// Include controllers
require_once __DIR__ . '/controllers/DashboardController.php';
require_once __DIR__ . '/controllers/UserController.php';
require_once __DIR__ . '/controllers/InterviewController.php';
require_once __DIR__ . '/controllers/JobOfferController.php';

// Initialize controllers
$dashboardController = new DashboardController();
$userController = new UserController();
$interviewController = new InterviewController();
$jobOfferController = new JobOfferController();

// Handle page routing
$page = isset($_GET['page']) ? $_GET['page'] : (isset($_GET['section']) ? $_GET['section'] : 'dashboard');

// Process form submissions first
$formResult = [];

// Add proper error handling for AJAX requests
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    set_exception_handler(function($e) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        exit;
    });
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    switch ($page) {
        case 'profile':
            $formResult = $userController->updateProfile();
            break;
            
        case 'settings':
            if (isset($_POST['change_password'])) {
                $formResult = $userController->updatePassword();
            } elseif (isset($_POST['update_notifications'])) {
                $formResult = $userController->updateNotifications();
            } elseif (isset($_POST['update_privacy'])) {
                $formResult = $userController->updatePrivacy();
            }
            break;
            
        case 'interviews':
            $action = $_POST['action'] ?? '';
            switch ($action) {
                case 'create':
                    $formResult = $interviewController->create();
                    if ($formResult['success']) {
                        $_SESSION['success_message'] = $formResult['message'];
                    } else {
                        $_SESSION['error_message'] = $formResult['message'];
                    }
                    header('Location: /web/components/Dashboard/index.php?section=interviews');
                    exit;
                    break;
                    
                case 'update':
                    $id = $_POST['id'] ?? 0;
                    $formResult = $interviewController->update($id);
                    if ($formResult['success']) {
                        $_SESSION['success_message'] = $formResult['message'];
                    } else {
                        $_SESSION['error_message'] = $formResult['message'];
                    }
                    header('Location: /web/components/Dashboard/index.php?section=interviews');
                    exit;
                    break;
                    
                case 'delete':
                    $id = $_POST['id'] ?? 0;
                    $formResult = $interviewController->delete($id);
                    if ($formResult['success']) {
                        $_SESSION['success_message'] = $formResult['message'];
                    } else {
                        $_SESSION['error_message'] = $formResult['message'];
                    }
                    header('Location: /web/components/Dashboard/index.php?section=interviews');
                    exit;
                    break;
            }
            break;
            
        case 'job-offers':
            $action = $_POST['action'] ?? '';
            switch ($action) {
                case 'create':
                    $formResult = $jobOfferController->create();
                    if ($formResult['success']) {
                        $_SESSION['success_message'] = $formResult['message'];
                    } else {
                        $_SESSION['error_message'] = $formResult['message'];
                    }
                    header('Location: /web/components/Dashboard/index.php?section=job-offers');
                    exit;
                    break;
                    
                case 'update':
                    $id = $_POST['job_id'] ?? 0;
                    $formResult = $jobOfferController->update($id);
                    if ($formResult['success']) {
                        $_SESSION['success_message'] = $formResult['message'];
                    } else {
                        $_SESSION['error_message'] = $formResult['message'];
                    }
                    header('Location: /web/components/Dashboard/index.php?section=job-offers');
                    exit;
                    break;
                    
                case 'delete':
                    $id = $_POST['job_id'] ?? 0;
                    $formResult = $jobOfferController->delete($id);
                    if ($formResult['success']) {
                        $_SESSION['success_message'] = $formResult['message'];
                    } else {
                        $_SESSION['error_message'] = $formResult['message'];
                    }
                    header('Location: /web/components/Dashboard/index.php?section=job-offers');
                    exit;
                    break;
            }
            break;
    }
}

// Start output buffering to capture the page content
ob_start();

// Route to the appropriate controller/action based on the page parameter
switch ($page) {
    case 'dashboard':
        $dashboardController->index();
        break;
    case 'profile':
        $userController->profile();
        break;
    case 'settings':
        $userController->settings();
        break;
    case 'job-offers':
        $jobOfferController->index();
        break;
    case 'interviews':
        // Get user info from session
        $user = $_SESSION['user'];
        $userEmail = $user['email'];
        $userType = $user['user_type'] ?? 'freelancer';
        
        // Process POST requests (form submissions)
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
            $result = $interviewController->processInterviewForm($_POST);
            
            // Handle AJAX requests
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                header('Content-Type: application/json');
                echo json_encode($result);
                exit;
            }
            
            // For regular form posts, set session messages and redirect
            if ($result['success']) {
                $_SESSION['success_message'] = $result['message'];
            } else {
                $_SESSION['error_message'] = $result['message'];
            }
            
            // Redirect to prevent form resubmission
            header('Location: /web/components/Dashboard/index.php?page=interviews');
            exit;
        }
        
        // Get interview data for the view
        $dashboardData = $interviewController->getInterviewDashboardData($userEmail);
        
        // Extract data for the view
        $interviews = $dashboardData['success'] ? $dashboardData['interviews'] : [];
        $nextInterview = $dashboardData['success'] ? $dashboardData['nextInterview'] : null;
        $error = $dashboardData['success'] ? null : $dashboardData['error'];
        
        // Load the appropriate view based on user type
        if ($userType === 'admin') {
            include __DIR__ . '/views/dashboard/admin_interviews.php';
        } else {
            include __DIR__ . '/views/dashboard/user_interviews.php';
        }
        break;
    default:
        // Default to dashboard if page not found
        $dashboardController->index();
        break;
}

// Get the content generated by the controller action
$pageContent = ob_get_clean();

// Now include the layout template which will use $pageContent
include_once __DIR__ . '/views/layout.php';