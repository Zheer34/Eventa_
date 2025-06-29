-- SQL script to add CV field to users table for event organizers

ALTER TABLE users
ADD COLUMN cv_path VARCHAR(255) DEFAULT NULL COMMENT 'Path to uploaded CV file for event organizers';
