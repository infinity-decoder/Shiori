-- Add BLOB storage columns for student photos
-- This ensures images survive database backup/restore and migration
-- Note: If columns already exist, you'll get an error - that's OK, just ignore it

ALTER TABLE students 
  ADD COLUMN photo_blob LONGBLOB NULL COMMENT 'Full image as binary data' AFTER photo_path,
  ADD COLUMN photo_mime VARCHAR(50) NULL COMMENT 'Image MIME type (image/jpeg, etc)' AFTER photo_blob,
  ADD COLUMN photo_hash VARCHAR(64) NULL COMMENT 'SHA256 hash for integrity verification' AFTER photo_mime;

-- Index for faster queries when checking image existence
CREATE INDEX idx_photo_hash ON students(photo_hash);
