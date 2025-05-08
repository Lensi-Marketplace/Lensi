<?php
/**
 * User Interviews View
 * Displays upcoming interviews for the current user
 */

// Get user data
$user = $_SESSION['user'];
$userType = $user['user_type'] ?? 'freelancer';
$userEmail = $user['email'];

// Initialize interview model
require_once __DIR__ . '/../../models/InterviewModel.php';
$interviewModel = new InterviewModel();

try {
    // Get user's interviews
    $interviews = $interviewModel->getUserInterviews($userEmail);
} catch (Exception $e) {
    $error = $e->getMessage();
}
?>

<div class="welcome-section d-flex justify-content-between align-items-center">
    <div>
        <h2 class="welcome-title">My Upcoming Interviews</h2>
        <p class="welcome-subtitle">View and manage your scheduled interviews</p>
    </div>
    <button id="openCalendarBtn" class="btn btn-primary">
        <i class="bi bi-calendar2-week me-2"></i>Open Calendar
    </button>
</div>

<!-- Interview Calendar Container -->
<div id="interviewCalendarContainer" class="interview-calendar-container">
    <div id="calendar"></div>
</div>

<!-- Interview Details Banner -->
<div id="interviewDetailsBanner" class="interview-details-banner">
    <div class="banner-header">
        <h3>Interview Details</h3>
        <button id="closeBannerBtn" class="btn-close"></button>
    </div>
    <div id="interviewBannerContent" class="banner-content">
        <!-- Content will be populated by JavaScript -->
    </div>
</div>

<?php if (isset($_SESSION['success_message'])): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <?php 
    echo htmlspecialchars($_SESSION['success_message']);
    unset($_SESSION['success_message']);
    ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<?php if (isset($_SESSION['error_message'])): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <?php 
    echo htmlspecialchars($_SESSION['error_message']);
    unset($_SESSION['error_message']);
    ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<?php if (isset($error)): ?>
<div class="alert alert-danger" role="alert">
    <?php echo htmlspecialchars($error); ?>
</div>
<?php else: ?>

<!-- Interviews Table -->
<section class="dashboard-table-section">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Position</th>
                    <th>Company</th>
                    <th>Date & Time</th>
                    <th>Location</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($interviews)): ?>
                <tr>
                    <td colspan="6" class="text-center py-4">
                        <i class="bi bi-calendar-x text-muted fs-2 d-block mb-2"></i>
                        <p class="text-muted mb-0">You don't have any upcoming interviews scheduled.</p>
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($interviews as $interview): ?>
                <tr>
                    <td><?php echo htmlspecialchars($interview['position_title']); ?></td>
                    <td><?php echo htmlspecialchars($interview['company_name'] ?? 'N/A'); ?></td>
                    <td><?php echo date('M j, Y g:i A', strtotime($interview['interview_date'])); ?></td>
                    <td><?php echo htmlspecialchars($interview['location']); ?></td>
                    <td>
                        <span class="status-badge <?php echo strtolower($interview['status']); ?>">
                            <?php echo $interview['status']; ?>
                        </span>
                    </td>
                    <td>
                        <button class="btn btn-sm btn-info me-1" onclick="showInterview(<?php echo $interview['id']; ?>)">
                            <i class="bi bi-eye"></i>
                        </button>
                        <button class="btn btn-sm btn-primary me-1" onclick="editInterview(<?php echo $interview['id']; ?>)">
                            <i class="bi bi-pencil"></i>
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<!-- Interview Details Modal -->
<div class="modal fade" id="interviewDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Interview Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="interview-details">
                    <!-- Details will be populated by JavaScript -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Interview Form Modal -->
<div class="modal fade" id="addInterviewModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Edit Interview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="interviewForm" method="POST" action="">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="id" value="">
                    
                    <div class="form-group mb-3">
                        <label class="form-label">Position Title</label>
                        <input type="text" class="form-control" name="position_title" required>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label class="form-label">Interview Date & Time</label>
                        <input type="datetime-local" class="form-control" name="interview_date" required>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label class="form-label">Interviewer</label>
                        <input type="text" class="form-control" name="interviewer" required>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label class="form-label">Location</label>
                        <input type="text" class="form-control" name="location" required>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status" required>
                            <option value="Scheduled">Scheduled</option>
                            <option value="Completed">Completed</option>
                            <option value="Cancelled">Cancelled</option>
                        </select>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label class="form-label">Candidate Name</label>
                        <input type="text" class="form-control" name="candidate_name" required>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label class="form-label">Feedback</label>
                        <textarea class="form-control" name="feedback" rows="3"></textarea>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label class="form-label">CV URL (optional)</label>
                        <input type="url" class="form-control" name="cv_url">
                    </div>
                    
                    <div class="text-end mt-4">
                        <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="submitBtn">Update Interview</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- FullCalendar Library -->
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.0/main.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.0/main.min.js"></script>

<!-- Interview Calendar Script -->
<script src="/web/components/Dashboard/views/dashboard/interview-calendar.js"></script>

<script>
// Store interviews data for calendar use
window.interviewsData = <?php echo json_encode($interviews); ?>;

// Show interview details
function showInterview(id) {
    fetch(`/web/components/home/offers/get_interview.php?id=${id}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Failed to fetch interview data');
            }
            return response.json();
        })
        .then(data => {
            const details = document.querySelector('.interview-details');
            details.innerHTML = `
                <div class="mb-3">
                    <strong>Position:</strong> ${data.position_title}
                    ${data.job_title ? `<br><small class="text-muted">For: ${data.job_title}</small>` : ''}
                </div>
                <div class="mb-3">
                    <strong>Date & Time:</strong> ${new Date(data.interview_date).toLocaleString()}
                </div>
                <div class="mb-3">
                    <strong>Location:</strong> ${data.location}
                </div>
                <div class="mb-3">
                    <strong>Interviewer:</strong> ${data.interviewer}
                </div>
                <div class="mb-3">
                    <strong>Status:</strong>
                    <span class="status-badge ${data.status.toLowerCase()}">${data.status}</span>
                </div>
                ${data.feedback ? `
                <div class="mb-3">
                    <strong>Feedback:</strong><br>
                    ${data.feedback}
                </div>` : ''}
                ${data.cv_url ? `
                <div class="mb-3">
                    <strong>CV:</strong><br>
                    <a href="${data.cv_url}" target="_blank">View CV</a>
                </div>` : ''}
            `;
            
            const modal = new bootstrap.Modal(document.getElementById('interviewDetailsModal'));
            modal.show();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to load interview details. Please try again.');
        });
}

// Edit interview functionality
function editInterview(id) {
    fetch(`/web/components/home/offers/get_interview.php?id=${id}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Failed to fetch interview data');
            }
            return response.json();
        })
        .then(data => {
            const form = document.getElementById('interviewForm');
            // Update form action and title
            form.elements['action'].value = 'update';
            form.elements['id'].value = data.id;
            
            // Update modal title to clearly indicate editing mode
            document.getElementById('modalTitle').textContent = 'Edit Interview';
            document.getElementById('submitBtn').textContent = 'Update Interview';
            
            // Populate all form fields
            form.elements['candidate_name'].value = data.candidate_name;
            form.elements['position_title'].value = data.position_title;
            // Fix date formatting for the datetime-local input
            const date = new Date(data.interview_date);
            const formattedDate = date.toISOString().slice(0, 16);
            form.elements['interview_date'].value = formattedDate;
            form.elements['interviewer'].value = data.interviewer;
            form.elements['location'].value = data.location;
            form.elements['status'].value = data.status;
            form.elements['feedback'].value = data.feedback || '';
            form.elements['cv_url'].value = data.cv_url || '';
            
            // Show the modal
            const modal = new bootstrap.Modal(document.getElementById('addInterviewModal'));
            // Ensure the modal title is set correctly before showing
            document.getElementById('modalTitle').textContent = 'Edit Interview';
            modal.show();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to load interview data. Please try again.');
        });
}

// Make editInterview function available globally
window.editInterview = editInterview;

// Form submission handling
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('interviewForm');
    if (form) {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            try {
                const formData = new FormData(this);
                const response = await fetch('/web/components/home/offers/interviews.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();
                
                // Get the modal body where the form is located
                const modalBody = form.closest('.modal-body');
                
                // Remove any existing alerts
                const existingAlert = modalBody.querySelector('.alert');
                if (existingAlert) {
                    existingAlert.remove();
                }
                
                // Create alert element
                const alertDiv = document.createElement('div');
                alertDiv.className = `alert alert-${result.success ? 'success' : 'danger'} alert-dismissible fade show`;
                alertDiv.innerHTML = `
                    ${result.message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `;
                
                // Insert alert at the beginning of the modal body
                modalBody.insertBefore(alertDiv, form);
                
                if (result.success) {
                    // Reload the page after a short delay on success
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
            }
        });
    }
});
</script>

<style>
/* Existing styles */
.status-badge {
    padding: 0.35rem 0.65rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 500;
}

.status-badge.scheduled {
    background-color: rgba(255, 153, 0, 0.1);
    color: #ff9900;
}

.status-badge.completed {
    background-color: rgba(25, 135, 84, 0.1);
    color: #198754;
}

.status-badge.cancelled {
    background-color: rgba(220, 53, 69, 0.1);
    color: #dc3545;
}

.interview-details {
    padding: 1rem;
}

/* Calendar Styles */
.interview-calendar-container {
    display: none;
    height: 0;
    overflow: hidden;
    transition: height 0.3s ease, opacity 0.3s ease;
    opacity: 0;
    margin-bottom: 1.5rem;
    background: var(--light);
    border-radius: var(--radius-md);
    box-shadow: var(--shadow-sm);
}

[data-bs-theme="dark"] .interview-calendar-container {
    background: var(--accent-dark);
}

.interview-calendar-container.calendar-visible {
    display: block;
    height: 650px;
    opacity: 1;
    padding: 1.5rem;
}

.interview-calendar-container.with-banner {
    margin-right: 350px;
    transition: margin-right 0.3s ease;
}

#calendar {
    height: 100%;
}

/* Interview Details Banner */
.interview-details-banner {
    position: fixed;
    top: calc(var(--topbar-height) + 1.5rem);
    right: -350px;
    width: 350px;
    height: calc(100vh - var(--topbar-height) - 3rem);
    background: var(--light);
    box-shadow: var(--shadow-md);
    border-radius: var(--radius-md) 0 0 var(--radius-md);
    transition: right 0.3s ease;
    z-index: 1000;
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

[data-bs-theme="dark"] .interview-details-banner {
    background: var(--accent-dark);
}

.interview-details-banner.banner-visible {
    right: 0;
}

.banner-header {
    padding: 1rem 1.5rem;
    border-bottom: 1px solid rgba(0,0,0,0.1);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

[data-bs-theme="dark"] .banner-header {
    border-bottom-color: rgba(255,255,255,0.1);
}

.banner-content {
    padding: 1.5rem;
    overflow-y: auto;
    flex: 1;
}

.no-interviews-message {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 2rem 0;
}

/* Interview Cards in Banner */
.interviews-carousel {
    display: flex;
    overflow-x: auto;
    padding-bottom: 1rem;
    gap: 1rem;
    scrollbar-width: thin;
}

.interview-card {
    min-width: 280px;
    background: var(--light-gray);
    border-radius: var(--radius-sm);
    padding: 1rem;
    box-shadow: var(--shadow-sm);
}

[data-bs-theme="dark"] .interview-card {
    background: rgba(0,0,0,0.2);
}

.interview-card-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1rem;
}

.interview-card-body p {
    margin-bottom: 0.5rem;
}

/* Responsive adjustments */
@media (max-width: 992px) {
    .interview-calendar-container.with-banner {
        margin-right: 0;
    }
    
    .interview-details-banner {
        width: 100%;
        right: -100%;
    }
}
</style>

<?php endif; ?>