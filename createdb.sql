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

CREATE TABLE `reviews` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`location_id` INT(11) NOT NULL,
	`user_id` INT(11) NOT NULL,
	`cleanliness_score` TINYINT(1) NOT NULL COMMENT '1-5 rating',
	`niceness_score` TINYINT(1) NOT NULL COMMENT '1-5 rating',
	`ease_of_finding_score` TINYINT(1) NOT NULL COMMENT '1-5 rating',
	`toilet_paper_quality_score` TINYINT(1) NOT NULL COMMENT '1-5 rating',
	`privacy_score` TINYINT(1) NOT NULL COMMENT '1-5 rating',
	`is_disabled_accessible` BOOLEAN NOT NULL,
	`is_customers_only` BOOLEAN NOT NULL,
	`has_baby_changing` BOOLEAN NULL DEFAULT NULL,
	`has_gender_neutral` BOOLEAN NULL DEFAULT NULL,
	`requires_payment` BOOLEAN NULL DEFAULT NULL,
	`comment` TEXT NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
	`created_at` DATETIME NULL DEFAULT current_timestamp(),
	`updated_at` DATETIME NULL DEFAULT NULL ON UPDATE current_timestamp(),
	`deleted_at` DATETIME NULL DEFAULT NULL,
	PRIMARY KEY (`id`) USING BTREE,
	INDEX `location_id` (`location_id`) USING BTREE,
	INDEX `user_id` (`user_id`) USING BTREE,
	CONSTRAINT `reviews_location_fk` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
	CONSTRAINT `reviews_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE ON DELETE CASCADE
)
COLLATE='utf8mb4_general_ci'
ENGINE=InnoDB
;

CREATE TABLE `review_logs` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`review_id` INT(11) NOT NULL,
	`user_id` INT(11) NOT NULL,
	`changes` JSON NOT NULL COMMENT 'JSON object containing previous values before update',
	`created_at` DATETIME NULL DEFAULT current_timestamp(),
	PRIMARY KEY (`id`) USING BTREE,
	INDEX `review_id` (`review_id`) USING BTREE,
	INDEX `user_id` (`user_id`) USING BTREE,
	CONSTRAINT `review_logs_review_fk` FOREIGN KEY (`review_id`) REFERENCES `reviews` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
	CONSTRAINT `review_logs_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE ON DELETE CASCADE
)
COLLATE='utf8mb4_general_ci'
ENGINE=InnoDB
;

DELIMITER $$

CREATE TRIGGER `before_review_update`
BEFORE UPDATE ON `reviews`
FOR EACH ROW
BEGIN
    -- Only log if the review is not being soft deleted and if actual content changed
    IF NEW.deleted_at IS NULL AND (
        OLD.cleanliness_score != NEW.cleanliness_score OR
        OLD.niceness_score != NEW.niceness_score OR
        OLD.ease_of_finding_score != NEW.ease_of_finding_score OR
        OLD.toilet_paper_quality_score != NEW.toilet_paper_quality_score OR
        OLD.privacy_score != NEW.privacy_score OR
        OLD.is_disabled_accessible != NEW.is_disabled_accessible OR
        OLD.is_customers_only != NEW.is_customers_only OR
        OLD.has_baby_changing != NEW.has_baby_changing OR
        OLD.has_gender_neutral != NEW.has_gender_neutral OR
        OLD.requires_payment != NEW.requires_payment OR
        OLD.comment != NEW.comment
    ) THEN
        INSERT INTO review_logs (review_id, user_id, changes)
        VALUES (
            OLD.id,
            OLD.user_id,
            JSON_OBJECT(
                'cleanliness_score', OLD.cleanliness_score,
                'niceness_score', OLD.niceness_score,
                'ease_of_finding_score', OLD.ease_of_finding_score,
                'toilet_paper_quality_score', OLD.toilet_paper_quality_score,
                'privacy_score', OLD.privacy_score,
                'is_disabled_accessible', OLD.is_disabled_accessible,
                'is_customers_only', OLD.is_customers_only,
                'has_baby_changing', OLD.has_baby_changing,
                'has_gender_neutral', OLD.has_gender_neutral,
                'requires_payment', OLD.requires_payment,
                'comment', OLD.comment,
                'updated_at', OLD.updated_at
            )
        );
    END IF;
END$$

DELIMITER ;

-- TODO: Future review improvements:
-- - Add photo upload support
-- - Add soap/hand drying facilities rating
-- - Add queue/wait time indicator