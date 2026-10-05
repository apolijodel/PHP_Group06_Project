-- ---------------------------------------------------------------------
-- Categories for shapes and designs.
--
-- Why: an uploaded PNG is just an image. Nothing about the file says whether
-- it is a usable bookmark silhouette or a holiday pattern, and no amount of
-- image analysis would say so reliably. Asking the person uploading it to
-- classify the thing, and asking an administrator to confirm that before it
-- goes live, is the part that actually works.
--
-- Additive and idempotent. Existing rows get 'Uncategorized' so nothing is
-- lost and no foreign key is touched.
-- ---------------------------------------------------------------------
USE markme_db;

ALTER TABLE shapes
    ADD COLUMN IF NOT EXISTS category VARCHAR(40) NOT NULL DEFAULT 'Uncategorized' AFTER name;

ALTER TABLE designs
    ADD COLUMN IF NOT EXISTS category VARCHAR(40) NOT NULL DEFAULT 'Uncategorized' AFTER name;

CREATE INDEX IF NOT EXISTS idx_shapes_category  ON shapes (category);
CREATE INDEX IF NOT EXISTS idx_designs_category ON designs (category);
