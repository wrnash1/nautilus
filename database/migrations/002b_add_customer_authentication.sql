ALTER TABLE `customers` ADD COLUMN `password` VARCHAR(255) AFTER `email`;
ALTER TABLE `customers` ADD COLUMN `email_verified_at` TIMESTAMP NULL AFTER `password`;
ALTER TABLE `customers` ADD COLUMN `remember_token` VARCHAR(100) NULL AFTER `email_verified_at`;

ALTER TABLE `customers` ADD UNIQUE INDEX `idx_unique_email` (`email`);
