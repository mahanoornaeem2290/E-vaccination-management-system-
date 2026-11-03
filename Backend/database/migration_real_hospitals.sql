-- VaxCare · Real Pakistan Hospitals Migration
-- Run this after database.sql to add real hospitals across all major cities.
-- Passwords all use Demo@123 hash. Admin must approve hospitals via admin panel.

-- ─────────────────────────────────────────────────────────────
-- USER ACCOUNTS (role = hospital)
-- ─────────────────────────────────────────────────────────────
INSERT INTO users (id, name, email, phone, password, role) VALUES
-- Karachi
(20,'Aga Khan University Hospital','akuh@vaxcare.test','021-34930051','$2y$10$mYLipAf/q2QkjJtaXOre8.7sow1NUgR2JtRJ7qgCYxZAVC9tzR47i','hospital'),
(21,'Liaquat National Hospital','lnh@vaxcare.test','021-34412442','$2y$10$mYLipAf/q2QkjJtaXOre8.7sow1NUgR2JtRJ7qgCYxZAVC9tzR47i','hospital'),
(22,'South City Hospital','southcity@vaxcare.test','021-35871471','$2y$10$mYLipAf/q2QkjJtaXOre8.7sow1NUgR2JtRJ7qgCYxZAVC9tzR47i','hospital'),
(23,'Indus Hospital','indus@vaxcare.test','021-35112709','$2y$10$mYLipAf/q2QkjJtaXOre8.7sow1NUgR2JtRJ7qgCYxZAVC9tzR47i','hospital'),
(24,'Ziauddin Hospital','ziauddin@vaxcare.test','021-36699000','$2y$10$mYLipAf/q2QkjJtaXOre8.7sow1NUgR2JtRJ7qgCYxZAVC9tzR47i','hospital'),
-- Lahore
(25,'Shaukat Khanum Memorial Cancer Hospital','skmch.lhr@vaxcare.test','042-35945100','$2y$10$mYLipAf/q2QkjJtaXOre8.7sow1NUgR2JtRJ7qgCYxZAVC9tzR47i','hospital'),
(26,'Services Hospital Lahore','services.lhr@vaxcare.test','042-99203390','$2y$10$mYLipAf/q2QkjJtaXOre8.7sow1NUgR2JtRJ7qgCYxZAVC9tzR47i','hospital'),
(27,'Doctors Hospital Lahore','doctors.lhr@vaxcare.test','042-35761999','$2y$10$mYLipAf/q2QkjJtaXOre8.7sow1NUgR2JtRJ7qgCYxZAVC9tzR47i','hospital'),
(28,'Ittefaq Hospital','ittefaq.lhr@vaxcare.test','042-35761340','$2y$10$mYLipAf/q2QkjJtaXOre8.7sow1NUgR2JtRJ7qgCYxZAVC9tzR47i','hospital'),
(29,'Ganga Ram Hospital','gangarum.lhr@vaxcare.test','042-37233337','$2y$10$mYLipAf/q2QkjJtaXOre8.7sow1NUgR2JtRJ7qgCYxZAVC9tzR47i','hospital'),
-- Islamabad / Rawalpindi
(30,'Shifa International Hospital','shifa.isb@vaxcare.test','051-8464646','$2y$10$mYLipAf/q2QkjJtaXOre8.7sow1NUgR2JtRJ7qgCYxZAVC9tzR47i','hospital'),
(31,'Pakistan Institute of Medical Sciences','pims.isb@vaxcare.test','051-9261170','$2y$10$mYLipAf/q2QkjJtaXOre8.7sow1NUgR2JtRJ7qgCYxZAVC9tzR47i','hospital'),
(32,'Holy Family Hospital Rawalpindi','holyfamily.rwp@vaxcare.test','051-9290311','$2y$10$mYLipAf/q2QkjJtaXOre8.7sow1NUgR2JtRJ7qgCYxZAVC9tzR47i','hospital'),
(33,'Benazir Bhutto Hospital','bgh.rwp@vaxcare.test','051-9270341','$2y$10$mYLipAf/q2QkjJtaXOre8.7sow1NUgR2JtRJ7qgCYxZAVC9tzR47i','hospital'),
-- Peshawar
(34,'Lady Reading Hospital','lrh.psh@vaxcare.test','091-9212301','$2y$10$mYLipAf/q2QkjJtaXOre8.7sow1NUgR2JtRJ7qgCYxZAVC9tzR47i','hospital'),
(35,'Hayatabad Medical Complex','hmc.psh@vaxcare.test','091-9217001','$2y$10$mYLipAf/q2QkjJtaXOre8.7sow1NUgR2JtRJ7qgCYxZAVC9tzR47i','hospital'),
(36,'Khyber Teaching Hospital','kth.psh@vaxcare.test','091-9216440','$2y$10$mYLipAf/q2QkjJtaXOre8.7sow1NUgR2JtRJ7qgCYxZAVC9tzR47i','hospital'),
-- Quetta
(37,'Bolan Medical Complex Hospital','bmc.qta@vaxcare.test','081-2821000','$2y$10$mYLipAf/q2QkjJtaXOre8.7sow1NUgR2JtRJ7qgCYxZAVC9tzR47i','hospital'),
(38,'Civil Hospital Quetta','civil.qta@vaxcare.test','081-2822020','$2y$10$mYLipAf/q2QkjJtaXOre8.7sow1NUgR2JtRJ7qgCYxZAVC9tzR47i','hospital'),
-- Faisalabad
(39,'Allied Hospital Faisalabad','allied.fsd@vaxcare.test','041-9200471','$2y$10$mYLipAf/q2QkjJtaXOre8.7sow1NUgR2JtRJ7qgCYxZAVC9tzR47i','hospital'),
(40,'Niazi Medical & Dental College Hospital','niazi.fsd@vaxcare.test','041-8812111','$2y$10$mYLipAf/q2QkjJtaXOre8.7sow1NUgR2JtRJ7qgCYxZAVC9tzR47i','hospital'),
-- Multan
(41,'Nishtar Hospital Multan','nishtar.mtn@vaxcare.test','061-9200303','$2y$10$mYLipAf/q2QkjJtaXOre8.7sow1NUgR2JtRJ7qgCYxZAVC9tzR47i','hospital'),
(42,'Children Hospital Multan','children.mtn@vaxcare.test','061-9200401','$2y$10$mYLipAf/q2QkjJtaXOre8.7sow1NUgR2JtRJ7qgCYxZAVC9tzR47i','hospital'),
-- Hyderabad
(43,'Liaquat University Hospital Hyderabad','luh.hyd@vaxcare.test','022-9200830','$2y$10$mYLipAf/q2QkjJtaXOre8.7sow1NUgR2JtRJ7qgCYxZAVC9tzR47i','hospital'),
-- Sukkur
(44,'Ghulam Muhammad Mahar Medical College Hospital','gmmmc.skr@vaxcare.test','071-5630071','$2y$10$mYLipAf/q2QkjJtaXOre8.7sow1NUgR2JtRJ7qgCYxZAVC9tzR47i','hospital'),
-- Abbottabad
(45,'Ayub Teaching Hospital','ayub.abb@vaxcare.test','0992-381136','$2y$10$mYLipAf/q2QkjJtaXOre8.7sow1NUgR2JtRJ7qgCYxZAVC9tzR47i','hospital'),
-- Gilgit
(46,'District Headquarters Hospital Gilgit','dhq.gilt@vaxcare.test','05811-920005','$2y$10$mYLipAf/q2QkjJtaXOre8.7sow1NUgR2JtRJ7qgCYxZAVC9tzR47i','hospital'),
-- Muzaffarabad (AJK)
(47,'Shaukat Khanum Hospital Peshawar (Regi)','skmch.psh@vaxcare.test','091-5703000','$2y$10$mYLipAf/q2QkjJtaXOre8.7sow1NUgR2JtRJ7qgCYxZAVC9tzR47i','hospital'),
-- Gujranwala
(48,'DHQ Teaching Hospital Gujranwala','dhq.gjw@vaxcare.test','055-9200102','$2y$10$mYLipAf/q2QkjJtaXOre8.7sow1NUgR2JtRJ7qgCYxZAVC9tzR47i','hospital');

-- ─────────────────────────────────────────────────────────────
-- HOSPITAL RECORDS (all approved so they appear in booking)
-- ─────────────────────────────────────────────────────────────
INSERT INTO hospitals (id, user_id, name, address, city, phone, status) VALUES
-- Karachi
(10,20,'Aga Khan University Hospital','Stadium Road, Karachi 74800','Karachi','021-34930051','approved'),
(11,21,'Liaquat National Hospital','National Stadium Road, Karachi','Karachi','021-34412442','approved'),
(12,22,'South City Hospital','Block-3, Clifton, Karachi','Karachi','021-35871471','approved'),
(13,23,'Indus Hospital','Rafi Iqbal Road, Korangi, Karachi','Karachi','021-35112709','approved'),
(14,24,'Ziauddin Hospital','North Nazimabad, Karachi','Karachi','021-36699000','approved'),
-- Lahore
(15,25,'Shaukat Khanum Memorial Cancer Hospital','7A Block R-3, MA Johar Town, Lahore','Lahore','042-35945100','approved'),
(16,26,'Services Hospital Lahore','Ghaus-ul-Azam Road, Lahore','Lahore','042-99203390','approved'),
(17,27,'Doctors Hospital Lahore','152-G/1 Canal Bank, Johar Town, Lahore','Lahore','042-35761999','approved'),
(18,28,'Ittefaq Hospital','17 Factory Area, Raiwind Road, Lahore','Lahore','042-35761340','approved'),
(19,29,'Sir Ganga Ram Hospital','Shama Chowk, The Mall, Lahore','Lahore','042-37233337','approved'),
-- Islamabad / Rawalpindi
(20,30,'Shifa International Hospital','H-8/4, Islamabad','Islamabad','051-8464646','approved'),
(21,31,'Pakistan Institute of Medical Sciences (PIMS)','G-8/3, Islamabad','Islamabad','051-9261170','approved'),
(22,32,'Holy Family Hospital Rawalpindi','Satellite Town, Rawalpindi','Rawalpindi','051-9290311','approved'),
(23,33,'Benazir Bhutto Hospital','Benazir Bhutto Road, Rawalpindi','Rawalpindi','051-9270341','approved'),
-- Peshawar
(24,34,'Lady Reading Hospital','Peshawar City, Peshawar','Peshawar','091-9212301','approved'),
(25,35,'Hayatabad Medical Complex','Phase-5, Hayatabad, Peshawar','Peshawar','091-9217001','approved'),
(26,36,'Khyber Teaching Hospital','University Road, Peshawar','Peshawar','091-9216440','approved'),
-- Quetta
(27,37,'Bolan Medical Complex Hospital','Brewery Road, Quetta','Quetta','081-2821000','approved'),
(28,38,'Civil Hospital Quetta','M.A. Jinnah Road, Quetta','Quetta','081-2822020','approved'),
-- Faisalabad
(29,39,'Allied Hospital Faisalabad','Sargodha Road, Faisalabad','Faisalabad','041-9200471','approved'),
(30,40,'Niazi Medical & Dental College Hospital','Canal Road, Faisalabad','Faisalabad','041-8812111','approved'),
-- Multan
(31,41,'Nishtar Hospital Multan','Nishtar Medical University Road, Multan','Multan','061-9200303','approved'),
(32,42,'Children Hospital Multan','Nishtar Road, Multan','Multan','061-9200401','approved'),
-- Hyderabad
(33,43,'Liaquat University Hospital Hyderabad','Jamshoro Road, Hyderabad','Hyderabad','022-9200830','approved'),
-- Sukkur
(34,44,'GMMMC Hospital Sukkur','Sukkur-Shikarpur Road, Sukkur','Sukkur','071-5630071','approved'),
-- Abbottabad
(35,45,'Ayub Teaching Hospital','Mansehra Road, Abbottabad','Abbottabad','0992-381136','approved'),
-- Gilgit
(36,46,'DHQ Hospital Gilgit','Jutial, Gilgit','Gilgit','05811-920005','approved'),
-- Peshawar (Regi campus)
(37,47,'Shaukat Khanum Hospital Peshawar','Regi Model Town, Peshawar','Peshawar','091-5703000','approved'),
-- Gujranwala
(38,48,'DHQ Teaching Hospital Gujranwala','GT Road, Gujranwala','Gujranwala','055-9200102','approved');

-- ─────────────────────────────────────────────────────────────
-- VACCINE STOCK for new hospitals (stock = 50, price = 0)
-- ─────────────────────────────────────────────────────────────
INSERT INTO hospital_vaccines(hospital_id, vaccine_id, stock, price_pkr)
  SELECT h.id, v.id, 50, 0.00
  FROM hospitals h
  CROSS JOIN vaccines v
  WHERE h.id BETWEEN 10 AND 38
  ON DUPLICATE KEY UPDATE stock = stock;
