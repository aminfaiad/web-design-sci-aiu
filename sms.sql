-- Create database
DROP DATABASE IF EXISTS `sms`;
CREATE DATABASE `sms`;
USE `sms`;
-- Create user and grant privileges
CREATE USER 'SMS'@'localhost' IDENTIFIED BY 'password';
GRANT ALL PRIVILEGES ON `sms`.* TO 'SMS'@'localhost';
FLUSH PRIVILEGES;

-- Table structure for table `lecturers`
CREATE TABLE `lecturers` (
    `lecturer_id` varchar(50) NOT NULL,
    `username` varchar(50) NOT NULL,
    `password` varchar(20) NOT NULL,
    `email` varchar(100) NOT NULL,
    `phone` varchar(15) NOT NULL DEFAULT 'N/A',
    `fullname` varchar(255) DEFAULT NULL,
    `address` varchar(255) NOT NULL,
    `profile_picture` varchar(255) DEFAULT NULL,
    PRIMARY KEY (`lecturer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert data into `lecturers`
INSERT INTO `lecturers` (`lecturer_id`, `username`, `password`, `email`, `phone`, `fullname`, `address`, `profile_picture`) VALUES
('AIU2210', 'abubakar', 'abu123', 'abubakar@example.com', '0123456788', 'Mr Abu Bakar', '321 Academic Rd', 'uploads/profile_pictures/abu.jpg'),
('AIU2211', 'nadiah', 'nadiah123', 'nadiah@example.com', '0112233446', 'Madam Nadiah', '654 Education Blvd', 'uploads/profile_pictures/nadiah.jpg'),
('AIU2212', 'james', 'james123', 'james@example.com', '0103344556', 'Dr James Smith', '789 Knowledge Ave', 'uploads/profile_pictures/james.jpg'),
('AIU2213', 'lisa', 'lisa123', 'lisa@example.com', '0192233445', 'Dr Lisa Wong', '567 Research Rd', 'uploads/profile_pictures/lisa.jpg'),
('AIU2214', 'omar', 'omar123', 'omar@example.com', '0189988776', 'Prof Omar Al-Bashir', '890 Academic Ln', 'uploads/profile_pictures/omar.jpg');

-- Table structure for table `courses`
CREATE TABLE `courses` (
    `course_id` int(11) NOT NULL,
    `course_name` varchar(100) NOT NULL,
    `lecturer_id` varchar(50) NOT NULL,
    PRIMARY KEY (`course_id`),
    FOREIGN KEY (`lecturer_id`) REFERENCES `lecturers` (`lecturer_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert data into `courses`
INSERT INTO `courses` (`course_id`, `course_name`, `lecturer_id`) VALUES
(1, 'Mathematics', 'AIU2210'),
(2, 'Physics', 'AIU2210'),
(3, 'Science', 'AIU2211'),
(4, 'Biology', 'AIU2211'),
(5, 'English', 'AIU2212'),
(6, 'Computer Science', 'AIU2212'),
(7, 'History', 'AIU2213'),
(8, 'Web Development', 'AIU2213'),
(9, 'Chemistry', 'AIU2214'),
(10, 'Data Structures', 'AIU2214');

-- Table structure for table `semesters`
CREATE TABLE `semesters` (
    `semester_id` int(11) NOT NULL,
    `semester_name` varchar(50) NOT NULL,
    PRIMARY KEY (`semester_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert data into `semesters`
INSERT INTO `semesters` (`semester_id`, `semester_name`) VALUES
(1, 'Semester 1'),
(2, 'Semester 2'),
(3, 'Semester 3');

-- Table structure for table `semester_courses`
CREATE TABLE `semester_courses` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `semester_id` int(11) NOT NULL,
    `course_id` int(11) NOT NULL,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`semester_id`) REFERENCES `semesters`(`semester_id`) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (`course_id`) REFERENCES `courses`(`course_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert data into `semester_courses`
INSERT INTO `semester_courses` (`semester_id`, `course_id`) VALUES
(1, 1), (1, 2), (1, 3),
(2, 4), (2, 5), (2, 6),
(3, 7), (3, 8), (3, 9), (3, 10);

-- Table structure for table `students`
CREATE TABLE `students` (
    `student_id` varchar(50) NOT NULL,
    `username` varchar(50) NOT NULL,
    `password` varchar(20) NOT NULL,
    `email` varchar(100) NOT NULL,
    `phone` varchar(20) DEFAULT NULL,
    `fullname` varchar(255) DEFAULT NULL,
    `address` varchar(100) NOT NULL,
    PRIMARY KEY (`student_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert data into `students`
INSERT INTO `students` (`student_id`, `username`, `password`, `email`, `phone`, `fullname`, `address`) VALUES
('AIU22102232', 'fathi', 'fathi123', 'fathi@example.com', '0123456789', 'Fathi Mohammed', '123 Main St'),
('AIU22104567', 'ali', 'ali123', 'ali@example.com', '0112233445', 'Ali Bin Ahmad', '456 Secondary St'),
('AIU22107890', 'sarah', 'sarah123', 'sarah@example.com', '0109876543', 'Sarah Binti Zain', '789 Tertiary St'),
('AIU22101234', 'ahmad1', 'ahmad123', 'ahmad1@example.com', '0101234561', 'Ahmad Bin Ali', '101 Main St'),
('AIU22101235', 'noraini2', 'noraini123', 'noraini2@example.com', '0102345672', 'Noraini Binti Zulkifli', '102 Main St'),
('AIU22101236', 'hafiz3', 'hafiz123', 'hafiz3@example.com', '0103456783', 'Hafiz Bin Osman', '103 Main St'),
('AIU22101237', 'izzah4', 'izzah123', 'izzah4@example.com', '0104567894', 'Izzah Binti Rahman', '104 Main St'),
('AIU22101238', 'farhan5', 'farhan123', 'farhan5@example.com', '0105678905', 'Farhan Bin Khalid', '105 Main St'),
('AIU22101239', 'syafiq6', 'syafiq123', 'syafiq6@example.com', '0106789016', 'Syafiq Bin Hassan', '106 Main St'),
('AIU22101240', 'nabila7', 'nabila123', 'nabila7@example.com', '0107890127', 'Nabila Binti Yusuf', '107 Main St');


-- Table structure for table `course_registrations`
CREATE TABLE `course_registrations` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `student_id` varchar(50) NOT NULL,
    `semester_course_id` int(11) NOT NULL,
    `status` enum('Pending','Approved','Rejected') DEFAULT 'Pending',
    PRIMARY KEY (`id`),
    FOREIGN KEY (`student_id`) REFERENCES `students`(`student_id`) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (`semester_course_id`) REFERENCES `semester_courses`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Sample course registrations
INSERT INTO `course_registrations` (`student_id`, `semester_course_id`, `status`) VALUES
-- Fathi in Semester 1
('AIU22102232', 1, 'Approved'),
('AIU22102232', 2, 'Approved'),
('AIU22102232', 3, 'Approved'),

-- Ali in Semester 2
('AIU22104567', 4, 'Approved'),
('AIU22104567', 5, 'Approved'),
('AIU22104567', 6, 'Approved'),

-- Sarah in Semester 3
('AIU22107890', 7, 'Approved'),
('AIU22107890', 8, 'Approved'),
('AIU22107890', 9, 'Approved'),

-- Ahmad in Semester 1
('AIU22101234', 1, 'Approved'),
('AIU22101234', 2, 'Approved'),
('AIU22101234', 3, 'Approved'),

-- Noraini in Semester 2
('AIU22101235', 4, 'Approved'),
('AIU22101235', 5, 'Approved'),
('AIU22101235', 6, 'Approved'),

-- Hafiz in Semester 3
('AIU22101236', 7, 'Approved'),
('AIU22101236', 8, 'Approved'),
('AIU22101236', 9, 'Approved'),

-- Izzah in Semester 1
('AIU22101237', 1, 'Approved'),
('AIU22101237', 2, 'Approved'),
('AIU22101237', 3, 'Approved'),

-- Farhan in Semester 2
('AIU22101238', 4, 'Approved'),
('AIU22101238', 5, 'Approved'),
('AIU22101238', 6, 'Approved'),

-- Syafiq in Semester 3
('AIU22101239', 7, 'Approved'),
('AIU22101239', 8, 'Approved'),
('AIU22101239', 9, 'Approved'),

-- Nabila in Semester 1
('AIU22101240', 1, 'Approved'),
('AIU22101240', 2, 'Approved'),
('AIU22101240', 3, 'Approved');



CREATE TABLE `student_marks` (
  `mark_id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` varchar(50) NOT NULL,
  `course_id` int(11) NOT NULL,
  `lecturer_id` varchar(50) NOT NULL,
  `coursework_marks` float DEFAULT NULL,
  `final_exam_marks` float DEFAULT NULL,
  PRIMARY KEY (`mark_id`),
  FOREIGN KEY (`student_id`) REFERENCES `students`(`student_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`course_id`) REFERENCES `courses`(`course_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`lecturer_id`) REFERENCES `lecturers`(`lecturer_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for table `class_schedule`
CREATE TABLE `class_schedule` (
  `class_id` int(11) NOT NULL AUTO_INCREMENT,
  `course_id` int(11) NOT NULL,
  `lecturer_id` varchar(50) NOT NULL,
  `class_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  PRIMARY KEY (`class_id`),
  FOREIGN KEY (`course_id`) REFERENCES `courses`(`course_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`lecturer_id`) REFERENCES `lecturers`(`lecturer_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



-- Insert sample class schedules
INSERT INTO `class_schedule` (`course_id`, `lecturer_id`, `class_date`, `start_time`, `end_time`) VALUES
(1, 'AIU2210', '2025-02-01', '09:00:00', '10:30:00'),
(2, 'AIU2210', '2025-02-02', '10:45:00', '12:15:00'),
(4, 'AIU2211', '2025-02-03', '13:00:00', '14:30:00');

-- Table structure for table `attendance`
CREATE TABLE `attendance` (
  `attendance_id` int(11) NOT NULL AUTO_INCREMENT,
  `class_id` int(11) NOT NULL,
  `student_id` varchar(50) NOT NULL,
  `status` enum('Present', 'Absent') DEFAULT 'Absent',
  PRIMARY KEY (`attendance_id`),
  FOREIGN KEY (`class_id`) REFERENCES `class_schedule`(`class_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`student_id`) REFERENCES `students`(`student_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE attendance ADD CONSTRAINT unique_attendance UNIQUE (class_id, student_id);

-- Insert sample attendance data
INSERT INTO `attendance` (`class_id`, `student_id`, `status`) VALUES
(1, 'AIU22102232', 'Present'),
(1, 'AIU22104567', 'Absent'),
(2, 'AIU22107890', 'Present');

COMMIT;
