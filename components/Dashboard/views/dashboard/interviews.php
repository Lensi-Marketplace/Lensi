<?php
/**
 * Interviews Dashboard View
 * Displays interview management interface in the dashboard
 */

// Get user data
$user = $_SESSION['user'];
$userType = $user['user_type'] ?? 'freelancer';
?>

<div class="welcome-section">
    <h2 class="welcome-title">Interview Management</h2>
    <p class="welcome-subtitle">Schedule and manage your upcoming interviews</p>
</div>

<div class="interviews-dashboard-content">
    <?php include_once __DIR__ . '/../../../home/offers/interviews.php'; ?>
</div>

<style>
.interviews-dashboard-content {
    background: var(--light);
    border-radius: var(--radius-md);
    padding: 1.5rem;
    box-shadow: var(--shadow-sm);
}

[data-bs-theme="dark"] .interviews-dashboard-content {
    background: var(--accent-dark);
}

/* Override some styles for dashboard context */
.interviews-section {
    min-height: auto;
    padding: 0;
}

.interviews-container {
    padding: 0;
}

.interviews-header {
    margin-bottom: 2rem;
}

.interviews-title {
    font-size: 2rem;
}

@media (max-width: 768px) {
    .interviews-dashboard-content {
        padding: 1rem;
    }
    
    .interviews-grid {
        grid-template-columns: 1fr;
    }
}
</style>