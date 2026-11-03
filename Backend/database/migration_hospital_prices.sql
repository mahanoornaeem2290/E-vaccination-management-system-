-- Run once on an existing VaxCare database to add hospital-managed vaccine prices.
USE vaccination_db;
ALTER TABLE hospital_vaccines
  ADD COLUMN price_pkr DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER stock;
