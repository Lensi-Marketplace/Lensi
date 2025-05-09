<?php
/**
 * Interview Controller
 * Handles all interview-related business logic and request processing
 * Following strict MVC architecture
 */
class InterviewController {
    private $interviewModel;
    
    /**
     * Constructor - initializes the model
     */
    public function __construct() {
        require_once __DIR__ . '/../models/InterviewModel.php';
        $this->interviewModel = new InterviewModel();
    }
    
    /**
     * Get data for the interview dashboard view
     * 
     * @param string $userEmail The email of the logged-in user
     * @return array Data for the view
     */
    public function getInterviewDashboardData($userEmail) {
        try {
            // Get all interviews for display in the table
            $interviews = $this->interviewModel->getUserInterviews($userEmail);
            
            // Get only the next upcoming interview for the countdown timer
            $nextInterview = $this->interviewModel->getNextInterview($userEmail);
            
            return [
                'success' => true,
                'interviews' => $interviews,
                'nextInterview' => $nextInterview
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Filter interviews by status
     * 
     * @param string $userEmail The email of the logged-in user
     * @param string $status The status to filter by
     * @return array Filtered interviews
     */
    public function filterInterviewsByStatus($userEmail, $status = 'all') {
        try {
            $interviews = $this->interviewModel->getInterviewsByStatus($userEmail, $status);
            
            return [
                'success' => true,
                'interviews' => $interviews
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Process interview form submission (update or create)
     * 
     * @param array $formData The submitted form data
     * @return array Result of the operation
     */
    public function processInterviewForm($formData) {
        try {
            // Validate form data
            $this->validateInterviewData($formData);
            
            // Process based on action type
            if ($formData['action'] === 'update' && !empty($formData['id'])) {
                $result = $this->interviewModel->updateInterview($formData['id'], $formData);
                $message = 'Interview updated successfully';
            } else {
                $result = $this->interviewModel->createInterview($formData);
                $message = 'Interview created successfully';
            }
            
            return [
                'success' => true,
                'message' => $message
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Validate interview data
     * Throws exceptions for invalid data
     * 
     * @param array $data The data to validate
     * @throws Exception If validation fails
     */
    private function validateInterviewData($data) {
        // Required fields
        $requiredFields = [
            'position_title' => 'Position title',
            'interview_date' => 'Interview date',
            'interviewer' => 'Interviewer',
            'location' => 'Location',
            'status' => 'Status',
            'candidate_name' => 'Candidate name'
        ];
        
        // Check required fields
        foreach ($requiredFields as $field => $label) {
            if (empty($data[$field])) {
                throw new Exception("{$label} is required");
            }
        }
        
        // Validate interview date is a valid date
        $interviewDate = strtotime($data['interview_date']);
        if ($interviewDate === false) {
            throw new Exception("Invalid interview date format");
        }
        
        // Validate status is one of the allowed values
        $allowedStatuses = ['Scheduled', 'Completed', 'Cancelled'];
        if (!in_array($data['status'], $allowedStatuses)) {
            throw new Exception("Invalid status value");
        }
        
        // Validate URL format for CV URL if provided
        if (!empty($data['cv_url']) && !filter_var($data['cv_url'], FILTER_VALIDATE_URL)) {
            throw new Exception("CV URL must be a valid URL");
        }
    }
}