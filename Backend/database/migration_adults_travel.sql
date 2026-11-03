-- Run ONCE on an existing vaccination_db to add adult vaccination + travel clearance.
-- (A fresh import of database.sql already includes all of this.)
USE vaccination_db;

ALTER TABLE children
  ADD COLUMN member_type ENUM('child','adult') NOT NULL DEFAULT 'child' AFTER blood_group,
  ADD COLUMN passport_no VARCHAR(20) NOT NULL DEFAULT '' AFTER member_type;

ALTER TABLE vaccines
  ADD COLUMN audience ENUM('child','adult') NOT NULL DEFAULT 'child',
  ADD COLUMN travel_required TINYINT(1) NOT NULL DEFAULT 0;

CREATE TABLE IF NOT EXISTS travel_plans(
  id INT AUTO_INCREMENT PRIMARY KEY,
  member_id INT NOT NULL,
  destination VARCHAR(100) NOT NULL,
  travel_date DATE NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX(member_id,travel_date),
  FOREIGN KEY(member_id) REFERENCES children(id) ON DELETE CASCADE
) ENGINE=InnoDB;

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

-- Give every hospital stock of the new vaccines
INSERT IGNORE INTO hospital_vaccines(hospital_id,vaccine_id,stock)
  SELECT h.id,v.id,40 FROM hospitals h CROSS JOIN vaccines v WHERE v.audience='adult';
