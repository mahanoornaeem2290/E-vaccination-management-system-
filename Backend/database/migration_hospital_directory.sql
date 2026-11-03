-- Run once on an existing demo database. The .test hospital accounts are sample records,
-- so require an admin to verify them before they appear as bookable partners.
USE vaccination_db;
UPDATE hospitals h
JOIN users u ON u.id = h.user_id
SET h.status = 'pending'
WHERE u.email IN (
  'hospital@vaxcare.test', 'clinic@vaxcare.test', 'national@vaxcare.test',
  'sunrise@vaxcare.test', 'lifeline@vaxcare.test', 'carepoint@vaxcare.test',
  'demo.gulshan@vaxcare.test', 'demo.clifton@vaxcare.test', 'demo.north@vaxcare.test'
);
