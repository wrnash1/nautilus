-- Seed data for certification agencies
-- This file populates the certification_agencies table with the major dive certification organizations

INSERT INTO certification_agencies (name, abbreviation, website_url, contact_email, primary_color, secondary_color, is_active, display_order, created_at) VALUES
-- PADI (Professional Association of Diving Instructors)
('Professional Association of Diving Instructors', 'PADI', 'https://www.padi.com', 'support@padi.com', '#0066CC', '#FFFFFF', 1, 1, NOW()),

-- SSI (Scuba Schools International)
('Scuba Schools International', 'SSI', 'https://www.divessi.com', 'info@divessi.com', '#CC0000', '#FFFFFF', 1, 2, NOW()),

-- NAUI (National Association of Underwater Instructors)
('National Association of Underwater Instructors', 'NAUI', 'https://www.naui.org', 'info@naui.org', '#000080', '#FFCC00', 1, 3, NOW()),

-- SDI (Scuba Diving International)
('Scuba Diving International', 'SDI', 'https://www.tdisdi.com', 'worldhq@tdisdi.com', '#000000', '#FF0000', 1, 4, NOW()),

-- TDI (Technical Diving International)
('Technical Diving International', 'TDI', 'https://www.tdisdi.com', 'worldhq@tdisdi.com', '#000000', '#0066FF', 1, 5, NOW()),

-- ERDI (Emergency Response Diving International)
('Emergency Response Diving International', 'ERDI', 'https://www.tdisdi.com', 'worldhq@tdisdi.com', '#FF0000', '#000000', 1, 6, NOW()),

-- PFI (Performance Freediving International)
('Performance Freediving International', 'PFI', 'https://www.performancefreediving.com', 'info@performancefreediving.com', '#0099FF', '#FFFFFF', 1, 7, NOW()),

-- BSAC (British Sub-Aqua Club)
('British Sub-Aqua Club', 'BSAC', 'https://www.bsac.com', 'info@bsac.com', '#003366', '#FFCC00', 1, 8, NOW()),

-- CMAS (World Underwater Federation)
('World Underwater Federation', 'CMAS', 'https://www.cmas.org', 'info@cmas.org', '#0066CC', '#FFFFFF', 1, 9, NOW()),

-- GUE (Global Underwater Explorers)
('Global Underwater Explorers', 'GUE', 'https://www.gue.com', 'info@gue.com', '#003366', '#FFFFFF', 1, 10, NOW()),

-- IANTD (International Association of Nitrox and Technical Divers)
('International Association of Nitrox and Technical Divers', 'IANTD', 'https://www.iantd.com', 'info@iantd.com', '#000066', '#CCCCCC', 1, 11, NOW()),

-- ACUC (American Canadian Underwater Certifications)
('American Canadian Underwater Certifications', 'ACUC', 'https://www.acuc.es', 'info@acuc.es', '#0066CC', '#FFFFFF', 1, 12, NOW()),

-- IDA (International Diving Association)
('International Diving Association', 'IDA', 'https://www.ida-worldwide.com', 'info@ida-worldwide.com', '#003366', '#FFFFFF', 1, 13, NOW()),

-- PDIC (Professional Diving Instructors Corporation)
('Professional Diving Instructors Corporation', 'PDIC', 'https://www.pdic-intl.com', 'info@pdic-intl.com', '#003366', '#FFFFFF', 1, 14, NOW()),

-- RAID (Rebreather Association of International Divers)
('Rebreather Association of International Divers', 'RAID', 'https://www.raid-diving.org', 'info@raid-diving.org', '#FF0000', '#000000', 1, 15, NOW());

-- Sample certifications for PADI (most popular agency)
-- Get the agency ID first in a separate query, then insert certifications

-- PADI Certifications
INSERT INTO certifications (agency_id, name, code, level, description, min_age, max_depth_meters, prerequisites, created_at)
SELECT id, 'Open Water Diver', 'OWD', 1, 'Entry-level scuba certification', 10, 18, NULL, NOW() FROM certification_agencies WHERE abbreviation = 'PADI';

INSERT INTO certifications (agency_id, name, code, level, description, min_age, max_depth_meters, prerequisites, created_at)
SELECT id, 'Advanced Open Water Diver', 'AOWD', 2, 'Advanced recreational diving certification', 12, 30, 'Open Water Diver', NOW() FROM certification_agencies WHERE abbreviation = 'PADI';

INSERT INTO certifications (agency_id, name, code, level, description, min_age, max_depth_meters, prerequisites, created_at)
SELECT id, 'Rescue Diver', 'RD', 3, 'Rescue and emergency response training', 12, 30, 'Advanced Open Water Diver', NOW() FROM certification_agencies WHERE abbreviation = 'PADI';

INSERT INTO certifications (agency_id, name, code, level, description, min_age, max_depth_meters, prerequisites, created_at)
SELECT id, 'Divemaster', 'DM', 4, 'Professional-level dive leader', 18, 40, 'Rescue Diver', NOW() FROM certification_agencies WHERE abbreviation = 'PADI';

INSERT INTO certifications (agency_id, name, code, level, description, min_age, max_depth_meters, prerequisites, created_at)
SELECT id, 'Instructor Development Course', 'IDC', 5, 'Dive instructor certification', 18, 40, 'Divemaster', NOW() FROM certification_agencies WHERE abbreviation = 'PADI';

-- PADI Specialty Certifications
INSERT INTO certifications (agency_id, name, code, level, description, min_age, max_depth_meters, prerequisites, created_at)
SELECT id, 'Enriched Air (Nitrox) Diver', 'NITROX', 2, 'Use of enriched air nitrox', 12, 40, 'Open Water Diver', NOW() FROM certification_agencies WHERE abbreviation = 'PADI';

INSERT INTO certifications (agency_id, name, code, level, description, min_age, max_depth_meters, prerequisites, created_at)
SELECT id, 'Deep Diver', 'DEEP', 2, 'Deep diving to 40 meters', 15, 40, 'Advanced Open Water Diver', NOW() FROM certification_agencies WHERE abbreviation = 'PADI';

INSERT INTO certifications (agency_id, name, code, level, description, min_age, max_depth_meters, prerequisites, created_at)
SELECT id, 'Wreck Diver', 'WRECK', 2, 'Wreck diving specialty', 15, 30, 'Advanced Open Water Diver', NOW() FROM certification_agencies WHERE abbreviation = 'PADI';

INSERT INTO certifications (agency_id, name, code, level, description, min_age, max_depth_meters, prerequisites, created_at)
SELECT id, 'Night Diver', 'NIGHT', 2, 'Night diving specialty', 12, 18, 'Open Water Diver', NOW() FROM certification_agencies WHERE abbreviation = 'PADI';

INSERT INTO certifications (agency_id, name, code, level, description, min_age, max_depth_meters, prerequisites, created_at)
SELECT id, 'Underwater Navigator', 'NAV', 2, 'Navigation specialty', 10, 30, 'Open Water Diver', NOW() FROM certification_agencies WHERE abbreviation = 'PADI';

-- SSI Certifications
INSERT INTO certifications (agency_id, name, code, level, description, min_age, max_depth_meters, prerequisites, created_at)
SELECT id, 'Open Water Diver', 'OWD', 1, 'Entry-level scuba certification', 10, 18, NULL, NOW() FROM certification_agencies WHERE abbreviation = 'SSI';

INSERT INTO certifications (agency_id, name, code, level, description, min_age, max_depth_meters, prerequisites, created_at)
SELECT id, 'Advanced Adventurer', 'AA', 2, 'Advanced recreational diving', 12, 30, 'Open Water Diver', NOW() FROM certification_agencies WHERE abbreviation = 'SSI';

INSERT INTO certifications (agency_id, name, code, level, description, min_age, max_depth_meters, prerequisites, created_at)
SELECT id, 'Stress & Rescue', 'SR', 3, 'Rescue and emergency response', 12, 30, 'Advanced Adventurer', NOW() FROM certification_agencies WHERE abbreviation = 'SSI';

INSERT INTO certifications (agency_id, name, code, level, description, min_age, max_depth_meters, prerequisites, created_at)
SELECT id, 'Dive Guide', 'DG', 4, 'Professional-level dive leader', 18, 40, 'Stress & Rescue', NOW() FROM certification_agencies WHERE abbreviation = 'SSI';

-- SDI Certifications
INSERT INTO certifications (agency_id, name, code, level, description, min_age, max_depth_meters, prerequisites, created_at)
SELECT id, 'Open Water Scuba Diver', 'OWSD', 1, 'Entry-level scuba certification', 10, 18, NULL, NOW() FROM certification_agencies WHERE abbreviation = 'SDI';

INSERT INTO certifications (agency_id, name, code, level, description, min_age, max_depth_meters, prerequisites, created_at)
SELECT id, 'Advanced Diver', 'AD', 2, 'Advanced recreational diving', 12, 30, 'Open Water Scuba Diver', NOW() FROM certification_agencies WHERE abbreviation = 'SDI';

-- TDI Technical Certifications
INSERT INTO certifications (agency_id, name, code, level, description, min_age, max_depth_meters, prerequisites, created_at)
SELECT id, 'Decompression Procedures', 'DECO', 4, 'Technical decompression diving', 18, 45, 'Advanced Nitrox', NOW() FROM certification_agencies WHERE abbreviation = 'TDI';

INSERT INTO certifications (agency_id, name, code, level, description, min_age, max_depth_meters, prerequisites, created_at)
SELECT id, 'Extended Range', 'ER', 4, 'Extended range technical diving', 18, 55, 'Decompression Procedures', NOW() FROM certification_agencies WHERE abbreviation = 'TDI';

INSERT INTO certifications (agency_id, name, code, level, description, min_age, max_depth_meters, prerequisites, created_at)
SELECT id, 'Trimix Diver', 'TRIMIX', 5, 'Trimix technical diving', 18, 100, 'Extended Range', NOW() FROM certification_agencies WHERE abbreviation = 'TDI';

-- Add some notes
SELECT 'Certification agencies and certifications seeded successfully!' as message;
SELECT COUNT(*) as total_agencies FROM certification_agencies;
SELECT COUNT(*) as total_certifications FROM certifications;
