SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles` (
  `id` tinyint unsigned NOT NULL AUTO_INCREMENT,
  `role_name` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `reward_type`;
CREATE TABLE `reward_type` (
  `id` tinyint unsigned NOT NULL AUTO_INCREMENT,
  `reward_name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `job_status`;
CREATE TABLE `job_status` (
  `id` tinyint unsigned NOT NULL AUTO_INCREMENT,
  `status_name` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `job_categories`;
CREATE TABLE `job_categories` (
  `id` tinyint NOT NULL,
  `category_name` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `category_name` (`category_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `admins`;
CREATE TABLE `admins` (
  `id` tinyint NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `executives`;
CREATE TABLE `executives` (
  `id` int NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `password` varchar(255) NOT NULL,
  `role_id` tinyint unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_role_id` (`role_id`),
  CONSTRAINT `fk_role_id` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `students`;
CREATE TABLE `students` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `major` varchar(255) DEFAULT NULL,
  `year` int DEFAULT NULL,
  `role_id` tinyint unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `fk_role_student_id` (`role_id`),
  CONSTRAINT `fk_role_student_id` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `teachers`;
CREATE TABLE `teachers` (
  `id` int NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `department` varchar(255) DEFAULT NULL,
  `role_id` tinyint unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `role_id` (`role_id`),
  CONSTRAINT `fk_role_techer_id` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `reporters`;
CREATE TABLE `reporters` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `user_type` enum('student','teacher') NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `reporters_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `report_categories`;
CREATE TABLE `report_categories` (
  `id` int NOT NULL AUTO_INCREMENT,
  `report_category_name` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `report_category_name` (`report_category_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `messages`;
CREATE TABLE `messages` (
  `id` int NOT NULL AUTO_INCREMENT,
  `sender_id` int NOT NULL,
  `receiver_id` int NOT NULL,
  `message_body` text NOT NULL,
  `sent_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `read_status` enum('unread','read') DEFAULT 'unread',
  PRIMARY KEY (`id`),
  KEY `sender_id` (`sender_id`),
  KEY `receiver_id` (`receiver_id`),
  CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `notifications`;
CREATE TABLE `notifications` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `role_id` tinyint unsigned NOT NULL,
  `event_type` enum('job_accepted','job_application','new_message','report_submitted') NOT NULL,
  `reference_table` enum('post_jobs','reports','messages') NOT NULL,
  `reference_id` int NOT NULL,
  `message` text NOT NULL,
  `status` enum('unread','read') NOT NULL DEFAULT 'unread',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `role_id` (`role_id`),
  CONSTRAINT `fk_notification_role_id` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_notification_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `students_notifications`;
CREATE TABLE `students_notifications` (
  `id` int NOT NULL AUTO_INCREMENT,
  `job_id` varchar(255) NOT NULL,
  `accepted_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` enum('unread','read') NOT NULL DEFAULT 'unread',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `teachers_notifications`;
CREATE TABLE `teachers_notifications` (
  `id` int NOT NULL AUTO_INCREMENT,
  `job_id` varchar(255) NOT NULL,
  `post_timeout` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` enum('unread','read') NOT NULL DEFAULT 'unread',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `post_jobs`;
CREATE TABLE `post_jobs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `status_id` tinyint unsigned NOT NULL,
  `reward_id` tinyint unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `number_student` int NOT NULL,
  `category_id` tinyint NOT NULL,
  `teacher_id` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `status_id` (`status_id`),
  KEY `fk_reward_id` (`reward_id`),
  KEY `category_id` (`category_id`),
  KEY `teacher_id` (`teacher_id`),
  CONSTRAINT `fk_reward_id` FOREIGN KEY (`reward_id`) REFERENCES `reward_type` (`id`),
  CONSTRAINT `post_jobs_fk_1` FOREIGN KEY (`category_id`) REFERENCES `job_categories` (`id`),
  CONSTRAINT `post_jobs_fk_2` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`),
  CONSTRAINT `post_jobs_ibfk_1` FOREIGN KEY (`status_id`) REFERENCES `job_status` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `job_applications`;
CREATE TABLE `job_applications` (
  `id` int NOT NULL AUTO_INCREMENT,
  `post_id` int NOT NULL,
  `student_id` int NOT NULL,
  `GPA` int NOT NULL,
  `Phone_number` int NOT NULL,
  `Resume` text,
  `status_id` tinyint unsigned NOT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `post_id` (`post_id`),
  KEY `student_id` (`student_id`),
  KEY `status_id` (`status_id`),
  CONSTRAINT `job_applications_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `post_jobs` (`id`),
  CONSTRAINT `job_applications_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`),
  CONSTRAINT `job_applications_ibfk_3` FOREIGN KEY (`status_id`) REFERENCES `job_status` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `accepted_applications`;
CREATE TABLE `accepted_applications` (
  `id` int NOT NULL AUTO_INCREMENT,
  `application_id` int NOT NULL,
  `post_id` int NOT NULL,
  `student_id` int NOT NULL,
  `accepted_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status_id` tinyint unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `application_id` (`application_id`),
  KEY `post_id` (`post_id`),
  KEY `student_id` (`student_id`),
  KEY `status_id` (`status_id`),
  CONSTRAINT `accept_applications_ibfk_1` FOREIGN KEY (`application_id`) REFERENCES `job_applications` (`id`) ON DELETE CASCADE,
  CONSTRAINT `accept_applications_ibfk_2` FOREIGN KEY (`post_id`) REFERENCES `post_jobs` (`id`) ON DELETE CASCADE,
  CONSTRAINT `accept_applications_ibfk_3` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  CONSTRAINT `accepted_applications_ibfk_1` FOREIGN KEY (`status_id`) REFERENCES `job_status` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `reports`;
CREATE TABLE `reports` (
  `id` int NOT NULL,
  `post_id` int NOT NULL,
  `reporter_id` int NOT NULL,
  `report_category_id` int NOT NULL,
  `details` text,
  `status_id` tinyint unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `post_id` (`post_id`),
  KEY `reporter_id` (`reporter_id`),
  KEY `report_category_id` (`report_category_id`),
  CONSTRAINT `reports_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `post_jobs` (`id`),
  CONSTRAINT `reports_ibfk_2` FOREIGN KEY (`reporter_id`) REFERENCES `reporters` (`id`),
  CONSTRAINT `reports_ibfk_3` FOREIGN KEY (`report_category_id`) REFERENCES `report_categories` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `reviews`;
CREATE TABLE `reviews` (
  `id` int NOT NULL AUTO_INCREMENT,
  `post_id` int NOT NULL,
  `student_id` int NOT NULL,
  `teacher_id` int NOT NULL,
  `rating` int DEFAULT NULL,
  `comment` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `post_id` (`post_id`),
  KEY `student_id` (`student_id`),
  KEY `teacher_id` (`teacher_id`),
  CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `post_jobs` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reviews_ibfk_3` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reviews_chk_1` CHECK ((`rating` between 1 and 5))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET FOREIGN_KEY_CHECKS = 1;
