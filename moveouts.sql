USE rms;
CREATE TABLE moveouts(moveout_id INT AUTO_INCREMENT PRIMARY KEY,lease_id INT,moveout_date DATE,deposit DECIMAL(10,2),balance DECIMAL(10,2),deductions DECIMAL(10,2),refund_due DECIMAL(10,2),notes VARCHAR(300),processed_by INT,created_at DATETIME DEFAULT CURRENT_TIMESTAMP,FOREIGN KEY(lease_id) REFERENCES leases(lease_id));
