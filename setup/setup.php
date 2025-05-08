<?php
/**
 * Database Setup Script
 * This script executes the database setup SQL commands
 */

// Get database configuration
require_once __DIR__ . '/../config/database.php';

try {
    // Disable foreign key checks temporarily
    $pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
    
    // Drop tables in correct order (reverse of creation) to avoid foreign key constraints
    $dropTables = [
        "DROP TABLE IF EXISTS interviews",
        "DROP TABLE IF EXISTS job_applications",
        "DROP TABLE IF EXISTS job_offers",
        "DROP TABLE IF EXISTS job_categories",
        "DROP TABLE IF EXISTS locations",
        "DROP TABLE IF EXISTS companies",  // Add companies table to drop list
        "DROP TABLE IF EXISTS blog_comments",
        "DROP TABLE IF EXISTS blogs",
        "DROP TABLE IF EXISTS user_settings",
        "DROP TABLE IF EXISTS user_profiles",
        "DROP TABLE IF EXISTS users"
    ];
    
    foreach ($dropTables as $dropStatement) {
        $pdo->exec($dropStatement);
    }
    
    echo "Existing tables dropped successfully!\n";
    
    // Read the SQL file
    $sql = file_get_contents(__DIR__ . '/database_setup.sql');
    
    // Split into individual statements
    $statements = array_filter(
        array_map(
            'trim',
            explode(';', $sql)
        )
    );
    
    // Execute each statement
    foreach ($statements as $statement) {
        if (!empty($statement)) {
            $pdo->exec($statement);
        }
    }
    
    echo "Database tables created successfully!\n";
    
    // Re-enable foreign key checks before inserting data
    $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
    
    // Create test user first (needed for foreign key constraints)
    $pdo->exec("INSERT INTO users (first_name, last_name, email, password, user_type) VALUES 
               ('Test', 'User', 'test@example.com', '" . password_hash('password123', PASSWORD_DEFAULT) . "', 'employer')");
    
    // Insert sample data for testing
    $sampleData = [
        // Sample companies
        "INSERT INTO companies (name, description, website) VALUES 
        ('TechCorp Inc.', 'Leading technology solutions provider', 'https://techcorp.example.com'),
        ('InnovateSoft', 'Innovative software development company', 'https://innovatesoft.example.com'),
        ('DesignHub', 'Creative design studio', 'https://designhub.example.com'),
        ('DataTech', 'Data science and analytics company', 'https://datatech.example.com')",

        // Sample job categories
        "INSERT INTO job_categories (name) VALUES 
        ('Web Development'),
        ('Mobile Development'),
        ('UI/UX Design'),
        ('Data Science'),
        ('DevOps Engineering')",
        
        // Sample locations
        "INSERT INTO locations (city, country, is_remote) VALUES 
        ('New York', 'USA', false),
        ('London', 'UK', false),
        ('Berlin', 'Germany', false),
        ('Remote', 'Worldwide', true),
        ('Paris', 'France', false)",
        
        // Sample job offers with company_id
        "INSERT INTO job_offers (title, description, category_id, salary_min, salary_max, location_id, company_id, image_url, slug) VALUES 
        ('Senior Full Stack Developer', 'We are looking for an experienced Full Stack Developer...', 1, 80000, 120000, 1, 1, 'https://example.com/images/job1.jpg', 'senior-full-stack-developer'),
        ('Mobile App Developer', 'Join our mobile development team...', 2, 70000, 100000, 2, 2, 'https://example.com/images/job2.jpg', 'mobile-app-developer'),
        ('UX/UI Designer', 'Design beautiful and intuitive interfaces...', 3, 60000, 90000, 3, 3, 'https://example.com/images/job3.jpg', 'ux-ui-designer'),
        ('Remote Data Scientist', 'Work from anywhere as our lead data scientist...', 4, 90000, 140000, 4, 4, 'https://example.com/images/job4.jpg', 'remote-data-scientist')"
    ];
    
    // Execute sample data insertions
    foreach ($sampleData as $statement) {
        $pdo->exec($statement);
        echo "Sample data inserted successfully.\n";
    }
    
    echo "Setup completed successfully with sample data!\n";
    
} catch (PDOException $e) {
    // Re-enable foreign key checks even if there's an error
    $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
    die("Database setup failed: " . $e->getMessage() . "\n");
}