CREATE DATABASE IF NOT EXISTS smart_garden CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE smart_garden;

CREATE TABLE roles (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(50) NOT NULL,
  slug VARCHAR(30) NOT NULL UNIQUE
);

CREATE TABLE users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  role_id INT UNSIGNED NOT NULL,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(190) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  created_at DATETIME NOT NULL,
  FOREIGN KEY (role_id) REFERENCES roles(id)
);

CREATE TABLE zones (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  name VARCHAR(120) NOT NULL,
  zone_type VARCHAR(80) NOT NULL,
  description TEXT NULL,
  created_at DATETIME NOT NULL,
  INDEX idx_zones_user (user_id),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE plant_catalog (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  category VARCHAR(80) NOT NULL,
  optimal_soil_humidity DECIMAL(5,2) NOT NULL,
  optimal_temperature DECIMAL(5,2) NOT NULL,
  optimal_light_hours INT NOT NULL,
  description TEXT NULL
);

CREATE TABLE plants (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  zone_id INT UNSIGNED NULL,
  catalog_id INT UNSIGNED NULL,
  name VARCHAR(120) NOT NULL,
  stage VARCHAR(80) NOT NULL,
  planted_at DATE NOT NULL,
  target_soil_humidity DECIMAL(5,2) NOT NULL,
  target_temperature DECIMAL(5,2) NOT NULL,
  target_light_hours INT NOT NULL,
  created_at DATETIME NOT NULL,
  INDEX idx_plants_user (user_id),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (zone_id) REFERENCES zones(id) ON DELETE SET NULL,
  FOREIGN KEY (catalog_id) REFERENCES plant_catalog(id) ON DELETE SET NULL
);

CREATE TABLE devices (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  zone_id INT UNSIGNED NULL,
  name VARCHAR(120) NOT NULL,
  device_type VARCHAR(60) NOT NULL,
  status ENUM('on','off') NOT NULL DEFAULT 'off',
  is_auto TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NULL,
  INDEX idx_devices_user (user_id),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (zone_id) REFERENCES zones(id) ON DELETE SET NULL
);

CREATE TABLE sensor_readings (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  zone_id INT UNSIGNED NULL,
  soil_humidity DECIMAL(5,2) NOT NULL,
  temperature DECIMAL(5,2) NOT NULL,
  air_humidity DECIMAL(5,2) NOT NULL,
  light_level DECIMAL(8,2) NOT NULL,
  reading_time DATETIME NOT NULL,
  INDEX idx_sensor_user_time (user_id, reading_time),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (zone_id) REFERENCES zones(id) ON DELETE SET NULL
);

CREATE TABLE growth_diary (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  plant_id INT UNSIGNED NOT NULL,
  entry_date DATE NOT NULL,
  note TEXT NOT NULL,
  height_cm DECIMAL(6,2) NULL,
  condition_text VARCHAR(255) NULL,
  photo_path VARCHAR(255) NULL,
  created_at DATETIME NOT NULL,
  INDEX idx_diary_user_date (user_id, entry_date),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (plant_id) REFERENCES plants(id) ON DELETE CASCADE
);

CREATE TABLE care_events (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  plant_id INT UNSIGNED NOT NULL,
  event_type VARCHAR(80) NOT NULL,
  event_date DATE NOT NULL,
  note TEXT NULL,
  created_at DATETIME NOT NULL,
  INDEX idx_care_user_date (user_id, event_date),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (plant_id) REFERENCES plants(id) ON DELETE CASCADE
);

CREATE TABLE schedules (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  name VARCHAR(120) NOT NULL,
  schedule_type VARCHAR(50) NOT NULL,
  execute_time TIME NOT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL,
  INDEX idx_sched_user (user_id),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE notifications (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  title VARCHAR(150) NOT NULL,
  message TEXT NOT NULL,
  severity ENUM('low','medium','high') NOT NULL DEFAULT 'low',
  is_read TINYINT(1) NOT NULL DEFAULT 0,
  read_at DATETIME NULL,
  created_at DATETIME NOT NULL,
  INDEX idx_notifications_user (user_id),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE system_logs (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NULL,
  action VARCHAR(120) NOT NULL,
  message TEXT NOT NULL,
  created_at DATETIME NOT NULL,
  INDEX idx_logs_user_time (user_id, created_at),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE password_resets (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  token VARCHAR(120) NOT NULL UNIQUE,
  expires_at DATETIME NOT NULL,
  used_at DATETIME NULL,
  created_at DATETIME NOT NULL,
  INDEX idx_reset_user (user_id),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE settings (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  setting_key VARCHAR(120) NOT NULL,
  setting_value TEXT NULL,
  UNIQUE KEY uniq_user_setting (user_id, setting_key),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
