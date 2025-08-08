-- APSIT Student Council Database Export
-- Generated on: 2025-08-08 21:52:22
-- Database: apsit_database

-- Table structure for table `admin_letters`
DROP TABLE IF EXISTS `admin_letters`;
CREATE TABLE `admin_letters` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `attachment` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Data for table `admin_letters`
INSERT INTO `admin_letters` VALUES ('1', '1', 'to the topic', 'admin ja my Many companies and individuals prefer to create a letterhead template in a word processor or other software application. That generally includes the same information as pre-printed stationery but at lower cost. Letterhead can then be printed on stationery or plain paper, as needed, on a local output device or sent electronically.\r\n\r\nLetterheads are generally printed by either the offset or letterpress methods. In most countries outside North America, company letterheads are printed A4 in size (210 mm x 297 mm).[1] In North America, the letter size is typically 8.5 x 11 inches (215 x 280 mm).\r\n\r\nAlthough modern technology makes letterheads very easy to imitate, they continue to be used as evidence of authenticity.[2][3]', 'letter_68813884c8f0a.pdf', '2025-07-24 01:01:16');
INSERT INTO `admin_letters` VALUES ('2', '3', 'abhishek', 'saaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaax', 'letter_688ce59c57130.JPG', '2025-08-01 21:34:44');

-- Table structure for table `admins`
DROP TABLE IF EXISTS `admins`;
-- Table structure for table `announcements`
DROP TABLE IF EXISTS `announcements`;
CREATE TABLE `announcements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `category` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Data for table `announcements`
INSERT INTO `announcements` VALUES ('1', 'vgv', 'vjvghgv', 'latest', '2025-07-24 00:46:03');
INSERT INTO `announcements` VALUES ('2', 'nxdfxfdxfdxf', 'vkjhvkhgvkhgvhkgv', 'latest', '2025-07-24 00:46:31');
INSERT INTO `announcements` VALUES ('3', 'pyhton bootcamp', 'lasbjhabsljdbhajlsdb', 'latest', '2025-07-24 00:49:48');
INSERT INTO `announcements` VALUES ('4', 'java', 'nsakjdnkjbkbajbhfjlasbdha', 'older', '2025-07-24 00:50:34');
INSERT INTO `announcements` VALUES ('5', 'aiml', 'machine learning', 'older', '2025-07-24 00:51:01');
INSERT INTO `announcements` VALUES ('6', 'cybersecurity', 'sdnkajfnkadsjfnkjsnkfjndskafn', 'older', '2025-07-24 00:51:23');

-- Table structure for table `calendar_settings`
DROP TABLE IF EXISTS `calendar_settings`;
-- Table structure for table `events`
DROP TABLE IF EXISTS `events`;
CREATE TABLE `events` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `description` text DEFAULT NULL,
  `pdf` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Data for table `events`
INSERT INTO `events` VALUES ('6', '1', 'Tech Trivial', 'C:\\xampp\\htdocs\\MY_PROJECT\\Events/uploads/img_688131c420eb5.jpeg', '2025-07-27', '2025-08-09', 'adsdaad', '', '2025-07-24 00:32:28');
INSERT INTO `events` VALUES ('7', '4', 'bitflip', 'C:\\xampp\\htdocs\\MY_PROJECT\\Events/uploads/img_6896448d88f6c.jpeg', '2025-08-17', '2025-08-29', 'kbsacjhbajsdbjabhsjdbhajdbhjsabd', '', '2025-08-09 00:10:13');
INSERT INTO `events` VALUES ('8', '4', 'Tech Trivial', 'C:\\xampp\\htdocs\\MY_PROJECT\\Events/uploads/img_689644aa62283.jpeg', '2025-08-19', '2025-08-28', 'asdasdddddddddddddddddddd', '', '2025-08-09 00:10:42');

-- Table structure for table `gpa_calculations`
DROP TABLE IF EXISTS `gpa_calculations`;
CREATE TABLE `gpa_calculations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `gpa` decimal(3,2) NOT NULL,
  `calculation_date` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `gpa_calculations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Data for table `gpa_calculations`
INSERT INTO `gpa_calculations` VALUES ('1', '1', '4.00', '2025-07-24 01:12:04');

-- Table structure for table `gpa_courses`
DROP TABLE IF EXISTS `gpa_courses`;
CREATE TABLE `gpa_courses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `calculation_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `credits` int(11) NOT NULL,
  `grade` decimal(3,1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `calculation_id` (`calculation_id`),
  CONSTRAINT `gpa_courses_ibfk_1` FOREIGN KEY (`calculation_id`) REFERENCES `gpa_calculations` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Data for table `gpa_courses`
INSERT INTO `gpa_courses` VALUES ('1', '1', '\0h', '3', '4.0');
INSERT INTO `gpa_courses` VALUES ('2', '1', '\0h', '2', '4.0');

-- Table structure for table `help_desk`
DROP TABLE IF EXISTS `help_desk`;
CREATE TABLE `help_desk` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `issue` text NOT NULL,
  `status` enum('pending','in_progress','resolved') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `help_desk_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Data for table `help_desk`
INSERT INTO `help_desk` VALUES ('1', '1', '\0abhishe', '\0SARVAIYA6334@GMAIL.CO', 'non working person', 'in_progress', '2025-07-24 01:04:37');

-- Table structure for table `help_desk_responses`
DROP TABLE IF EXISTS `help_desk_responses`;
CREATE TABLE `help_desk_responses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ticket_id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `admin_name` varchar(255) NOT NULL,
  `response` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Data for table `help_desk_responses`
INSERT INTO `help_desk_responses` VALUES ('1', '1', '1', 'admin@example.com', 'sakjnakjsdkajsdnkasnd', '2025-07-24 01:10:45');

-- Table structure for table `meeting_minutes`
DROP TABLE IF EXISTS `meeting_minutes`;
CREATE TABLE `meeting_minutes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `meeting_date` date DEFAULT NULL,
  `meeting_time` time DEFAULT NULL,
  `attendees` text DEFAULT NULL,
  `agenda` text DEFAULT NULL,
  `discussion` text DEFAULT NULL,
  `action_items` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Data for table `meeting_minutes`
INSERT INTO `meeting_minutes` VALUES ('1', '1', 'teaching', '2025-07-18', '02:02:00', 'askjdnak sakdjkjdbakj', 'asdjskjbdjabhsjdbhajsd', 'asdabhjdbjkhadsbsjadb', 'bjlabhsjldbhajldbhajsdb', '2025-07-23 21:25:21', '2025-07-23 21:25:21');
INSERT INTO `meeting_minutes` VALUES ('2', '1', 'presntation', '2025-07-11', '09:09:00', 'jsdkjbjhbdsjbas', 'hbdsjcbjshbdjcshjchbscb', 'csdbcjhsbdjcbsjc', 'csbdcbsjbhcjsbcjhsbd', '2025-07-23 21:26:00', '2025-07-23 21:26:00');

-- Table structure for table `notifications`
DROP TABLE IF EXISTS `notifications`;
-- Table structure for table `profile`
DROP TABLE IF EXISTS `profile`;
-- Table structure for table `study_materials`
DROP TABLE IF EXISTS `study_materials`;
CREATE TABLE `study_materials` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `subject` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `file_path` varchar(255) NOT NULL,
  `upload_date` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `study_materials_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Data for table `study_materials`
INSERT INTO `study_materials` VALUES ('4', '3', '\0ssssssss', '\0Computer Scienc', 'sssssssssssssssssssssssss', '\0688cf517005a74.72982214.pd', '2025-08-01 22:40:47');

-- Table structure for table `study_materials_backup`
DROP TABLE IF EXISTS `study_materials_backup`;
CREATE TABLE `study_materials_backup` (
  `id` int(11) NOT NULL DEFAULT 0,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `subject` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `file_path` varchar(255) NOT NULL,
  `upload_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Data for table `study_materials_backup`
INSERT INTO `study_materials_backup` VALUES ('1', '1', '\0math', '\0Chemistr', 'csacajnskjcnkajcs', '\068813b85c80ec2.21651528.pd', '2025-07-24 01:14:05');
INSERT INTO `study_materials_backup` VALUES ('2', '3', '\0dssssssssssssssss', '\0Computer Scienc', 'asdadadad', '\0688cea74b8bc95.58109395.pd', '2025-08-01 21:55:24');
INSERT INTO `study_materials_backup` VALUES ('3', '3', '\0c+', '\0Computer Scienc', 'campbelll', '\0688ceb0d70fb68.19186825.pd', '2025-08-01 21:57:57');

-- Table structure for table `tasks`
DROP TABLE IF EXISTS `tasks`;
CREATE TABLE `tasks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `course` varchar(100) DEFAULT NULL,
  `deadline` date NOT NULL,
  `priority` enum('High','Medium','Low') DEFAULT 'Medium',
  `completed` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `tasks_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Data for table `tasks`
INSERT INTO `tasks` VALUES ('1', '1', '\0tas', '\0m', '2025-07-19', 'High', '0', '2025-07-24 01:13:09');

-- Table structure for table `timetable_events`
DROP TABLE IF EXISTS `timetable_events`;
CREATE TABLE `timetable_events` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `day` enum('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday') NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `timetable_events_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Data for table `timetable_events`
INSERT INTO `timetable_events` VALUES ('1', '1', '\0playing cricke', 'Thursday', '09:00:00', '03:08:00', '\0groun', '2025-07-24 01:12:36');

-- Table structure for table `user_preferences`
DROP TABLE IF EXISTS `user_preferences`;
CREATE TABLE `user_preferences` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `preference_key` varchar(100) NOT NULL,
  `preference_value` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_preference_unique` (`user_id`,`preference_key`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for table `users`
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `department` varchar(100) DEFAULT NULL,
  `year` varchar(50) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- Data for table `users`
INSERT INTO `users` VALUES ('1', 'ASIF ALIULLA KHAN', 'SARVAIYA6334@GMAIL.COM', '$2y$10$wFoOzXp9h45FtB5/anFYJu2iFO638cuzMSpU7Wpt5lI3FXCEHjof6', '2025-07-24 00:12:18', NULL, NULL, NULL);
INSERT INTO `users` VALUES ('2', 'ASIF ALIULLA KHAN', 'SARVAIYA63345@GMAIL.COM', '$2y$10$dyVg5CwvLFsu63qYPLJf7OiKdYYuyP/j0w.TtncnINkHQo0Wvh7Ue', '2025-07-26 16:33:34', NULL, NULL, NULL);
INSERT INTO `users` VALUES ('3', 'ASIF ALIULLA KHAN', 'abhishek1703@gmail.com', '$2y$10$SKj8t6k0gX1Mm5HU9.vweeEIENNI9su9tNy5dMvT5Y4BmXmiJeDJ2', '2025-08-01 21:33:43', NULL, NULL, NULL);
INSERT INTO `users` VALUES ('4', 'Abhishek', 'SARVAIYA63348@GMAIL.COM', '$2y$10$xmD22PL4jYqf2j/4C2Y4U.tqxWakEkJLklbXSelAZ0mfDEgJ/ijvy', '2025-08-09 00:07:17', 'Information Technology', 'Second Year', 'sdcsdcsdcsdc');

-- Table structure for table `workshop_registrations`
DROP TABLE IF EXISTS `workshop_registrations`;
CREATE TABLE `workshop_registrations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `workshop_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `student_id` varchar(50) NOT NULL,
  `registration_date` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `workshop_id` (`workshop_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `workshop_registrations_ibfk_1` FOREIGN KEY (`workshop_id`) REFERENCES `workshops` (`id`),
  CONSTRAINT `workshop_registrations_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- Data for table `workshop_registrations`
INSERT INTO `workshop_registrations` VALUES ('1', '1', '3', 'abhishek', 'SARVAIYA6334@GMAIL.COM', '23102180', '2025-08-01 21:54:05');

-- Table structure for table `workshops`
DROP TABLE IF EXISTS `workshops`;
CREATE TABLE `workshops` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `date` date NOT NULL,
  `time` time NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `max_participants` int(11) DEFAULT 50,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- Data for table `workshops`
INSERT INTO `workshops` VALUES ('1', 'workdop1', 'dsaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaacxvxcv', '2025-08-14', '02:02:00', 'Computer Science Lab, Room 101', '23', '2025-08-01 21:53:26');

-- End of export
