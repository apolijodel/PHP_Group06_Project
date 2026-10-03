-- ---------------------------------------------------------------------
-- Approval workflow for shapes and designs.
--
-- Additive only: no column is dropped or renamed, and no existing row
-- changes meaning. Everything already in the catalog is marked 'approved',
-- because it is already live and customers are already choosing from it —
-- defaulting it to 'pending' would silently empty the design studio.
--
-- New uploads get 'pending' from the column default and stay invisible to
-- customers until an administrator approves them.
--
-- Safe to run more than once.
-- ---------------------------------------------------------------------
USE markme_db;

ALTER TABLE shapes
    ADD COLUMN IF NOT EXISTS status ENUM('pending','approved','rejected')
        NOT NULL DEFAULT 'pending' AFTER image_path,
    ADD COLUMN IF NOT EXISTS uploaded_by INT UNSIGNED NULL AFTER status,
    ADD COLUMN IF NOT EXISTS reviewed_by INT UNSIGNED NULL AFTER uploaded_by,
    ADD COLUMN IF NOT EXISTS reviewed_at TIMESTAMP NULL AFTER reviewed_by,
    ADD COLUMN IF NOT EXISTS review_note VARCHAR(255) NULL AFTER reviewed_at,
    -- Whether a malware scanner actually ran on the file, so the record can
    -- never imply a check that did not happen.
    ADD COLUMN IF NOT EXISTS virus_scanned TINYINT(1) NOT NULL DEFAULT 0 AFTER review_note;

ALTER TABLE designs
    ADD COLUMN IF NOT EXISTS status ENUM('pending','approved','rejected')
        NOT NULL DEFAULT 'pending' AFTER image_path,
    ADD COLUMN IF NOT EXISTS uploaded_by INT UNSIGNED NULL AFTER status,
    ADD COLUMN IF NOT EXISTS reviewed_by INT UNSIGNED NULL AFTER uploaded_by,
    ADD COLUMN IF NOT EXISTS reviewed_at TIMESTAMP NULL AFTER reviewed_by,
    ADD COLUMN IF NOT EXISTS review_note VARCHAR(255) NULL AFTER reviewed_at,
    ADD COLUMN IF NOT EXISTS virus_scanned TINYINT(1) NOT NULL DEFAULT 0 AFTER review_note;

-- Grandfather the existing catalog. Only rows that predate this migration are
-- touched: anything uploaded afterwards has an uploaded_by and is left alone.
UPDATE shapes  SET status = 'approved' WHERE uploaded_by IS NULL AND reviewed_at IS NULL;
UPDATE designs SET status = 'approved' WHERE uploaded_by IS NULL AND reviewed_at IS NULL;

-- Customers filter on status, administrators filter on it too.
CREATE INDEX IF NOT EXISTS idx_shapes_status  ON shapes (status);
CREATE INDEX IF NOT EXISTS idx_designs_status ON designs (status);

ALTER TABLE shapes
    ADD CONSTRAINT fk_shapes_uploader FOREIGN KEY IF NOT EXISTS (uploaded_by)
        REFERENCES users(user_id) ON DELETE SET NULL ON UPDATE CASCADE,
    ADD CONSTRAINT fk_shapes_reviewer FOREIGN KEY IF NOT EXISTS (reviewed_by)
        REFERENCES users(user_id) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE designs
    ADD CONSTRAINT fk_designs_uploader FOREIGN KEY IF NOT EXISTS (uploaded_by)
        REFERENCES users(user_id) ON DELETE SET NULL ON UPDATE CASCADE,
    ADD CONSTRAINT fk_designs_reviewer FOREIGN KEY IF NOT EXISTS (reviewed_by)
        REFERENCES users(user_id) ON DELETE SET NULL ON UPDATE CASCADE;
