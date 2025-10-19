-- Service Reminder Templates

INSERT INTO service_reminder_templates (name, reminder_type, days_before, email_subject, email_body, sms_message, send_email, send_sms, is_active) VALUES
('Tank VIP Due Reminder', 'tank_vip', 30, 'Tank VIP Inspection Due Soon',
'<p>Hi {first_name},</p><p>This is a friendly reminder that your scuba tank VIP inspection is due on {due_date}.</p><p>To ensure your safety and comply with industry standards, please schedule an appointment with us to have your tank inspected.</p><p>Call us or stop by the shop to schedule your service.</p><p>Best regards,<br>Nautilus Dive Shop</p>',
'Hi {first_name}, your tank VIP is due on {due_date}. Schedule your inspection today!',
TRUE, TRUE, TRUE),

('Tank Hydro Due Reminder', 'tank_hydro', 45, 'Tank Hydrostatic Test Due',
'<p>Hi {first_name},</p><p>Your scuba tank hydrostatic test is due on {due_date}.</p><p>This is a required 5-year test to ensure your tank''s structural integrity.</p><p>Please bring your tank to the shop to schedule the hydro test.</p><p>Best regards,<br>Nautilus Dive Shop</p>',
'Hi {first_name}, your tank hydro test is due on {due_date}. Schedule today!',
TRUE, TRUE, TRUE),

('Regulator Service Due', 'regulator_service', 30, 'Annual Regulator Service Due',
'<p>Hi {first_name},</p><p>Your regulator is due for its annual service on {due_date}.</p><p>Regular service ensures optimal performance and your safety underwater.</p><p>Schedule your regulator service with us today!</p><p>Best regards,<br>Nautilus Dive Shop</p>',
'Hi {first_name}, your regulator service is due on {due_date}. Book your appointment!',
TRUE, TRUE, TRUE),

('BCD Service Due', 'bcd_service', 30, 'BCD Annual Service Reminder',
'<p>Hi {first_name},</p><p>Your BCD is due for annual service on {due_date}.</p><p>Keep your BCD in top condition with our professional service.</p><p>Call us to schedule!</p><p>Best regards,<br>Nautilus Dive Shop</p>',
'Hi {first_name}, your BCD service is due on {due_date}. Schedule today!',
TRUE, FALSE, TRUE),

('Certification Renewal', 'certification_renewal', 60, 'Certification Renewal Reminder',
'<p>Hi {first_name},</p><p>Your diving certification expires on {due_date}.</p><p>Don''t let your certification lapse! Contact us to schedule a refresher course or renewal.</p><p>Best regards,<br>Nautilus Dive Shop</p>',
'Hi {first_name}, your dive cert expires on {due_date}. Renew now!',
TRUE, TRUE, TRUE),

('Course Follow-up', 'course_followup', 7, 'How Was Your Dive Course?',
'<p>Hi {first_name},</p><p>Congratulations again on completing your dive course!</p><p>We hope you had a great experience. We''d love to hear your feedback and help you plan your next diving adventure.</p><p>Ready for your next certification or a fun dive trip?</p><p>Best regards,<br>Nautilus Dive Shop</p>',
'Hi {first_name}, congrats on completing your course! Ready for your next diving adventure?',
TRUE, FALSE, TRUE),

('Birthday Wishes', 'birthday', 0, 'Happy Birthday from Nautilus Dive Shop!',
'<p>Happy Birthday, {first_name}!</p><p>Wishing you an amazing year filled with incredible dives and underwater adventures!</p><p>As our birthday gift to you, mention this email for a special surprise on your next visit.</p><p>Dive safe and have a fantastic birthday!</p><p>Best regards,<br>Nautilus Dive Shop</p>',
'Happy Birthday {first_name}! Wishing you amazing dives this year! Stop by for a birthday surprise!',
TRUE, TRUE, TRUE),

('Customer Anniversary', 'anniversary', 0, 'Happy Dive Anniversary!',
'<p>Hi {first_name},</p><p>Happy Dive Anniversary! It''s been amazing having you as part of our diving community.</p><p>Thank you for choosing Nautilus Dive Shop for your diving adventures!</p><p>Best regards,<br>Nautilus Dive Shop</p>',
'Happy Dive Anniversary {first_name}! Thanks for being part of our community!',
TRUE, FALSE, TRUE);

-- Popular Dive Sites

INSERT INTO dive_sites (name, location, country, region, latitude, longitude, max_depth_meters, min_depth_meters, skill_level, minimum_certification_level, site_type, description, highlights, marine_life, hazards, best_season, average_visibility_meters, average_current, entry_exit_type, facilities, is_active) VALUES
-- Florida Keys
('Molasses Reef', 'Key Largo, FL', 'United States', 'Florida Keys', 25.0115, -80.3764, 15, 3, 'beginner', 2, 'reef',
'One of the most popular diving locations in the Florida Keys with easy access and abundant marine life.',
'Coral formations, mooring buoys, statue of Christ, great snorkeling',
'Barracuda, angelfish, parrotfish, sea turtles, rays, nurse sharks',
'Boat traffic, strong currents possible',
'Year-round, best May-September',
20, 'mild', 'Boat only', '{"moorings": true, "facilities_nearby": true, "dive_shop_access": true}', TRUE),

('Spiegel Grove', 'Key Largo, FL', 'United States', 'Florida Keys', 25.0556, -80.3019, 40, 18, 'advanced', 4, 'wreck',
'510-foot Navy ship sunk in 2002, now one of the best wreck dives in the world.',
'Massive shipwreck, penetration opportunities, abundant fish life',
'Goliath groupers, barracuda, sharks, rays, schools of tropical fish',
'Depth, overhead environment, currents',
'Year-round, best April-October',
25, 'moderate', 'Boat only', '{"moorings": true, "facilities_nearby": true, "technical_diving": true}', TRUE),

('Looe Key Reef', 'Big Pine Key, FL', 'United States', 'Florida Keys', 24.5450, -81.4040, 11, 2, 'beginner', 2, 'reef',
'Pristine coral reef named after HMS Looe, a British frigate that ran aground in 1744.',
'Healthy coral formations, shipwreck artifacts, abundant sea life',
'Angelfish, parrotfish, sea turtles, rays, lobster, tropical fish',
'Boat traffic, occasional strong currents',
'Year-round, best visibility April-October',
22, 'mild', 'Boat only', '{"moorings": true, "sanctuary": true}', TRUE),

-- Cozumel, Mexico
('Palancar Reef', 'Cozumel', 'Mexico', 'Caribbean', 20.3355, -87.0317, 25, 15, 'intermediate', 3, 'wall',
'Famous drift dive along spectacular coral wall formations.',
'Dramatic wall formations, swim-throughs, caverns, drift diving',
'Sea turtles, eagle rays, moray eels, angelfish, groupers',
'Strong currents, depth',
'Year-round, best May-September',
30, 'moderate', 'Boat only', '{"moorings": true, "drift_diving": true}', TRUE),

('Santa Rosa Wall', 'Cozumel', 'Mexico', 'Caribbean', 20.4100, -87.0200, 35, 18, 'advanced', 4, 'wall',
'Stunning wall dive with dramatic drop-offs and incredible coral formations.',
'Vertical wall, swim-throughs, large coral formations',
'Eagle rays, turtles, sharks, large groupers, barracuda',
'Strong currents, depth, downcurrents possible',
'Year-round, best April-October',
35, 'strong', 'Boat only', '{"moorings": true, "drift_diving": true, "deep_diving": true}', TRUE),

-- Bahamas
('Thunderball Grotto', 'Exuma', 'Bahamas', 'Caribbean', 24.1667, -76.4500, 6, 1, 'beginner', 2, 'cave',
'Unique underwater cave system featured in James Bond films.',
'Cave diving, light beams, shallow depths, excellent snorkeling',
'Sergeant majors, snappers, tropical fish, friendly fish',
'Tide dependent entry, shallow ceiling',
'Year-round, best April-October',
18, 'mild', 'Boat or shore', '{"snorkeling": true, "tidal": true}', TRUE),

('Shark Wall', 'Nassau', 'Bahamas', 'Caribbean', 25.0443, -77.3504, 60, 20, 'advanced', 4, 'wall',
'Famous shark diving location with regular shark encounters.',
'Shark feeding, wall diving, deep drops',
'Caribbean reef sharks, bull sharks, rays, groupers',
'Depth, sharks, currents',
'Year-round, best November-May',
30, 'moderate', 'Boat only', '{"shark_diving": true, "deep_diving": true}', TRUE),

-- Hawaii
('Molokini Crater', 'Maui, HI', 'United States', 'Hawaii', 20.6283, -156.4950, 45, 6, 'beginner', 2, 'reef',
'Crescent-shaped volcanic crater offering incredible visibility and marine life.',
'Crystal clear water, volcanic formations, protected marine sanctuary',
'Manta rays, reef sharks, sea turtles, dolphins, tropical fish',
'Depth on back wall, boat traffic, currents possible',
'Year-round, best May-September',
40, 'mild', 'Boat only', '{"moorings": true, "sanctuary": true, "multiple_sites": true}', TRUE),

('Kealakekua Bay', 'Big Island, HI', 'United States', 'Hawaii', 19.4833, -155.9167, 30, 3, 'beginner', 2, 'reef',
'Protected marine sanctuary with incredible coral reefs and spinner dolphins.',
'Pristine coral, dolphin encounters, Captain Cook Monument, historical site',
'Spinner dolphins, sea turtles, tropical fish, manta rays',
'Boat traffic, protected area regulations',
'Year-round, best April-October',
35, 'none', 'Boat or shore', '{"sanctuary": true, "dolphins": true, "historical": true}', TRUE),

-- California
('Casino Point', 'Catalina Island, CA', 'United States', 'California', 33.3450, -118.3267, 18, 3, 'beginner', 2, 'shore',
'Popular shore dive with kelp forests and abundant marine life.',
'Kelp forest, easy shore access, underwater park, great for photography',
'Garibaldi, horn sharks, octopus, lobster, sheephead, sea lions',
'Kelp entanglement, cold water, low visibility possible',
'Year-round, best August-November',
12, 'mild', 'Shore', '{"shore_access": true, "facilities": true, "park": true}', TRUE),

-- Great Barrier Reef
('Cod Hole', 'Great Barrier Reef', 'Australia', 'Queensland', -14.6833, 145.6167, 25, 12, 'intermediate', 3, 'reef',
'World-famous site known for friendly potato cod encounters.',
'Giant potato cod, pristine coral, excellent visibility',
'Potato cod, Maori wrasse, sharks, rays, tropical fish',
'Depth, remote location, boat dependent',
'Year-round, best June-October',
30, 'mild', 'Boat only', '{"liveaboard": true, "remote": true}', TRUE),

-- Red Sea
('Blue Hole', 'Dahab', 'Egypt', 'Red Sea', 28.5950, 34.5200, 130, 7, 'advanced', 4, 'blue_hole',
'Famous and challenging blue hole dive requiring advanced training.',
'The Arch at 56m, incredible blue water, challenging conditions',
'Lionfish, groupers, jackfish, napoleons',
'Extreme depth, The Arch, fatalities, technical dive',
'Year-round, best March-November',
30, 'mild', 'Shore', '{"technical_diving": true, "extreme_depth": true, "dangerous": true}', TRUE);
