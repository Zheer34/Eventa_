# CV Upload Feature for Event Organizers

## Overview
This feature allows event organizers to upload their CV (Curriculum Vitae) during registration. Only PDF files are accepted for security and consistency reasons.

## Implementation Details

### Database Changes
- Added `cv_path` column to the `users` table
- Run the SQL script `add_cv_field.sql` to update your database:
```sql
ALTER TABLE users
ADD COLUMN cv_path VARCHAR(255) DEFAULT NULL COMMENT 'Path to uploaded CV file for event organizers';
```

### Files Modified

1. **SignUp_LogIn_Form.php**
   - Added file upload input for CV (only visible for event organizer role)
   - Added `enctype="multipart/form-data"` to the form

2. **register.php**
   - Added CV file upload handling
   - Validates file type (PDF only) and size (max 5MB)
   - Stores uploaded files in `uploads/cv/` directory
   - Generates unique filenames to prevent conflicts

3. **view_organizer.php**
   - Added CV display for admin review
   - Shows "View CV" button if CV is uploaded

4. **SignUp_LogIn_Form.css**
   - Added styling for file input fields

### New Files Created

1. **cv_viewer.php**
   - Secure CV viewer for admins
   - Validates file paths and admin permissions
   - Serves PDF files for viewing

2. **add_cv_field.sql**
   - SQL script to add the CV field to the database

3. **uploads/cv/** directory
   - Storage location for uploaded CV files

## Security Features

- **File Type Validation**: Only PDF files are allowed
- **File Size Limit**: Maximum 5MB per file
- **Secure File Storage**: Files stored outside web root with unique names
- **Admin-Only Access**: Only admins can view uploaded CVs
- **Path Validation**: Prevents directory traversal attacks

## Usage

### For Event Organizers:
1. Register with role "Event Organizer"
2. Fill in additional fields including CV upload
3. Only PDF files under 5MB are accepted
4. CV upload is optional but recommended

### For Admins:
1. Go to "Verify Event Organizers" page
2. View organizer details
3. Click "View CV" to review uploaded CVs
4. Verify organizers after reviewing their information

## File Naming Convention
Uploaded CV files are renamed using the pattern: `{unique_id}_cv_{username}.pdf`

This ensures no file conflicts and maintains security while allowing easy identification.
