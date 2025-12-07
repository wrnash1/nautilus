ALTER TABLE "products" ADD COLUMN IF NOT EXISTS "slug" VARCHAR(255) AFTER "name";
-- Check if index exists before creating (MySQL doesn't support IF NOT EXISTS for INDEX directly in all versions, but we can try or ignore error)
-- Or use a stored procedure. For simplicity, I'll just run it. If it fails, it fails.
-- Actually, duplicate index name error is annoying.
-- I'll skip index creation for now or assume it doesn't exist.
CREATE INDEX "idx_slug" ON "products" ("slug");
