/**
 * Schedule Interview Modal Handler
 * Handles the functionality for scheduling interviews from job offers
 */

document.addEventListener('DOMContentLoaded', function() {
    // Add event listeners to all Apply Now buttons
    const applyButtons = document.querySelectorAll('.job-card-apply');
    
    applyButtons.forEach(button => {
        button.addEventListener('click', function(event) {
            event.preventDefault();
            
            // Get the job data from the parent card
            const jobCard = this.closest('.job-card');
            const jobTitle = jobCard.querySelector('.job-card-title').textContent;
            const jobId = this.getAttribute('data-job-id');
            
            // Set the job title and ID in the modal
            const modal = document.getElementById('scheduleInterviewModal');
            if (modal) {
                // Debug session status
                console.log('Modal found, setting job details');
                
                const positionInput = modal.querySelector('input[name="position_title"]');
                const jobIdInput = modal.querySelector('input[name="job_offer_id"]');
                
                if (positionInput) {
                    positionInput.value = jobTitle;
                    console.log('Position title set:', jobTitle);
                } else {
                    console.error('Position title input not found');
                }
                
                if (jobIdInput) {
                    jobIdInput.value = jobId;
                    console.log('Job ID set:', jobId);
                } else {
                    console.error('Job ID input not found');
                }
                
                // Show the modal
                const bsModal = new bootstrap.Modal(modal);
                bsModal.show();
            } else {
                console.error('Schedule Interview Modal not found');
            }
        });
    });
    
    // Form validation and submission
    const interviewForm = document.getElementById('scheduleInterviewForm');
    if (interviewForm) {
        interviewForm.addEventListener('submit', function(event) {
            // Basic form validation
            const requiredFields = interviewForm.querySelectorAll('[required]');
            let isValid = true;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.classList.add('is-invalid');
                } else {
                    field.classList.remove('is-invalid');
                    field.classList.add('is-valid');
                }
            });
            
            if (!isValid) {
                event.preventDefault();
                return false;
            }
            
            // If using AJAX submission (optional enhancement)
            /* 
            event.preventDefault();
            
            const formData = new FormData(interviewForm);
            
            fetch('/web/components/home/offers/interviews.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message and redirect to interviews page
                    alert(data.message);
                    window.location.href = '/web/components/Dashboard/index.php?page=interviews';
                } else {
                    // Show error message
                    alert(data.message || 'Failed to schedule interview');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
            });
            */
        });
    }
});