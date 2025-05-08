<?php
/**
 * Admin Job Offers Dashboard View
 * Displays all job offers and their statistics
 */

// Get user data
$user = $_SESSION['user'];
$userType = $user['user_type'] ?? 'freelancer';

// Initialize job offer model
require_once __DIR__ . '/../../models/JobOfferModel.php';
$jobOfferModel = new JobOfferModel();

try {
    // Get all job offers (admin only)
    $jobOffers = $jobOfferModel->getAllJobOffers($user['email']);
    
    // Get job statistics
    $stats = $jobOfferModel->getJobStats($user['email']);
    
    // Get categories and locations for forms
    $categories = $jobOfferModel->getCategories();
    $locations = $jobOfferModel->getLocations();
} catch (Exception $e) {
    $error = $e->getMessage();
}
?>

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

<div class="welcome-section">
    <h2 class="welcome-title">Job Offers Management</h2>
    <p class="welcome-subtitle">Administrator view of all job offers</p>
</div>

<?php if (isset($error)): ?>
<div class="alert alert-danger" role="alert">
    <?php echo htmlspecialchars($error); ?>
</div>
<?php else: ?>

<!-- Statistics Section -->
<div class="dashboard-stats">
    <div class="stat-card">
        <div class="stat-icon blue">
            <i class="bi bi-briefcase"></i>
        </div>
        <div class="stat-title">Total Job Offers</div>
        <div class="stat-value"><?php echo $stats['total']; ?></div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon green">
            <i class="bi bi-people"></i>
        </div>
        <div class="stat-title">Total Applications</div>
        <div class="stat-value"><?php echo $stats['total_applications']; ?></div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon orange">
            <i class="bi bi-check-circle"></i>
        </div>
        <div class="stat-title">Accepted Applications</div>
        <div class="stat-value"><?php echo $stats['applications_by_status']['accepted'] ?? 0; ?></div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon purple">
            <i class="bi bi-hourglass-split"></i>
        </div>
        <div class="stat-title">Pending Applications</div>
        <div class="stat-value"><?php echo $stats['applications_by_status']['pending'] ?? 0; ?></div>
    </div>
</div>

<!-- Job Offers Table -->
<section class="dashboard-table-section">
    <div class="dashboard-table-header d-flex justify-content-between align-items-center">
        <h3 class="dashboard-table-title">All Job Offers</h3>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#jobOfferModal">
            <i class="bi bi-plus-circle me-2"></i>Add New Job Offer
        </button>
    </div>
    
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Location</th>
                    <th>Salary Range</th>
                    <th>Applications</th>
                    <th>Posted</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($jobOffers as $job): ?>
                <tr>
                    <td><?php echo htmlspecialchars($job['title']); ?></td>
                    <td><?php echo htmlspecialchars($job['category_name']); ?></td>
                    <td>
                        <?php echo $job['is_remote'] ? 'Remote' : htmlspecialchars($job['city'] . ', ' . $job['country']); ?>
                    </td>
                    <td>$<?php echo number_format($job['salary_min']); ?> - $<?php echo number_format($job['salary_max']); ?></td>
                    <td><?php echo $job['applicant_count']; ?></td>
                    <td><?php echo date('M j, Y', strtotime($job['created_at'])); ?></td>
                    <td>
                        <!-- Actions column -->
                        <button class="btn btn-sm btn-info me-1" onclick="showJobOffer(<?php echo $job['job_id']; ?>)">
                            <i class="bi bi-eye"></i>
                        </button>
                        <button class="btn btn-sm btn-primary me-1 edit-job" onclick="editJobOffer(<?php echo $job['job_id']; ?>)">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="deleteJobOffer(<?php echo $job['job_id']; ?>)">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<!-- Add/Edit Job Offer Modal -->
<div class="modal fade" id="jobOfferModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Add Job Offer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="jobOfferForm" method="POST" action="/web/components/Dashboard/index.php?page=job-offers" novalidate>
                    <input type="hidden" name="action" value="create">
                    <input type="hidden" name="job_id" value="">
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Title</label>
                            <input type="text" class="form-control" name="title" required>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Category</label>
                            <select class="form-select" name="category_id" required>
                                <option value="">Select a category</option>
                                <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['category_id']; ?>">
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="4" required></textarea>
                        <div class="invalid-feedback"></div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Minimum Salary</label>
                            <input type="number" class="form-control" name="salary_min" required min="0">
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Maximum Salary</label>
                            <input type="number" class="form-control" name="salary_max" required min="0">
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Location</label>
                        <select class="form-select" name="location_id" required>
                            <option value="">Select a location</option>
                            <?php foreach ($locations as $location): ?>
                            <option value="<?php echo $location['location_id']; ?>">
                                <?php echo $location['is_remote'] ? 'Remote' : htmlspecialchars($location['city'] . ', ' . $location['country']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Image URL (optional)</label>
                        <input type="url" class="form-control" name="image_url">
                        <div class="invalid-feedback"></div>
                        <small class="form-text text-muted">Enter a valid URL for the job image (e.g., https://example.com/image.jpg)</small>
                    </div>
                    
                    <div class="text-end mt-4">
                        <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="submitBtn">Create Job Offer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- View Job Offer Modal -->
<div class="modal fade" id="viewJobOfferModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Job Offer Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="job-offer-details">
                    <!-- Details will be populated by JavaScript -->
                </div>
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
        feedback.remove();
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
    
    const feedback = document.createElement('div');
    feedback.className = 'invalid-feedback';
    feedback.textContent = message;
    element.parentNode.appendChild(feedback);
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
    
    // Remove any existing invalid feedback
    const existingFeedback = element.parentNode.querySelector('.invalid-feedback');
    if (existingFeedback) {
        existingFeedback.remove();
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

// Show job offer details
function showJobOffer(id) {
    fetch(`/web/components/home/offers/get_job_offer.php?id=${id}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Failed to fetch job offer data');
            }
            return response.json();
        })
        .then(data => {
            const details = document.querySelector('.job-offer-details');
            details.innerHTML = `
                <div class="mb-3">
                    <strong>Title:</strong> ${data.title}
                </div>
                <div class="mb-3">
                    <strong>Category:</strong> ${data.category_name}
                </div>
                <div class="mb-3">
                    <strong>Description:</strong><br>
                    ${data.description}
                </div>
                <div class="mb-3">
                    <strong>Salary Range:</strong><br>
                    $${Number(data.salary_min).toLocaleString()} - $${Number(data.salary_max).toLocaleString()}
                </div>
                <div class="mb-3">
                    <strong>Location:</strong><br>
                    ${data.is_remote ? 'Remote' : `${data.city}, ${data.country}`}
                </div>
                <div class="mb-3">
                    <strong>Applications:</strong> ${data.applicant_count}
                </div>
                ${data.image_url ? `
                <div class="mb-3">
                    <strong>Image:</strong><br>
                    <img src="${data.image_url}" alt="${data.title}" class="img-fluid rounded">
                </div>` : ''}
            `;
            
            const modal = new bootstrap.Modal(document.getElementById('viewJobOfferModal'));
            modal.show();
        })
        .catch(error => {
            console.error('Error fetching job offer details:', error);
            alert('Failed to load job offer details');
        });
}

// Edit job offer
function editJobOffer(id) {
    fetch(`/web/components/home/offers/get_job_offer.php?id=${id}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Failed to fetch job offer data');
            }
            return response.json();
        })
        .then(data => {
            if (!data || data.error) {
                throw new Error(data.error || 'Failed to load job offer data');
            }
            
            const form = document.getElementById('jobOfferForm');
            
            // Clear any previous validation styles
            form.classList.remove('was-validated');
            
            // Update form fields
            form.elements['action'].value = 'update';
            form.elements['job_id'].value = data.job_id;
            form.elements['title'].value = data.title;
            form.elements['description'].value = data.description;
            form.elements['category_id'].value = data.category_id;
            form.elements['salary_min'].value = data.salary_min;
            form.elements['salary_max'].value = data.salary_max;
            form.elements['location_id'].value = data.location_id;
            form.elements['image_url'].value = data.image_url || '';
            
            // Update modal title and submit button
            document.getElementById('modalTitle').textContent = 'Edit Job Offer';
            document.getElementById('submitBtn').textContent = 'Update Job Offer';
            
            // Show the modal
            const modal = new bootstrap.Modal(document.getElementById('jobOfferModal'));
            modal.show();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to load job offer data: ' + error.message);
        });
}

// Delete job offer
function deleteJobOffer(id) {
    if (confirm('Are you sure you want to delete this job offer?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/web/components/Dashboard/index.php?page=job-offers';
        form.innerHTML = `
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="job_id" value="${id}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

// Reset form when opening modal for new job offer
document.getElementById('jobOfferModal').addEventListener('show.bs.modal', function(event) {
    const button = event.relatedTarget;
    
    // Only reset if it's not being opened programmatically by the edit function
    if (button) {
        const form = document.getElementById('jobOfferForm');
        form.reset();
        form.elements['action'].value = 'create';
        form.elements['job_id'].value = '';
        document.getElementById('modalTitle').textContent = 'Add Job Offer';
        document.getElementById('submitBtn').textContent = 'Create Job Offer';
        
        // Clear all validation states
        form.querySelectorAll('.is-invalid, .is-valid').forEach(el => {
            el.classList.remove('is-invalid', 'is-valid');
            el.style.borderColor = ''; // Reset border color to default
        });
        
        // Remove all feedback icons
        form.querySelectorAll('.invalid-feedback-icon, .valid-feedback-icon').forEach(el => {
            el.remove();
        });
        
        // Remove all feedback messages
        form.querySelectorAll('.invalid-feedback').forEach(el => {
            el.remove();
        });
    }
});

// Add real-time validation for form fields
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('jobOfferForm');
    
    // Title validation
    form.elements['title'].addEventListener('input', function() {
        const value = this.value.trim();
        if (value && value.length >= 3) {
            addSuccessMessage(this);
        } else if (value.length > 0) {
            addErrorMessage(this, 'Job title must be at least 3 characters');
        } else {
            addErrorMessage(this, 'Job title is required');
        }
    });
    
    // Description validation
    form.elements['description'].addEventListener('input', function() {
        const value = this.value.trim();
        if (value && value.length >= 20) {
            addSuccessMessage(this);
        } else if (value.length > 0) {
            addErrorMessage(this, 'Description must be at least 20 characters');
        } else {
            addErrorMessage(this, 'Job description is required');
        }
    });
    
    // Category validation
    form.elements['category_id'].addEventListener('change', function() {
        if (this.value) {
            addSuccessMessage(this);
        } else {
            addErrorMessage(this, 'Please select a category');
        }
    });
    
    // Salary validation
    form.elements['salary_min'].addEventListener('input', function() {
        const value = parseFloat(this.value);
        if (!isNaN(value) && value >= 0) {
            addSuccessMessage(this);
            
            // Check if max salary is valid compared to min salary
            const maxSalary = parseFloat(form.elements['salary_max'].value);
            if (!isNaN(maxSalary) && maxSalary > value) {
                addSuccessMessage(form.elements['salary_max']);
            } else if (!isNaN(maxSalary)) {
                addErrorMessage(form.elements['salary_max'], 'Maximum salary must be greater than minimum salary');
            }
        } else {
            addErrorMessage(this, 'Please enter a valid minimum salary');
        }
    });
    
    form.elements['salary_max'].addEventListener('input', function() {
        const value = parseFloat(this.value);
        const minSalary = parseFloat(form.elements['salary_min'].value);
        
        if (isNaN(value) || value < 0) {
            addErrorMessage(this, 'Please enter a valid maximum salary');
        } else if (!isNaN(minSalary) && value <= minSalary) {
            addErrorMessage(this, 'Maximum salary must be greater than minimum salary');
        } else {
            addSuccessMessage(this);
        }
    });
    
    // Location validation
    form.elements['location_id'].addEventListener('change', function() {
        if (this.value) {
            addSuccessMessage(this);
        } else {
            addErrorMessage(this, 'Please select a location');
        }
    });
    
    // Image URL validation
    form.elements['image_url'].addEventListener('input', function() {
        const value = this.value.trim();
        if (value && !isValidUrl(value)) {
            addErrorMessage(this, 'Please enter a valid URL');
        } else if (value) {
            addSuccessMessage(this);
        } else {
            // Remove any validation for empty optional field
            this.classList.remove('is-invalid', 'is-valid');
            this.style.borderColor = ''; // Reset border color to default
            const errorIcon = this.parentNode.querySelector('.invalid-feedback-icon');
            if (errorIcon) errorIcon.remove();
            const successIcon = this.parentNode.querySelector('.valid-feedback-icon');
            if (successIcon) successIcon.remove();
            const feedback = this.parentNode.querySelector('.invalid-feedback');
            if (feedback) feedback.remove();
        }
    });
});

// Form validation and submission
document.getElementById('jobOfferForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    // Get form elements
    const form = this;
    const title = form.elements['title'].value.trim();
    const description = form.elements['description'].value.trim();
    const categoryId = form.elements['category_id'].value;
    const salaryMin = parseFloat(form.elements['salary_min'].value);
    const salaryMax = parseFloat(form.elements['salary_max'].value);
    const locationId = form.elements['location_id'].value;
    const imageUrl = form.elements['image_url'].value.trim();
    
    // Clear previous error messages
    const errorMessages = form.querySelectorAll('.invalid-feedback');
    errorMessages.forEach(el => el.remove());
    
    // Reset validation classes
    form.querySelectorAll('.is-invalid').forEach(el => {
        el.classList.remove('is-invalid');
    });
    
    // Validate all fields
    let isValid = true;
    
    // Title validation
    if (!title) {
        isValid = false;
        addErrorMessage(form.elements['title'], 'Job title is required');
    } else if (title.length < 3) {
        isValid = false;
        addErrorMessage(form.elements['title'], 'Job title must be at least 3 characters');
    } else {
        addSuccessMessage(form.elements['title']);
    }
    
    // Description validation
    if (!description) {
        isValid = false;
        addErrorMessage(form.elements['description'], 'Job description is required');
    } else if (description.length < 20) {
        isValid = false;
        addErrorMessage(form.elements['description'], 'Description must be at least 20 characters');
    } else {
        addSuccessMessage(form.elements['description']);
    }
    
    // Category validation
    if (!categoryId) {
        isValid = false;
        addErrorMessage(form.elements['category_id'], 'Please select a category');
    } else {
        addSuccessMessage(form.elements['category_id']);
    }
    
    // Salary validation
    if (isNaN(salaryMin) || salaryMin < 0) {
        isValid = false;
        addErrorMessage(form.elements['salary_min'], 'Please enter a valid minimum salary');
    } else {
        addSuccessMessage(form.elements['salary_min']);
    }
    
    if (isNaN(salaryMax) || salaryMax < 0) {
        isValid = false;
        addErrorMessage(form.elements['salary_max'], 'Please enter a valid maximum salary');
    } else if (!isNaN(salaryMin) && !isNaN(salaryMax) && salaryMax <= salaryMin) {
        isValid = false;
        addErrorMessage(form.elements['salary_max'], 'Maximum salary must be greater than minimum salary');
    } else {
        addSuccessMessage(form.elements['salary_max']);
    }
    
    // Location validation
    if (!locationId) {
        isValid = false;
        addErrorMessage(form.elements['location_id'], 'Please select a location');
    } else {
        addSuccessMessage(form.elements['location_id']);
    }
    
    // Image URL validation (optional field)
    if (imageUrl && !isValidUrl(imageUrl)) {
        isValid = false;
        addErrorMessage(form.elements['image_url'], 'Please enter a valid URL');
    } else if (imageUrl) {
        addSuccessMessage(form.elements['image_url']);
    }
    
    // If validation fails, stop form submission
    if (!isValid) {
        return;
    }
    
    try {
        const formData = new FormData(this);
        const response = await fetch('/web/components/Dashboard/index.php?page=job-offers', {
            method: 'POST',
            body: formData
        });
        
        // If response is a redirect, follow it
        if (response.redirected) {
            window.location.href = response.url;
            return;
        }
        
        // Get the modal body where the form is located
        const modalBody = this.closest('.modal-body');
        
        // Remove any existing alerts
        const existingAlert = modalBody.querySelector('.alert');
        if (existingAlert) {
            existingAlert.remove();
        }
        
        // Create alert element
        const alertDiv = document.createElement('div');
        alertDiv.className = 'alert alert-success alert-dismissible fade show';
        alertDiv.innerHTML = `
            Job offer saved successfully!
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        // Insert alert at the beginning of the modal body
        modalBody.insertBefore(alertDiv, this);
        
        // Reload the page after a short delay
        setTimeout(() => {
            window.location.reload();
        }, 1500);
    } catch (error) {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
    }
});
</script>

<?php endif; ?>