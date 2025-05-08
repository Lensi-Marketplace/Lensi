<?php
/**
 * Interview Model
 * Handles all database operations for interviews
 */
class InterviewModel {
    private $pdo;
    
    public function __construct() {
        require_once __DIR__ . '/../../../config/database.php';
        
        // Store the PDO connection
        $this->pdo = $GLOBALS['pdo'];
        
        // Verify database connection is working
        $this->checkDatabaseConnection();
    }
    
    /**
     * Check if database connection is working properly
     */
    private function checkDatabaseConnection() {
        try {
            // Try a simple query to check connection
            $this->pdo->query('SELECT 1');
        } catch (PDOException $e) {
            // Log the error
            error_log("Database connection error: " . $e->getMessage());
            error_log("Connection details: Host=" . $GLOBALS['db_host'] . ", Database=" . $GLOBALS['db_name'] . ", User=" . $GLOBALS['db_user']);
            throw new Exception("Failed to connect to database. Please check your configuration.");
        }
    }

    /**
     * Get all interviews for a specific user
     */
    public function getUserInterviews($userEmail) {
        $sql = "SELECT i.*, j.title as job_title, j.description as job_description,
                       j.salary_min, j.salary_max, jc.name as job_category,
                       l.city, l.country, l.is_remote, 
                       COALESCE(c.name, j.title) as company_name
                FROM interviews i
                LEFT JOIN job_offers j ON i.job_offer_id = j.job_id
                LEFT JOIN job_categories jc ON j.category_id = jc.category_id
                LEFT JOIN locations l ON j.location_id = l.location_id
                LEFT JOIN companies c ON j.company_id = c.company_id
                WHERE i.user_email = ?
                ORDER BY i.interview_date DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$userEmail]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Create a new interview
     */
    public function createInterview($data) {
        // Start transaction
        $this->pdo->beginTransaction();

        try {
            // We're no longer requiring the user to exist in the database
            // This allows non-logged-in users to schedule interviews

            // Validate interview date is valid
            $interviewDate = strtotime($data['interview_date']);
            if ($interviewDate === false) {
                throw new Exception("Invalid interview date format");
            }

            // Create the interview
            $sql = "INSERT INTO interviews (
                candidate_name, position_title, interview_date, interviewer, 
                location, status, feedback, cv_url, user_email, job_offer_id
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->pdo->prepare($sql);
            $result = $stmt->execute([
                $data['candidate_name'],
                $data['position_title'],
                $data['interview_date'],
                $data['interviewer'],
                $data['location'],
                $data['status'],
                $data['feedback'] ?? null,
                $data['cv_url'] ?? null,
                $data['user_email'],
                $data['job_offer_id'] ?? null
            ]);

            if (!$result) {
                throw new Exception("Failed to create interview record");
            }

            // If there's a job offer ID, update the job application status
            if (!empty($data['job_offer_id'])) {
                $sql = "INSERT INTO job_applications (job_id, user_email, status, created_at) 
                        VALUES (?, ?, 'interviewing', NOW())
                        ON DUPLICATE KEY UPDATE 
                        status = 'interviewing',
                        updated_at = NOW()";
                
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([
                    $data['job_offer_id'],
                    $data['user_email']
                ]);
            }

            // Commit the transaction
            $this->pdo->commit();
            return true;

        } catch (Exception $e) {
            // Rollback transaction on error
            $this->pdo->rollBack();
            throw new Exception("Database error: " . $e->getMessage());
        }
    }
    
    /**
     * Update an existing interview
     */
    public function updateInterview($id, $data) {
        // Start transaction
        $this->pdo->beginTransaction();

        try {
            // Log the attempt for debugging
            error_log("Attempting to update interview ID: $id");
            error_log("Update data: " . print_r($data, true));
            
            // First validate that the interview exists
            $stmt = $this->pdo->prepare("SELECT id, user_email FROM interviews WHERE id = ?");
            $stmt->execute([$id]);
            $interview = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$interview) {
                throw new Exception("Interview not found with ID: $id");
            }

            // Check if user is admin
            $isAdmin = $this->isAdmin($data['user_email']);
            
            // If not admin, check permission (must be the interview owner)
            if (!$isAdmin && $interview['user_email'] !== $data['user_email']) {
                error_log("Permission denied: User {$data['user_email']} tried to update interview owned by {$interview['user_email']}");
                throw new Exception("You don't have permission to update this interview");
            }

            // Validate interview date is valid
            $interviewDate = strtotime($data['interview_date']);
            if ($interviewDate === false) {
                throw new Exception("Invalid interview date format");
            }

            // Prepare the SQL query
            $sql = "UPDATE interviews SET 
                    candidate_name = ?,
                    position_title = ?,
                    interview_date = ?,
                    interviewer = ?,
                    location = ?,
                    status = ?,
                    feedback = ?,
                    cv_url = ?,
                    job_offer_id = ?,
                    updated_at = NOW()
                    WHERE id = ?";
            
            // Format parameters array
            $params = [
                $data['candidate_name'],
                $data['position_title'],
                $data['interview_date'],
                $data['interviewer'],
                $data['location'],
                $data['status'],
                $data['feedback'] ?? null,
                $data['cv_url'] ?? null,
                $data['job_offer_id'] ?? null,
                $id
            ];
            
            // If not admin, add user_email to WHERE clause for extra safety
            if (!$isAdmin) {
                $sql .= " AND user_email = ?";
                $params[] = $data['user_email'];
            }
            
            // Log SQL and params for debugging
            error_log("Update SQL: $sql");
            error_log("Update Params: " . print_r($params, true));
            
            // Prepare and execute the update
            $stmt = $this->pdo->prepare($sql);
            $result = $stmt->execute($params);
            
            // Check for PDO errors
            if (!$result) {
                $errorInfo = $stmt->errorInfo();
                error_log("PDO Error: " . print_r($errorInfo, true));
                throw new Exception("Database error: " . ($errorInfo[2] ?? 'Unknown error'));
            }
            
            // Check if any rows were affected
            if ($stmt->rowCount() === 0) {
                // This could happen if the data hasn't changed
                error_log("Interview update resulted in 0 affected rows. This might be OK if no data changed.");
            }

            // If there's a job offer ID, update the job application status
            if (!empty($data['job_offer_id'])) {
                $sql = "INSERT INTO job_applications (job_id, user_email, status, created_at) 
                        VALUES (?, ?, 'interviewing', NOW())
                        ON DUPLICATE KEY UPDATE 
                        status = 'interviewing',
                        updated_at = NOW()";
                
                $stmt = $this->pdo->prepare($sql);
                try {
                    $stmt->execute([
                        $data['job_offer_id'],
                        $data['user_email']
                    ]);
                } catch (Exception $e) {
                    // Log but don't fail if job application update fails
                    error_log("Failed to update job application status: " . $e->getMessage());
                }
            }

            // Commit the transaction
            $this->pdo->commit();
            error_log("Interview update successful for ID: $id");
            return true;

        } catch (Exception $e) {
            // Rollback transaction on error
            $this->pdo->rollBack();
            error_log("Error updating interview: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            throw new Exception($e->getMessage());
        }
    }
    
    /**
     * Delete an interview
     */
    public function deleteInterview($id, $userEmail) {
        $stmt = $this->pdo->prepare("DELETE FROM interviews WHERE id = ? AND user_email = ?");
        return $stmt->execute([$id, $userEmail]);
    }
    
    /**
     * Get a single interview by ID
     */
    public function getInterview($id, $userEmail) {
        $sql = "SELECT i.*, j.title as job_title, j.description as job_description,
                       j.salary_min, j.salary_max, jc.name as job_category,
                       l.city, l.country, l.is_remote
                FROM interviews i
                LEFT JOIN job_offers j ON i.job_offer_id = j.job_id
                LEFT JOIN job_categories jc ON j.category_id = jc.category_id
                LEFT JOIN locations l ON j.location_id = l.location_id
                WHERE i.id = ? AND (i.user_email = ? OR ? IN (
                    SELECT email FROM users WHERE user_type = 'admin'
                ))";
                
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id, $userEmail, $userEmail]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get all available job offers
     */
    public function getJobOffers() {
        $sql = "SELECT jo.*, jc.name as category_name, l.city, l.country, l.is_remote
                FROM job_offers jo
                LEFT JOIN job_categories jc ON jo.category_id = jc.category_id
                LEFT JOIN locations l ON jo.location_id = l.location_id
                ORDER BY jo.created_at DESC";
        
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get all interviews (admin only)
     */
    public function getAllInterviews($userEmail) {
        // First check if user is admin
        if (!$this->isAdmin($userEmail)) {
            throw new Exception("Unauthorized access");
        }

        $sql = "SELECT i.*, j.title as job_title, j.description as job_description,
                       j.salary_min, j.salary_max, jc.name as job_category,
                       l.city, l.country, l.is_remote,
                       CONCAT(u.first_name, ' ', u.last_name) as user_name,
                       COALESCE(c.name, j.title) as company_name
                FROM interviews i
                LEFT JOIN job_offers j ON i.job_offer_id = j.job_id
                LEFT JOIN job_categories jc ON j.category_id = jc.category_id
                LEFT JOIN locations l ON j.location_id = l.location_id
                LEFT JOIN users u ON i.user_email = u.email
                LEFT JOIN companies c ON j.company_id = c.company_id
                ORDER BY i.interview_date DESC";
        
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get interview statistics (admin only)
     */
    public function getInterviewStats($userEmail) {
        // First check if user is admin
        if (!$this->isAdmin($userEmail)) {
            throw new Exception("Unauthorized access");
        }

        $stats = [];
        
        // Total interviews
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM interviews");
        $stats['total'] = $stmt->fetchColumn();
        
        // Interviews by status
        $stmt = $this->pdo->query("SELECT status, COUNT(*) as count FROM interviews GROUP BY status");
        $stats['by_status'] = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        
        // Upcoming interviews (next 7 days)
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM interviews WHERE interview_date BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 7 DAY)");
        $stats['upcoming'] = $stmt->fetchColumn();
        
        return $stats;
    }

    /**
     * Check if a user is an admin
     */
    private function isAdmin($userEmail) {
        try {
            $stmt = $this->pdo->prepare("SELECT user_type FROM users WHERE email = ?");
            $stmt->execute([$userEmail]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Log for debugging
            error_log("Admin check for user $userEmail: " . print_r($user, true));
            
            return $user && $user['user_type'] === 'admin';
        } catch (Exception $e) {
            error_log("Error checking admin status: " . $e->getMessage());
            return false;
        }
    }

    private function validateUser($email) {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetchColumn() > 0;
    }
}