-- SQL script to create the events table for event management

CREATE TABLE IF NOT EXISTS events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    date DATE NOT NULL,
    time TIME NOT NULL,
    location VARCHAR(255) NOT NULL,
    image VARCHAR(255),
    category VARCHAR(100) NOT NULL,
    visibility ENUM('public', 'private', 'invite-only') NOT NULL DEFAULT 'public',
    recurring ENUM('no', 'daily', 'weekly', 'monthly') NOT NULL DEFAULT 'no',
    agenda TEXT,
    speakers TEXT,
    sponsors TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
