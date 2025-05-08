<?php
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../Dashboard/controllers/JobOfferController.php';

// Initialize message variable
$message = '';
$error = '';

function validateJobOffer($data) {
    $errors = [];
    
    // Title validation
    if (empty($data['title'])) {
        $errors['title'] = "Title is required";
    } elseif (strlen($data['title']) < 3 || strlen($data['title']) > 100) {
        $errors['title'] = "Title must be between 3 and 100 characters";
    }
    
    // Description validation
    if (empty($data['description'])) {
        $errors['description'] = "Description is required";
    } elseif (strlen($data['description']) < 10) {
        $errors['description'] = "Description must be at least 10 characters";
    }
    
    // Category validation
    if (empty($data['category_id'])) {
        $errors['category'] = "Category is required";
    }
    
    // Salary validation
    if (empty($data['salary_min']) || empty($data['salary_max'])) {
        $errors['salary'] = "Both minimum and maximum salary are required";
    } elseif ($data['salary_min'] > $data['salary_max']) {
        $errors['salary'] = "Minimum salary cannot be greater than maximum salary";
    } elseif ($data['salary_min'] < 0 || $data['salary_max'] < 0) {
        $errors['salary'] = "Salary cannot be negative";
    }
    
    // Location validation
    if (empty($data['location_id'])) {
        $errors['location'] = "Location is required";
    }
    
    return $errors;
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $jobOfferController = new JobOfferController();
    
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create':
                $validation_errors = validateJobOffer($_POST);
                if (empty($validation_errors)) {
                    $result = $jobOfferController->create();
                    if ($result['success']) {
                        $_SESSION['success_message'] = $result['message'];
                        header('Location: /web/components/home/offers/offers.php');
                        exit;
                    } else {
                        $error = $result['message'];
                    }
                } else {
                    $error = implode("<br>", $validation_errors);
                }
                break;

            case 'update':
                $validation_errors = validateJobOffer($_POST);
                if (empty($validation_errors)) {
                    $id = $_POST['job_id'];
                    $result = $jobOfferController->update($id);
                    if ($result['success']) {
                        $_SESSION['success_message'] = $result['message'];
                        header('Location: /web/components/home/offers/offers.php');
                        exit;
                    } else {
                        $error = $result['message'];
                    }
                } else {
                    $error = implode("<br>", $validation_errors);
                }
                break;

            case 'delete':
                $id = $_POST['job_id'];
                if (!empty($id)) {
                    $result = $jobOfferController->delete($id);
                    if ($result['success']) {
                        $_SESSION['success_message'] = $result['message'];
                        header('Location: /web/components/home/offers/offers.php');
                        exit;
                    } else {
                        $error = $result['message'];
                    }
                }
                break;
        }
    }
}

// Fetch all job offers
$sql = "SELECT jo.*, jc.name as category_name, l.city, l.country, l.is_remote,
        (SELECT COUNT(*) FROM job_applications WHERE job_id = jo.job_id) as applicant_count
        FROM job_offers jo
        LEFT JOIN job_categories jc ON jo.category_id = jc.category_id
        LEFT JOIN locations l ON jo.location_id = l.location_id
        ORDER BY jo.created_at DESC";
$stmt = $pdo->query($sql);
$jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch categories for dropdown
$sql = "SELECT * FROM job_categories";
$stmt = $pdo->query($sql);
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch locations for dropdown
$sql = "SELECT * FROM locations";
$stmt = $pdo->query($sql);
$locations = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Show any session messages and clear them
if (isset($_SESSION['success_message'])) {
    $message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}
if (isset($_SESSION['error_message'])) {
    $error = $_SESSION['error_message'];
    unset($_SESSION['error_message']);
}
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Offers Management - LenSi</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <style>
        :root {
            --primary-color: #5D8BB3;
            --accent-color: #8FB3DE;
            --background-light: #F7F8FA;
            --background-dark: #121518;
            --text-dark: #1D2D44;
            --text-light: #A4C2E5;
        }

        body {
            background-color: var(--background-light);
            color: var(--text-dark);
            min-height: 100vh;
            padding-top: 60px;
        }

        [data-bs-theme="dark"] {
            .page-header {
                color: #FFFFFF;
            }

            .job-card {
                background: rgba(31, 32, 40, 0.8);
                border: 1px solid rgba(70, 90, 120, 0.2);
            }

            .card-title {
                color: #FFFFFF;
            }

            .card-text {
                color: #A4C2E5;
            }

            .job-meta-item {
                color: #A4C2E5;
            }

            .job-meta-item i {
                color: #8FB3DE;
            }

            .modal-content {
                background: var(--background-dark);
                color: #FFFFFF;
            }

            .modal-title {
                color: #FFFFFF;
            }

            .form-label {
                color: #A4C2E5;
            }

            .form-text {
                color: rgba(255, 255, 255, 0.7);
            }
        }

        .page-header {
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .job-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            margin-bottom: 1.5rem;
            overflow: hidden;
        }

        .job-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .job-card-header {
            padding: 1rem;
            border-bottom: 1px solid rgba(0,0,0,0.05);
        }

        .job-card-content {
            padding: 1rem;
        }

        .job-meta {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            margin: 1rem 0;
        }

        .job-meta-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
            color: var(--primary-color);
        }

        .job-actions {
            padding: 1rem;
            background: rgba(0,0,0,0.02);
            display: flex;
            justify-content: flex-end;
            gap: 0.5rem;
        }

        [data-bs-theme="dark"] .job-actions {
            background: rgba(255,255,255,0.02);
        }

        .btn-primary {
            background: linear-gradient(90deg, var(--primary-color), var(--accent-color));
            border: none;
        }

        .btn-primary:hover {
            background: linear-gradient(90deg, var(--accent-color), var (--primary-color));
        }

        .floating-add-btn {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            width: 60px;
            height: 60px;
            border-radius: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            z-index: 1000;
        }

        .alert {
            margin-bottom: 2rem;
        }

        .modal-content {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        [data-bs-theme="dark"] .modal-content {
            background: var(--background-dark);
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }
    </style>
</head>
<body>
    <?php include_once(__DIR__ . '/../navbar.php'); ?>

    <div class="page-header">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="mb-0">Job Offers</h1>
                    <p class="mb-0">Manage and view all job opportunities</p>
                </div>
                <button type="button" class="btn btn-light" data-bs-toggle="modal" data-bs-target="#addJobModal">
                    <i class="bi bi-plus-lg"></i> Add New Job
                </button>
            </div>
        </div>
    </div>

    <div class="container">
        <?php if ($message): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($error); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <?php foreach ($jobs as $job): ?>
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="job-card">
                        <div class="job-card-header">
                            <h5 class="card-title mb-1"><?php echo htmlspecialchars($job['title']); ?></h5>
                            <span class="badge bg-primary"><?php echo htmlspecialchars($job['category_name']); ?></span>
                        </div>
                        <div class="job-card-content">
                            <p class="card-text"><?php echo htmlspecialchars(substr($job['description'], 0, 150)) . '...'; ?></p>
                            
                            <div class="job-meta">
                                <div class="job-meta-item">
                                    <i class="bi bi-cash-stack"></i>
                                    <span>$<?php echo number_format($job['salary_min']); ?> - $<?php echo number_format($job['salary_max']); ?></span>
                                </div>
                                <div class="job-meta-item">
                                    <i class="bi bi-geo-alt"></i>
                                    <span><?php echo $job['is_remote'] ? 'Remote' : htmlspecialchars($job['city'] . ', ' . $job['country']); ?></span>
                                </div>
                                <div class="job-meta-item">
                                    <i class="bi bi-people"></i>
                                    <span><?php echo $job['applicant_count']; ?> applicants</span>
                                </div>
                            </div>
                        </div>
                        <div class="job-actions">
                            <button class="btn btn-sm btn-outline-info show-job" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#showJobModal"
                                    data-job='<?php echo json_encode($job); ?>'>
                                <i class="bi bi-eye"></i> Show
                            </button>
                            <button class="btn btn-sm btn-outline-primary edit-job" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#jobModal"
                                    data-job='<?php echo json_encode($job); ?>'>
                                <i class="bi bi-pencil"></i> Edit
                            </button>
                            <form action="/web/components/Dashboard/index.php?section=job-offers" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this job?');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="job_id" value="<?php echo $job['job_id']; ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <i class="bi bi-trash"></i> Delete
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Show Job Modal -->
    <div class="modal fade" id="showJobModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Job Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-4">
                        <h3 id="show_title" class="mb-3"></h3>
                        <span id="show_category" class="badge bg-primary mb-3 d-inline-block"></span>
                    </div>
                    
                    <div class="mb-4">
                        <h5 class="text-muted mb-3">Description</h5>
                        <p id="show_description" class="mb-4"></p>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h5 class="text-muted mb-3">Salary Range</h5>
                            <p id="show_salary" class="mb-0"></p>
                        </div>
                        <div class="col-md-6">
                            <h5 class="text-muted mb-3">Location</h5>
                            <p id="show_location" class="mb-0"></p>
                        </div>
                    </div>

                    <div class="mb-4">
                        <h5 class="text-muted mb-3">Statistics</h5>
                        <p id="show_applicants" class="mb-0"></p>
                    </div>

                    <div class="text-center mt-4">
                        <img id="show_image" class="img-fluid rounded" style="max-height: 300px;" alt="Job image">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Single Job Modal for both Add and Edit -->
<div class="modal fade" id="jobModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Add New Job</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="/web/components/home/offers/offers.php">
                <div class="modal-body">
                    <input type="hidden" name="action" value="create">
                    <input type="hidden" name="job_id" id="job_id">
                    <div class="mb-3">
                        <label for="title" class="form-label">Job Title</label>
                        <input type="text" class="form-control" id="title" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label for="category_id" class="form-label">Category</label>
                        <select class="form-control" id="category_id" name="category_id" required>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['category_id']; ?>">
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="salary_min" class="form-label">Minimum Salary</label>
                                <input type="number" class="form-control" id="salary_min" name="salary_min" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="salary_max" class="form-label">Maximum Salary</label>
                                <input type="number" class="form-control" id="salary_max" name="salary_max" required>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="location_id" class="form-label">Location</label>
                        <select class="form-control" id="location_id" name="location_id" required>
                            <?php foreach ($locations as $location): ?>
                                <option value="<?php echo $location['location_id']; ?>">
                                    <?php echo $location['is_remote'] ? 'Remote' : htmlspecialchars($location['city'] . ', ' . $location['country']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="image_url" class="form-label">Image URL</label>
                        <input type="url" class="form-control" id="image_url" name="image_url">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" id="submitBtn">Add Job</button>
                </div>
            </form>
        </div>
    </div>
</div>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Edit job functionality
        document.querySelectorAll('.edit-job').forEach(button => {
            button.addEventListener('click', function() {
                const job = JSON.parse(this.dataset.job);
                const form = document.querySelector('#jobModal form');
                
                // Update form for editing
                form.elements['action'].value = 'update';
                form.elements['job_id'].value = job.job_id;
                form.elements['title'].value = job.title;
                form.elements['category_id'].value = job.category_id;
                form.elements['description'].value = job.description;
                form.elements['salary_min'].value = job.salary_min;
                form.elements['salary_max'].value = job.salary_max;
                form.elements['location_id'].value = job.location_id;
                form.elements['image_url'].value = job.image_url || '';
                
                // Update modal title and button
                document.getElementById('modalTitle').textContent = 'Edit Job';
                document.getElementById('submitBtn').textContent = 'Update Job';
            });
        });

        // Reset form when opening modal for new job
        document.getElementById('jobModal').addEventListener('show.bs.modal', function(event) {
            // Only reset if it's not coming from the edit button
            if (!event.relatedTarget || !event.relatedTarget.classList.contains('edit-job')) {
                const form = this.querySelector('form');
                form.reset();
                form.elements['action'].value = 'create';
                form.elements['job_id'].value = '';
                document.getElementById('modalTitle').textContent = 'Add New Job';
                document.getElementById('submitBtn').textContent = 'Add Job';
            }
        });

        // Form validation
        const forms = document.querySelectorAll('form');
        forms.forEach(form => {
            form.addEventListener('submit', function(e) {
                if (this.elements['salary_max'] && this.elements['salary_min']) {
                    const salaryMin = parseFloat(this.elements['salary_min'].value);
                    const salaryMax = parseFloat(this.elements['salary_max'].value);
                    if (salaryMax <= salaryMin) {
                        e.preventDefault();
                        alert('Maximum salary must be greater than minimum salary');
                    }
                }
            });
        });
    });
    </script>
</body>
</html>