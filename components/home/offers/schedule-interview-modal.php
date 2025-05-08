<?php
/**
 * Schedule Interview Modal Component
 * This component provides a modal for users to schedule interviews when applying for jobs
 */

// Don't try to start or restart the session here - it should be started in the parent file
// This prevents the 'headers already sent' warning 

// Get user data if logged in
$userLoggedIn = isset($_SESSION['user']) && !empty($_SESSION['user']);
$userData = $_SESSION['user'] ?? null;

// Get job offers for dropdown
require_once __DIR__ . '/../../Dashboard/models/InterviewModel.php';
$interviewModel = new InterviewModel();
$jobOffers = $interviewModel->getJobOffers();
?>

<!-- Schedule Interview Modal -->
<div class="modal fade" id="scheduleInterviewModal" tabindex="-1" aria-labelledby="scheduleInterviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="scheduleInterviewModalLabel">Schedule Interview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="scheduleInterviewForm" method="POST" action="/web/components/home/offers/interviews.php">
                    <input type="hidden" name="action" value="create">
                    <input type="hidden" name="job_offer_id" value="">
                    <input type="hidden" name="status" value="Scheduled">
                    
                    <?php if ($userLoggedIn): ?>
                        <input type="hidden" name="user_email" value="<?php echo htmlspecialchars($userData['email'] ?? ''); ?>">
                    <?php endif; ?>
                    
                    <!-- The form fields will be generated with JavaScript -->
                    <div id="formFields"></div>
                    
                    <div class="text-end mt-4">
                        <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Schedule Interview</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript to generate and handle form fields -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const formFieldsContainer = document.getElementById('formFields');
    const userLoggedIn = <?php echo $userLoggedIn ? 'true' : 'false'; ?>;
    
    // Create form fields dynamically
    createFormFields();
    
    function createFormFields() {
        // Clear existing fields
        formFieldsContainer.innerHTML = '';
        
        // Add email field if user is not logged in
        if (!userLoggedIn) {
            addEmailField();
        }
        
        // Add all other form fields
        addPositionTitleField();
        addNameField();
        addDateTimeField();
        addLocationField();
        addNotesField();
        addCVUrlField();
        
        // Add hidden interviewer field
        const interviewerField = document.createElement('input');
        interviewerField.type = 'hidden';
        interviewerField.name = 'interviewer';
        interviewerField.value = 'To be assigned';
        formFieldsContainer.appendChild(interviewerField);
    }
    
    function addEmailField() {
        const fieldWrapper = document.createElement('div');
        fieldWrapper.className = 'form-group mb-3';
        
        const label = document.createElement('label');
        label.className = 'form-label';
        label.textContent = 'Your Email Address *';
        
        const input = document.createElement('input');
        input.type = 'email';
        input.className = 'form-control';
        input.name = 'user_email';
        input.required = true;
        
        // Add validation
        input.addEventListener('input', validateEmail);
        input.addEventListener('blur', validateEmail);
        
        const helpText = document.createElement('small');
        helpText.className = 'form-text text-muted';
        helpText.textContent = "We'll use this to track your interview and send updates";
        
        // Add feedback element for validation
        const feedback = document.createElement('div');
        feedback.className = 'invalid-feedback';
        feedback.textContent = 'Please enter a valid email address';
        
        fieldWrapper.appendChild(label);
        fieldWrapper.appendChild(input);
        fieldWrapper.appendChild(helpText);
        fieldWrapper.appendChild(feedback);
        formFieldsContainer.appendChild(fieldWrapper);
    }
    
    function addPositionTitleField() {
        const fieldWrapper = document.createElement('div');
        fieldWrapper.className = 'form-group mb-3';
        
        const label = document.createElement('label');
        label.className = 'form-label';
        label.textContent = 'Position Title';
        
        const input = document.createElement('input');
        input.type = 'text';
        input.className = 'form-control';
        input.name = 'position_title';
        input.required = true;
        input.readOnly = true;
        
        fieldWrapper.appendChild(label);
        fieldWrapper.appendChild(input);
        formFieldsContainer.appendChild(fieldWrapper);
    }
    
    function addNameField() {
        const fieldWrapper = document.createElement('div');
        fieldWrapper.className = 'form-group mb-3';
        
        const label = document.createElement('label');
        label.className = 'form-label';
        label.textContent = 'Your Name';
        
        const input = document.createElement('input');
        input.type = 'text';
        input.className = 'form-control';
        input.name = 'candidate_name';
        input.required = true;
        
        // Set value if user is logged in
        if (userLoggedIn) {
            input.value = '<?php echo htmlspecialchars($userData['name'] ?? ''); ?>';
        }
        
        // Add validation
        input.addEventListener('input', validateName);
        input.addEventListener('blur', validateName);
        
        const feedback = document.createElement('div');
        feedback.className = 'invalid-feedback';
        feedback.textContent = 'Please enter your name';
        
        fieldWrapper.appendChild(label);
        fieldWrapper.appendChild(input);
        fieldWrapper.appendChild(feedback);
        formFieldsContainer.appendChild(fieldWrapper);
    }
    
    function addDateTimeField() {
        const fieldWrapper = document.createElement('div');
        fieldWrapper.className = 'form-group mb-3';
        
        const label = document.createElement('label');
        label.className = 'form-label';
        label.textContent = 'Preferred Interview Date & Time';
        
        const input = document.createElement('input');
        input.type = 'datetime-local';
        input.className = 'form-control';
        input.name = 'interview_date';
        input.required = true;
        
        // Set min date to today
        const today = new Date();
        const formattedDate = today.toISOString().slice(0, 16);
        input.min = formattedDate;
        
        // Add validation
        input.addEventListener('input', validateDateTime);
        input.addEventListener('blur', validateDateTime);
        
        const helpText = document.createElement('small');
        helpText.className = 'form-text text-muted';
        helpText.textContent = 'Please select your preferred date and time for the interview';
        
        const feedback = document.createElement('div');
        feedback.className = 'invalid-feedback';
        feedback.textContent = 'Please select a valid future date and time';
        
        fieldWrapper.appendChild(label);
        fieldWrapper.appendChild(input);
        fieldWrapper.appendChild(helpText);
        fieldWrapper.appendChild(feedback);
        formFieldsContainer.appendChild(fieldWrapper);
    }
    
    function addLocationField() {
        const fieldWrapper = document.createElement('div');
        fieldWrapper.className = 'form-group mb-3';
        
        const label = document.createElement('label');
        label.className = 'form-label';
        label.textContent = 'Preferred Location';
        
        const select = document.createElement('select');
        select.className = 'form-select';
        select.name = 'location';
        select.required = true;
        
        const options = [
            { value: 'Remote (Video Call)', text: 'Remote (Video Call)' },
            { value: 'In-Person', text: 'In-Person' },
            { value: 'Phone Call', text: 'Phone Call' }
        ];
        
        options.forEach(opt => {
            const option = document.createElement('option');
            option.value = opt.value;
            option.textContent = opt.text;
            select.appendChild(option);
        });
        
        fieldWrapper.appendChild(label);
        fieldWrapper.appendChild(select);
        formFieldsContainer.appendChild(fieldWrapper);
    }
    
    function addNotesField() {
        const fieldWrapper = document.createElement('div');
        fieldWrapper.className = 'form-group mb-3';
        
        const label = document.createElement('label');
        label.className = 'form-label';
        label.textContent = 'Additional Notes';
        
        const textarea = document.createElement('textarea');
        textarea.className = 'form-control';
        textarea.name = 'feedback';
        textarea.rows = 3;
        textarea.placeholder = 'Any additional information you\'d like to share...';
        
        fieldWrapper.appendChild(label);
        fieldWrapper.appendChild(textarea);
        formFieldsContainer.appendChild(fieldWrapper);
    }
    
    function addCVUrlField() {
        const fieldWrapper = document.createElement('div');
        fieldWrapper.className = 'form-group mb-3';
        
        const label = document.createElement('label');
        label.className = 'form-label';
        label.textContent = 'CV/Resume URL (optional)';
        
        const input = document.createElement('input');
        input.type = 'url';
        input.className = 'form-control';
        input.name = 'cv_url';
        input.placeholder = 'https://example.com/my-resume.pdf';
        
        // Add validation for URL format
        input.addEventListener('input', validateUrl);
        input.addEventListener('blur', validateUrl);
        
        const helpText = document.createElement('small');
        helpText.className = 'form-text text-muted';
        helpText.textContent = 'Link to your CV or resume if available online';
        
        const feedback = document.createElement('div');
        feedback.className = 'invalid-feedback';
        feedback.textContent = 'Please enter a valid URL or leave empty';
        
        fieldWrapper.appendChild(label);
        fieldWrapper.appendChild(input);
        fieldWrapper.appendChild(helpText);
        fieldWrapper.appendChild(feedback);
        formFieldsContainer.appendChild(fieldWrapper);
    }
    
    // Validation functions
    function validateEmail(e) {
        const input = e.target;
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        
        if (input.required && !input.value) {
            setInvalid(input, 'Email address is required');
            return false;
        } else if (input.value && !emailRegex.test(input.value)) {
            setInvalid(input, 'Please enter a valid email address');
            return false;
        } else {
            setValid(input);
            return true;
        }
    }
    
    function validateName(e) {
        const input = e.target;
        
        if (input.required && !input.value.trim()) {
            setInvalid(input, 'Name is required');
            return false;
        } else {
            setValid(input);
            return true;
        }
    }
    
    function validateDateTime(e) {
        const input = e.target;
        const selectedDate = new Date(input.value);
        const now = new Date();
        
        if (input.required && !input.value) {
            setInvalid(input, 'Date and time are required');
            return false;
        } else if (selectedDate <= now) {
            setInvalid(input, 'Please select a future date and time');
            return false;
        } else {
            setValid(input);
            return true;
        }
    }
    
    function validateUrl(e) {
        const input = e.target;
        
        // Skip validation if empty
        if (!input.value) {
            setValid(input);
            return true;
        }
        
        try {
            new URL(input.value);
            setValid(input);
            return true;
        } catch (err) {
            setInvalid(input, 'Please enter a valid URL');
            return false;
        }
    }
    
    function setValid(input) {
        input.classList.remove('is-invalid');
        input.classList.add('is-valid');
    }
    
    function setInvalid(input, message) {
        input.classList.remove('is-valid');
        input.classList.add('is-invalid');
        
        // Update feedback message if exists
        const feedback = input.nextElementSibling;
        if (feedback && feedback.classList.contains('invalid-feedback')) {
            feedback.textContent = message;
        }
    }
    
    // Form validation
    const form = document.getElementById('scheduleInterviewForm');
    if (form) {
        form.addEventListener('submit', function(event) {
            let isValid = true;
            
            // Validate all required fields
            const requiredFields = form.querySelectorAll('[required]');
            requiredFields.forEach(field => {
                if (field.type === 'email') {
                    isValid = validateEmail({ target: field }) && isValid;
                } else if (field.type === 'datetime-local') {
                    isValid = validateDateTime({ target: field }) && isValid;
                } else if (field.type === 'text' && field.name === 'candidate_name') {
                    isValid = validateName({ target: field }) && isValid;
                } else if (field.type === 'url') {
                    isValid = validateUrl({ target: field }) && isValid;
                } else if (!field.value.trim()) {
                    setInvalid(field, 'This field is required');
                    isValid = false;
                }
            });
            
            if (!isValid) {
                event.preventDefault();
                
                // Show alert for first invalid field
                const firstInvalid = form.querySelector('.is-invalid');
                if (firstInvalid) {
                    firstInvalid.focus();
                    
                    // Create alert
                    const alertElement = document.createElement('div');
                    alertElement.className = 'alert alert-warning alert-dismissible fade show';
                    alertElement.innerHTML = `
                        <strong>Please fill out this field.</strong>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    `;
                    
                    // Add alert before the first invalid field
                    const fieldWrapper = firstInvalid.closest('.form-group');
                    fieldWrapper.insertBefore(alertElement, fieldWrapper.firstChild);
                    
                    // Remove alert after 3 seconds
                    setTimeout(() => {
                        alertElement.remove();
                    }, 3000);
                }
                
                return false;
            }
        });
    }
});
</script>