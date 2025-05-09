/**
 * Interview Management JavaScript
 * Structured in namespaced modules following clean code principles
 */

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