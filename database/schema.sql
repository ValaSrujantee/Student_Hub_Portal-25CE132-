CREATE DATABASE IF NOT EXISTS studenthub CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE studenthub;

CREATE TABLE IF NOT EXISTS users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(120) NOT NULL,
  email VARCHAR(190) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('student', 'faculty', 'admin') NOT NULL DEFAULT 'student',
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  last_login DATETIME NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS students (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NULL UNIQUE,
  full_name VARCHAR(120) NOT NULL,
  email VARCHAR(190) NOT NULL UNIQUE,
  mobile VARCHAR(24) NULL,
  course VARCHAR(80) NOT NULL,
  study_year TINYINT UNSIGNED NOT NULL,
  gender VARCHAR(20) NULL,
  profile VARCHAR(255) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_students_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
  CONSTRAINT chk_students_year CHECK (study_year BETWEEN 1 AND 4)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS events (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(150) NOT NULL,
  event_date DATE NOT NULL,
  category VARCHAR(80) NOT NULL,
  description TEXT NOT NULL,
  status ENUM('open', 'closed') NOT NULL DEFAULT 'open',
  poster_path VARCHAR(255) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS registrations (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NULL,
  full_name VARCHAR(120) NOT NULL,
  course VARCHAR(80) NOT NULL,
  study_year TINYINT UNSIGNED NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_registrations_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS audit_logs (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NULL,
  action VARCHAR(80) NOT NULL,
  details VARCHAR(255) NOT NULL DEFAULT '',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_audit_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

INSERT IGNORE INTO students (full_name, email, course, study_year, profile) VALUES
('Aarav Patel', 'aarav.patel@studenthub.local', 'Computer Science', 1, 'Interested in web interfaces and programming fundamentals.'),
('Diya Shah', 'diya.shah@studenthub.local', 'Mathematics', 2, 'Enjoys statistics and problem solving.'),
('Ishaan Mehta', 'ishaan.mehta@studenthub.local', 'Physics', 3, 'Focuses on laboratory work and applied physics.'),
('Anaya Desai', 'anaya.desai@studenthub.local', 'Chemistry', 1, 'Explores practical chemistry and laboratory methods.'),
('Vivaan Rao', 'vivaan.rao@studenthub.local', 'English', 2, 'Writes short fiction and participates in reading groups.'),
('Meera Iyer', 'meera.iyer@studenthub.local', 'Biology', 3, 'Interested in ecology and biological research.'),
('Arjun Joshi', 'arjun.joshi@studenthub.local', 'History', 4, 'Studies modern history and archival sources.'),
('Sana Khan', 'sana.khan@studenthub.local', 'Economics', 2, 'Interested in economic policy and data analysis.'),
('Reyansh Shah', 'reyansh.shah@studenthub.local', 'Computer Science', 3, 'Builds small applications and studies software design.'),
('Ira Patel', 'ira.patel@studenthub.local', 'Mathematics', 1, 'Enjoys algebra and collaborative problem solving.'),
('Kabir Mehta', 'kabir.mehta@studenthub.local', 'Physics', 4, 'Works on electronics and experimental measurements.'),
('Tara Desai', 'tara.desai@studenthub.local', 'Chemistry', 2, 'Interested in materials and laboratory analysis.'),
('Neil Rao', 'neil.rao@studenthub.local', 'English', 3, 'Studies literature and academic communication.'),
('Aditi Iyer', 'aditi.iyer@studenthub.local', 'Biology', 4, 'Interested in biodiversity and conservation.'),
('Rohan Joshi', 'rohan.joshi@studenthub.local', 'Economics', 1, 'Explores markets and introductory economics.'),
('Kavya Patel', 'kavya.patel@studenthub.local', 'Computer Science', 2, 'Practices frontend development and database basics.'),
('Dev Shah', 'dev.shah@studenthub.local', 'Mathematics', 3, 'Enjoys discrete mathematics and statistics.'),
('Riya Mehta', 'riya.mehta@studenthub.local', 'Physics', 1, 'Interested in mechanics and practical experiments.'),
('Om Desai', 'om.desai@studenthub.local', 'Biology', 2, 'Studies cell biology and environmental science.'),
('Mira Rao', 'mira.rao@studenthub.local', 'History', 3, 'Interested in historical research and culture.');

INSERT IGNORE INTO events (title, event_date, category, description, status) VALUES
('Student Orientation', '2026-08-03', 'Campus', 'Meet the student support team and learn about campus facilities.', 'open'),
('Web Development Workshop', '2026-08-12', 'Workshop', 'Practice building accessible pages with HTML and CSS.', 'open'),
('Library Research Session', '2026-08-19', 'Academic', 'Learn how to find and cite reliable academic sources.', 'open'),
('Inter-College Quiz', '2026-08-26', 'Competition', 'Take part in a team quiz covering science and general knowledge.', 'open'),
('Career Guidance Talk', '2026-09-02', 'Career', 'Hear practical advice about internships and career planning.', 'open'),
('Tree Plantation Drive', '2026-09-09', 'Community', 'Join a campus tree planting and clean-up activity.', 'open'),
('Mathematics Problem-Solving Hour', '2026-09-16', 'Academic', 'Work through selected problems with peer mentors.', 'open'),
('Student Photography Exhibit', '2026-09-23', 'Arts', 'View photographs created by students across the campus.', 'open'),
('Robotics Demonstration', '2026-10-01', 'Technology', 'Explore student-built robots and basic control systems.', 'open'),
('Annual Sports Meet', '2026-10-08', 'Sports', 'Compete in track, field, and team sporting events.', 'open'),
('Literary Reading Circle', '2026-10-15', 'Arts', 'Share short readings and discuss selected works.', 'open'),
('Cyber Safety Seminar', '2026-10-22', 'Technology', 'Review safe account and device practices for students.', 'open'),
('Science Poster Showcase', '2026-11-04', 'Academic', 'Present student science projects as research posters.', 'open'),
('Community Service Day', '2026-11-12', 'Community', 'Volunteer with local community support activities.', 'open'),
('Semester Project Exhibition', '2026-11-20', 'Technology', 'Demonstrate semester projects to students and faculty.', 'open');

INSERT INTO registrations (user_id, full_name, course, study_year) VALUES
(NULL, 'Sample Student One', 'Computer Science', 1),
(NULL, 'Sample Student Two', 'Mathematics', 2),
(NULL, 'Sample Student Three', 'Physics', 3);
