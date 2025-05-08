<?php
/**
 * Interview Controller - Handles all interview-related operations
 */
class InterviewController {
    private $interviewModel;
    
    public function __construct() {
        require_once __DIR__ . '/../models/InterviewModel.php';
        $this->interviewModel = new InterviewModel();
    }
    
    /**
     * Display interview listing page
     */
    public function index() {
        $user = $_SESSION['user'];
        $userEmail = $user['email'];
        $userType = $user['user_type'] ?? 'freelancer';
        
        try {
            if ($userType === 'admin') {
                $data = [
                    'interviews' => $this->interviewModel->getAllInterviews($userEmail),
                    'jobOffers' => $this->interviewModel->getJobOffers(),
                    'stats' => $this->interviewModel->getInterviewStats($userEmail)
                ];
                include __DIR__ . '/../views/dashboard/admin_interviews.php';
            } else {
                include __DIR__ . '/../views/dashboard/user_interviews.php';
            }
        } catch (Exception $e) {
            $error = $e->getMessage();
            include __DIR__ . '/../views/error.php';
        }
    }
    
    /**
     * Handle interview creation
     */
    public function create() {
        // Get user email from form or session if user is logged in
        $userEmail = $_POST['user_email'] ?? ($_SESSION['user']['email'] ?? null);
        
        if (empty($userEmail)) {
            throw new Exception('User email is required');
        }
        
        try {
            // Validate required fields
            if (empty($_POST['candidate_name']) || empty($_POST['position_title']) || 
                empty($_POST['interview_date']) || empty($_POST['interviewer']) || 
                empty($_POST['location']) || empty($_POST['status'])) {
                throw new Exception('All required fields must be filled');
            }

            $interviewData = $_POST;
            $result = $this->interviewModel->createInterview($interviewData);
            
            // If it's an AJAX request, return JSON response
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => $result,
                    'message' => $result ? 'Interview scheduled successfully!' : 'Failed to schedule interview'
                ]);
                exit;
            }
            
            return [
                'success' => $result,
                'message' => $result ? 'Interview scheduled successfully!' : 'Failed to schedule interview'
            ];
        } catch (Exception $e) {
            // If it's an AJAX request, return JSON response
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
                exit;
            }
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Handle interview update
     */
    public function update($id) {
        // Get user email either from form or session
        $userEmail = $_POST['user_email'] ?? ($_SESSION['user']['email'] ?? null);
        
        if (!$userEmail) {
            error_log("Update Interview Error: No user email provided");
            throw new Exception('User email is required');
        }

        try {
            if (!$id) {
                error_log("Update Interview Error: No interview ID provided");
                throw new Exception('Interview ID is required');
            }

            // Log incoming data for debugging
            error_log("Update interview request for ID: $id from user: $userEmail");
            error_log("POST data: " . print_r($_POST, true));

            // Validate required fields
            $requiredFields = ['candidate_name', 'position_title', 'interview_date', 'interviewer', 'location', 'status'];
            $missingFields = [];
            
            foreach ($requiredFields as $field) {
                if (empty($_POST[$field])) {
                    $missingFields[] = $field;
                }
            }
            
            if (!empty($missingFields)) {
                $errorMsg = 'Missing required fields: ' . implode(', ', $missingFields);
                error_log("Update Interview Error: $errorMsg");
                throw new Exception($errorMsg);
            }

            // Create data array with user email
            $interviewData = array_merge($_POST, ['user_email' => $userEmail]);
            
            // Ensure job_offer_id is properly handled (can be null)
            if (empty($interviewData['job_offer_id'])) {
                $interviewData['job_offer_id'] = null;
            }
            
            // Update the interview
            $result = $this->interviewModel->updateInterview($id, $interviewData);

            // Log the result
            error_log("Update result for interview ID $id: " . ($result ? "Success" : "Failed"));

            // If it's an AJAX request, return JSON response
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                // Ensure Content-Type header is set if not already sent
                if (!headers_sent()) {
                    header('Content-Type: application/json');
                }
                echo json_encode([
                    'success' => $result,
                    'message' => $result ? 'Interview updated successfully!' : 'Failed to update interview'
                ]);
                exit;
            }

            return [
                'success' => $result,
                'message' => $result ? 'Interview updated successfully!' : 'Failed to update interview'
            ];
        } catch (Exception $e) {
            // Log the error
            error_log("Error updating interview ID $id: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            
            // If it's an AJAX request, return JSON response
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                // Ensure Content-Type header is set if not already sent
                if (!headers_sent()) {
                    header('Content-Type: application/json');
                }
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
                exit;
            }
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Handle interview deletion
     */
    public function delete($id) {
        $userEmail = $_SESSION['user']['email'];
        try {
            $result = $this->interviewModel->deleteInterview($id, $userEmail);
            return [
                'success' => $result,
                'message' => $result ? 'Interview deleted successfully!' : 'Failed to delete interview'
            ];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Get interview details
     */
    public function getInterview($id) {
        $userEmail = $_SESSION['user']['email'];
        try {
            $interview = $this->interviewModel->getInterview($id, $userEmail);
            if (!$interview) {
                throw new Exception('Interview not found');
            }
            return ['success' => true, 'data' => $interview];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}