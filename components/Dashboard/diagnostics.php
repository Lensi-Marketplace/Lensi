<?php
/**
 * Database Diagnostics Script
 * This script helps troubleshoot database connection issues
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

// Include database configuration
require_once __DIR__ . '/../../config/database.php';

// Security measure - only show redacted database credentials
$redactedDbUser = substr($GLOBALS['db_user'], 0, 2) . '***' . substr($GLOBALS['db_user'], -2);
$redactedDbPass = '********';
$redactedDbName = substr($GLOBALS['db_name'], 0, 2) . '***' . substr($GLOBALS['db_name'], -2);

// Function to test database connection
function testDatabaseConnection() {
    global $pdo;
    
    try {
        // Test basic connectivity
        $pdo->query('SELECT 1');
        
        // Test interviews table
        $stmt = $pdo->query("SHOW TABLES LIKE 'interviews'");
        $interviewsTableExists = $stmt->rowCount() > 0;
        
        if ($interviewsTableExists) {
            // Check interviews table structure
            $stmt = $pdo->query("DESCRIBE interviews");
            $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
        }
        
        return [
            'success' => true,
            'message' => 'Database connection successful',
            'interviews_table_exists' => $interviewsTableExists,
            'columns' => $interviewsTableExists ? $columns : []
        ];
    } catch (PDOException $e) {
        error_log("Diagnostic DB Error: " . $e->getMessage());
        return [
            'success' => false,
            'message' => $e->getMessage(),
            'error_code' => $e->getCode()
        ];
    }
}

// Run the diagnostics
$diagnosticResult = testDatabaseConnection();

// Log the result
error_log("Database diagnostics run at " . date('Y-m-d H:i:s'));
error_log("Diagnostics result: " . ($diagnosticResult['success'] ? 'Success' : 'Failed'));
if (!$diagnosticResult['success']) {
    error_log("Error details: " . $diagnosticResult['message']);
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Diagnostics</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container py-5">
        <h1>Database Diagnostics</h1>
        <div class="alert alert-info mb-4">
            This tool helps troubleshoot database connection issues for the Interview Management System.
        </div>
        
        <div class="card mb-4">
            <div class="card-header">
                <h2 class="h5 mb-0">Database Configuration</h2>
            </div>
            <div class="card-body">
                <dl class="row">
                    <dt class="col-sm-3">Database Host:</dt>
                    <dd class="col-sm-9"><?php echo htmlspecialchars($GLOBALS['db_host']); ?></dd>
                    
                    <dt class="col-sm-3">Database Name:</dt>
                    <dd class="col-sm-9"><?php echo htmlspecialchars($redactedDbName); ?> (redacted)</dd>
                    
                    <dt class="col-sm-3">Database User:</dt>
                    <dd class="col-sm-9"><?php echo htmlspecialchars($redactedDbUser); ?> (redacted)</dd>
                    
                    <dt class="col-sm-3">PHP Version:</dt>
                    <dd class="col-sm-9"><?php echo htmlspecialchars(PHP_VERSION); ?></dd>
                    
                    <dt class="col-sm-3">PDO Drivers:</dt>
                    <dd class="col-sm-9"><?php echo htmlspecialchars(implode(', ', PDO::getAvailableDrivers())); ?></dd>
                </dl>
            </div>
        </div>
        
        <div class="card mb-4">
            <div class="card-header">
                <h2 class="h5 mb-0">Connection Test Results</h2>
            </div>
            <div class="card-body">
                <?php if ($diagnosticResult['success']): ?>
                <div class="alert alert-success">
                    <strong>Success!</strong> <?php echo htmlspecialchars($diagnosticResult['message']); ?>
                </div>
                
                <h3 class="h6 mt-4">Interviews Table:</h3>
                <?php if ($diagnosticResult['interviews_table_exists']): ?>
                <div class="alert alert-success">
                    <strong>Table Found!</strong> The interviews table exists in the database.
                </div>
                
                <h3 class="h6 mt-4">Columns:</h3>
                <ul class="list-group">
                    <?php foreach ($diagnosticResult['columns'] as $column): ?>
                    <li class="list-group-item"><?php echo htmlspecialchars($column); ?></li>
                    <?php endforeach; ?>
                </ul>
                <?php else: ?>
                <div class="alert alert-danger">
                    <strong>Table Missing!</strong> The interviews table does not exist in the database.
                </div>
                <?php endif; ?>
                
                <?php else: ?>
                <div class="alert alert-danger">
                    <strong>Connection Failed!</strong> <?php echo htmlspecialchars($diagnosticResult['message']); ?>
                </div>
                <div class="mt-3">
                    <strong>Error Code:</strong> <?php echo htmlspecialchars($diagnosticResult['error_code']); ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <a href="/web/components/Dashboard/index.php?page=interviews" class="btn btn-primary">Return to Interviews</a>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 