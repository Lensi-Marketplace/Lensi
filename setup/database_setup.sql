-- Create the database if it doesn't exist
CREATE DATABASE IF NOT EXISTS lensi_db;

-- Use the lensi_db database
USE lensi_db;

-- Create users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    user_type ENUM('freelancer', 'employer', 'admin') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    profile_image VARCHAR(255) DEFAULT NULL,
    is_verified BOOLEAN DEFAULT FALSE,
    verification_token VARCHAR(100) DEFAULT NULL,
    reset_token VARCHAR(100) DEFAULT NULL,
    reset_token_expires_at TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create an index on the email column for faster lookups
CREATE INDEX IF NOT EXISTS users_email_idx ON users(email);

-- Create user_profiles table
CREATE TABLE IF NOT EXISTS user_profiles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_email VARCHAR(100) NOT NULL,
    bio TEXT,
    skills TEXT,
    hourly_rate DECIMAL(10,2) DEFAULT 0,
    location VARCHAR(100),
    website VARCHAR(255),
    profile_image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_email) REFERENCES users(email) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create an index on the user_email column for faster lookups
CREATE INDEX IF NOT EXISTS user_profiles_email_idx ON user_profiles(user_email);

-- Create user_settings table
CREATE TABLE IF NOT EXISTS user_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_email VARCHAR(100) NOT NULL,
    email_notifications TINYINT(1) DEFAULT 1,
    project_notifications TINYINT(1) DEFAULT 1,
    message_notifications TINYINT(1) DEFAULT 1,
    marketing_emails TINYINT(1) DEFAULT 0,
    profile_visibility ENUM('public', 'private', 'contacts') DEFAULT 'public',
    show_earnings TINYINT(1) DEFAULT 0,
    show_projects TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_email) REFERENCES users(email) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create an index on the user_email column for faster lookups in user_settings
CREATE INDEX IF NOT EXISTS user_settings_email_idx ON user_settings(user_email);

-- Create companies table first (before job_offers)
CREATE TABLE IF NOT EXISTS companies (
    company_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    website VARCHAR(255),
    logo_url VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create job categories table
CREATE TABLE IF NOT EXISTS job_categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create locations table
CREATE TABLE IF NOT EXISTS locations (
    location_id INT AUTO_INCREMENT PRIMARY KEY,
    city VARCHAR(100) NOT NULL,
    country VARCHAR(100) NOT NULL,
    is_remote BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create job offers table with company_id
CREATE TABLE IF NOT EXISTS job_offers (
    job_id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    category_id INT NOT NULL,
    salary_min DECIMAL(10,2) NOT NULL,
    salary_max DECIMAL(10,2) NOT NULL,
    location_id INT NOT NULL,
    company_id INT,
    image_url VARCHAR(255),
    slug VARCHAR(150) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES job_categories(category_id),
    FOREIGN KEY (location_id) REFERENCES locations(location_id),
    FOREIGN KEY (company_id) REFERENCES companies(company_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create blogs table
CREATE TABLE IF NOT EXISTS blogs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    user_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    status ENUM('published', 'draft', 'archived') DEFAULT 'published',
    thumbnail_url VARCHAR(255),
    views INT DEFAULT 0,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Create blog_comments table
CREATE TABLE IF NOT EXISTS blog_comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    blog_id INT NOT NULL,
    user_id INT NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    parent_id INT DEFAULT NULL,
    status ENUM('approved', 'pending', 'spam') DEFAULT 'approved',
    FOREIGN KEY (blog_id) REFERENCES blogs(id),
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (parent_id) REFERENCES blog_comments(id)
);

-- Create job applications table
CREATE TABLE IF NOT EXISTS job_applications (
    application_id INT AUTO_INCREMENT PRIMARY KEY,
    job_id INT NOT NULL,
    user_email VARCHAR(100) NOT NULL,
    status ENUM('pending', 'reviewing', 'accepted', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (job_id) REFERENCES job_offers(job_id),
    FOREIGN KEY (user_email) REFERENCES users(email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create interviews table
CREATE TABLE IF NOT EXISTS interviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    candidate_name VARCHAR(100) NOT NULL,
    position_title VARCHAR(100) NOT NULL,
    interview_date DATETIME NOT NULL,
    interviewer VARCHAR(100) NOT NULL,
    location VARCHAR(100) NOT NULL,
    status ENUM('Scheduled', 'Completed', 'Cancelled') DEFAULT 'Scheduled',
    feedback TEXT,
    cv_url VARCHAR(255),
    user_email VARCHAR(100) NOT NULL,
    job_offer_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_email) REFERENCES users(email) ON DELETE CASCADE,
    FOREIGN KEY (job_offer_id) REFERENCES job_offers(job_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Add company_id to job_offers table if it doesn't exist
ALTER TABLE job_offers
ADD COLUMN IF NOT EXISTS company_id INT,
ADD FOREIGN KEY (company_id) REFERENCES companies(company_id) ON DELETE SET NULL;

-- Create indices for better performance
CREATE INDEX IF NOT EXISTS job_offers_category_idx ON job_offers(category_id);
CREATE INDEX IF NOT EXISTS job_offers_location_idx ON job_offers(location_id);
CREATE INDEX IF NOT EXISTS job_applications_job_idx ON job_applications(job_id);
CREATE INDEX IF NOT EXISTS job_applications_user_idx ON job_applications(user_email);
CREATE INDEX IF NOT EXISTS interviews_user_email_idx ON interviews(user_email);
CREATE INDEX IF NOT EXISTS interviews_job_offer_idx ON interviews(job_offer_id);