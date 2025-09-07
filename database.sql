-- Portfolio Website Database Setup
-- Run this SQL script to create the database and tables

CREATE DATABASE IF NOT EXISTS db_abddcf_meet123;
USE db_abddcf_meet123;

-- Contact form submissions table
CREATE TABLE contacts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    subject VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    status ENUM('new', 'read', 'replied') DEFAULT 'new',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Portfolio items table (for dynamic portfolio)
CREATE TABLE portfolio_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    technologies VARCHAR(500),
    image_url VARCHAR(500),
    project_url VARCHAR(500),
    featured BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Admin users table
CREATE TABLE admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert sample portfolio items
INSERT INTO portfolio_items (title, description, technologies, image_url, project_url, featured) VALUES
('E-Commerce Platform', 'A full-featured online store with payment integration and admin panel.', 'PHP,MySQL,Bootstrap', '', '', 1),
('Blog Management System', 'A content management system for bloggers with rich text editor.', 'PHP,JavaScript,MySQL', '', '', 1),
('Task Management App', 'A productivity tool for teams to manage projects and deadlines.', 'React,PHP API,MySQL', '', '', 1),
('Restaurant Website', 'A modern restaurant website with online ordering system.', 'PHP,MySQL,Bootstrap', '', '', 0),
('School Management System', 'Complete school administration and student management system.', 'PHP,MySQL,JavaScript', '', '', 0),
('Real Estate Portal', 'Property listing and management portal with advanced search.', 'PHP,MySQL,Bootstrap,JavaScript', '', '', 0);

-- Create default admin user
-- Username: admin, Password: admin123 (change after first login)
INSERT INTO admin_users (username, password, email) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@portfolio.com');

-- Sample contact entries (optional)
INSERT INTO contacts (name, email, subject, message, status) VALUES
('John Doe', 'john@example.com', 'Website Development Inquiry', 'Hi Alex, I am interested in discussing a website project for my business. Could we schedule a meeting?', 'new'),
('Sarah Smith', 'sarah@example.com', 'Collaboration Opportunity', 'Hello! I saw your portfolio and would love to collaborate on a React project. Let me know if you are interested.', 'read'),
('Mike Johnson', 'mike@techcorp.com', 'Job Opportunity', 'We have an exciting opportunity for a PHP developer at our company. Are you open to discussing this role?', 'new');

-- Create indexes for better performance
CREATE INDEX idx_contacts_status ON contacts(status);
CREATE INDEX idx_contacts_created_at ON contacts(created_at);
CREATE INDEX idx_portfolio_featured ON portfolio_items(featured);

-- Display table information
SHOW TABLES;
SELECT 'Database setup completed successfully!' AS message;