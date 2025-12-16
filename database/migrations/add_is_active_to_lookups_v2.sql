-- 1. Classes
ALTER TABLE classes ADD COLUMN is_active TINYINT(1) DEFAULT 1;

-- 2. Sections
ALTER TABLE sections ADD COLUMN is_active TINYINT(1) DEFAULT 1;

-- 3. Categories
ALTER TABLE categories ADD COLUMN is_active TINYINT(1) DEFAULT 1;

-- 4. Family Categories
ALTER TABLE family_categories ADD COLUMN is_active TINYINT(1) DEFAULT 1;
