-- Add is_active column to all lookup tables
-- 1. Classes
ALTER TABLE classes ADD COLUMN IF NOT EXISTS is_active TINYINT(1) DEFAULT 1;

-- 2. Sections
ALTER TABLE sections ADD COLUMN IF NOT EXISTS is_active TINYINT(1) DEFAULT 1;

-- 3. Categories
ALTER TABLE categories ADD COLUMN IF NOT EXISTS is_active TINYINT(1) DEFAULT 1;

-- 4. Family Categories
ALTER TABLE family_categories ADD COLUMN IF NOT EXISTS is_active TINYINT(1) DEFAULT 1;
