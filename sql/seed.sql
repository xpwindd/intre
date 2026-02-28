USE smart_garden;

INSERT INTO roles (name, slug) VALUES
('Пользователь', 'user'),
('Администратор', 'admin');

INSERT INTO users (role_id, name, email, password_hash, created_at) VALUES
((SELECT id FROM roles WHERE slug='admin'), 'Админ', 'admin@smartgarden.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NOW()),
((SELECT id FROM roles WHERE slug='user'), 'Иван Петров', 'user@smartgarden.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NOW());

INSERT INTO plant_catalog (name, category, optimal_soil_humidity, optimal_temperature, optimal_light_hours, description) VALUES
('Томат черри', 'овощи', 62, 24, 14, 'Компактный сорт для дома и теплицы'),
('Огурец мини', 'овощи', 68, 23, 13, 'Скороспелый сорт для контейнера'),
('Базилик', 'зелень', 58, 24, 12, 'Ароматная зелень'),
('Петрушка', 'зелень', 56, 20, 10, 'Неприхотливая зелень'),
('Салат листовой', 'зелень', 64, 19, 11, 'Быстрый цикл роста'),
('Мята', 'травы', 60, 21, 9, 'Хорошо растет в полутени'),
('Клубника ремонтантная', 'ягоды', 65, 22, 12, 'Плодоношение волнами'),
('Розмарин', 'травы', 45, 22, 10, 'Требует умеренного полива'),
('Микрозелень гороха', 'микрозелень', 70, 21, 8, 'Короткий цикл выращивания'),
('Шпинат', 'зелень', 63, 18, 10, 'Холодостойкая культура'),
('Перец сладкий', 'овощи', 61, 25, 14, 'Теплолюбивый сорт'),
('Укроп', 'зелень', 57, 19, 10, 'Подходит для подоконника');

INSERT INTO zones (user_id, name, zone_type, description, created_at) VALUES
(2, 'Кухонный стеллаж', 'комната', 'Основная зона гидропоники', NOW()),
(2, 'Балкон южный', 'балкон', 'Сезонные контейнеры', NOW()),
(2, 'Мини-теплица', 'теплица', 'Зона рассады', NOW());

INSERT INTO plants (user_id, zone_id, catalog_id, name, stage, planted_at, target_soil_humidity, target_temperature, target_light_hours, created_at) VALUES
(2, 1, 3, 'Базилик №1', 'рост', CURDATE(), 58, 24, 12, NOW()),
(2, 2, 1, 'Томат Черри', 'вегетация', CURDATE(), 62, 24, 14, NOW()),
(2, 3, 11, 'Перец сладкий', 'рассада', CURDATE(), 61, 25, 14, NOW());

INSERT INTO devices (user_id, zone_id, name, device_type, status, is_auto, created_at, updated_at) VALUES
(2, 1, 'Насос полива A1', 'pump', 'off', 1, NOW(), NOW()),
(2, 1, 'LED лампа L1', 'lamp', 'on', 1, NOW(), NOW()),
(2, 2, 'Вентилятор F1', 'fan', 'off', 0, NOW(), NOW()),
(2, 3, 'Увлажнитель H1', 'humidifier', 'on', 1, NOW(), NOW()),
(2, 3, 'Насос капельный B2', 'pump', 'off', 0, NOW(), NOW());

INSERT INTO schedules (user_id, name, schedule_type, execute_time, is_active, created_at) VALUES
(2, 'Утренний полив', 'watering', '08:00:00', 1, NOW()),
(2, 'Вечерний свет', 'lighting', '18:30:00', 1, NOW());

INSERT INTO notifications (user_id, title, message, severity, is_read, created_at) VALUES
(2, 'Низкая влажность', 'В зоне "Балкон южный" влажность почвы ниже порога.', 'high', 0, NOW()),
(2, 'Напоминание о подкормке', 'Для томата рекомендуется подкормка каждые 10 дней.', 'medium', 0, NOW()),
(2, 'Пропущенный полив', 'Расписание "Утренний полив" не выполнено вовремя.', 'high', 1, NOW());

INSERT INTO growth_diary (user_id, plant_id, entry_date, note, height_cm, condition_text, created_at) VALUES
(2, 1, CURDATE(), 'Появились новые листья, цвет насыщенно-зеленый.', 14.2, 'здоровое', NOW()),
(2, 2, CURDATE(), 'Стебель укрепился, наблюдается активный рост.', 22.7, 'хорошее', NOW());

INSERT INTO care_events (user_id, plant_id, event_type, event_date, note, created_at) VALUES
(2, 1, 'полив', CURDATE(), 'Полив 300 мл', NOW()),
(2, 2, 'подкормка', CURDATE(), 'Комплексное удобрение NPK', NOW());

INSERT INTO sensor_readings (user_id, zone_id, soil_humidity, temperature, air_humidity, light_level, reading_time) VALUES
(2, 1, 59.3, 23.6, 47.1, 5100, DATE_SUB(NOW(), INTERVAL 70 MINUTE)),
(2, 1, 58.5, 23.8, 46.5, 5200, DATE_SUB(NOW(), INTERVAL 55 MINUTE)),
(2, 1, 57.9, 24.0, 46.0, 5300, DATE_SUB(NOW(), INTERVAL 40 MINUTE)),
(2, 2, 52.8, 21.7, 49.4, 6100, DATE_SUB(NOW(), INTERVAL 30 MINUTE)),
(2, 2, 51.9, 21.4, 50.0, 6200, DATE_SUB(NOW(), INTERVAL 20 MINUTE)),
(2, 3, 66.2, 24.3, 55.2, 4500, DATE_SUB(NOW(), INTERVAL 15 MINUTE)),
(2, 3, 65.8, 24.1, 54.9, 4400, DATE_SUB(NOW(), INTERVAL 5 MINUTE));

INSERT INTO system_logs (user_id, action, message, created_at) VALUES
(2, 'seed.init', 'Инициализация демонстрационных данных', NOW());
