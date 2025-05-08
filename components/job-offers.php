<?php
/**
 * Job Offers Component
 * Displays current job opportunities with interactive 3D hover effects
 */

// Ensure no output has been sent before starting the session
if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    session_start();
} elseif (session_status() === PHP_SESSION_NONE && headers_sent()) {
    // Log the error but continue execution
    error_log('Warning: Session could not be started in job-offers.php because headers have already been sent');
}

// Include database connection
require_once __DIR__ . '/../config/database.php';

// Fetch all job offers
$sql = "SELECT jo.*, jc.name as category_name, l.city, l.country, l.is_remote,
       (SELECT COUNT(*) FROM job_applications WHERE job_id = jo.job_id) as applicant_count
        FROM job_offers jo
        LEFT JOIN job_categories jc ON jo.category_id = jc.category_id
        LEFT JOIN locations l ON jo.location_id = l.location_id
        ORDER BY jo.created_at DESC";

$stmt = $pdo->query($sql);
$jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<style>
/* Job Offers Section - Matching Frame Design */
.job-offers-section {
    position: relative;
    min-height: 100vh;
    display: flex;
    align-items: center;
    padding: 6rem 0;
    overflow: hidden;
    scroll-margin-top: 80px;
    background-color: rgba(247, 248, 250, 0.5);
}

[data-bs-theme="dark"] .job-offers-section {
    background-color: rgba(18, 21, 30, 0.5);
}

.job-offers-container {
    width: 100%;
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 1rem;
    z-index: 2;
    position: relative;
}

.job-offers-header {
    text-align: center;
    margin-bottom: 3rem;
    position: relative;
}

.job-offers-title {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 1rem;
    position: relative;
    display: inline-block;
    color: var(--accent);
}

.job-offers-title::after {
    content: '';
    position: absolute;
    left: 50%;
    bottom: -0.5rem;
    width: 50px;
    height: 3px;
    background: linear-gradient(90deg, #5D8BB3, #8FB3DE);
    transform: translateX(-50%);
}

[data-bs-theme="dark"] .job-offers-title::after {
    background: linear-gradient(90deg, #7BA4CD, #A8C8E8);
}

.job-offers-subtitle {
    font-size: 1.2rem;
    max-width: 700px;
    margin: 0 auto;
    color: var(--secondary);
}

.job-offers-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 2rem;
    margin-top: 2rem;
    perspective: 1000px;
}

/* Enhanced 3D Card Styles */
.job-card {
    background: white;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
    transition: all 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    border: 1px solid rgba(0, 0, 0, 0.03);
    display: flex;
    flex-direction: column;
    height: 100%;
    transform-style: preserve-3d;
    position: relative;
    will-change: transform;
}

.job-card:hover {
    transform: translateY(-10px) translateZ(20px) rotateX(3deg) rotateY(2deg) scale(1.02);
    box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15), 0 5px 15px rgba(0, 0, 0, 0.1);
}

.job-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, rgba(255,255,255,0.3) 0%, transparent 50%);
    opacity: 0;
    transition: opacity 0.3s ease;
    border-radius: 15px;
    pointer-events: none;
}

.job-card:hover::before {
    opacity: 1;
}

.job-card-img {
    height: 180px;
    background-size: cover;
    background-position: center;
    position: relative;
    overflow: hidden;
    transform: translateZ(10px);
    transition: transform 0.5s ease;
}

.job-card:hover .job-card-img {
    transform: translateZ(15px);
}

.job-card-img::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(to bottom, transparent 70%, rgba(0, 0, 0, 0.7));
    z-index: 1;
}

.job-card-category {
    position: absolute;
    top: 1rem;
    left: 1rem;
    background: rgba(255, 255, 255, 0.9);
    padding: 0.3rem 0.8rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
    color: #1D2D44;
    z-index: 2;
    transform: translateZ(15px);
}

[data-bs-theme="dark"] .job-card-category {
    background: rgba(30, 35, 45, 0.9);
    color: #A4C2E5;
}

.job-card-content {
    padding: 1.5rem;
    flex-grow: 1;
    display: flex;
    flex-direction: column;
    transform: translateZ(5px);
}

.job-card-title {
    font-size: 1.3rem;
    font-weight: 600;
    margin-bottom: 0.8rem;
    color: #1D2D44;
}

.job-card-description {
    font-size: 0.95rem;
    color: #748CAB;
    margin-bottom: 1.5rem;
    flex-grow: 1;
}

.job-card-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 0.8rem;
    margin-bottom: 1.5rem;
}

.job-meta-item {
    display: flex;
    align-items: center;
    font-size: 0.85rem;
    color: #3E5C76;
}

.job-meta-item i {
    margin-right: 0.4rem;
    color: #5D8BB3;
}

.job-card-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 1rem;
    border-top: 1px solid rgba(0, 0, 0, 0.05);
}

.job-card-salary {
    font-weight: 700;
    font-size: 1.1rem;
    color: #3E5C76;
}

.job-card-apply {
    padding: 0.5rem 1.2rem;
    background: linear-gradient(90deg, #5D8BB3, #8FB3DE);
    color: white;
    border: none;
    border-radius: 6px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    transform: translateZ(5px);
}

.job-card-apply:hover {
    background: linear-gradient(90deg, #8FB3DE, #5D8BB3);
    transform: translateY(-2px) translateZ(10px);
    box-shadow: 0 5px 15px rgba(93, 139, 179, 0.3);
}

.post-job-btn {
    padding: 0.75rem 2rem;
    font-size: 1.1rem;
    box-shadow: 0 5px 15px rgba(93, 139, 179, 0.25);
    transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

.post-job-btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(93, 139, 179, 0.4);
}

/* Dark mode adjustments */
[data-bs-theme="dark"] .job-card {
    background: rgba(31, 32, 40, 0.8);
    border-color: rgba(70, 90, 120, 0.2);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
}

[data-bs-theme="dark"] .job-card:hover {
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3), 0 10px 10px rgba(0, 0, 0, 0.2);
}

[data-bs-theme="dark"] .job-card::before {
    background: linear-gradient(135deg, rgba(255,255,255,0.1) 0%, transparent 50%);
}

[data-bs-theme="dark"] .job-card-title {
    color: #FFFFFF;
}

[data-bs-theme="dark"] .job-card-description {
    color: #A4C2E5;
}

[data-bs-theme="dark"] .job-card-salary {
    color: #8FB3DE;
}

[data-bs-theme="dark"] .job-meta-item {
    color: #A4C2E5;
}

[data-bs-theme="dark"] .job-meta-item i {
    color: #8FB3DE;
}

[data-bs-theme="dark"] .job-card-footer {
    border-top: 1px solid rgba(70, 90, 120, 0.2);
}

/* Corner decorations matching other sections */
.job-corner-decoration {
    position: absolute;
    width: 300px;
    height: 300px;
    z-index: 1;
    opacity: 0.1;
    pointer-events: none;
}

.job-corner-decoration-1 {
    top: -100px;
    right: -100px;
    background: linear-gradient(135deg, transparent, rgba(93, 139, 179, 0.3));
    transform: rotate(45deg);
    border-radius: 50px;
}

.job-corner-decoration-2 {
    bottom: -100px;
    left: -100px;
    background: linear-gradient(45deg, rgba(93, 139, 179, 0.3), transparent);
    transform: rotate(45deg);
    border-radius: 50px;
}

/* Responsive adjustments */
@media (max-width: 1200px) {
    .job-offers-section {
        padding: 5rem 0;
    }
    
    .job-offers-container {
        padding: 0 1.5rem;
    }
    
    .job-offers-title {
        font-size: 2.2rem;
    }
}

@media (max-width: 992px) {
    .job-offers-grid {
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    }
}

@media (max-width: 768px) {
    .job-offers-section {
        min-height: auto;
        padding: 5rem 0;
    }
    
    .job-offers-title {
        font-size: 2rem;
    }
    
    .job-offers-subtitle {
        font-size: 1.1rem;
    }
    
    .job-offers-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 480px) {
    .job-offers-section {
        padding: 4rem 0;
    }
    
    .job-offers-title {
        font-size: 1.8rem;
    }
    
    .job-offers-subtitle {
        font-size: 1rem;
    }
}
</style>

<section class="job-offers-section section-animate" id="job-offers">
    <div class="job-corner-decoration job-corner-decoration-1"></div>
    <div class="job-offers-container">
        <div class="job-offers-header">
            <h2 class="job-offers-title">Current Job Opportunities</h2>
            <p class="job-offers-subtitle">Join our team of talented professionals and work on exciting projects</p>
            <div class="text-center mt-4">
                <a href="/web/components/home/offers/offers.php" class="btn job-card-apply post-job-btn" onclick="window.location.href='/web/components/home/offers/offers.php'; return false;">Post a Job</a>
            </div>
        </div>
        
        <div class="job-offers-grid">
            <?php if (empty($jobs)): ?>
                <div class="text-center w-100 py-5">
                    <i class="bi bi-briefcase-x display-4 text-muted"></i>
                    <p class="mt-3 text-muted">No job offers available at the moment.</p>
                </div>
            <?php else: ?>
                <?php foreach ($jobs as $job): ?>
                    <div class="job-card stagger-item">
                        <div class="job-card-img" style="background-image: url('<?php echo htmlspecialchars($job['image_url'] ?? 'https://images.unsplash.com/photo-1522071820081-009f0129c71c?ixlib=rb-1.2.1&auto=format&fit=crop&w=600&q=80'); ?>')">
                            <span class="job-card-category"><?php echo htmlspecialchars($job['category_name']); ?></span>
                        </div>
                        <div class="job-card-content">
                            <h3 class="job-card-title"><?php echo htmlspecialchars($job['title']); ?></h3>
                            <p class="job-card-description"><?php echo htmlspecialchars(substr($job['description'], 0, 150)) . '...'; ?></p>
                            
                            <div class="job-card-meta">
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
                            
                            <div class="job-card-footer">
                                <span class="job-card-salary">Annual Salary</span>
                                <button class="job-card-apply" data-job-id="<?php echo $job['job_id']; ?>">
                                    Apply Now
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    <div class="job-corner-decoration job-corner-decoration-2"></div>
</section>

<!-- Include the Schedule Interview Modal -->
<?php
// Session is already started at the beginning of this file
include_once(__DIR__ . '/home/offers/schedule-interview-modal.php');
?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add stagger animation styles to items
    document.querySelectorAll('.job-offers-section .stagger-item').forEach((item, index) => {
        item.style.opacity = '0';
        item.style.transform = 'translateY(30px)';
        item.style.transition = 'opacity 0.6s ease-out, transform 0.6s cubic-bezier(0.175, 0.885, 0.32, 1.275)';
        item.style.transitionDelay = `${index * 0.1}s`;
    });
    
    // Observe the section for intersection
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
                
                // Handle stagger animations for child elements
                const staggerItems = entry.target.querySelectorAll('.stagger-item');
                staggerItems.forEach((item, index) => {
                    setTimeout(() => {
                        item.style.opacity = '1';
                        item.style.transform = 'translateY(0)';
                    }, index * 100);
                });
            }
        });
    }, {
        threshold: 0.15,
        rootMargin: '0px 0px -100px 0px'
    });
    
    // Observe the job offers section
    const jobSection = document.querySelector('.job-offers-section');
    if (jobSection) {
        observer.observe(jobSection);
    }
});

// Include the Schedule Interview JS
document.write('<script src="/web/components/home/offers/schedule-interview.js"><\/script>');

// Add event listener for the Post a Job button
document.addEventListener('DOMContentLoaded', function() {
    const postJobBtn = document.querySelector('.post-job-btn');
    if (postJobBtn) {
        postJobBtn.addEventListener('click', function(e) {
            e.preventDefault();
            window.location.href = '/web/components/home/offers/offers.php';
        });
    }
});
</script>