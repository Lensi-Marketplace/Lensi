<?php
/**
 * Admin Interviews Dashboard View
 * Displays all interviews and their statistics
 */

// Get user data
$user = $_SESSION['user'];
$userType = $user['user_type'] ?? 'freelancer';

// Initialize interview model
require_once __DIR__ . '/../../models/InterviewModel.php';
$interviewModel = new InterviewModel();

try {
    // Get all interviews (admin only)
    $interviews = $interviewModel->getAllInterviews($user['email']);
    
    // Get interview statistics
    $stats = $interviewModel->getInterviewStats($user['email']);
    
    // Get all job offers for the interview form
    $jobOffers = $interviewModel->getJobOffers();
} catch (Exception $e) {
    $error = $e->getMessage();
}
?>

<div class="welcome-section">
    <h2 class="welcome-title">Interview Management</h2>
    <p class="welcome-subtitle">Administrator view of all interviews</p>
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

<!-- Statistics Section -->
<div class="dashboard-stats">
    <div class="stat-card">
        <div class="stat-icon blue">
            <i class="bi bi-calendar2-week"></i>
        </div>
        <div class="stat-title">Total Interviews</div>
        <div class="stat-value"><?php echo $stats['total']; ?></div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon green">
            <i class="bi bi-calendar-check"></i>
        </div>
        <div class="stat-title">Completed</div>
        <div class="stat-value"><?php echo $stats['by_status']['Completed'] ?? 0; ?></div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon orange">
            <i class="bi bi-calendar-date"></i>
        </div>
        <div class="stat-title">Scheduled</div>
        <div class="stat-value"><?php echo $stats['by_status']['Scheduled'] ?? 0; ?></div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon purple">
            <i class="bi bi-calendar-range"></i>
        </div>
        <div class="stat-title">Next 7 Days</div>
        <div class="stat-value"><?php echo $stats['upcoming']; ?></div>
    </div>
</div>

<!-- Interviews Table -->
<section class="dashboard-table-section">
    <div class="dashboard-table-header d-flex justify-content-between align-items-center">
        <h3 class="dashboard-table-title">All Interviews</h3>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addInterviewModal">
            <i class="bi bi-plus-circle me-2"></i>Schedule New Interview
        </button>
    </div>
    
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Candidate</th>
                    <th>Position</th>
                    <th>Date & Time</th>
                    <th>Interviewer</th>
                    <th>Location</th>
                    <th>User</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($interviews as $interview): ?>
                <tr>
                    <td><?php echo htmlspecialchars($interview['candidate_name']); ?></td>
                    <td><?php echo htmlspecialchars($interview['position_title']); ?></td>
                    <td><?php echo date('M j, Y g:i A', strtotime($interview['interview_date'])); ?></td>
                    <td><?php echo htmlspecialchars($interview['interviewer']); ?></td>
                    <td><?php echo htmlspecialchars($interview['location']); ?></td>
                    <td><?php echo htmlspecialchars($interview['user_name']); ?></td>
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
                        <button class="btn btn-sm btn-danger" onclick="deleteInterview(<?php echo $interview['id']; ?>)">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
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
                <h5 class="modal-title" id="modalTitle">Schedule Interview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="interviewForm" method="POST" action="" novalidate>
                    <input type="hidden" name="action" value="create">
                    <input type="hidden" name="id" value="">
                    
                    <div class="form-group mb-3">
                        <label class="form-label">Job Offer</label>
                        <select class="form-select" name="job_offer_id" id="job_offer_id">
                            <option value="">-- Select a job offer --</option>
                            <?php foreach ($jobOffers as $job): ?>
                            <option value="<?php echo $job['job_id']; ?>">
                                <?php echo htmlspecialchars($job['title']); ?> 
                                (<?php echo $job['is_remote'] ? 'Remote' : htmlspecialchars($job['city'] . ', ' . $job['country']); ?>)
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback"></div>
                        <small class="form-text text-muted">Optional: Link this interview to a specific job offer</small>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label class="form-label">Candidate Name</label>
                        <input type="text" class="form-control" name="candidate_name" required>
                        <div class="invalid-feedback"></div>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label class="form-label">Position Title</label>
                        <input type="text" class="form-control" name="position_title" required>
                        <div class="invalid-feedback"></div>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label class="form-label">Interview Date & Time</label>
                        <input type="datetime-local" class="form-control" name="interview_date" required>
                        <div class="invalid-feedback"></div>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label class="form-label">Interviewer</label>
                        <input type="text" class="form-control" name="interviewer" required>
                        <div class="invalid-feedback"></div>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label class="form-label">Location</label>
                        <input type="text" class="form-control" name="location" required>
                        <div class="invalid-feedback"></div>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status" required>
                            <option value="Scheduled">Scheduled</option>
                            <option value="Completed">Completed</option>
                            <option value="Cancelled">Cancelled</option>
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label class="form-label">Feedback</label>
                        <textarea class="form-control" name="feedback" rows="3"></textarea>
                        <div class="invalid-feedback"></div>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label class="form-label">CV URL (optional)</label>
                        <input type="url" class="form-control" name="cv_url">
                        <div class="invalid-feedback"></div>
                        <small class="form-text text-muted">Enter a valid URL for the candidate's CV (e.g., https://example.com/cv.pdf)</small>
                    </div>
                    
                    <div class="text-end mt-4">
                        <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="submitBtn">Schedule Interview</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Helper function to add error message to form field
function addErrorMessage(element, message) {
    element.classList.add('is-invalid');
    element.classList.remove('is-valid');
    element.style.borderColor = '#dc3545'; // Red border color
    
    // Remove any existing success icon if present
    const existingSuccessIcon = element.parentNode.querySelector('.valid-feedback-icon');
    if (existingSuccessIcon) {
        existingSuccessIcon.remove();
    }
    
    // Remove any existing error messages to prevent duplicates
    const existingFeedbacks = element.parentNode.querySelectorAll('.invalid-feedback');
    existingFeedbacks.forEach(feedback => {
        feedback.textContent = '';
    });
    
    // Add error icon (exclamation mark)
    const iconSpan = document.createElement('span');
    iconSpan.className = 'invalid-feedback-icon';
    iconSpan.innerHTML = '<i class="bi bi-exclamation-circle-fill text-danger"></i>';
    iconSpan.style.position = 'absolute';
    iconSpan.style.right = '2rem';
    iconSpan.style.top = '50%';
    iconSpan.style.transform = 'translateY(-50%)';
    iconSpan.style.pointerEvents = 'none';
    
    // Only add the icon if it doesn't already exist
    if (!element.parentNode.querySelector('.invalid-feedback-icon')) {
        element.parentNode.style.position = 'relative';
        element.parentNode.appendChild(iconSpan);
    }
    
    // Set the error message
    const feedback = element.parentNode.querySelector('.invalid-feedback');
    if (feedback) {
        feedback.textContent = message;
    }
}

// Helper function to add success message to form field
function addSuccessMessage(element) {
    element.classList.remove('is-invalid');
    element.classList.add('is-valid');
    element.style.borderColor = '#198754'; // Green border color
    
    // Remove any existing error icon if present
    const existingErrorIcon = element.parentNode.querySelector('.invalid-feedback-icon');
    if (existingErrorIcon) {
        existingErrorIcon.remove();
    }
    
    // Clear any error messages
    const feedback = element.parentNode.querySelector('.invalid-feedback');
    if (feedback) {
        feedback.textContent = '';
    }
    
    // Add success icon (checkmark)
    const iconSpan = document.createElement('span');
    iconSpan.className = 'valid-feedback-icon';
    iconSpan.innerHTML = '<i class="bi bi-check-circle-fill text-success"></i>';
    iconSpan.style.position = 'absolute';
    iconSpan.style.right = '2rem';
    iconSpan.style.top = '50%';
    iconSpan.style.transform = 'translateY(-50%)';
    iconSpan.style.pointerEvents = 'none';
    
    // Only add the icon if it doesn't already exist
    if (!element.parentNode.querySelector('.valid-feedback-icon')) {
        element.parentNode.style.position = 'relative';
        element.parentNode.appendChild(iconSpan);
    }
}

// Helper function to validate URL
function isValidUrl(url) {
    try {
        new URL(url);
        return true;
    } catch (e) {
        return false;
    }
}

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
                    <strong>Candidate:</strong> ${data.candidate_name}
                </div>
                <div class="mb-3">
                    <strong>Date & Time:</strong> ${new Date(data.interview_date).toLocaleString()}
                </div>
                <div class="mb-3">
                    <strong>Interviewer:</strong> ${data.interviewer}
                </div>
                <div class="mb-3">
                    <strong>Location:</strong> ${data.location}
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
            console.error('Error fetching interview details:', error);
            alert('Failed to load interview details');
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
            document.getElementById('modalTitle').textContent = 'Edit Interview';
            document.getElementById('submitBtn').textContent = 'Update Interview';
            
            // Populate all form fields
            form.elements['job_offer_id'].value = data.job_offer_id || '';
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
            modal.show();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to load interview data. Please try again.');
        });
}

// Delete interview
function deleteInterview(id) {
    if (confirm('Are you sure you want to delete this interview?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="${id}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

// Add real-time validation for form fields
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('interviewForm');
    if (form) {
        // Candidate Name validation
        form.elements['candidate_name'].addEventListener('input', function() {
            const value = this.value.trim();
            if (value && value.length >= 3) {
                addSuccessMessage(this);
            } else if (value.length > 0) {
                addErrorMessage(this, 'Name must be at least 3 characters');
            } else {
                addErrorMessage(this, 'Candidate name is required');
            }
        });
        
        // Position Title validation
        form.elements['position_title'].addEventListener('input', function() {
            const value = this.value.trim();
            if (value && value.length >= 3) {
                addSuccessMessage(this);
            } else if (value.length > 0) {
                addErrorMessage(this, 'Position title must be at least 3 characters');
            } else {
                addErrorMessage(this, 'Position title is required');
            }
        });
        
        // Interview Date validation
        form.elements['interview_date'].addEventListener('input', function() {
            const value = this.value.trim();
            if (value) {
                const selectedDate = new Date(value);
                const now = new Date();
                
                if (selectedDate < now) {
                    addErrorMessage(this, 'Interview date cannot be in the past');
                } else {
                    addSuccessMessage(this);
                }
            } else {
                addErrorMessage(this, 'Interview date is required');
            }
        });
        
        // Interviewer validation
        form.elements['interviewer'].addEventListener('input', function() {
            const value = this.value.trim();
            if (value && value.length >= 3) {
                addSuccessMessage(this);
            } else if (value.length > 0) {
                addErrorMessage(this, 'Interviewer name must be at least 3 characters');
            } else {
                addErrorMessage(this, 'Interviewer name is required');
            }
        });
        
        // Location validation
        form.elements['location'].addEventListener('input', function() {
            const value = this.value.trim();
            if (value && value.length >= 3) {
                addSuccessMessage(this);
            } else if (value.length > 0) {
                addErrorMessage(this, 'Location must be at least 3 characters');
            } else {
                addErrorMessage(this, 'Location is required');
            }
        });
        
        // Status validation
        form.elements['status'].addEventListener('change', function() {
            if (this.value) {
                addSuccessMessage(this);
            } else {
                addErrorMessage(this, 'Please select a status');
            }
        });
        
        // Feedback validation (optional field)
        form.elements['feedback'].addEventListener('input', function() {
            const value = this.value.trim();
            if (value && value.length < 10 && value.length > 0) {
                addErrorMessage(this, 'Feedback should be at least 10 characters');
            } else if (value && value.length >= 10) {
                addSuccessMessage(this);
            } else {
                // Remove validation for empty optional field
                this.classList.remove('is-invalid', 'is-valid');
                this.style.borderColor = ''; // Reset border color
                const errorIcon = this.parentNode.querySelector('.invalid-feedback-icon');
                if (errorIcon) errorIcon.remove();
                const successIcon = this.parentNode.querySelector('.valid-feedback-icon');
                if (successIcon) successIcon.remove();
                const feedback = this.parentNode.querySelector('.invalid-feedback');
                if (feedback) feedback.textContent = '';
            }
        });
        
        // CV URL validation (optional field)
        form.elements['cv_url'].addEventListener('input', function() {
            const value = this.value.trim();
            if (value && !isValidUrl(value)) {
                addErrorMessage(this, 'Please enter a valid URL');
            } else if (value) {
                addSuccessMessage(this);
            } else {
                // Remove validation for empty optional field
                this.classList.remove('is-invalid', 'is-valid');
                this.style.borderColor = ''; // Reset border color
                const errorIcon = this.parentNode.querySelector('.invalid-feedback-icon');
                if (errorIcon) errorIcon.remove();
                const successIcon = this.parentNode.querySelector('.valid-feedback-icon');
                if (successIcon) successIcon.remove();
                const feedback = this.parentNode.querySelector('.invalid-feedback');
                if (feedback) feedback.textContent = '';
            }
        });
        
        // Form submission handling
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            // Validate all fields before submission
            let isValid = true;
            
            // Candidate Name validation
            const candidateName = form.elements['candidate_name'].value.trim();
            if (!candidateName) {
                isValid = false;
                addErrorMessage(form.elements['candidate_name'], 'Candidate name is required');
            } else if (candidateName.length < 3) {
                isValid = false;
                addErrorMessage(form.elements['candidate_name'], 'Name must be at least 3 characters');
            } else {
                addSuccessMessage(form.elements['candidate_name']);
            }
            
            // Position Title validation
            const positionTitle = form.elements['position_title'].value.trim();
            if (!positionTitle) {
                isValid = false;
                addErrorMessage(form.elements['position_title'], 'Position title is required');
            } else if (positionTitle.length < 3) {
                isValid = false;
                addErrorMessage(form.elements['position_title'], 'Position title must be at least 3 characters');
            } else {
                addSuccessMessage(form.elements['position_title']);
            }
            
            // Interview Date validation
            const interviewDate = form.elements['interview_date'].value.trim();
            if (!interviewDate) {
                isValid = false;
                addErrorMessage(form.elements['interview_date'], 'Interview date is required');
            } else {
                const selectedDate = new Date(interviewDate);
                const now = new Date();
                
                if (selectedDate < now) {
                    isValid = false;
                    addErrorMessage(form.elements['interview_date'], 'Interview date cannot be in the past');
                } else {
                    addSuccessMessage(form.elements['interview_date']);
                }
            }
            
            // Interviewer validation
            const interviewer = form.elements['interviewer'].value.trim();
            if (!interviewer) {
                isValid = false;
                addErrorMessage(form.elements['interviewer'], 'Interviewer name is required');
            } else if (interviewer.length < 3) {
                isValid = false;
                addErrorMessage(form.elements['interviewer'], 'Interviewer name must be at least 3 characters');
            } else {
                addSuccessMessage(form.elements['interviewer']);
            }
            
            // Location validation
            const location = form.elements['location'].value.trim();
            if (!location) {
                isValid = false;
                addErrorMessage(form.elements['location'], 'Location is required');
            } else if (location.length < 3) {
                isValid = false;
                addErrorMessage(form.elements['location'], 'Location must be at least 3 characters');
            } else {
                addSuccessMessage(form.elements['location']);
            }
            
            // Status validation
            const status = form.elements['status'].value;
            if (!status) {
                isValid = false;
                addErrorMessage(form.elements['status'], 'Please select a status');
            } else {
                addSuccessMessage(form.elements['status']);
            }
            
            // CV URL validation (optional)
            const cvUrl = form.elements['cv_url'].value.trim();
            if (cvUrl && !isValidUrl(cvUrl)) {
                isValid = false;
                addErrorMessage(form.elements['cv_url'], 'Please enter a valid URL');
            } else if (cvUrl) {
                addSuccessMessage(form.elements['cv_url']);
            }
            
            if (!isValid) {
                return false;
            }
            
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
    
    // Handle job offer selection
    const jobOfferSelect = document.getElementById('job_offer_id');
    const positionTitleInput = document.querySelector('input[name="position_title"]');
    
    if (jobOfferSelect && positionTitleInput) {
        jobOfferSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            if (selectedOption.value) {
                // Auto-fill position title with job offer title
                positionTitleInput.value = selectedOption.text.split('(')[0].trim();
                // Trigger input event to validate the field
                const event = new Event('input', { bubbles: true });
                positionTitleInput.dispatchEvent(event);
            }
        });
    }
});

// Reset form when opening modal for new interview
document.getElementById('addInterviewModal').addEventListener('show.bs.modal', function (event) {
    const button = event.relatedTarget;
    
    // Only reset if it's not being opened programmatically by the edit function
    if (button) {
        const form = document.getElementById('interviewForm');
        form.reset();
        form.classList.remove('was-validated');
        form.elements['action'].value = 'create';
        form.elements['id'].value = '';
        document.getElementById('modalTitle').textContent = 'Schedule Interview';
        document.getElementById('submitBtn').textContent = 'Schedule Interview';
        
        // Clear all validation states
        form.querySelectorAll('.is-invalid, .is-valid').forEach(el => {
            el.classList.remove('is-invalid', 'is-valid');
            el.style.borderColor = ''; // Reset border color to default
        });
        
        // Remove all feedback icons
        form.querySelectorAll('.invalid-feedback-icon, .valid-feedback-icon').forEach(el => {
            el.remove();
        });
        
        // Clear all feedback messages
        form.querySelectorAll('.invalid-feedback').forEach(el => {
            el.textContent = '';
        });
    }
});

// Initialize validation icons when modal is opened for editing
document.getElementById('addInterviewModal').addEventListener('shown.bs.modal', function (event) {
    const form = document.getElementById('interviewForm');
    
    // If this is an edit (id has a value), validate all fields
    if (form.elements['id'].value) {
        // Validate all required fields
        const requiredFields = ['candidate_name', 'position_title', 'interview_date', 'interviewer', 'location', 'status'];
        
        requiredFields.forEach(fieldName => {
            const field = form.elements[fieldName];
            if (field && field.value.trim()) {
                addSuccessMessage(field);
            }
        });
        
        // Validate optional fields if they have values
        const optionalFields = ['feedback', 'cv_url'];
        
        optionalFields.forEach(fieldName => {
            const field = form.elements[fieldName];
            if (field && field.value.trim()) {
                // For CV URL, validate the URL format
                if (fieldName === 'cv_url') {
                    if (isValidUrl(field.value.trim())) {
                        addSuccessMessage(field);
                    } else {
                        addErrorMessage(field, 'Please enter a valid URL');
                    }
                } 
                // For feedback, check minimum length
                else if (fieldName === 'feedback') {
                    if (field.value.trim().length >= 10) {
                        addSuccessMessage(field);
                    } else {
                        addErrorMessage(field, 'Feedback should be at least 10 characters');
                    }
                }
            }
        });
    }
});

// Initialize validation icons when modal is opened for editing
document.getElementById('addInterviewModal').addEventListener('shown.bs.modal', function (event) {
    const form = document.getElementById('interviewForm');
    
    // If this is an edit (id has a value), validate all fields
    if (form.elements['id'].value) {
        // Validate all required fields
        const requiredFields = ['candidate_name', 'position_title', 'interview_date', 'interviewer', 'location', 'status'];
        
        requiredFields.forEach(fieldName => {
            const field = form.elements[fieldName];
            if (field && field.value.trim()) {
                addSuccessMessage(field);
            }
        });
        
        // Validate optional fields if they have values
        const optionalFields = ['feedback', 'cv_url'];
        
        optionalFields.forEach(fieldName => {
            const field = form.elements[fieldName];
            if (field && field.value.trim()) {
                // For CV URL, validate the URL format
                if (fieldName === 'cv_url') {
                    if (isValidUrl(field.value.trim())) {
                        addSuccessMessage(field);
                    } else {
                        addErrorMessage(field, 'Please enter a valid URL');
                    }
                } 
                // For feedback, check minimum length
                else if (fieldName === 'feedback') {
                    if (field.value.trim().length >= 10) {
                        addSuccessMessage(field);
                    } else {
                        addErrorMessage(field, 'Feedback should be at least 10 characters');
                    }
                }
            }
        });
    }
});

// Initialize validation icons when modal is opened for editing
document.getElementById('addInterviewModal').addEventListener('shown.bs.modal', function (event) {
    const form = document.getElementById('interviewForm');
    
    // If this is an edit (id has a value), validate all fields
    if (form.elements['id'].value) {
        // Validate all required fields
        const requiredFields = ['candidate_name', 'position_title', 'interview_date', 'interviewer', 'location', 'status'];
        
        requiredFields.forEach(fieldName => {
            const field = form.elements[fieldName];
            if (field && field.value.trim()) {
                addSuccessMessage(field);
            }
        });
        
        // Validate optional fields if they have values
        const optionalFields = ['feedback', 'cv_url'];
        
        optionalFields.forEach(fieldName => {
            const field = form.elements[fieldName];
            if (field && field.value.trim()) {
                // For CV URL, validate the URL format
                if (fieldName === 'cv_url') {
                    if (isValidUrl(field.value.trim())) {
                        addSuccessMessage(field);
                    } else {
                        addErrorMessage(field, 'Please enter a valid URL');
                    }
                } 
                // For feedback, check minimum length
                else if (fieldName === 'feedback') {
                    if (field.value.trim().length >= 10) {
                        addSuccessMessage(field);
                    } else {
                        addErrorMessage(field, 'Feedback should be at least 10 characters');
                    }
                }
            }
        });
    }
});

</script>

<style>
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
</style>

<?php endif; ?>