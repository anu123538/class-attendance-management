-- Create the class_management database
CREATE DATABASE IF NOT EXISTS class_management;

-- Use the newly created database
USE class_management;

-- Create the Department table (Main Primary Table)
CREATE TABLE IF NOT EXISTS Department (
    department_id INT AUTO_INCREMENT PRIMARY KEY,
    department_name VARCHAR(100) UNIQUE NOT NULL
);

-- Create the Faculty table, referencing the Department table
CREATE TABLE IF NOT EXISTS Faculty (
    faculty_id INT AUTO_INCREMENT PRIMARY KEY,
    faculty_name VARCHAR(100) UNIQUE NOT NULL,
    department_id INT NOT NULL,
    FOREIGN KEY (department_id) REFERENCES Department(department_id)
    ON DELETE CASCADE ON UPDATE CASCADE
);

-- Create the Course table, referencing the Department and Faculty tables
CREATE TABLE IF NOT EXISTS Course (
    course_id INT AUTO_INCREMENT PRIMARY KEY,
    course_name VARCHAR(100) NOT NULL,
    department_id INT NOT NULL,
    faculty_id INT NOT NULL,
    FOREIGN KEY (department_id) REFERENCES Department(department_id)
    ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (faculty_id) REFERENCES Faculty(faculty_id)
    ON DELETE CASCADE ON UPDATE CASCADE
);

-- Create the Student table, referencing the Department, Course, and Faculty tables
CREATE TABLE IF NOT EXISTS Student (
    student_id VARCHAR(50) PRIMARY KEY,
    student_name VARCHAR(100) NOT NULL,
    email_address VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20),
    department_id INT,
    course_id INT,
    faculty_id INT,
    img_path VARCHAR(255), -- Path to the photo
    FOREIGN KEY (department_id) REFERENCES Department(department_id)
    ON DELETE SET NULL ON UPDATE CASCADE,
    FOREIGN KEY (course_id) REFERENCES Course(course_id)
    ON DELETE SET NULL ON UPDATE CASCADE,
    FOREIGN KEY (faculty_id) REFERENCES Faculty(faculty_id)
    ON DELETE SET NULL ON UPDATE CASCADE
);

-- Create the Attendance table, referencing both Student and Course tables
CREATE TABLE IF NOT EXISTS Attendance (
    attendance_id INT AUTO_INCREMENT PRIMARY KEY,
    student_id VARCHAR(50) NOT NULL,
    course_id INT NOT NULL,
    week_01 VARCHAR(10) DEFAULT 'Absent',
    week_02 VARCHAR(10) DEFAULT 'Absent',
    week_03 VARCHAR(10) DEFAULT 'Absent',
    week_04 VARCHAR(10) DEFAULT 'Absent',
    week_05 VARCHAR(10) DEFAULT 'Absent',
    week_06 VARCHAR(10) DEFAULT 'Absent',
    week_07 VARCHAR(10) DEFAULT 'Absent',
    week_08 VARCHAR(10) DEFAULT 'Absent',
    week_09 VARCHAR(10) DEFAULT 'Absent',
    week_10 VARCHAR(10) DEFAULT 'Absent',
    FOREIGN KEY (student_id) REFERENCES Student(student_id)
    ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (course_id) REFERENCES Course(course_id)
    ON DELETE CASCADE ON UPDATE CASCADE
);

-- Create the Lecturer table, referencing both Faculty and Course tables
CREATE TABLE IF NOT EXISTS Lecturer (
    lecturer_id INT AUTO_INCREMENT PRIMARY KEY,
    lecturer_name VARCHAR(100) NOT NULL,
    faculty_id INT NOT NULL,
    course_id INT NOT NULL,
    module VARCHAR(100),
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    FOREIGN KEY (faculty_id) REFERENCES Faculty(faculty_id)
    ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (course_id) REFERENCES Course(course_id)
    ON DELETE CASCADE ON UPDATE CASCADE
);


-- Insert default values into the Department table
INSERT IGNORE INTO Department (department_name)
VALUES ('IT'),
       ('ENGINEERING'),
       ('HEALTH SCIENCE');

-- Insert default values into the Faculty table
INSERT IGNORE INTO Faculty (faculty_name, department_id)
SELECT 'Computing', department_id FROM Department WHERE department_name = 'IT'
UNION
SELECT 'Engineering and Technology', department_id FROM Department WHERE department_name = 'ENGINEERING'
UNION
SELECT 'Health Science', department_id FROM Department WHERE department_name = 'HEALTH SCIENCE';

-- Insert default values into the Course table
INSERT IGNORE INTO Course (course_name, department_id, faculty_id)
SELECT 'Software Engineering',
       (SELECT department_id FROM Department WHERE department_name = 'IT'),
       (SELECT faculty_id FROM Faculty WHERE faculty_name = 'Computing')
UNION
SELECT 'Computer Science',
       (SELECT department_id FROM Department WHERE department_name = 'IT'),
       (SELECT faculty_id FROM Faculty WHERE faculty_name = 'Computing')
UNION
SELECT 'Civil Engineering',
       (SELECT department_id FROM Department WHERE department_name = 'ENGINEERING'),
       (SELECT faculty_id FROM Faculty WHERE faculty_name = 'Engineering and Technology')
UNION
SELECT 'Electronic and Electrical Engineering',
       (SELECT department_id FROM Department WHERE department_name = 'ENGINEERING'),
       (SELECT faculty_id FROM Faculty WHERE faculty_name = 'Engineering and Technology')
UNION
SELECT 'Mechanical Engineering',
       (SELECT department_id FROM Department WHERE department_name = 'ENGINEERING'),
       (SELECT faculty_id FROM Faculty WHERE faculty_name = 'Engineering and Technology')
UNION
SELECT 'Cosmetic Science',
       (SELECT department_id FROM Department WHERE department_name = 'HEALTH SCIENCE'),
       (SELECT faculty_id FROM Faculty WHERE faculty_name = 'Health Science')
UNION
SELECT 'Physics Science',
       (SELECT department_id FROM Department WHERE department_name = 'HEALTH SCIENCE'),
       (SELECT faculty_id FROM Faculty WHERE faculty_name = 'Health Science')
UNION
SELECT 'Chemistry',
       (SELECT department_id FROM Department WHERE department_name = 'HEALTH SCIENCE'),
       (SELECT faculty_id FROM Faculty WHERE faculty_name = 'Health Science');
