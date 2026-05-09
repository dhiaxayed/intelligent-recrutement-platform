CREATE DATABASE IF NOT EXISTS recruitment_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE recruitment_system;

-- Core users table. Authentication owns these fields; candidate profiles reuse name/email from here.
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('candidate', 'recruiter') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- One profile per candidate. The UNIQUE candidate_id enforces that rule at database level.
CREATE TABLE IF NOT EXISTS candidate_profiles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    candidate_id INT NOT NULL UNIQUE,
    phone VARCHAR(30) NOT NULL,
    linkedin VARCHAR(255) NOT NULL,
    github VARCHAR(255) NULL,
    cv_path VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (candidate_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Recruiter-owned jobs. Person 3 will implement create/update behavior for this table.
CREATE TABLE IF NOT EXISTS job_profiles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    recruiter_id INT NOT NULL,
    title VARCHAR(150) NOT NULL,
    company VARCHAR(150) NOT NULL,
    location VARCHAR(150) NOT NULL,
    description TEXT NOT NULL,
    requirements TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (recruiter_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Applications connect candidates to jobs. UNIQUE(candidate_id, job_id) prevents duplicates.
CREATE TABLE IF NOT EXISTS applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    candidate_id INT NOT NULL,
    job_id INT NOT NULL,
    status ENUM('pending', 'accepted', 'rejected') DEFAULT 'pending',
    applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (candidate_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (job_id) REFERENCES job_profiles(id) ON DELETE CASCADE,
    UNIQUE (candidate_id, job_id)
) ENGINE=InnoDB;

-- Sample recruiter used only to make candidate/jobs.php testable before Person 3 finishes recruiter pages.
INSERT INTO users (first_name, last_name, email, password, role)
VALUES ('Demo', 'Recruiter', 'recruiter@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC.BGYFB2A/AmyeF5Z2i', 'recruiter')
ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id);

SET @sample_recruiter_id = LAST_INSERT_ID();

-- Insert sample jobs only when they are missing, so rerunning this file does not flood the jobs page.
INSERT INTO job_profiles (recruiter_id, title, company, location, description, requirements)
SELECT @sample_recruiter_id, 'Junior PHP Developer', 'BrightWorks Labs', 'Lagos, Nigeria', 'Build and maintain internal recruitment tools using PHP, MySQL, HTML, CSS, and JavaScript.', 'Good PHP fundamentals, SQL knowledge, and ability to work with existing code.'
WHERE NOT EXISTS (
    SELECT 1 FROM job_profiles WHERE recruiter_id = @sample_recruiter_id AND title = 'Junior PHP Developer' AND company = 'BrightWorks Labs'
);

INSERT INTO job_profiles (recruiter_id, title, company, location, description, requirements)
SELECT @sample_recruiter_id, 'Frontend Support Intern', 'TalentBridge', 'Remote', 'Support frontend updates for candidate and recruiter dashboards using native JavaScript and CSS.', 'Basic HTML, CSS, JavaScript, and attention to detail.'
WHERE NOT EXISTS (
    SELECT 1 FROM job_profiles WHERE recruiter_id = @sample_recruiter_id AND title = 'Frontend Support Intern' AND company = 'TalentBridge'
);

INSERT INTO job_profiles (recruiter_id, title, company, location, description, requirements)
SELECT @sample_recruiter_id, 'Database Assistant', 'PeopleOps Systems', 'Abuja, Nigeria', 'Assist with MySQL data checks, reporting queries, and application data quality tasks.', 'Understanding of relational databases and prepared statement concepts.'
WHERE NOT EXISTS (
    SELECT 1 FROM job_profiles WHERE recruiter_id = @sample_recruiter_id AND title = 'Database Assistant' AND company = 'PeopleOps Systems'
);
