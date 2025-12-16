-- Migration: Convert Admin to Super Admin
-- 1. Create a backup of the users table for safety
CREATE TABLE IF NOT EXISTS users_backup AS SELECT * FROM users;

-- 2. Modify the role enum to include 'super_admin'
-- Valid roles: super_admin, admin, staff, viewer
ALTER TABLE users MODIFY COLUMN role ENUM('super_admin', 'admin', 'staff', 'viewer') DEFAULT 'viewer';

-- 3. Promote existing admins to super_admin
UPDATE users SET role = 'super_admin' WHERE role = 'admin';

-- 4. (Optional) If you want to create a new specific 'admin' role later, you can, 
-- but for now potential future 'admin' users will need to be created manually or via a new UI.
