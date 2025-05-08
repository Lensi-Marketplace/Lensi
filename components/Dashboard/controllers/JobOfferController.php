<?php
/**
 * Job Offer Controller - Handles all job offer-related operations
 */
class JobOfferController {
    private $jobOfferModel;
    
    public function __construct() {
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        require_once __DIR__ . '/../models/JobOfferModel.php';
        $this->jobOfferModel = new JobOfferModel();
    }
    
    /**
     * Display job offer listing page
     */
    public function index() {
        // Verify user is logged in
        if (!isset($_SESSION['user'])) {
            header('Location: /web/components/login/login.php');
            exit;
        }

        $user = $_SESSION['user'];
        $userEmail = $user['email'];
        $userType = $user['user_type'] ?? 'freelancer';
        
        try {
            $data = [
                'jobOffers' => $this->jobOfferModel->getAllJobOffers($userEmail),
                'categories' => $this->jobOfferModel->getCategories(),
                'locations' => $this->jobOfferModel->getLocations()
            ];
            
            if ($userType === 'admin') {
                $data['stats'] = $this->jobOfferModel->getJobStats($userEmail);
                include __DIR__ . '/../views/dashboard/admin_job_offers.php';
            } else {
                include __DIR__ . '/../views/dashboard/job_offers.php';
            }
        } catch (Exception $e) {
            $error = $e->getMessage();
            include __DIR__ . '/../views/error.php';
        }
    }
    
    /**
     * Handle job offer creation
     */
    public function create() {
        // Verify user is logged in
        if (!isset($_SESSION['user'])) {
            return ['success' => false, 'message' => 'Unauthorized access'];
        }

        $userEmail = $_SESSION['user']['email'];
        try {
            // Sanitize and validate inputs
            $title = trim($_POST['title'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $categoryId = $_POST['category_id'] ?? '';
            $salaryMin = isset($_POST['salary_min']) ? floatval($_POST['salary_min']) : null;
            $salaryMax = isset($_POST['salary_max']) ? floatval($_POST['salary_max']) : null;
            $locationId = $_POST['location_id'] ?? '';
            $imageUrl = trim($_POST['image_url'] ?? '');
            
            // Validate title
            if (empty($title)) {
                throw new Exception('Job title is required');
            }
            if (strlen($title) < 3) {
                throw new Exception('Job title must be at least 3 characters');
            }
            
            // Validate description
            if (empty($description)) {
                throw new Exception('Job description is required');
            }
            if (strlen($description) < 20) {
                throw new Exception('Description must be at least 20 characters');
            }
            
            // Validate category
            if (empty($categoryId)) {
                throw new Exception('Please select a category');
            }
            
            // Validate salaries
            if ($salaryMin === null || $salaryMin < 0) {
                throw new Exception('Please enter a valid minimum salary');
            }
            if ($salaryMax === null || $salaryMax < 0) {
                throw new Exception('Please enter a valid maximum salary');
            }
            if ($salaryMax <= $salaryMin) {
                throw new Exception('Maximum salary must be greater than minimum salary');
            }
            
            // Validate location
            if (empty($locationId)) {
                throw new Exception('Please select a location');
            }
            
            // Validate image URL if provided
            if (!empty($imageUrl) && !filter_var($imageUrl, FILTER_VALIDATE_URL)) {
                throw new Exception('Please enter a valid image URL');
            }
            
            // Update POST data with sanitized values
            $_POST['title'] = $title;
            $_POST['description'] = $description;
            $_POST['salary_min'] = $salaryMin;
            $_POST['salary_max'] = $salaryMax;
            $_POST['image_url'] = $imageUrl;
            
            $result = $this->jobOfferModel->createJobOffer($userEmail, $_POST);
            
            return [
                'success' => $result,
                'message' => $result ? 'Job offer created successfully!' : 'Failed to create job offer'
            ];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Handle job offer update
     */
    public function update($id) {
        // Verify user is logged in
        if (!isset($_SESSION['user'])) {
            return ['success' => false, 'message' => 'Unauthorized access'];
        }

        $userEmail = $_SESSION['user']['email'];
        try {
            if (empty($id)) {
                throw new Exception('Job offer ID is required');
            }
            
            // Sanitize and validate inputs
            $title = trim($_POST['title'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $categoryId = $_POST['category_id'] ?? '';
            $salaryMin = isset($_POST['salary_min']) ? floatval($_POST['salary_min']) : null;
            $salaryMax = isset($_POST['salary_max']) ? floatval($_POST['salary_max']) : null;
            $locationId = $_POST['location_id'] ?? '';
            $imageUrl = trim($_POST['image_url'] ?? '');
            
            // Validate title
            if (empty($title)) {
                throw new Exception('Job title is required');
            }
            if (strlen($title) < 3) {
                throw new Exception('Job title must be at least 3 characters');
            }
            
            // Validate description
            if (empty($description)) {
                throw new Exception('Job description is required');
            }
            if (strlen($description) < 20) {
                throw new Exception('Description must be at least 20 characters');
            }
            
            // Validate category
            if (empty($categoryId)) {
                throw new Exception('Please select a category');
            }
            
            // Validate salaries
            if ($salaryMin === null || $salaryMin < 0) {
                throw new Exception('Please enter a valid minimum salary');
            }
            if ($salaryMax === null || $salaryMax < 0) {
                throw new Exception('Please enter a valid maximum salary');
            }
            if ($salaryMax <= $salaryMin) {
                throw new Exception('Maximum salary must be greater than minimum salary');
            }
            
            // Validate location
            if (empty($locationId)) {
                throw new Exception('Please select a location');
            }
            
            // Validate image URL if provided
            if (!empty($imageUrl) && !filter_var($imageUrl, FILTER_VALIDATE_URL)) {
                throw new Exception('Please enter a valid image URL');
            }
            
            // Update POST data with sanitized values
            $_POST['title'] = $title;
            $_POST['description'] = $description;
            $_POST['salary_min'] = $salaryMin;
            $_POST['salary_max'] = $salaryMax;
            $_POST['image_url'] = $imageUrl;
            
            $result = $this->jobOfferModel->updateJobOffer($userEmail, $id, $_POST);
            
            return [
                'success' => $result,
                'message' => $result ? 'Job offer updated successfully!' : 'Failed to update job offer'
            ];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Handle job offer deletion
     */
    public function delete($id) {
        // Verify user is logged in
        if (!isset($_SESSION['user'])) {
            return ['success' => false, 'message' => 'Unauthorized access'];
        }

        $userEmail = $_SESSION['user']['email'];
        try {
            $result = $this->jobOfferModel->deleteJobOffer($userEmail, $id);
            return [
                'success' => $result,
                'message' => $result ? 'Job offer deleted successfully!' : 'Failed to delete job offer'
            ];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Get job offer details
     */
    public function getJobOffer($id) {
        // Verify user is logged in
        if (!isset($_SESSION['user'])) {
            return ['success' => false, 'message' => 'Unauthorized access'];
        }

        try {
            $jobOffer = $this->jobOfferModel->getJobOffer($id);
            if (!$jobOffer) {
                throw new Exception('Job offer not found');
            }
            return ['success' => true, 'data' => $jobOffer];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}