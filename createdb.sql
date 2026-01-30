CREATE TABLE `users` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`email` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_general_ci',
	`password_hash` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_general_ci',
	`fullname` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
	`created_at` DATETIME NULL DEFAULT current_timestamp(),
	`updated_at` DATETIME NULL DEFAULT NULL ON UPDATE current_timestamp(),
	`deleted_at` DATETIME NULL DEFAULT NULL,
	PRIMARY KEY (`id`) USING BTREE,
	UNIQUE INDEX `Index 2` (`email`) USING BTREE
)
COLLATE='utf8mb4_general_ci'
ENGINE=InnoDB
;
INSERT INTO `users` (`id`, `email`, `password_hash`, `fullname`) VALUES (1, 'tommy.j.bradbury@gmail.com', '$2y$10$M.fqyOEwYN2kkmH0WXdpT.tWOArJ2pZ3o80OZPEUX3ddpm0DFQumm', 'Tommy Bradbury');

CREATE TABLE `locations` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`user_id` INT(11) NOT NULL,
	`name` VARCHAR(255) NULL DEFAULT NULL,
	`whatthreewords` VARCHAR(255) NULL DEFAULT NULL,
	`latitude` DECIMAL(10,8) NULL DEFAULT NULL,
	`longitude` DECIMAL(11,8) NULL DEFAULT NULL,
	`created_at` DATETIME NULL DEFAULT current_timestamp(),
	`updated_at` DATETIME NULL DEFAULT NULL ON UPDATE current_timestamp(),
	`deleted_at` DATETIME NULL DEFAULT NULL,
	PRIMARY KEY (`id`) USING BTREE,
	INDEX `user_id` (`user_id`) USING BTREE,
	CONSTRAINT `locations_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE ON DELETE CASCADE
)
COLLATE='utf8mb4_general_ci'
ENGINE=InnoDB
;
-- TODO: Future improvements for location types:
-- - Add support for Google Maps Place IDs
-- - Add support for postal addresses
-- - Add support for geohash encoding
-- - Add support for H3 spatial indexing