USE rms;
CREATE TABLE expenses(expense_id INT AUTO_INCREMENT PRIMARY KEY,landlord_id INT,property_id INT NULL,category VARCHAR(30),amount DECIMAL(10,2),expense_date DATE,description VARCHAR(200),created_by INT,created_at DATETIME DEFAULT CURRENT_TIMESTAMP);
