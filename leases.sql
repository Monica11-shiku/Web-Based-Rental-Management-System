USE rms;
CREATE TABLE lease_renewals(renewal_id INT AUTO_INCREMENT PRIMARY KEY,lease_id INT,old_end DATE,new_end DATE,old_rent DECIMAL(10,2),new_rent DECIMAL(10,2),renewed_by INT,created_at DATETIME DEFAULT CURRENT_TIMESTAMP,FOREIGN KEY(lease_id) REFERENCES leases(lease_id));
