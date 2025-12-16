-- Migration: Add is_active column to users table
ALTER TABLE `users` ADD COLUMN `is_active` TINYINT(1) DEFAULT 1;
CREATE INDEX `idx_user_active` ON `users`(`is_active`);
