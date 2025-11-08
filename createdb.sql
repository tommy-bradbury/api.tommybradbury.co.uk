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
INSERT INTO `nonameyet`.`users` (`id`, `email`, `password_hash`, `fullname`) VALUES (1, 'tommy.j.bradbury@gmail.com', '$2y$10$M.fqyOEwYN2kkmH0WXdpT.tWOArJ2pZ3o80OZPEUX3ddpm0DFQumm', 'Tommy Bradbury');