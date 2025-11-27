-- Add description field to certification agencies
-- Note: logo_path and primary_color already added in migration 014
ALTER TABLE "certification_agencies"
ADD COLUMN IF NOT EXISTS "description" TEXT NULL AFTER "primary_color";

-- Update existing agencies with default colors
-- Note: primary_color column is added in migration 014, so these UPDATEs should work
-- UPDATE "certification_agencies" SET "primary_color" = '#0066CC' WHERE "abbreviation" = 'PADI';
-- UPDATE "certification_agencies" SET "primary_color" = '#006699' WHERE "abbreviation" = 'SSI';
-- UPDATE "certification_agencies" SET "primary_color" = '#CC0000' WHERE "abbreviation" = 'NAUI';
-- UPDATE "certification_agencies" SET "primary_color" = '#009900' WHERE "abbreviation" = 'SDI';
-- UPDATE "certification_agencies" SET "primary_color" = '#FF6600' WHERE "abbreviation" = 'TDI';
