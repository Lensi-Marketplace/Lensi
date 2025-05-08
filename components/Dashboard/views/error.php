<?php
/**
 * Error View
 * Displays error messages in a consistent format
 */
?>

<div class="alert alert-danger" role="alert">
    <div class="d-flex align-items-center">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        <strong>Error:</strong>
        <span class="ms-2"><?php echo htmlspecialchars($error); ?></span>
    </div>
</div>