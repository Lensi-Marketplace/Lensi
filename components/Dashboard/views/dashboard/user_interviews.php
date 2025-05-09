<?php
/**
 * User Interviews View
 * Displays upcoming interviews for the current user
 * Following strict MVC architecture - contains only display logic
 */

// All data is now received from the controller:
// $interviews - All user interviews
// $nextInterview - The next upcoming interview (closest future interview)
// $error - Any error message to display
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

<!-- Next Interview Countdown Timer -->
<div class="countdown-timer-container mb-4">
    <div class="row">
        <div class="col-md-12">
            <div class="countdown-card">
                <div class="countdown-header">
                    <h5><i class="bi bi-clock me-2"></i>Next Interview</h5>
                </div>
                <div id="nextInterviewContainer" class="countdown-content">
                    <?php if ($nextInterview): ?>
                    <!-- Next interview data -->
                    <div class="interview-info mb-3">
                        <h6><?php echo htmlspecialchars($nextInterview['position_title']); ?></h6>
                        <p class="mb-1">
                            <i class="bi bi-building me-1"></i>
                            <?php echo htmlspecialchars($nextInterview['company_name'] ?? 'N/A'); ?>
                        </p>
                        <p class="mb-1">
                            <i class="bi bi-calendar-date me-1"></i>
                            <?php echo date('F j, Y', strtotime($nextInterview['interview_date'])); ?>
                        </p>
                        <p class="mb-1">
                            <i class="bi bi-clock me-1"></i>
                            <?php echo date('g:i A', strtotime($nextInterview['interview_date'])); ?>
                        </p>
                        <p class="mb-1">
                            <i class="bi bi-geo-alt me-1"></i>
                            <?php echo htmlspecialchars($nextInterview['location']); ?>
                        </p>
                    </div>
                    <!-- Countdown will be populated by JavaScript -->
                    <div class="countdown-timer" id="countdown-display">
                        <div class="countdown-item">
                            <span class="countdown-value" id="countdown-days">-</span>
                            <span class="countdown-label">Days</span>
                        </div>
                        <div class="countdown-item">
                            <span class="countdown-value" id="countdown-hours">-</span>
                            <span class="countdown-label">Hours</span>
                        </div>
                        <div class="countdown-item">
                            <span class="countdown-value" id="countdown-minutes">-</span>
                            <span class="countdown-label">Minutes</span>
                        </div>
                        <div class="countdown-item">
                            <span class="countdown-value" id="countdown-seconds">-</span>
                            <span class="countdown-label">Seconds</span>
                        </div>
                    </div>
                    <div class="countdown-actions mt-3">
                        <button class="btn btn-sm btn-primary interview-action" data-action="edit" data-id="<?php echo $nextInterview['id']; ?>">
                            <i class="bi bi-pencil me-1"></i>Edit
                        </button>
                        <button class="btn btn-sm btn-info interview-action" data-action="details" data-id="<?php echo $nextInterview['id']; ?>">
                            <i class="bi bi-eye me-1"></i>Details
                        </button>
                    </div>
                    <!-- Store next interview date for JS -->
                    <input type="hidden" id="next-interview-date" value="<?php echo $nextInterview['interview_date']; ?>">
                    <input type="hidden" id="next-interview-id" value="<?php echo $nextInterview['id']; ?>">
                    <?php else: ?>
                    <!-- No upcoming interviews -->
                    <div class="text-center py-3">
                        <i class="bi bi-calendar-x text-muted fs-2 d-block mb-2"></i>
                        <p class="text-muted mb-0">You have no upcoming interviews.</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Interviews Filters -->
<div class="interview-filters mb-4">
    <div class="filter-buttons">
        <button class="btn btn-filter active" data-filter="all">All</button>
        <button class="btn btn-filter" data-filter="scheduled">Scheduled</button>
        <button class="btn btn-filter" data-filter="completed">Completed</button>
        <button class="btn btn-filter" data-filter="cancelled">Cancelled</button>
        <button class="btn btn-sort ms-auto" id="sortByDate">
            <i class="bi bi-sort-down me-1"></i>By Date
        </button>
    </div>
</div>

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
            <tbody id="interviews-table-body">
                <?php if (empty($interviews)): ?>
                <tr>
                    <td colspan="6" class="text-center py-4">
                        <i class="bi bi-calendar-x text-muted fs-2 d-block mb-2"></i>
                        <p class="text-muted mb-0">You don't have any interviews scheduled.</p>
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($interviews as $interview): ?>
                <tr class="interview-row" data-status="<?php echo strtolower($interview['status']); ?>">
                    <td><?php echo htmlspecialchars($interview['position_title']); ?></td>
                    <td><?php echo htmlspecialchars($interview['company_name'] ?? 'N/A'); ?></td>
                    <td data-timestamp="<?php echo strtotime($interview['interview_date']); ?>">
                        <?php echo date('M j, Y g:i A', strtotime($interview['interview_date'])); ?>
                    </td>
                    <td><?php echo htmlspecialchars($interview['location']); ?></td>
                    <td>
                        <span class="status-badge <?php echo strtolower($interview['status']); ?>">
                            <?php echo $interview['status']; ?>
                        </span>
                    </td>
                    <td>
                        <button class="btn btn-sm btn-info me-1 interview-action" data-action="details" data-id="<?php echo $interview['id']; ?>">
                            <i class="bi bi-eye"></i>
                        </button>
                        <button class="btn btn-sm btn-primary me-1 interview-action" data-action="edit" data-id="<?php echo $interview['id']; ?>">
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
                <!-- Form validation messages will be displayed here -->
                <div id="form-validation-messages"></div>
                
                <form id="interviewForm" method="POST" action="">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="id" value="">
                    <input type="hidden" name="user_email" value="<?php echo htmlspecialchars($userEmail); ?>">
                    
                    <div class="form-group mb-3">
                        <label class="form-label">Position Title</label>
                        <input type="text" class="form-control" name="position_title" data-validation="required">
                        <div class="invalid-feedback">Please enter a position title.</div>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label class="form-label">Interview Date & Time</label>
                        <input type="datetime-local" class="form-control" name="interview_date" data-validation="required">
                        <div class="invalid-feedback">Please select a valid interview date and time.</div>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label class="form-label">Interviewer</label>
                        <input type="text" class="form-control" name="interviewer" data-validation="required">
                        <div class="invalid-feedback">Please enter an interviewer name.</div>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label class="form-label">Location</label>
                        <input type="text" class="form-control" name="location" data-validation="required">
                        <div class="invalid-feedback">Please enter an interview location.</div>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status" data-validation="required">
                            <option value="">Select a status</option>
                            <option value="Scheduled">Scheduled</option>
                            <option value="Completed">Completed</option>
                            <option value="Cancelled">Cancelled</option>
                        </select>
                        <div class="invalid-feedback">Please select a status.</div>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label class="form-label">Candidate Name</label>
                        <input type="text" class="form-control" name="candidate_name" data-validation="required">
                        <div class="invalid-feedback">Please enter the candidate name.</div>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label class="form-label">Feedback</label>
                        <textarea class="form-control" name="feedback" rows="3"></textarea>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label class="form-label">CV URL (optional)</label>
                        <input type="url" class="form-control" name="cv_url" data-validation="url">
                        <div class="invalid-feedback">Please enter a valid URL.</div>
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
/**
 * Interview Management JavaScript
 * Structured in namespaced modules following clean code principles
 */

// Store interviews data for global access
window.interviewsData = <?php echo json_encode($interviews); ?>;

// Global variables
let activeFilter = 'all';
let sortDirection = 'asc';
let countdownInterval = null;

/**
 * Document ready handler
 */
document.addEventListener('DOMContentLoaded', function() {
    // Initialize all components
    initializeFilters();
    initializeCountdown();
    initializeFormValidation();
    initializeEventHandlers();
});

/**
 * Initialize filter and sort functionality
 */
function initializeFilters() {
    const filterButtons = document.querySelectorAll('.btn-filter');
    const sortButton = document.getElementById('sortByDate');
    const tableBody = document.querySelector('table tbody');
    
    if (!tableBody || !filterButtons.length) return;
    
    // Add click handlers to filter buttons
    filterButtons.forEach(button => {
        button.addEventListener('click', () => {
            const filter = button.dataset.filter;
            applyFilter(filter, filterButtons, tableBody);
        });
    });
    
    // Add click handler to sort button
    if (sortButton) {
        sortButton.addEventListener('click', () => {
            sortByDate(sortButton, tableBody);
        });
    }
}

/**
 * Apply filter to interview list
 */
function applyFilter(filter, filterButtons, tableBody) {
    // Update active filter
    activeFilter = filter;
    
    // Update button states
    filterButtons.forEach(btn => {
        btn.classList.toggle('active', btn.dataset.filter === filter);
    });
    
    // Apply filter to rows
    const rows = tableBody.querySelectorAll('.interview-row');
    let visibleCount = 0;
    
    rows.forEach(row => {
        const status = row.dataset.status;
        const isVisible = filter === 'all' || status === filter.toLowerCase();
        row.style.display = isVisible ? '' : 'none';
        if (isVisible) visibleCount++;
    });
    
    // Show empty state if no rows match filter
    updateEmptyState(tableBody, visibleCount);
}

/**
 * Update empty state message when no interviews match criteria
 */
function updateEmptyState(tableBody, visibleCount) {
    const emptyStateRow = tableBody.querySelector('.empty-state-row');
    
    // If no rows are visible and there isn't already an empty state row
    if (visibleCount === 0 && !emptyStateRow) {
        const newEmptyRow = document.createElement('tr');
        newEmptyRow.className = 'empty-state-row';
        newEmptyRow.innerHTML = `
            <td colspan="6" class="text-center py-4">
                <i class="bi bi-filter-circle text-muted fs-2 d-block mb-2"></i>
                <p class="text-muted mb-0">No interviews match the selected filter.</p>
            </td>
        `;
        tableBody.appendChild(newEmptyRow);
    } 
    // If there are visible rows and there's an empty state row, remove it
    else if (visibleCount > 0 && emptyStateRow) {
        emptyStateRow.remove();
    }
}

/**
 * Sort table by date
 */
function sortByDate(sortButton, tableBody) {
    // Toggle sort direction
    sortDirection = sortDirection === 'asc' ? 'desc' : 'asc';
    
    // Update button icon
    const icon = sortButton.querySelector('i');
    if (icon) {
        icon.className = sortDirection === 'asc' ? 
            'bi bi-sort-down me-1' : 'bi bi-sort-up me-1';
    }
    
    // Toggle active class
    sortButton.classList.toggle('active');
    
    // Get all rows (excluding empty state row)
    const rows = Array.from(tableBody.querySelectorAll('.interview-row'));
    if (rows.length === 0) return;
    
    // Sort rows by timestamp
    rows.sort((a, b) => {
        const dateColA = a.querySelector('td[data-timestamp]');
        const dateColB = b.querySelector('td[data-timestamp]');
        
        if (!dateColA || !dateColB) return 0;
        
        const timestampA = parseInt(dateColA.dataset.timestamp);
        const timestampB = parseInt(dateColB.dataset.timestamp);
        
        return sortDirection === 'asc' 
            ? timestampA - timestampB 
            : timestampB - timestampA;
    });
    
    // Remove all rows
    rows.forEach(row => row.remove());
    
    // Append rows in sorted order
    rows.forEach(row => tableBody.appendChild(row));
}

/**
 * Initialize countdown timer
 */
function initializeCountdown() {
    const nextInterviewDateInput = document.getElementById('next-interview-date');
    const nextInterviewId = document.getElementById('next-interview-id');
    const countdownDisplay = document.getElementById('countdown-display');
    
    // If no next interview date or no countdown display, exit
    if (!nextInterviewDateInput || !countdownDisplay) return;
    
    const interviewDate = new Date(nextInterviewDateInput.value);
    const now = new Date();
    
    // Calculate the time difference in milliseconds
    const timeDiff = interviewDate - now;
    
    // Calculate days, hours, minutes, seconds
    const days = Math.floor(timeDiff / (1000 * 60 * 60 * 24));
    const hours = Math.floor((timeDiff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
    const minutes = Math.floor((timeDiff % (1000 * 60 * 60)) / (1000 * 60));
    const seconds = Math.floor((timeDiff % (1000 * 60)) / 1000);
    
    // Update the countdown values
    document.getElementById('countdown-days').textContent = days;
    document.getElementById('countdown-hours').textContent = hours;
    document.getElementById('countdown-minutes').textContent = minutes;
    document.getElementById('countdown-seconds').textContent = seconds;
    
    // Update countdown every second
    if (countdownInterval) {
        clearInterval(countdownInterval);
    }
    
    countdownInterval = setInterval(() => {
        // Get current time and calculate new difference
        const currentTime = new Date();
        const newTimeDiff = interviewDate - currentTime;
        
        // If the interview time has passed, clear interval and update message
        if (newTimeDiff <= 0) {
            clearInterval(countdownInterval);
            const nextInterviewContainer = document.getElementById('nextInterviewContainer');
            if (nextInterviewContainer) {
                const interviewTitle = nextInterviewContainer.querySelector('.interview-info h6')?.textContent || 'Interview';
                nextInterviewContainer.innerHTML = `
                    <div class="interview-info mb-3">
                        <h6>${interviewTitle}</h6>
                        <p class="mb-0 text-success">
                            <i class="bi bi-check-circle me-1"></i>
                            It's time for your interview!
                        </p>
                    </div>
                    <button class="btn btn-sm btn-primary interview-action" data-action="edit" data-id="${nextInterviewId.value}">
                        <i class="bi bi-pencil me-1"></i>Update Status
                    </button>
                `;
            }
            return;
        }
        
        // Calculate new days, hours, minutes, seconds
        const newDays = Math.floor(newTimeDiff / (1000 * 60 * 60 * 24));
        const newHours = Math.floor((newTimeDiff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const newMinutes = Math.floor((newTimeDiff % (1000 * 60 * 60)) / (1000 * 60));
        const newSeconds = Math.floor((newTimeDiff % (1000 * 60)) / 1000);
        
        // Update the countdown values
        document.getElementById('countdown-days').textContent = newDays;
        document.getElementById('countdown-hours').textContent = newHours;
        document.getElementById('countdown-minutes').textContent = newMinutes;
        document.getElementById('countdown-seconds').textContent = newSeconds;
    }, 1000);
}

/**
 * Initialize form validation
 */
function initializeFormValidation() {
    const form = document.getElementById('interviewForm');
    if (!form) return;
    
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        // Clear previous validation errors
        clearValidationErrors(form);
        
        // Validate form
        if (!validateForm(form)) {
            return;
        }
        
        try {
            // Disable submit button and show loading state
            const submitBtn = form.querySelector('#submitBtn');
            const originalBtnText = submitBtn.textContent;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';
            submitBtn.disabled = true;
            
            // Submit the form
            const formData = new FormData(this);
            const response = await fetch('/web/components/home/offers/interviews.php', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            // Show result message
            const messagesContainer = document.getElementById('form-validation-messages');
            messagesContainer.innerHTML = `
                <div class="alert alert-${result.success ? 'success' : 'danger'} alert-dismissible fade show" role="alert">
                    ${result.message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            `;
            
            // On success, reload page after delay
            if (result.success) {
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                // Reset button state
                submitBtn.innerHTML = originalBtnText;
                submitBtn.disabled = false;
            }
        } catch (error) {
            console.error('Error:', error);
            const messagesContainer = document.getElementById('form-validation-messages');
            messagesContainer.innerHTML = `
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    An unexpected error occurred. Please try again.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            `;
            
            // Reset button
            const submitBtn = form.querySelector('#submitBtn');
            submitBtn.innerHTML = 'Update Interview';
            submitBtn.disabled = false;
        }
    });
}

/**
 * Validate form inputs
 */
function validateForm(form) {
    let isValid = true;
    
    // Get all inputs with validation requirements
    const inputs = form.querySelectorAll('[data-validation]');
    
    // Check each input
    inputs.forEach(input => {
        const validationType = input.dataset.validation;
        let fieldIsValid = true;
        
        // Required fields check
        if (validationType === 'required' && !input.value.trim()) {
            fieldIsValid = false;
        }
        
        // URL validation
        if (validationType === 'url' && input.value.trim()) {
            try {
                new URL(input.value);
            } catch (e) {
                fieldIsValid = false;
            }
        }
        
        // Date validation
        if (validationType === 'required' && input.type === 'datetime-local') {
            const date = new Date(input.value);
            if (isNaN(date.getTime())) {
                fieldIsValid = false;
            }
        }
        
        // Mark invalid fields
        if (!fieldIsValid) {
            input.classList.add('is-invalid');
            isValid = false;
        } else {
            input.classList.remove('is-invalid');
        }
    });
    
    return isValid;
}

/**
 * Clear validation errors
 */
function clearValidationErrors(form) {
    // Clear any previous error messages
    const messagesContainer = document.getElementById('form-validation-messages');
    if (messagesContainer) {
        messagesContainer.innerHTML = '';
    }
    
    // Remove invalid class from all inputs
    const inputs = form.querySelectorAll('.form-control, .form-select');
    inputs.forEach(input => {
        input.classList.remove('is-invalid');
    });
}

/**
 * Initialize event handlers for buttons and interactions
 */
function initializeEventHandlers() {
    // Handle interview actions (view/edit)
    document.addEventListener('click', (e) => {
        // Check if the clicked element is an action button
        const actionButton = e.target.closest('.interview-action');
        if (!actionButton) return;
        
        // Get action type and interview ID
        const action = actionButton.dataset.action;
        const interviewId = actionButton.dataset.id;
        
        if (!action || !interviewId) return;
        
        // Execute the appropriate action
        switch (action) {
            case 'details':
                showInterviewDetails(interviewId);
                break;
            case 'edit':
                editInterview(interviewId);
                break;
        }
    });
    
    // Calendar toggle
    const calendarBtn = document.getElementById('openCalendarBtn');
    const calendarContainer = document.getElementById('interviewCalendarContainer');
    
    if (calendarBtn && calendarContainer) {
        calendarBtn.addEventListener('click', () => {
            calendarContainer.classList.toggle('calendar-visible');
            calendarBtn.innerHTML = calendarContainer.classList.contains('calendar-visible') ? 
                '<i class="bi bi-x-lg me-2"></i>Close Calendar' : 
                '<i class="bi bi-calendar2-week me-2"></i>Open Calendar';
        });
    }
}

/**
 * Show interview details in modal
 */
function showInterviewDetails(id) {
    // Get modal and show loader
    const modal = document.getElementById('interviewDetailsModal');
    const detailsContainer = modal?.querySelector('.interview-details');
    
    if (!modal || !detailsContainer) return;
    
    // Show loading indicator
    detailsContainer.innerHTML = `
        <div class="text-center py-3">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    `;
    
    // Show the modal
    const modalInstance = new bootstrap.Modal(modal);
    modalInstance.show();
    
    // Fetch interview details
    fetch(`/web/components/home/offers/get_interview.php?id=${id}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Failed to fetch interview data');
            }
            return response.json();
        })
        .then(data => {
            // Format date accounting for timezone
            const interviewDate = new Date(data.interview_date);
            
            // Format the details
            detailsContainer.innerHTML = `
                <div class="mb-3">
                    <strong>Position:</strong> ${data.position_title}
                    ${data.job_title ? `<br><small class="text-muted">For: ${data.job_title}</small>` : ''}
                </div>
                <div class="mb-3">
                    <strong>Date & Time:</strong> ${interviewDate.toLocaleString()}
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
                    <a href="${data.cv_url}" target="_blank" rel="noopener noreferrer">View CV</a>
                </div>` : ''}
            `;
        })
        .catch(error => {
            console.error('Error:', error);
            detailsContainer.innerHTML = `
                <div class="alert alert-danger">
                    Failed to load interview details. Please try again.
                </div>
            `;
        });
}

/**
 * Populate and show edit interview form
 */
function editInterview(id) {
    // Get modal and form
    const modal = document.getElementById('addInterviewModal');
    const form = document.getElementById('interviewForm');
    
    if (!modal || !form) return;
    
    // Show loader in form container
    const formContainer = form.parentElement;
    form.style.display = 'none';
    
    formContainer.insertAdjacentHTML('afterbegin', `
        <div class="text-center py-3" id="form-loader">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    `);
    
    // Show the modal
    const modalInstance = new bootstrap.Modal(modal);
    modalInstance.show();
    
    // Fetch interview data
    fetch(`/web/components/home/offers/get_interview.php?id=${id}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Failed to fetch interview data');
            }
            return response.json();
        })
        .then(data => {
            // Remove loader
            document.getElementById('form-loader')?.remove();
            
            // Update form action and ID
            form.elements['action'].value = 'update';
            form.elements['id'].value = data.id;
            
            // Update modal title
            document.getElementById('modalTitle').textContent = 'Edit Interview';
            document.getElementById('submitBtn').textContent = 'Update Interview';
            
            // Populate form fields
            form.elements['candidate_name'].value = data.candidate_name || '';
            form.elements['position_title'].value = data.position_title || '';
            
            // Format date for datetime-local input
            const date = new Date(data.interview_date);
            const formattedDate = date.toISOString().slice(0, 16);
            form.elements['interview_date'].value = formattedDate;
            
            form.elements['interviewer'].value = data.interviewer || '';
            form.elements['location'].value = data.location || '';
            form.elements['status'].value = data.status || 'Scheduled';
            form.elements['feedback'].value = data.feedback || '';
            form.elements['cv_url'].value = data.cv_url || '';
            
            // Show the form
            form.style.display = 'block';
        })
        .catch(error => {
            console.error('Error:', error);
            formContainer.innerHTML = `
                <div class="alert alert-danger">
                    Failed to load interview data. Please try again.
                </div>
                <div class="text-center mt-3">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            `;
        });
}
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

/* Filter Styles */
.interview-filters {
    background-color: var(--light);
    border-radius: var(--radius-md);
    padding: 1rem;
    box-shadow: var(--shadow-sm);
}

[data-bs-theme="dark"] .interview-filters {
    background-color: var(--accent-dark);
}

.filter-buttons {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.btn-filter {
    border-radius: 20px;
    padding: 0.5rem 1rem;
    font-size: 0.875rem;
    font-weight: 500;
    background-color: var(--bs-gray-200);
    color: var(--bs-gray-700);
    border: none;
    transition: all 0.2s ease;
}

[data-bs-theme="dark"] .btn-filter {
    background-color: var(--bs-gray-700);
    color: var(--bs-gray-200);
}

.btn-filter:hover {
    background-color: var(--bs-primary-bg-subtle);
    color: var(--bs-primary);
}

.btn-filter.active {
    background-color: var(--bs-primary);
    color: white;
}

.btn-sort {
    border-radius: 20px;
    padding: 0.5rem 1rem;
    font-size: 0.875rem;
    font-weight: 500;
    background-color: var(--bs-gray-200);
    color: var(--bs-gray-700);
    border: none;
    margin-left: auto;
    display: flex;
    align-items: center;
}

[data-bs-theme="dark"] .btn-sort {
    background-color: var(--bs-gray-700);
    color: var(--bs-gray-200);
}

.btn-sort:hover {
    background-color: var(--bs-primary-bg-subtle);
    color: var(--bs-primary);
}

.btn-sort.active {
    background-color: var(--bs-primary);
    color: white;
}

/* Countdown Timer Styles */
.countdown-timer-container {
    margin-top: 1rem;
}

.countdown-card {
    background-color: var(--light);
    border-radius: var(--radius-md);
    padding: 1.5rem;
    box-shadow: var(--shadow-sm);
    transition: all 0.3s ease;
}

[data-bs-theme="dark"] .countdown-card {
    background-color: var(--accent-dark);
}

.countdown-header {
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
}

.countdown-header h5 {
    margin-bottom: 0;
    font-weight: 600;
}

.countdown-timer {
    display: flex;
    justify-content: space-between;
    gap: 0.5rem;
    margin-bottom: 1rem;
}

.countdown-item {
    flex: 1;
    background-color: var(--bs-primary);
    color: white;
    border-radius: var(--radius-sm);
    padding: 0.75rem 0.5rem;
    text-align: center;
    display: flex;
    flex-direction: column;
    align-items: center;
    box-shadow: var(--shadow-sm);
}

.countdown-value {
    font-size: 1.5rem;
    font-weight: 700;
    line-height: 1;
    margin-bottom: 0.25rem;
}

.countdown-label {
    font-size: 0.75rem;
    font-weight: 500;
    text-transform: uppercase;
}

.interview-info h6 {
    font-weight: 600;
    margin-bottom: 0.75rem;
}

.interview-info p {
    font-size: 0.875rem;
    color: var(--bs-gray-600);
}

[data-bs-theme="dark"] .interview-info p {
    color: var(--bs-gray-400);
}

.countdown-actions {
    display: flex;
    gap: 0.5rem;
}

@media (max-width: 576px) {
    .countdown-timer {
        flex-wrap: wrap;
    }
    
    .countdown-item {
        min-width: calc(50% - 0.25rem);
        margin-bottom: 0.5rem;
    }
}
</style>

<?php endif; ?>