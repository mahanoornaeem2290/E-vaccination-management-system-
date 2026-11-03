-- VaxCare · Vaccination Management System
-- Import this file in phpMyAdmin (or: mysql -u root < database.sql)
CREATE DATABASE IF NOT EXISTS vaccination_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE vaccination_db;
SET FOREIGN_KEY_CHECKS=0;
DROP TABLE IF EXISTS travel_plans, notifications, appointments, schedule, hospital_vaccines, children, hospitals, parents, vaccines, users;
SET FOREIGN_KEY_CHECKS=1;

CREATE TABLE users(
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  phone VARCHAR(20) NOT NULL DEFAULT '',
  password VARCHAR(255) NOT NULL,
  role ENUM('admin','parent','hospital') NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE parents(
  user_id INT PRIMARY KEY,
  address VARCHAR(255) NOT NULL DEFAULT '',
  city VARCHAR(80) NOT NULL DEFAULT '',
  FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE hospitals(
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL UNIQUE,
  name VARCHAR(150) NOT NULL,
  address VARCHAR(255) NOT NULL,
  city VARCHAR(80) NOT NULL,
  phone VARCHAR(20) NOT NULL DEFAULT '',
  status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE children(
  id INT AUTO_INCREMENT PRIMARY KEY,
  parent_id INT NOT NULL,
  name VARCHAR(100) NOT NULL,
  dob DATE NOT NULL,
  gender ENUM('Male','Female') NOT NULL,
  blood_group VARCHAR(5) NOT NULL DEFAULT '',
  member_type ENUM('child','adult') NOT NULL DEFAULT 'child',
  passport_no VARCHAR(20) NOT NULL DEFAULT '',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX(parent_id),
  FOREIGN KEY(parent_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE vaccines(
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  description VARCHAR(255) NOT NULL DEFAULT '',
  age_days INT NOT NULL DEFAULT 0,
  available TINYINT(1) NOT NULL DEFAULT 1,
  audience ENUM('child','adult') NOT NULL DEFAULT 'child',
  travel_required TINYINT(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB;

CREATE TABLE hospital_vaccines(
  hospital_id INT NOT NULL,
  vaccine_id INT NOT NULL,
  stock INT NOT NULL DEFAULT 0,
  price_pkr DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY(hospital_id,vaccine_id),
  FOREIGN KEY(hospital_id) REFERENCES hospitals(id) ON DELETE CASCADE,
  FOREIGN KEY(vaccine_id) REFERENCES vaccines(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE schedule(
  id INT AUTO_INCREMENT PRIMARY KEY,
  child_id INT NOT NULL,
  vaccine_id INT NOT NULL,
  due_date DATE NOT NULL,
  status ENUM('pending','booked','vaccinated') NOT NULL DEFAULT 'pending',
  given_on DATE NULL,
  INDEX(due_date),
  FOREIGN KEY(child_id) REFERENCES children(id) ON DELETE CASCADE,
  FOREIGN KEY(vaccine_id) REFERENCES vaccines(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE appointments(
  id INT AUTO_INCREMENT PRIMARY KEY,
  schedule_id INT NOT NULL,
  child_id INT NOT NULL,
  hospital_id INT NOT NULL,
  vaccine_id INT NOT NULL,
  appt_date DATE NOT NULL,
  slot VARCHAR(20) NOT NULL,
  admin_status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  admin_note VARCHAR(255) NOT NULL DEFAULT '',
  vax_status ENUM('pending','vaccinated','not_vaccinated') NOT NULL DEFAULT 'pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX(appt_date), INDEX(hospital_id,appt_date),
  FOREIGN KEY(schedule_id) REFERENCES schedule(id) ON DELETE CASCADE,
  FOREIGN KEY(child_id) REFERENCES children(id) ON DELETE CASCADE,
  FOREIGN KEY(hospital_id) REFERENCES hospitals(id) ON DELETE CASCADE,
  FOREIGN KEY(vaccine_id) REFERENCES vaccines(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE travel_plans(
  id INT AUTO_INCREMENT PRIMARY KEY,
  member_id INT NOT NULL,
  destination VARCHAR(100) NOT NULL,
  travel_date DATE NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX(member_id,travel_date),
  FOREIGN KEY(member_id) REFERENCES children(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE notifications(
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  message VARCHAR(255) NOT NULL,
  is_read TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX(user_id,is_read),
  FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Vaccines (Pakistan EPI-style schedule; age in days from birth)
INSERT INTO vaccines(name,description,age_days) VALUES
('BCG','Protects against tuberculosis',0),
('Hepatitis B (birth dose)','Protects against hepatitis B',0),
('OPV-0','Oral polio vaccine, birth dose',0),
('Pentavalent-1','DPT, Hepatitis B and Hib, dose 1',42),
('PCV-1','Pneumococcal vaccine, dose 1',42),
('Rotavirus-1','Protects against severe diarrhoea, dose 1',42),
('Pentavalent-2','DPT, Hepatitis B and Hib, dose 2',70),
('PCV-2','Pneumococcal vaccine, dose 2',70),
('Pentavalent-3','DPT, Hepatitis B and Hib, dose 3',98),
('IPV','Injectable polio vaccine',98),
('Measles-Rubella 1','Measles and rubella, dose 1',270),
('Measles-Rubella 2','Measles and rubella, dose 2',450),
('Typhoid conjugate','Protects against typhoid fever',540);

-- Adult / travel vaccines (age_days = 0: due as soon as the adult is registered).
-- travel_required = 1 means the person is NOT cleared to travel abroad until it is given.
-- Sample list only: the admin can change it under Vaccines. Confirm against the rules of the destination country.
INSERT INTO vaccines(name,description,age_days,audience,travel_required) VALUES
('Polio (IPV/OPV)','Polio proof is checked for international travellers',0,'adult',1),
('Meningococcal ACWY','Needed for Hajj, Umrah and travel to some regions',0,'adult',1),
('COVID-19 vaccine','Primary course or booster, checked by some destinations',0,'adult',1),
('Typhoid conjugate (adult)','Recommended for South Asia and Africa trips',0,'adult',0),
('Hepatitis A','Recommended for most overseas trips',0,'adult',0),
('Hepatitis B (adult)','Protects against hepatitis B',0,'adult',0),
('Tetanus-diphtheria (Td)','Booster every 10 years',0,'adult',0),
('MMR','Measles, mumps and rubella catch-up',0,'adult',0),
('Yellow fever','Required when travelling to or from endemic countries',0,'adult',0),
('Seasonal influenza','Yearly flu shot',0,'adult',0);

-- Demo accounts: admin@vaxcare.test / Admin@123 ; others / Demo@123
INSERT INTO users(id,name,email,phone,password,role) VALUES
(1,'System Admin','admin@vaxcare.test','0300-0000000','$2y$10$okLazmmHEFP6fygqwr9VGOGq/RDtyje2ixP7NdJWtUuXPKgKw7/K2','admin'),
(2,'Ayesha Khan','parent@vaxcare.test','0301-1234567','$2y$10$mYLipAf/q2QkjJtaXOre8.7sow1NUgR2JtRJ7qgCYxZAVC9tzR47i','parent'),
(3,'City Care Hospital','hospital@vaxcare.test','021-34000001','$2y$10$mYLipAf/q2QkjJtaXOre8.7sow1NUgR2JtRJ7qgCYxZAVC9tzR47i','hospital'),
(4,'Green Crescent Clinic','clinic@vaxcare.test','021-34000002','$2y$10$mYLipAf/q2QkjJtaXOre8.7sow1NUgR2JtRJ7qgCYxZAVC9tzR47i','hospital'),
(5,'National Medical Center','national@vaxcare.test','021-34567890','$2y$10$mYLipAf/q2QkjJtaXOre8.7sow1NUgR2JtRJ7qgCYxZAVC9tzR47i','hospital'),
(6,'Sunrise Children Hospital','sunrise@vaxcare.test','021-34567891','$2y$10$mYLipAf/q2QkjJtaXOre8.7sow1NUgR2JtRJ7qgCYxZAVC9tzR47i','hospital'),
(7,'Lifeline General Hospital','lifeline@vaxcare.test','021-34567892','$2y$10$mYLipAf/q2QkjJtaXOre8.7sow1NUgR2JtRJ7qgCYxZAVC9tzR47i','hospital'),
(8,'CarePoint Hospital','carepoint@vaxcare.test','021-34567893','$2y$10$mYLipAf/q2QkjJtaXOre8.7sow1NUgR2JtRJ7qgCYxZAVC9tzR47i','hospital'),
(9,'Demo Partner Hospital - Gulshan','demo.gulshan@vaxcare.test','021-555-0101','$2y$10$mYLipAf/q2QkjJtRJ7qgCYxZAVC9tzR47i','hospital'),
(10,'Demo Partner Clinic - Clifton','demo.clifton@vaxcare.test','021-555-0102','$2y$10$mYLipAf/q2QkjJtaXOre8.7sow1NUgR2JtRJ7qgCYxZAVC9tzR47i','hospital'),
(11,'Demo Vaccination Centre - North Nazimabad','demo.north@vaxcare.test','021-555-0103','$2y$10$mYLipAf/q2QkjJtaXOre8.7sow1NUgR2JtRJ7qgCYxZAVC9tzR47i','hospital');
INSERT INTO parents(user_id,address,city) VALUES (2,'House 12, Block B, Gulshan','Karachi');
INSERT INTO hospitals(id,user_id,name,address,city,phone,status) VALUES
(1,3,'City Care Hospital','Shahrah-e-Faisal','Karachi','021-34000001','pending'),
(2,4,'Green Crescent Clinic','Saddar','Karachi','021-34000002','pending'),
(3,5,'National Medical Center','Gulshan-e-Iqbal','Karachi','021-34567890','pending'),
(4,6,'Sunrise Children Hospital','Clifton','Karachi','021-34567891','pending'),
(5,7,'Lifeline General Hospital','Nazimabad','Karachi','021-34567892','pending'),
(6,8,'CarePoint Hospital','DHA','Karachi','021-34567893','pending'),
-- Explicit demo-only partner entries; these are not verified real hospitals.
(7,9,'Demo Partner Hospital - Gulshan','Demo location, Gulshan-e-Iqbal','Karachi','021-555-0101','pending'),
(8,10,'Demo Partner Clinic - Clifton','Demo location, Clifton','Karachi','021-555-0102','pending'),
(9,11,'Demo Vaccination Centre - North Nazimabad','Demo location, North Nazimabad','Karachi','021-555-0103','pending');
-- Demo values only. Partner hospitals must replace these with their current prices and stock.
INSERT INTO hospital_vaccines(hospital_id,vaccine_id,stock,price_pkr) SELECT h.id,v.id,40,0.00 FROM hospitals h CROSS JOIN vaccines v;

INSERT INTO children(id,parent_id,name,dob,gender,blood_group,member_type,passport_no) VALUES
(1,2,'Zain Ahmed',DATE_SUB(CURDATE(),INTERVAL 100 DAY),'Male','B+','child',''),
(2,2,'Ayesha Khan','1992-05-14','Female','O+','adult','AB1234567');
INSERT INTO schedule(child_id,vaccine_id,due_date)
  SELECT c.id,v.id,IF(c.member_type='adult',CURDATE(),DATE_ADD(c.dob,INTERVAL v.age_days DAY))
  FROM children c JOIN vaccines v ON v.audience=c.member_type;
UPDATE schedule s JOIN vaccines v ON v.id=s.vaccine_id SET s.status='vaccinated',s.given_on=s.due_date WHERE v.audience='child' AND v.age_days<=42;
UPDATE schedule s JOIN vaccines v ON v.id=s.vaccine_id SET s.status='vaccinated',s.given_on=DATE_SUB(CURDATE(),INTERVAL 30 DAY) WHERE s.child_id=2 AND v.name='Polio (IPV/OPV)';
INSERT INTO travel_plans(member_id,destination,travel_date) VALUES (2,'Dubai, UAE',DATE_ADD(CURDATE(),INTERVAL 30 DAY));
INSERT INTO notifications(user_id,message) VALUES (2,'Welcome to VaxCare. Zain''s vaccination schedule is ready.');
