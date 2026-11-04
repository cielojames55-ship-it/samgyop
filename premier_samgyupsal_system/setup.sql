USE premier_samgyupsal;

-- Staff login table. The initial password is "password".
CREATE TABLE IF NOT EXISTS staff (
    staff_id INT AUTO_INCREMENT PRIMARY KEY,
    staff_name VARCHAR(100) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('Admin','Staff') NOT NULL DEFAULT 'Staff',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO staff (staff_name, username, password_hash, role)
SELECT 'System Administrator', 'admin',
       '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
       'Admin'
WHERE NOT EXISTS (SELECT 1 FROM staff WHERE username = 'admin');

-- NOTE: Existing customers.table_number is UNIQUE in the supplied schema.
-- This starter system treats one customer record as the active customer per table.
