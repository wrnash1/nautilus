ALTER TABLE `customers` ADD COLUMN `external_id` VARCHAR(50) NULL AFTER `id`;
ALTER TABLE `customers` ADD INDEX `idx_external_id` (`external_id`);
