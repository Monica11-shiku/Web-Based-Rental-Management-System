USE rms;
CREATE TABLE announcements(announcement_id INT AUTO_INCREMENT PRIMARY KEY,landlord_id INT,message VARCHAR(500),created_by INT,created_at DATETIME DEFAULT CURRENT_TIMESTAMP);
CREATE TABLE password_resets(reset_id INT AUTO_INCREMENT PRIMARY KEY,user_id INT,status ENUM('pending','approved','used','expired') DEFAULT 'pending',code_hash CHAR(64) NULL,attempts INT DEFAULT 0,requested_at DATETIME DEFAULT CURRENT_TIMESTAMP,approved_by INT NULL,expires_at DATETIME NULL,FOREIGN KEY(user_id) REFERENCES users(user_id));
