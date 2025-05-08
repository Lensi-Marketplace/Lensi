<?php
/**
 * Interview Update Test Script
 * This script helps diagnose issues with the update interview functionality
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in and is admin
if (!isset($_SESSION['user']) || $_SESSION['user']['user_type'] !== 'admin') {
    echo "Unauthorized access. This tool is for administrators only.";
    exit;
}

// Set headers to prevent caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Include required files
require_once __DIR__ . '/controllers/InterviewController.php';
require_once __DIR__ . '/models/InterviewModel.php';

// Get user email from session
$userEmail = $_SESSION['user']['email'];

// Initialize the controller
$interviewController = new InterviewController();
$interviewModel = new InterviewModel();

// Function to run a test update
function testInterviewUpdate($id, $data) {
    global $interviewController;
    
    try {
        // Create a copy of the original $_POST
        $originalPost = $_POST;
        
        // Set up the test data
        $_POST = $data;
        
        // Attempt the update
        $result = $interviewController->update($id);
        
        // Restore original $_POST
        $_POST = $originalPost;
        
        return [
            'success' => true,
            'result' => $result
        ];
    } catch (Exception $e) {
        // Restore original $_POST
        $_POST = $originalPost;
        
        return [
            'success' => false,
            'error' => $e->getMessage(),
            'stack' => $e->getTraceAsString()
        ];
    }
}

// Get available interviews for testing
try {
    $interviews = $interviewModel->getUserInterviews($userEmail);
} catch (Exception $e) {
    $interviews = [];
    $error = $e->getMessage();
}

// Handle test submission
$testResult = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['test_id'])) {
    $testId = (int)$_POST['test_id'];
    
    // Create test data
    $testData = [
        'candidate_name' => $_POST['candidate_name'],
        'position_title' => $_POST['position_title'],
        'interview_date' => $_POST['interview_date'],
        'interviewer' => $_POST['interviewer'],
        'location' => $_POST['location'],
        'status' => $_POST['status'],
        'feedback' => $_POST['feedback'],
        'cv_url' => $_POST['cv_url'],
        'job_offer_id' => $_POST['job_offer_id'],
        'user_email' => $userEmail,
        'action' => 'update'
    ];
    
    // Run the test
    $testResult = testInterviewUpdate($testId, $testData);
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Interview Update Test</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container py-5">
        <h1>Interview Update Test Tool</h1>
        
        <div class="alert alert-info mb-4">
            This tool allows you to test the interview update functionality in isolation.
        </div>
        
        <?php if (isset($error)): ?>
        <div class="alert alert-danger">
            <strong>Error:</strong> <?php echo htmlspecialchars($error); ?>
        </div>
        <?php endif; ?>
        
        <?php if ($testResult): ?>
        <div class="card mb-4">
            <div class="card-header">
                <h2 class="h5 mb-0">Test Results</h2>
            </div>
            <div class="card-body">
                <?php if ($testResult['success']): ?>
                <div class="alert alert-<?php echo $testResult['result']['success'] ? 'success' : 'warning'; ?>">
                    <strong><?php echo $testResult['result']['success'] ? 'Success!' : 'Warning!'; ?></strong> 
                    <?php echo htmlspecialchars($testResult['result']['message']); ?>
                </div>
                <?php else: ?>
                <div class="alert alert-danger">
                    <strong>Error:</strong> <?php echo htmlspecialchars($testResult['error']); ?>
                </div>
                <h3 class="h6 mt-3">Stack Trace:</h3>
                <pre class="bg-light p-3"><?php echo htmlspecialchars($testResult['stack']); ?></pre>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <div class="card">
            <div class="card-header">
                <h2 class="h5 mb-0">Run Interview Update Test</h2>
            </div>
            <div class="card-body">
                <?php if (empty($interviews)): ?>
                <div class="alert alert-warning">
                    No interviews found for testing. Create an interview first.
                </div>
                <?php else: ?>
                <form method="POST" action="">
                    <div class="mb-3">
                        <label class="form-label">Select Interview to Test</label>
                        <select name="test_id" class="form-select" required>
                            <option value="">-- Select an interview --</option>
                            <?php foreach ($interviews as $interview): ?>
                            <option value="<?php echo $interview['id']; ?>">
                                <?php echo htmlspecialchars($interview['position_title'] . ' - ' . 
                                    date('M j, Y g:i A', strtotime($interview['interview_date']))); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Candidate Name</label>
                        <input type="text" class="form-control" name="candidate_name" value="Test Candidate" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Position Title</label>
                        <input type="text" class="form-control" name="position_title" value="Test Position" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Interview Date & Time</label>
                        <input type="datetime-local" class="form-control" name="interview_date" 
                               value="<?php echo date('Y-m-d\TH:i', strtotime('+1 day')); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Interviewer</label>
                        <input type="text" class="form-control" name="interviewer" value="Test Interviewer" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Location</label>
                        <input type="text" class="form-control" name="location" value="Test Location" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status" required>
                            <option value="Scheduled" selected>Scheduled</option>
                            <option value="Completed">Completed</option>
                            <option value="Cancelled">Cancelled</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Feedback</label>
                        <textarea class="form-control" name="feedback" rows="3">Test feedback for the interview</textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">CV URL (optional)</label>
                        <input type="url" class="form-control" name="cv_url" value="">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Job Offer ID (optional)</label>
                        <input type="text" class="form-control" name="job_offer_id" value="">
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <button type="submit" class="btn btn-primary">Run Test</button>
                        <a href="/web/components/Dashboard/index.php?page=interviews" class="btn btn-secondary">Back to Interviews</a>
                    </div>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 