<?php
/**
 * Job Offer Model
 * Handles all database operations for job offers
 */
class JobOfferModel {
    private $pdo;
    
    public function __construct() {
        require_once __DIR__ . '/../../../config/database.php';
        $this->pdo = $GLOBALS['pdo'];
    }

    /**
     * Check if user is admin
     */
    private function isAdmin($email) {
        $stmt = $this->pdo->prepare("SELECT user_type FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result && $result['user_type'] === 'admin';
    }

    /**
     * Get all job offers with related data (admin view)
     */
    public function getAllJobOffers($userEmail) {
        if (!$this->isAdmin($userEmail)) {
            throw new Exception("Unauthorized access");
        }

        $sql = "SELECT jo.*, jc.name as category_name, l.city, l.country, l.is_remote,
                       (SELECT COUNT(*) FROM job_applications WHERE job_id = jo.job_id) as applicant_count
                FROM job_offers jo
                LEFT JOIN job_categories jc ON jo.category_id = jc.category_id
                LEFT JOIN locations l ON jo.location_id = l.location_id
                ORDER BY jo.created_at DESC";
        
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get job offer statistics (admin only)
     */
    public function getJobStats($userEmail) {
        if (!$this->isAdmin($userEmail)) {
            throw new Exception("Unauthorized access");
        }

        $stats = [];
        
        // Total job offers
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM job_offers");
        $stats['total'] = $stmt->fetchColumn();
        
        // Jobs by category
        $stmt = $this->pdo->query("SELECT jc.name, COUNT(*) as count 
                                  FROM job_offers jo 
                                  JOIN job_categories jc ON jo.category_id = jc.category_id 
                                  GROUP BY jc.category_id");
        $stats['by_category'] = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        
        // Total applications
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM job_applications");
        $stats['total_applications'] = $stmt->fetchColumn();
        
        // Applications by status
        $stmt = $this->pdo->query("SELECT status, COUNT(*) as count FROM job_applications GROUP BY status");
        $stats['applications_by_status'] = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        
        return $stats;
    }

    /**
     * Create a new job offer (admin only)
     */
    public function createJobOffer($userEmail, $data) {
        if (!$this->isAdmin($userEmail)) {
            throw new Exception("Unauthorized access");
        }

        // Validate required fields
        if (empty($data['title']) || empty($data['description']) || 
            empty($data['category_id']) || !isset($data['salary_min']) || 
            !isset($data['salary_max']) || empty($data['location_id'])) {
            throw new Exception("All required fields must be filled");
        }

        // Validate salary
        if ($data['salary_max'] <= $data['salary_min']) {
            throw new Exception("Maximum salary must be greater than minimum salary");
        }

        try {
            $sql = "INSERT INTO job_offers (
                title, description, category_id, salary_min, salary_max, 
                location_id, image_url, slug, created_at, updated_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)";
            
            $stmt = $this->pdo->prepare($sql);
            $result = $stmt->execute([
                $data['title'],
                $data['description'],
                $data['category_id'],
                $data['salary_min'],
                $data['salary_max'],
                $data['location_id'],
                $data['image_url'] ?? null,
                $this->generateSlug($data['title'])
            ]);

            if (!$result) {
                throw new Exception("Failed to insert job offer into database");
            }

            return true;
        } catch (PDOException $e) {
            throw new Exception("Database error while creating job offer: " . $e->getMessage());
        }
    }

    /**
     * Update an existing job offer (admin only)
     */
    public function updateJobOffer($userEmail, $jobId, $data) {
        if (!$this->isAdmin($userEmail)) {
            throw new Exception("Unauthorized access");
        }

        // Validate job exists
        $stmt = $this->pdo->prepare("SELECT job_id FROM job_offers WHERE job_id = ?");
        $stmt->execute([$jobId]);
        if (!$stmt->fetch()) {
            throw new Exception("Job offer not found");
        }
        
        // Validate required fields
        if (empty($data['title']) || empty($data['description']) || 
            empty($data['category_id']) || !isset($data['salary_min']) || 
            !isset($data['salary_max']) || empty($data['location_id'])) {
            throw new Exception("All required fields must be filled");
        }

        // Validate salary
        if ($data['salary_max'] <= $data['salary_min']) {
            throw new Exception("Maximum salary must be greater than minimum salary");
        }

        try {
            $sql = "UPDATE job_offers SET 
                    title = :title,
                    description = :description,
                    category_id = :category_id,
                    salary_min = :salary_min,
                    salary_max = :salary_max,
                    location_id = :location_id,
                    image_url = :image_url,
                    slug = :slug,
                    updated_at = CURRENT_TIMESTAMP
                    WHERE job_id = :job_id";
            
            $stmt = $this->pdo->prepare($sql);
            $result = $stmt->execute([
                ':title' => $data['title'],
                ':description' => $data['description'],
                ':category_id' => $data['category_id'],
                ':salary_min' => $data['salary_min'],
                ':salary_max' => $data['salary_max'],
                ':location_id' => $data['location_id'],
                ':image_url' => $data['image_url'] ?? null,
                ':slug' => $this->generateSlug($data['title']),
                ':job_id' => $jobId
            ]);

            if (!$result) {
                throw new Exception("Failed to update job offer in database");
            }

            return true;
        } catch (PDOException $e) {
            throw new Exception("Database error while updating job offer: " . $e->getMessage());
        }
    }

    /**
     * Delete a job offer (admin only)
     */
    public function deleteJobOffer($userEmail, $jobId) {
        if (!$this->isAdmin($userEmail)) {
            throw new Exception("Unauthorized access");
        }

        $stmt = $this->pdo->prepare("DELETE FROM job_offers WHERE job_id = ?");
        return $stmt->execute([$jobId]);
    }

    /**
     * Get a single job offer by ID
     */
    public function getJobOffer($jobId) {
        $sql = "SELECT jo.*, jc.name as category_name, l.city, l.country, l.is_remote,
                       (SELECT COUNT(*) FROM job_applications WHERE job_id = jo.job_id) as applicant_count
                FROM job_offers jo
                LEFT JOIN job_categories jc ON jo.category_id = jc.category_id
                LEFT JOIN locations l ON jo.location_id = l.location_id
                WHERE jo.job_id = ?";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$jobId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get all job categories
     */
    public function getCategories() {
        $stmt = $this->pdo->query("SELECT * FROM job_categories ORDER BY name");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get all locations
     */
    public function getLocations() {
        $stmt = $this->pdo->query("SELECT * FROM locations ORDER BY city");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Generate URL-friendly slug from title
     */
    private function generateSlug($title) {
        // Convert the title to lowercase and replace spaces with hyphens
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
        // Remove any extra hyphens
        $slug = trim($slug, '-');
        return $slug;
    }
}