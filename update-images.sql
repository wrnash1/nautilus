-- Update product images in the database
UPDATE products SET image_url = '/assets/img/products/regulator.png' WHERE name LIKE '%regulator%' OR id = 1;
UPDATE products SET image_url = '/assets/img/products/mask.png' WHERE name LIKE '%mask%' OR id = 2;
UPDATE products SET image_url = '/assets/img/products/fins.png' WHERE name LIKE '%fin%' OR id = 3;
UPDATE products SET image_url = '/assets/img/products/wetsuit.png' WHERE name LIKE '%wetsuit%' OR id = 4;
UPDATE products SET image_url = '/assets/img/products/bcd.png' WHERE name LIKE '%bcd%' OR name LIKE '%buoyancy%' OR id = 5;
UPDATE products SET image_url = '/assets/img/products/computer.png' WHERE name LIKE '%computer%' OR id = 6;
UPDATE products SET image_url = '/assets/img/products/snorkel.png' WHERE name LIKE '%snorkel%' OR id = 7;
UPDATE products SET image_url = '/assets/img/products/tank.png' WHERE name LIKE '%tank%' OR id = 8;
UPDATE products SET image_url = '/assets/img/products/light.png' WHERE name LIKE '%light%' OR name LIKE '%torch%' OR id = 9;

-- Fill remaining products with rotating images
UPDATE products SET image_url = '/assets/img/products/regulator.png' WHERE image_url IS NULL AND (id % 9) = 0;
UPDATE products SET image_url = '/assets/img/products/mask.png' WHERE image_url IS NULL AND (id % 9) = 1;
UPDATE products SET image_url = '/assets/img/products/fins.png' WHERE image_url IS NULL AND (id % 9) = 2;
UPDATE products SET image_url = '/assets/img/products/wetsuit.png' WHERE image_url IS NULL AND (id % 9) = 3;
UPDATE products SET image_url = '/assets/img/products/bcd.png' WHERE image_url IS NULL AND (id % 9) = 4;
UPDATE products SET image_url = '/assets/img/products/computer.png' WHERE image_url IS NULL AND (id % 9) = 5;
UPDATE products SET image_url = '/assets/img/products/snorkel.png' WHERE image_url IS NULL AND (id % 9) = 6;
UPDATE products SET image_url = '/assets/img/products/tank.png' WHERE image_url IS NULL AND (id % 9) = 7;
UPDATE products SET image_url = '/assets/img/products/light.png' WHERE image_url IS NULL AND (id % 9) = 8;

SELECT 'Products updated with images' as result;
