-- Seed major diving certification agencies

INSERT INTO certification_agencies (name, abbreviation, logo_path, primary_color, website, country, api_endpoint, verification_enabled, verification_url, is_active) VALUES
('Professional Association of Diving Instructors', 'PADI', '/assets/images/agencies/padi-logo.png', '#0066CC', 'https://www.padi.com', 'United States', 'https://api.padi.com/v1', TRUE, 'https://apps.padi.com/scuba-diving/ecard-verification/', TRUE),
('Scuba Schools International', 'SSI', '/assets/images/agencies/ssi-logo.png', '#003DA5', 'https://www.divessi.com', 'United States', 'https://api.divessi.com/v1', TRUE, 'https://www.divessi.com/verification', TRUE),
('Scuba Diving International', 'SDI', '/assets/images/agencies/sdi-logo.png', '#C41E3A', 'https://www.tdisdi.com', 'United States', 'https://api.tdisdi.com/v1', TRUE, 'https://www.tdisdi.com/verification', TRUE),
('National Association of Underwater Instructors', 'NAUI', '/assets/images/agencies/naui-logo.png', '#00529B', 'https://www.naui.org', 'United States', 'https://api.naui.org/v1', TRUE, 'https://www.naui.org/verify', TRUE),
('British Sub-Aqua Club', 'BSAC', '/assets/images/agencies/bsac-logo.png', '#0052A5', 'https://www.bsac.com', 'United Kingdom', NULL, FALSE, NULL, TRUE),
('Confédération Mondiale des Activités Subaquatiques', 'CMAS', '/assets/images/agencies/cmas-logo.png', '#005EB8', 'https://www.cmas.org', 'International', NULL, FALSE, NULL, TRUE),
('Global Underwater Explorers', 'GUE', '/assets/images/agencies/gue-logo.png', '#000000', 'https://www.gue.com', 'United States', NULL, FALSE, NULL, TRUE),
('Technical Diving International', 'TDI', '/assets/images/agencies/tdi-logo.png', '#1C3F94', 'https://www.tdisdi.com', 'United States', 'https://api.tdisdi.com/v1', TRUE, 'https://www.tdisdi.com/verification', TRUE),
('Emergency Response Diving International', 'ERDI', '/assets/images/agencies/erdi-logo.png', '#DC143C', 'https://www.tdisdi.com/erdi', 'United States', NULL, FALSE, NULL, TRUE),
('Professional Diving Instructors Corporation', 'PDIC', '/assets/images/agencies/pdic-logo.png', '#1560BD', 'https://www.pdic-intl.com', 'Japan', NULL, FALSE, NULL, TRUE);

-- PADI Certifications
INSERT INTO certifications (agency_id, name, level, code, description, prerequisites, is_active) VALUES
(1, 'Discover Scuba Diving', 0, 'DSD', 'Introduction to scuba diving in confined water', '[]', TRUE),
(1, 'Scuba Diver', 1, 'SD', 'Entry-level certification with depth limit of 12 meters', '[]', TRUE),
(1, 'Open Water Diver', 2, 'OW', 'Basic scuba certification allowing dives to 18 meters', '[]', TRUE),
(1, 'Adventure Diver', 3, 'AD', 'Try 3 adventure dives', '[{"certification": "Open Water Diver", "level": 2}]', TRUE),
(1, 'Advanced Open Water Diver', 4, 'AOW', 'Complete 5 adventure dives including deep and navigation', '[{"certification": "Open Water Diver", "level": 2}]', TRUE),
(1, 'Rescue Diver', 5, 'RD', 'Learn to prevent and manage dive emergencies', '[{"certification": "Advanced Open Water Diver", "level": 4}, {"certification": "EFR", "external": true}]', TRUE),
(1, 'Master Scuba Diver', 6, 'MSD', 'Highest recreational level - requires 5 specialties', '[{"certification": "Rescue Diver", "level": 5}, {"specialties_count": 5}, {"logged_dives": 50}]', TRUE),
(1, 'Divemaster', 7, 'DM', 'Professional level - supervise and assist dive activities', '[{"certification": "Rescue Diver", "level": 5}, {"logged_dives": 40}, {"age": 18}]', TRUE),
(1, 'Assistant Instructor', 8, 'AI', 'Assist instructors with training', '[{"certification": "Divemaster", "level": 7}, {"logged_dives": 60}]', TRUE),
(1, 'Open Water Scuba Instructor', 9, 'OWSI', 'Teach scuba diving courses', '[{"certification": "Divemaster", "level": 7}, {"logged_dives": 100}, {"age": 18}]', TRUE),
(1, 'Enriched Air (Nitrox) Diver', 3, 'EAN', 'Dive with enriched air nitrox', '[{"certification": "Open Water Diver", "level": 2}]', TRUE),
(1, 'Deep Diver', 4, 'DEEP', 'Dive to 40 meters', '[{"certification": "Adventure Diver", "level": 3}, {"age": 15}]', TRUE),
(1, 'Wreck Diver', 4, 'WRECK', 'Explore shipwrecks safely', '[{"certification": "Adventure Diver", "level": 3}]', TRUE),
(1, 'Drift Diver', 3, 'DRIFT', 'Dive in currents', '[{"certification": "Open Water Diver", "level": 2}]', TRUE),
(1, 'Night Diver', 3, 'NIGHT', 'Night diving techniques', '[{"certification": "Open Water Diver", "level": 2}]', TRUE);

-- SSI Certifications
INSERT INTO certifications (agency_id, name, level, code, description, prerequisites, is_active) VALUES
(2, 'Try Scuba', 0, 'TS', 'Introduction to scuba in pool', '[]', TRUE),
(2, 'Scuba Diver', 1, 'SD', 'Basic certification to 12 meters', '[]', TRUE),
(2, 'Open Water Diver', 2, 'OWD', 'Complete open water certification to 18 meters', '[]', TRUE),
(2, 'Advanced Adventurer', 3, 'AA', 'Complete 5 specialty dives', '[{"certification": "Open Water Diver", "level": 2}]', TRUE),
(2, 'Advanced Open Water Diver', 4, 'AOWD', 'Complete 4 specialty certifications', '[{"certification": "Open Water Diver", "level": 2}, {"logged_dives": 24}]', TRUE),
(2, 'Stress & Rescue', 5, 'S&R', 'Rescue diver certification', '[{"certification": "Advanced Open Water Diver", "level": 4}]', TRUE),
(2, 'Master Diver', 6, 'MD', 'SSI Master Diver rating', '[{"certification": "Stress & Rescue", "level": 5}, {"specialties_count": 4}, {"logged_dives": 50}]', TRUE),
(2, 'Divemaster', 7, 'DM', 'Professional leadership level', '[{"certification": "Stress & Rescue", "level": 5}, {"logged_dives": 50}, {"age": 18}]', TRUE),
(2, 'Dive Guide', 7, 'DG', 'Guide certified divers', '[{"certification": "Advanced Open Water Diver", "level": 4}, {"logged_dives": 40}]', TRUE),
(2, 'Enriched Air Nitrox', 3, 'EAN', 'Nitrox diving', '[{"certification": "Open Water Diver", "level": 2}]', TRUE),
(2, 'Deep Diving', 4, 'DD', 'Diving to 40 meters', '[{"certification": "Open Water Diver", "level": 2}, {"age": 15}]', TRUE),
(2, 'Wreck Diving', 4, 'WD', 'Wreck exploration', '[{"certification": "Open Water Diver", "level": 2}]', TRUE);

-- SDI Certifications
INSERT INTO certifications (agency_id, name, level, code, description, prerequisites, is_active) VALUES
(3, 'Discover Scuba Diving', 0, 'DSD', 'Scuba introduction', '[]', TRUE),
(3, 'Open Water Scuba Diver', 2, 'OW', 'Entry level certification', '[]', TRUE),
(3, 'Advanced Diver', 4, 'AD', 'Advanced training with specialties', '[{"certification": "Open Water Scuba Diver", "level": 2}]', TRUE),
(3, 'Rescue Diver', 5, 'RD', 'Emergency response training', '[{"certification": "Advanced Diver", "level": 4}]', TRUE),
(3, 'Master Scuba Diver', 6, 'MSD', 'Highest recreational rating', '[{"certification": "Rescue Diver", "level": 5}, {"specialties_count": 4}, {"logged_dives": 50}]', TRUE),
(3, 'Divemaster', 7, 'DM', 'Leadership level', '[{"certification": "Rescue Diver", "level": 5}, {"logged_dives": 40}, {"age": 18}]', TRUE),
(3, 'Nitrox Diver', 3, 'NITROX', 'Enriched air certification', '[{"certification": "Open Water Scuba Diver", "level": 2}]', TRUE),
(3, 'Deep Diver', 4, 'DEEP', 'Deep diving to 40m', '[{"certification": "Advanced Diver", "level": 4}]', TRUE),
(3, 'Wreck Diver', 4, 'WRECK', 'Wreck diving specialty', '[{"certification": "Open Water Scuba Diver", "level": 2}]', TRUE);

-- NAUI Certifications
INSERT INTO certifications (agency_id, name, level, code, description, prerequisites, is_active) VALUES
(4, 'Passport Diver', 1, 'PD', 'Introduction certification', '[]', TRUE),
(4, 'Scuba Diver', 2, 'SD', 'Basic open water certification', '[]', TRUE),
(4, 'Advanced Scuba Diver', 4, 'ASD', 'Advanced diving skills', '[{"certification": "Scuba Diver", "level": 2}, {"logged_dives": 25}]', TRUE),
(4, 'Rescue Scuba Diver', 5, 'RSD', 'Rescue and emergency skills', '[{"certification": "Advanced Scuba Diver", "level": 4}]', TRUE),
(4, 'Master Scuba Diver', 6, 'MSD', 'Master diver rating', '[{"certification": "Rescue Scuba Diver", "level": 5}, {"logged_dives": 50}]', TRUE),
(4, 'Divemaster', 7, 'DM', 'Leadership certification', '[{"certification": "Rescue Scuba Diver", "level": 5}, {"logged_dives": 50}, {"age": 18}]', TRUE),
(4, 'Nitrox Diver', 3, 'NITROX', 'Enriched air nitrox', '[{"certification": "Scuba Diver", "level": 2}]', TRUE),
(4, 'Deep Diver', 4, 'DEEP', 'Deep diving certification', '[{"certification": "Scuba Diver", "level": 2}]', TRUE);

-- TDI Technical Certifications
INSERT INTO certifications (agency_id, name, level, code, description, prerequisites, is_active) VALUES
(8, 'Nitrox Diver', 3, 'NITROX', 'Nitrox certification', '[{"certification": "Open Water", "level": 2}]', TRUE),
(8, 'Advanced Nitrox Diver', 5, 'ADV_NITROX', 'Advanced nitrox to 40m', '[{"certification": "Nitrox Diver", "level": 3}, {"logged_dives": 25}]', TRUE),
(8, 'Decompression Procedures', 6, 'DECO', 'Staged decompression diving', '[{"certification": "Advanced Nitrox Diver", "level": 5}]', TRUE),
(8, 'Extended Range', 6, 'ER', 'Deep diving to 55m', '[{"certification": "Advanced Nitrox Diver", "level": 5}]', TRUE),
(8, 'Trimix Diver', 7, 'TRIMIX', 'Helium-based mix diving', '[{"certification": "Decompression Procedures", "level": 6}, {"logged_dives": 100}]', TRUE),
(8, 'Full Cave Diver', 7, 'CAVE', 'Full overhead environment', '[{"certification": "Cavern", "level": 5}, {"logged_dives": 100}]', TRUE),
(8, 'Sidemount Diver', 4, 'SIDEMOUNT', 'Sidemount configuration', '[{"certification": "Open Water", "level": 2}]', TRUE);
