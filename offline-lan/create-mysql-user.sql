-- ============================================================
--  RUN THIS ON THE INVENTORY PC (192.168.150.80) ONLY
--  This is the PC that holds the shared database.
--
--  How to run:
--    1. Open XAMPP -> MySQL -> "Admin" (opens phpMyAdmin in browser)
--    2. Click the "SQL" tab at the top
--    3. Paste everything below, click "Go"
--
--  This lets the STORE PC (192.168.150.106) connect to the database.
--  If you change the password, also change it in the store PC's .env file.
-- ============================================================

CREATE USER IF NOT EXISTS 'inventory'@'192.168.150.106' IDENTIFIED BY 'imprint2026';
GRANT ALL PRIVILEGES ON inventory_system.* TO 'inventory'@'192.168.150.106';
FLUSH PRIVILEGES;
