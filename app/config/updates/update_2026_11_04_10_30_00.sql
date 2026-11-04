START TRANSACTION;

CREATE TABLE IF NOT EXISTS `vouchers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `calendar_id` int(10) unsigned DEFAULT NULL,
  `code` varchar(50) NOT NULL,
  `type` enum('percent','amount') NOT NULL DEFAULT 'percent',
  `apply` enum('each','total') NOT NULL DEFAULT 'each',
  `discount` decimal(16,2) unsigned NOT NULL DEFAULT '0.00',
  `valid` enum('always','fixed','period','recurring') NOT NULL DEFAULT 'always',
  `valid_on` enum('booking','appointment') NOT NULL DEFAULT 'booking',
  `date_from` date DEFAULT NULL,
  `date_to` date DEFAULT NULL,
  `time_from` time DEFAULT NULL,
  `time_to` time DEFAULT NULL,
  `every` enum('monday','tuesday','wednesday','thursday','friday','saturday','sunday') DEFAULT NULL,
  `max_uses` int(10) unsigned DEFAULT NULL,
  `max_uses_per_email` int(10) unsigned DEFAULT NULL,
  `status` enum('T','F') NOT NULL DEFAULT 'T',
  `created` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `calendar_code` (`calendar_id`,`code`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `vouchers_services` (
  `voucher_id` int(10) unsigned NOT NULL,
  `service_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`voucher_id`,`service_id`),
  KEY `service_id` (`service_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 2) bookings columns (the prefixed table name is built from a literal so the installer's prefixing applies)
SET @bk := (SELECT REPLACE(REPLACE('FROM `bookings`', 'FROM `', ''), '`', ''));
SET @c1 := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @bk AND COLUMN_NAME = 'booking_discount');
SET @s1 := IF(@c1 = 0, CONCAT('ALTER TABLE `', @bk, '` ADD COLUMN `booking_discount` decimal(16,2) unsigned DEFAULT NULL AFTER `booking_tax`'), 'SELECT 1');
PREPARE st1 FROM @s1;
EXECUTE st1;
DEALLOCATE PREPARE st1;
SET @c2 := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @bk AND COLUMN_NAME = 'voucher_code');
SET @s2 := IF(@c2 = 0, CONCAT('ALTER TABLE `', @bk, '` ADD COLUMN `voucher_code` varchar(50) DEFAULT NULL AFTER `booking_discount`, ADD KEY `voucher_code` (`voucher_code`)'), 'SELECT 1');
PREPARE st2 FROM @s2;
EXECUTE st2;
DEALLOCATE PREPARE st2;

-- 3) permissions
INSERT INTO `plugin_auth_permissions` (`id`, `parent_id`, `key`, `is_shown`)
SELECT NULL, NULL, 'pjAdminVouchers', 'T' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `plugin_auth_permissions` x WHERE x.`key` = 'pjAdminVouchers');

INSERT INTO `plugin_auth_permissions` (`id`, `parent_id`, `key`, `is_shown`)
SELECT NULL, (SELECT p.`id` FROM `plugin_auth_permissions` p WHERE p.`key` = 'pjAdminVouchers' LIMIT 1), 'pjAdminVouchers_pjActionIndex', 'T' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `plugin_auth_permissions` x WHERE x.`key` = 'pjAdminVouchers_pjActionIndex');

INSERT INTO `plugin_auth_permissions` (`id`, `parent_id`, `key`, `is_shown`)
SELECT NULL, (SELECT p.`id` FROM `plugin_auth_permissions` p WHERE p.`key` = 'pjAdminVouchers_pjActionIndex' LIMIT 1), 'pjAdminVouchers_pjActionCreate', 'T' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `plugin_auth_permissions` x WHERE x.`key` = 'pjAdminVouchers_pjActionCreate');

INSERT INTO `plugin_auth_permissions` (`id`, `parent_id`, `key`, `is_shown`)
SELECT NULL, (SELECT p.`id` FROM `plugin_auth_permissions` p WHERE p.`key` = 'pjAdminVouchers_pjActionIndex' LIMIT 1), 'pjAdminVouchers_pjActionUpdate', 'T' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `plugin_auth_permissions` x WHERE x.`key` = 'pjAdminVouchers_pjActionUpdate');

INSERT INTO `plugin_auth_permissions` (`id`, `parent_id`, `key`, `is_shown`)
SELECT NULL, (SELECT p.`id` FROM `plugin_auth_permissions` p WHERE p.`key` = 'pjAdminVouchers_pjActionIndex' LIMIT 1), 'pjAdminVouchers_pjActionDeleteVoucher', 'T' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `plugin_auth_permissions` x WHERE x.`key` = 'pjAdminVouchers_pjActionDeleteVoucher');

INSERT INTO `plugin_auth_permissions` (`id`, `parent_id`, `key`, `is_shown`)
SELECT NULL, (SELECT p.`id` FROM `plugin_auth_permissions` p WHERE p.`key` = 'pjAdminVouchers_pjActionIndex' LIMIT 1), 'pjAdminVouchers_pjActionDeleteVoucherBulk', 'T' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `plugin_auth_permissions` x WHERE x.`key` = 'pjAdminVouchers_pjActionDeleteVoucherBulk');

INSERT INTO `plugin_auth_permissions` (`id`, `parent_id`, `key`, `is_shown`)
SELECT NULL, (SELECT p.`id` FROM `plugin_auth_permissions` p WHERE p.`key` = 'pjAdminVouchers_pjActionIndex' LIMIT 1), 'pjAdminVouchers_pjActionGetVoucher', 'F' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `plugin_auth_permissions` x WHERE x.`key` = 'pjAdminVouchers_pjActionGetVoucher');

INSERT INTO `plugin_auth_permissions` (`id`, `parent_id`, `key`, `is_shown`)
SELECT NULL, (SELECT p.`id` FROM `plugin_auth_permissions` p WHERE p.`key` = 'pjAdminVouchers_pjActionIndex' LIMIT 1), 'pjAdminVouchers_pjActionSaveVoucher', 'F' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `plugin_auth_permissions` x WHERE x.`key` = 'pjAdminVouchers_pjActionSaveVoucher');

INSERT INTO `plugin_auth_permissions` (`id`, `parent_id`, `key`, `is_shown`)
SELECT NULL, (SELECT p.`id` FROM `plugin_auth_permissions` p WHERE p.`key` = 'pjAdminVouchers_pjActionIndex' LIMIT 1), 'pjAdminVouchers_pjActionCheckCode', 'F' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `plugin_auth_permissions` x WHERE x.`key` = 'pjAdminVouchers_pjActionCheckCode');

-- permission titles
-- German (de-AT) text for every German locale of the installation
INSERT IGNORE INTO `plugin_base_multi_lang` (`id`, `foreign_id`, `model`, `locale`, `field`, `content`, `source`)
SELECT NULL, p.`id`, 'pjAuthPermission', l.`locale`, 'title', t.`txt`, 'data'
FROM `plugin_auth_permissions` p
CROSS JOIN (SELECT `id` AS `locale` FROM `plugin_base_locale` WHERE `language_iso` LIKE 'de%') l
INNER JOIN (
SELECT 'pjAdminVouchers' AS `k`, 'Gutschein-Menü' AS `txt`
UNION ALL SELECT 'pjAdminVouchers_pjActionIndex', 'Gutscheinliste'
UNION ALL SELECT 'pjAdminVouchers_pjActionCreate', 'Gutschein hinzufügen'
UNION ALL SELECT 'pjAdminVouchers_pjActionUpdate', 'Gutschein bearbeiten'
UNION ALL SELECT 'pjAdminVouchers_pjActionDeleteVoucher', 'Einzelnen Gutschein löschen'
UNION ALL SELECT 'pjAdminVouchers_pjActionDeleteVoucherBulk', 'Mehrere Gutscheine löschen'
UNION ALL SELECT 'pjAdminVouchers_pjActionGetVoucher', 'Listenelemente anzeigen'
UNION ALL SELECT 'pjAdminVouchers_pjActionSaveVoucher', 'Gutschein speichern'
UNION ALL SELECT 'pjAdminVouchers_pjActionCheckCode', 'Gutscheincode prüfen'
) t ON t.`k` = p.`key`;

-- English text for every other locale (translate afterwards in the label editor)
INSERT IGNORE INTO `plugin_base_multi_lang` (`id`, `foreign_id`, `model`, `locale`, `field`, `content`, `source`)
SELECT NULL, p.`id`, 'pjAuthPermission', l.`locale`, 'title', t.`txt`, 'data'
FROM `plugin_auth_permissions` p
CROSS JOIN (SELECT DISTINCT `locale` FROM `plugin_base_multi_lang` WHERE `model` = 'pjBaseField' AND `locale` IS NOT NULL) l
INNER JOIN (
SELECT 'pjAdminVouchers' AS `k`, 'Voucher menu' AS `txt`
UNION ALL SELECT 'pjAdminVouchers_pjActionIndex', 'Voucher List'
UNION ALL SELECT 'pjAdminVouchers_pjActionCreate', 'Add voucher'
UNION ALL SELECT 'pjAdminVouchers_pjActionUpdate', 'Edit voucher'
UNION ALL SELECT 'pjAdminVouchers_pjActionDeleteVoucher', 'Delete single voucher'
UNION ALL SELECT 'pjAdminVouchers_pjActionDeleteVoucherBulk', 'Delete multiple vouchers'
UNION ALL SELECT 'pjAdminVouchers_pjActionGetVoucher', 'Show list elements'
UNION ALL SELECT 'pjAdminVouchers_pjActionSaveVoucher', 'Save voucher'
UNION ALL SELECT 'pjAdminVouchers_pjActionCheckCode', 'Check voucher code'
) t ON t.`k` = p.`key`;

-- grants: Administrator role/user, and every role/user that can already manage Services
INSERT IGNORE INTO `plugin_auth_roles_permissions` (`id`, `role_id`, `permission_id`)
SELECT NULL, 1, p.`id` FROM `plugin_auth_permissions` p WHERE p.`key` LIKE 'pjAdminVouchers%';

INSERT IGNORE INTO `plugin_auth_roles_permissions` (`id`, `role_id`, `permission_id`)
SELECT NULL, rp.`role_id`, p.`id`
FROM `plugin_auth_roles_permissions` rp
INNER JOIN (SELECT `id` FROM `plugin_auth_permissions` WHERE `key` LIKE 'pjAdminVouchers%') p
WHERE rp.`permission_id` = (SELECT s.`id` FROM `plugin_auth_permissions` s WHERE s.`key` = 'pjAdminServices' LIMIT 1);

INSERT IGNORE INTO `plugin_auth_users_permissions` (`id`, `user_id`, `permission_id`)
SELECT NULL, up.`user_id`, p.`id`
FROM `plugin_auth_users_permissions` up
INNER JOIN (SELECT `id` FROM `plugin_auth_permissions` WHERE `key` LIKE 'pjAdminVouchers%') p
WHERE up.`permission_id` = (SELECT s.`id` FROM `plugin_auth_permissions` s WHERE s.`key` = 'pjAdminServices' LIMIT 1);

INSERT IGNORE INTO `plugin_auth_users_permissions` (`id`, `user_id`, `permission_id`)
SELECT NULL, 1, p.`id` FROM `plugin_auth_permissions` p WHERE p.`key` LIKE 'pjAdminVouchers%';

-- 4) labels
INSERT IGNORE INTO `plugin_base_fields` (`id`, `key`, `type`, `label`, `source`, `modified`) VALUES
(NULL, 'menuVouchers', 'backend', 'Label / Vouchers', 'script', NULL),
(NULL, 'infoVouchersTitle', 'backend', 'Label / Vouchers', 'script', NULL),
(NULL, 'infoVouchersDesc', 'backend', 'Label / Create discount codes that customers can enter during checko', 'script', NULL),
(NULL, 'btnAddVoucher', 'backend', 'Label / Add voucher', 'script', NULL),
(NULL, 'infoAddVoucherTitle', 'backend', 'Label / Add voucher', 'script', NULL),
(NULL, 'infoAddVoucherDesc', 'backend', 'Label / Fill in the form below to create a new voucher code. The dis', 'script', NULL),
(NULL, 'infoUpdateVoucherTitle', 'backend', 'Label / Update voucher', 'script', NULL),
(NULL, 'infoUpdateVoucherDesc', 'backend', 'Label / Change the voucher below. Bookings that already used the vou', 'script', NULL),
(NULL, 'voucher_code', 'backend', 'Label / Code', 'script', NULL),
(NULL, 'voucher_code_hint', 'backend', 'Label / Letters, numbers, dash, underscore or dot. 2-50 characters. ', 'script', NULL),
(NULL, 'voucher_discount', 'backend', 'Label / Discount', 'script', NULL),
(NULL, 'voucher_type', 'backend', 'Label / Discount type', 'script', NULL),
(NULL, 'voucher_apply', 'backend', 'Label / Fixed amount is taken off', 'script', NULL),
(NULL, 'voucher_services', 'backend', 'Label / Services', 'script', NULL),
(NULL, 'voucher_services_hint', 'backend', 'Label / Leave empty to accept the voucher for every service. Otherwi', 'script', NULL),
(NULL, 'voucher_max_uses', 'backend', 'Label / Maximum uses', 'script', NULL),
(NULL, 'voucher_max_uses_hint', 'backend', 'Label / Total number of bookings that may use this voucher. Leave em', 'script', NULL),
(NULL, 'voucher_max_uses_per_email', 'backend', 'Label / Maximum uses per customer e-mail', 'script', NULL),
(NULL, 'voucher_max_uses_per_email_hint', 'backend', 'Label / Limit how many times the same e-mail address may use this vo', 'script', NULL),
(NULL, 'voucher_valid', 'backend', 'Label / Validity', 'script', NULL),
(NULL, 'voucher_valid_on', 'backend', 'Label / Check the date and time against', 'script', NULL),
(NULL, 'voucher_valid_on_hint', 'backend', 'Label / "Time of booking" = when the customer makes the booking. "Ap', 'script', NULL),
(NULL, 'voucher_status', 'backend', 'Label / Status', 'script', NULL),
(NULL, 'voucher_status_active', 'backend', 'Label / Active', 'script', NULL),
(NULL, 'voucher_status_inactive', 'backend', 'Label / Inactive', 'script', NULL),
(NULL, 'voucher_usage', 'backend', 'Label / Used', 'script', NULL),
(NULL, 'voucher_date', 'backend', 'Label / Date', 'script', NULL),
(NULL, 'voucher_date_from', 'backend', 'Label / From date', 'script', NULL),
(NULL, 'voucher_date_to', 'backend', 'Label / To date', 'script', NULL),
(NULL, 'voucher_time_from', 'backend', 'Label / From time', 'script', NULL),
(NULL, 'voucher_time_to', 'backend', 'Label / To time', 'script', NULL),
(NULL, 'voucher_every', 'backend', 'Label / Day of the week', 'script', NULL),
(NULL, 'voucher_time_hint', 'backend', 'Label / The "to" time is inclusive until the end of that minute. Tim', 'script', NULL),
(NULL, 'voucher_err_code', 'backend', 'Label / The code must be 2-50 characters: letters, numbers, dash, un', 'script', NULL),
(NULL, 'voucher_err_code_exists', 'backend', 'Label / This code is already used by another voucher.', 'script', NULL),
(NULL, 'voucher_err_discount', 'backend', 'Label / Enter a discount greater than 0 (a percentage cannot be more', 'script', NULL),
(NULL, 'voucher_err_dates', 'backend', 'Label / Please enter a valid date.', 'script', NULL),
(NULL, 'voucher_err_time_order', 'backend', 'Label / The "to" time must be later than the "from" time.', 'script', NULL),
(NULL, 'voucher_err_period_order', 'backend', 'Label / The end of the period must be later than its start.', 'script', NULL),
(NULL, 'voucher_err_limit', 'backend', 'Label / Usage limits must be whole numbers greater than 0, or empty.', 'script', NULL),
(NULL, 'booking_voucher', 'backend', 'Label / Voucher code', 'script', NULL),
(NULL, 'booking_discount', 'backend', 'Label / Discount', 'script', NULL),
(NULL, 'voucher_types_ARRAY_percent', 'arrays', 'voucher_types_ARRAY_percent', 'script', NULL),
(NULL, 'voucher_types_ARRAY_amount', 'arrays', 'voucher_types_ARRAY_amount', 'script', NULL),
(NULL, 'voucher_apply_arr_ARRAY_each', 'arrays', 'voucher_apply_arr_ARRAY_each', 'script', NULL),
(NULL, 'voucher_apply_arr_ARRAY_total', 'arrays', 'voucher_apply_arr_ARRAY_total', 'script', NULL),
(NULL, 'voucher_valids_ARRAY_always', 'arrays', 'voucher_valids_ARRAY_always', 'script', NULL),
(NULL, 'voucher_valids_ARRAY_fixed', 'arrays', 'voucher_valids_ARRAY_fixed', 'script', NULL),
(NULL, 'voucher_valids_ARRAY_period', 'arrays', 'voucher_valids_ARRAY_period', 'script', NULL),
(NULL, 'voucher_valids_ARRAY_recurring', 'arrays', 'voucher_valids_ARRAY_recurring', 'script', NULL),
(NULL, 'voucher_valid_on_arr_ARRAY_booking', 'arrays', 'voucher_valid_on_arr_ARRAY_booking', 'script', NULL),
(NULL, 'voucher_valid_on_arr_ARRAY_appointment', 'arrays', 'voucher_valid_on_arr_ARRAY_appointment', 'script', NULL),
(NULL, 'voucher_days_ARRAY_monday', 'arrays', 'voucher_days_ARRAY_monday', 'script', NULL),
(NULL, 'voucher_days_ARRAY_tuesday', 'arrays', 'voucher_days_ARRAY_tuesday', 'script', NULL),
(NULL, 'voucher_days_ARRAY_wednesday', 'arrays', 'voucher_days_ARRAY_wednesday', 'script', NULL),
(NULL, 'voucher_days_ARRAY_thursday', 'arrays', 'voucher_days_ARRAY_thursday', 'script', NULL),
(NULL, 'voucher_days_ARRAY_friday', 'arrays', 'voucher_days_ARRAY_friday', 'script', NULL),
(NULL, 'voucher_days_ARRAY_saturday', 'arrays', 'voucher_days_ARRAY_saturday', 'script', NULL),
(NULL, 'voucher_days_ARRAY_sunday', 'arrays', 'voucher_days_ARRAY_sunday', 'script', NULL),
(NULL, 'error_titles_ARRAY_AV01', 'arrays', 'error_titles_ARRAY_AV01', 'script', NULL),
(NULL, 'error_bodies_ARRAY_AV01', 'arrays', 'error_bodies_ARRAY_AV01', 'script', NULL),
(NULL, 'error_titles_ARRAY_AV02', 'arrays', 'error_titles_ARRAY_AV02', 'script', NULL),
(NULL, 'error_bodies_ARRAY_AV02', 'arrays', 'error_bodies_ARRAY_AV02', 'script', NULL),
(NULL, 'error_titles_ARRAY_AV03', 'arrays', 'error_titles_ARRAY_AV03', 'script', NULL),
(NULL, 'error_bodies_ARRAY_AV03', 'arrays', 'error_bodies_ARRAY_AV03', 'script', NULL),
(NULL, 'error_titles_ARRAY_AV04', 'arrays', 'error_titles_ARRAY_AV04', 'script', NULL),
(NULL, 'error_bodies_ARRAY_AV04', 'arrays', 'error_bodies_ARRAY_AV04', 'script', NULL),
(NULL, 'error_titles_ARRAY_AV08', 'arrays', 'error_titles_ARRAY_AV08', 'script', NULL),
(NULL, 'error_bodies_ARRAY_AV08', 'arrays', 'error_bodies_ARRAY_AV08', 'script', NULL),
(NULL, 'error_titles_ARRAY_AV09', 'arrays', 'error_titles_ARRAY_AV09', 'script', NULL),
(NULL, 'error_bodies_ARRAY_AV09', 'arrays', 'error_bodies_ARRAY_AV09', 'script', NULL),
(NULL, 'front_voucher_label', 'frontend', 'Frontend / Have a voucher code?', 'script', NULL),
(NULL, 'front_voucher_placeholder', 'frontend', 'Frontend / Enter your code', 'script', NULL),
(NULL, 'front_voucher_apply', 'frontend', 'Frontend / Apply', 'script', NULL),
(NULL, 'front_voucher_remove', 'frontend', 'Frontend / Remove', 'script', NULL),
(NULL, 'front_voucher_discount', 'frontend', 'Frontend / Discount', 'script', NULL),
(NULL, 'front_voucher_applied', 'frontend', 'Frontend / Voucher applied.', 'script', NULL),
(NULL, 'front_voucher_removed', 'frontend', 'Frontend / Voucher removed.', 'script', NULL),
(NULL, 'front_voucher_missing', 'frontend', 'Frontend / Please enter a voucher code.', 'script', NULL),
(NULL, 'front_voucher_not_found', 'frontend', 'Frontend / This voucher code is not valid.', 'script', NULL),
(NULL, 'front_voucher_inactive', 'frontend', 'Frontend / This voucher is not active.', 'script', NULL),
(NULL, 'front_voucher_expired', 'frontend', 'Frontend / This voucher has expired.', 'script', NULL),
(NULL, 'front_voucher_not_started', 'frontend', 'Frontend / This voucher is not valid yet.', 'script', NULL),
(NULL, 'front_voucher_not_for_service', 'frontend', 'Frontend / This voucher does not apply to the selected service(s).', 'script', NULL),
(NULL, 'front_voucher_not_for_time', 'frontend', 'Frontend / This voucher is not valid for the selected date or time.', 'script', NULL),
(NULL, 'front_voucher_limit', 'frontend', 'Frontend / This voucher has reached its usage limit.', 'script', NULL),
(NULL, 'front_voucher_limit_email', 'frontend', 'Frontend / You have already used this voucher the maximum number of tim', 'script', NULL),
(NULL, 'front_voucher_empty_cart', 'frontend', 'Frontend / Please add a service before applying a voucher.', 'script', NULL),
(NULL, 'front_voucher_busy', 'frontend', 'Frontend / The voucher is being used by another booking right now. Plea', 'script', NULL),
(NULL, 'front_voucher_throttled', 'frontend', 'Frontend / Too many attempts. Please wait a few minutes and try again.', 'script', NULL),
(NULL, 'pjAdminVouchers', 'backend', 'Label / Voucher menu', 'script', NULL),
(NULL, 'pjAdminVouchers_pjActionIndex', 'backend', 'Label / Voucher List', 'script', NULL),
(NULL, 'pjAdminVouchers_pjActionCreate', 'backend', 'Label / Add voucher', 'script', NULL),
(NULL, 'pjAdminVouchers_pjActionUpdate', 'backend', 'Label / Edit voucher', 'script', NULL),
(NULL, 'pjAdminVouchers_pjActionDeleteVoucher', 'backend', 'Label / Delete single voucher', 'script', NULL),
(NULL, 'pjAdminVouchers_pjActionDeleteVoucherBulk', 'backend', 'Label / Delete multiple vouchers', 'script', NULL),
(NULL, 'pjAdminVouchers_pjActionGetVoucher', 'backend', 'Label / Show list elements', 'script', NULL),
(NULL, 'pjAdminVouchers_pjActionSaveVoucher', 'backend', 'Label / Save voucher', 'script', NULL),
(NULL, 'pjAdminVouchers_pjActionCheckCode', 'backend', 'Label / Check voucher code', 'script', NULL);

-- German (de-AT) text for every German locale of the installation
INSERT IGNORE INTO `plugin_base_multi_lang` (`id`, `foreign_id`, `model`, `locale`, `field`, `content`, `source`)
SELECT NULL, f.`id`, 'pjBaseField', l.`locale`, 'title', t.`txt`, 'script'
FROM `plugin_base_fields` f
CROSS JOIN (SELECT `id` AS `locale` FROM `plugin_base_locale` WHERE `language_iso` LIKE 'de%') l
INNER JOIN (
SELECT 'menuVouchers' AS `k`, 'Gutscheine' AS `txt`
UNION ALL SELECT 'infoVouchersTitle', 'Gutscheine'
UNION ALL SELECT 'infoVouchersDesc', 'Erstellen Sie Rabattcodes, die Kunden beim Checkout eingeben können. Ein Gutschein kann einen Prozentsatz oder einen Fixbetrag abziehen und auf bestimmte Leistungen, einen Datums- oder Zeitraum und eine maximale Anzahl an Verwendungen beschränkt werden.'
UNION ALL SELECT 'btnAddVoucher', 'Gutschein hinzufügen'
UNION ALL SELECT 'infoAddVoucherTitle', 'Gutschein hinzufügen'
UNION ALL SELECT 'infoAddVoucherDesc', 'Füllen Sie das Formular aus, um einen neuen Gutscheincode zu erstellen. Der Rabatt wird immer auf dem Server aus den Leistungen im Warenkorb berechnet.'
UNION ALL SELECT 'infoUpdateVoucherTitle', 'Gutschein aktualisieren'
UNION ALL SELECT 'infoUpdateVoucherDesc', 'Ändern Sie den Gutschein unten. Buchungen, die den Gutschein bereits verwendet haben, behalten den gewährten Rabatt.'
UNION ALL SELECT 'voucher_code', 'Code'
UNION ALL SELECT 'voucher_code_hint', 'Buchstaben, Zahlen, Bindestrich, Unterstrich oder Punkt. 2-50 Zeichen. Groß-/Kleinschreibung wird nicht unterschieden.'
UNION ALL SELECT 'voucher_discount', 'Rabatt'
UNION ALL SELECT 'voucher_type', 'Rabattart'
UNION ALL SELECT 'voucher_apply', 'Fixbetrag wird abgezogen'
UNION ALL SELECT 'voucher_services', 'Leistungen'
UNION ALL SELECT 'voucher_services_hint', 'Leer lassen, um den Gutschein für alle Leistungen zu akzeptieren. Andernfalls werden nur die ausgewählten Leistungen vergünstigt.'
UNION ALL SELECT 'voucher_max_uses', 'Maximale Verwendungen'
UNION ALL SELECT 'voucher_max_uses_hint', 'Gesamtzahl der Buchungen, die diesen Gutschein verwenden dürfen. Leer lassen für unbegrenzt. Stornierte Buchungen zählen nicht.'
UNION ALL SELECT 'voucher_max_uses_per_email', 'Maximale Verwendungen pro Kunden-E-Mail'
UNION ALL SELECT 'voucher_max_uses_per_email_hint', 'Begrenzt, wie oft dieselbe E-Mail-Adresse diesen Gutschein verwenden darf. Leer lassen für unbegrenzt.'
UNION ALL SELECT 'voucher_valid', 'Gültigkeit'
UNION ALL SELECT 'voucher_valid_on', 'Datum und Uhrzeit prüfen gegen'
UNION ALL SELECT 'voucher_valid_on_hint', '"Zeitpunkt der Buchung" = wann der Kunde die Buchung vornimmt. "Termin (Datum und Uhrzeit)" = Datum und Startzeit jedes Termins im Warenkorb.'
UNION ALL SELECT 'voucher_status', 'Status'
UNION ALL SELECT 'voucher_status_active', 'Aktiv'
UNION ALL SELECT 'voucher_status_inactive', 'Inaktiv'
UNION ALL SELECT 'voucher_usage', 'Verwendet'
UNION ALL SELECT 'voucher_date', 'Datum'
UNION ALL SELECT 'voucher_date_from', 'Von Datum'
UNION ALL SELECT 'voucher_date_to', 'Bis Datum'
UNION ALL SELECT 'voucher_time_from', 'Von Uhrzeit'
UNION ALL SELECT 'voucher_time_to', 'Bis Uhrzeit'
UNION ALL SELECT 'voucher_every', 'Wochentag'
UNION ALL SELECT 'voucher_time_hint', 'Die "Bis"-Uhrzeit gilt einschließlich bis zum Ende dieser Minute. Es werden das Datums- und Zeitformat und die Zeitzone aus den Skript-Optionen verwendet.'
UNION ALL SELECT 'voucher_err_code', 'Der Code muss 2-50 Zeichen lang sein: Buchstaben, Zahlen, Bindestrich, Unterstrich oder Punkt.'
UNION ALL SELECT 'voucher_err_code_exists', 'Dieser Code wird bereits von einem anderen Gutschein verwendet.'
UNION ALL SELECT 'voucher_err_discount', 'Geben Sie einen Rabatt größer als 0 ein (ein Prozentsatz darf nicht mehr als 100 betragen).'
UNION ALL SELECT 'voucher_err_dates', 'Bitte geben Sie ein gültiges Datum ein.'
UNION ALL SELECT 'voucher_err_time_order', 'Die "Bis"-Uhrzeit muss nach der "Von"-Uhrzeit liegen.'
UNION ALL SELECT 'voucher_err_period_order', 'Das Ende des Zeitraums muss nach dessen Beginn liegen.'
UNION ALL SELECT 'voucher_err_limit', 'Verwendungsgrenzen müssen ganze Zahlen größer als 0 sein, oder leer.'
UNION ALL SELECT 'booking_voucher', 'Gutscheincode'
UNION ALL SELECT 'booking_discount', 'Rabatt'
UNION ALL SELECT 'voucher_types_ARRAY_percent', 'Prozent (%)'
UNION ALL SELECT 'voucher_types_ARRAY_amount', 'Fixbetrag'
UNION ALL SELECT 'voucher_apply_arr_ARRAY_each', 'Auf jede vergünstigte Leistung'
UNION ALL SELECT 'voucher_apply_arr_ARRAY_total', 'Einmal auf die gesamte Buchung'
UNION ALL SELECT 'voucher_valids_ARRAY_always', 'Immer gültig'
UNION ALL SELECT 'voucher_valids_ARRAY_fixed', 'Ein Tag, Zeitfenster'
UNION ALL SELECT 'voucher_valids_ARRAY_period', 'Datums- und Zeitraum'
UNION ALL SELECT 'voucher_valids_ARRAY_recurring', 'Wöchentlich, Zeitfenster'
UNION ALL SELECT 'voucher_valid_on_arr_ARRAY_booking', 'Zeitpunkt der Buchung'
UNION ALL SELECT 'voucher_valid_on_arr_ARRAY_appointment', 'Termin (Datum und Uhrzeit)'
UNION ALL SELECT 'voucher_days_ARRAY_monday', 'Montag'
UNION ALL SELECT 'voucher_days_ARRAY_tuesday', 'Dienstag'
UNION ALL SELECT 'voucher_days_ARRAY_wednesday', 'Mittwoch'
UNION ALL SELECT 'voucher_days_ARRAY_thursday', 'Donnerstag'
UNION ALL SELECT 'voucher_days_ARRAY_friday', 'Freitag'
UNION ALL SELECT 'voucher_days_ARRAY_saturday', 'Samstag'
UNION ALL SELECT 'voucher_days_ARRAY_sunday', 'Sonntag'
UNION ALL SELECT 'error_titles_ARRAY_AV01', 'Gutschein hinzugefügt'
UNION ALL SELECT 'error_bodies_ARRAY_AV01', 'Der Gutschein wurde hinzugefügt.'
UNION ALL SELECT 'error_titles_ARRAY_AV02', 'Gutschein konnte nicht hinzugefügt werden'
UNION ALL SELECT 'error_bodies_ARRAY_AV02', 'Der Gutschein konnte nicht hinzugefügt werden.'
UNION ALL SELECT 'error_titles_ARRAY_AV03', 'Gutschein aktualisiert'
UNION ALL SELECT 'error_bodies_ARRAY_AV03', 'Der Gutschein wurde aktualisiert.'
UNION ALL SELECT 'error_titles_ARRAY_AV04', 'Gutschein(e) gelöscht'
UNION ALL SELECT 'error_bodies_ARRAY_AV04', 'Die ausgewählten Gutscheine wurden gelöscht.'
UNION ALL SELECT 'error_titles_ARRAY_AV08', 'Gutschein nicht gefunden'
UNION ALL SELECT 'error_bodies_ARRAY_AV08', 'Der Gutschein, den Sie bearbeiten möchten, wurde nicht gefunden.'
UNION ALL SELECT 'error_titles_ARRAY_AV09', 'Gutschein nicht akzeptiert'
UNION ALL SELECT 'error_bodies_ARRAY_AV09', 'Die Buchung wurde nicht gespeichert, da der Gutscheincode dafür nicht gültig ist. Prüfen Sie Code, Leistungen sowie Datum oder Uhrzeit, oder entfernen Sie den Gutschein.'
UNION ALL SELECT 'front_voucher_label', 'Haben Sie einen Gutscheincode?'
UNION ALL SELECT 'front_voucher_placeholder', 'Code eingeben'
UNION ALL SELECT 'front_voucher_apply', 'Einlösen'
UNION ALL SELECT 'front_voucher_remove', 'Entfernen'
UNION ALL SELECT 'front_voucher_discount', 'Rabatt'
UNION ALL SELECT 'front_voucher_applied', 'Gutschein eingelöst.'
UNION ALL SELECT 'front_voucher_removed', 'Gutschein entfernt.'
UNION ALL SELECT 'front_voucher_missing', 'Bitte geben Sie einen Gutscheincode ein.'
UNION ALL SELECT 'front_voucher_not_found', 'Dieser Gutscheincode ist nicht gültig.'
UNION ALL SELECT 'front_voucher_inactive', 'Dieser Gutschein ist nicht aktiv.'
UNION ALL SELECT 'front_voucher_expired', 'Dieser Gutschein ist abgelaufen.'
UNION ALL SELECT 'front_voucher_not_started', 'Dieser Gutschein ist noch nicht gültig.'
UNION ALL SELECT 'front_voucher_not_for_service', 'Dieser Gutschein gilt nicht für die ausgewählten Leistungen.'
UNION ALL SELECT 'front_voucher_not_for_time', 'Dieser Gutschein ist für das gewählte Datum oder die gewählte Uhrzeit nicht gültig.'
UNION ALL SELECT 'front_voucher_limit', 'Dieser Gutschein hat sein Verwendungslimit erreicht.'
UNION ALL SELECT 'front_voucher_limit_email', 'Sie haben diesen Gutschein bereits die maximale Anzahl an Malen verwendet.'
UNION ALL SELECT 'front_voucher_empty_cart', 'Bitte fügen Sie eine Leistung hinzu, bevor Sie einen Gutschein einlösen.'
UNION ALL SELECT 'front_voucher_busy', 'Der Gutschein wird gerade für eine andere Buchung verwendet. Bitte versuchen Sie es in einem Moment erneut.'
UNION ALL SELECT 'front_voucher_throttled', 'Zu viele Versuche. Bitte warten Sie einige Minuten und versuchen Sie es erneut.'
UNION ALL SELECT 'pjAdminVouchers', 'Gutschein-Menü'
UNION ALL SELECT 'pjAdminVouchers_pjActionIndex', 'Gutscheinliste'
UNION ALL SELECT 'pjAdminVouchers_pjActionCreate', 'Gutschein hinzufügen'
UNION ALL SELECT 'pjAdminVouchers_pjActionUpdate', 'Gutschein bearbeiten'
UNION ALL SELECT 'pjAdminVouchers_pjActionDeleteVoucher', 'Einzelnen Gutschein löschen'
UNION ALL SELECT 'pjAdminVouchers_pjActionDeleteVoucherBulk', 'Mehrere Gutscheine löschen'
UNION ALL SELECT 'pjAdminVouchers_pjActionGetVoucher', 'Listenelemente anzeigen'
UNION ALL SELECT 'pjAdminVouchers_pjActionSaveVoucher', 'Gutschein speichern'
UNION ALL SELECT 'pjAdminVouchers_pjActionCheckCode', 'Gutscheincode prüfen'
) t ON t.`k` = f.`key`;

-- English text for every other locale (translate afterwards in the label editor)
INSERT IGNORE INTO `plugin_base_multi_lang` (`id`, `foreign_id`, `model`, `locale`, `field`, `content`, `source`)
SELECT NULL, f.`id`, 'pjBaseField', l.`locale`, 'title', t.`txt`, 'script'
FROM `plugin_base_fields` f
CROSS JOIN (SELECT DISTINCT `locale` FROM `plugin_base_multi_lang` WHERE `model` = 'pjBaseField' AND `locale` IS NOT NULL) l
INNER JOIN (
SELECT 'menuVouchers' AS `k`, 'Vouchers' AS `txt`
UNION ALL SELECT 'infoVouchersTitle', 'Vouchers'
UNION ALL SELECT 'infoVouchersDesc', 'Create discount codes that customers can enter during checkout. A voucher can give a percentage or a fixed amount off, be limited to chosen services, to a date or time window and to a maximum number of uses.'
UNION ALL SELECT 'btnAddVoucher', 'Add voucher'
UNION ALL SELECT 'infoAddVoucherTitle', 'Add voucher'
UNION ALL SELECT 'infoAddVoucherDesc', 'Fill in the form below to create a new voucher code. The discount is always calculated on the server from the services in the cart.'
UNION ALL SELECT 'infoUpdateVoucherTitle', 'Update voucher'
UNION ALL SELECT 'infoUpdateVoucherDesc', 'Change the voucher below. Bookings that already used the voucher keep the discount they were given.'
UNION ALL SELECT 'voucher_code', 'Code'
UNION ALL SELECT 'voucher_code_hint', 'Letters, numbers, dash, underscore or dot. 2-50 characters. Codes are not case sensitive.'
UNION ALL SELECT 'voucher_discount', 'Discount'
UNION ALL SELECT 'voucher_type', 'Discount type'
UNION ALL SELECT 'voucher_apply', 'Fixed amount is taken off'
UNION ALL SELECT 'voucher_services', 'Services'
UNION ALL SELECT 'voucher_services_hint', 'Leave empty to accept the voucher for every service. Otherwise only the selected services are discounted.'
UNION ALL SELECT 'voucher_max_uses', 'Maximum uses'
UNION ALL SELECT 'voucher_max_uses_hint', 'Total number of bookings that may use this voucher. Leave empty for unlimited. Cancelled bookings do not count.'
UNION ALL SELECT 'voucher_max_uses_per_email', 'Maximum uses per customer e-mail'
UNION ALL SELECT 'voucher_max_uses_per_email_hint', 'Limit how many times the same e-mail address may use this voucher. Leave empty for unlimited.'
UNION ALL SELECT 'voucher_valid', 'Validity'
UNION ALL SELECT 'voucher_valid_on', 'Check the date and time against'
UNION ALL SELECT 'voucher_valid_on_hint', '"Time of booking" = when the customer makes the booking. "Appointment date and time" = the date and start time of each appointment in the cart.'
UNION ALL SELECT 'voucher_status', 'Status'
UNION ALL SELECT 'voucher_status_active', 'Active'
UNION ALL SELECT 'voucher_status_inactive', 'Inactive'
UNION ALL SELECT 'voucher_usage', 'Used'
UNION ALL SELECT 'voucher_date', 'Date'
UNION ALL SELECT 'voucher_date_from', 'From date'
UNION ALL SELECT 'voucher_date_to', 'To date'
UNION ALL SELECT 'voucher_time_from', 'From time'
UNION ALL SELECT 'voucher_time_to', 'To time'
UNION ALL SELECT 'voucher_every', 'Day of the week'
UNION ALL SELECT 'voucher_time_hint', 'The "to" time is inclusive until the end of that minute. Times use the date and time format and the time zone set in the script options.'
UNION ALL SELECT 'voucher_err_code', 'The code must be 2-50 characters: letters, numbers, dash, underscore or dot.'
UNION ALL SELECT 'voucher_err_code_exists', 'This code is already used by another voucher.'
UNION ALL SELECT 'voucher_err_discount', 'Enter a discount greater than 0 (a percentage cannot be more than 100).'
UNION ALL SELECT 'voucher_err_dates', 'Please enter a valid date.'
UNION ALL SELECT 'voucher_err_time_order', 'The "to" time must be later than the "from" time.'
UNION ALL SELECT 'voucher_err_period_order', 'The end of the period must be later than its start.'
UNION ALL SELECT 'voucher_err_limit', 'Usage limits must be whole numbers greater than 0, or empty.'
UNION ALL SELECT 'booking_voucher', 'Voucher code'
UNION ALL SELECT 'booking_discount', 'Discount'
UNION ALL SELECT 'voucher_types_ARRAY_percent', 'Percentage (%)'
UNION ALL SELECT 'voucher_types_ARRAY_amount', 'Fixed amount'
UNION ALL SELECT 'voucher_apply_arr_ARRAY_each', 'Off every discounted service'
UNION ALL SELECT 'voucher_apply_arr_ARRAY_total', 'Once, off the whole booking'
UNION ALL SELECT 'voucher_valids_ARRAY_always', 'Always valid'
UNION ALL SELECT 'voucher_valids_ARRAY_fixed', 'One day, time window'
UNION ALL SELECT 'voucher_valids_ARRAY_period', 'Date and time period'
UNION ALL SELECT 'voucher_valids_ARRAY_recurring', 'Weekly, time window'
UNION ALL SELECT 'voucher_valid_on_arr_ARRAY_booking', 'Time of booking'
UNION ALL SELECT 'voucher_valid_on_arr_ARRAY_appointment', 'Appointment date and time'
UNION ALL SELECT 'voucher_days_ARRAY_monday', 'Monday'
UNION ALL SELECT 'voucher_days_ARRAY_tuesday', 'Tuesday'
UNION ALL SELECT 'voucher_days_ARRAY_wednesday', 'Wednesday'
UNION ALL SELECT 'voucher_days_ARRAY_thursday', 'Thursday'
UNION ALL SELECT 'voucher_days_ARRAY_friday', 'Friday'
UNION ALL SELECT 'voucher_days_ARRAY_saturday', 'Saturday'
UNION ALL SELECT 'voucher_days_ARRAY_sunday', 'Sunday'
UNION ALL SELECT 'error_titles_ARRAY_AV01', 'Voucher added'
UNION ALL SELECT 'error_bodies_ARRAY_AV01', 'The voucher has been added.'
UNION ALL SELECT 'error_titles_ARRAY_AV02', 'Voucher failed to add'
UNION ALL SELECT 'error_bodies_ARRAY_AV02', 'The voucher could not be added.'
UNION ALL SELECT 'error_titles_ARRAY_AV03', 'Voucher updated'
UNION ALL SELECT 'error_bodies_ARRAY_AV03', 'The voucher has been updated.'
UNION ALL SELECT 'error_titles_ARRAY_AV04', 'Voucher(s) deleted'
UNION ALL SELECT 'error_bodies_ARRAY_AV04', 'The selected voucher(s) have been deleted.'
UNION ALL SELECT 'error_titles_ARRAY_AV08', 'Voucher not found'
UNION ALL SELECT 'error_bodies_ARRAY_AV08', 'The voucher you are trying to edit was not found.'
UNION ALL SELECT 'error_titles_ARRAY_AV09', 'Voucher not accepted'
UNION ALL SELECT 'error_bodies_ARRAY_AV09', 'The booking was not saved because the voucher code is not valid for it. Check the code, the services and the date or time, or remove the voucher.'
UNION ALL SELECT 'front_voucher_label', 'Have a voucher code?'
UNION ALL SELECT 'front_voucher_placeholder', 'Enter your code'
UNION ALL SELECT 'front_voucher_apply', 'Apply'
UNION ALL SELECT 'front_voucher_remove', 'Remove'
UNION ALL SELECT 'front_voucher_discount', 'Discount'
UNION ALL SELECT 'front_voucher_applied', 'Voucher applied.'
UNION ALL SELECT 'front_voucher_removed', 'Voucher removed.'
UNION ALL SELECT 'front_voucher_missing', 'Please enter a voucher code.'
UNION ALL SELECT 'front_voucher_not_found', 'This voucher code is not valid.'
UNION ALL SELECT 'front_voucher_inactive', 'This voucher is not active.'
UNION ALL SELECT 'front_voucher_expired', 'This voucher has expired.'
UNION ALL SELECT 'front_voucher_not_started', 'This voucher is not valid yet.'
UNION ALL SELECT 'front_voucher_not_for_service', 'This voucher does not apply to the selected service(s).'
UNION ALL SELECT 'front_voucher_not_for_time', 'This voucher is not valid for the selected date or time.'
UNION ALL SELECT 'front_voucher_limit', 'This voucher has reached its usage limit.'
UNION ALL SELECT 'front_voucher_limit_email', 'You have already used this voucher the maximum number of times.'
UNION ALL SELECT 'front_voucher_empty_cart', 'Please add a service before applying a voucher.'
UNION ALL SELECT 'front_voucher_busy', 'The voucher is being used by another booking right now. Please try again in a moment.'
UNION ALL SELECT 'front_voucher_throttled', 'Too many attempts. Please wait a few minutes and try again.'
UNION ALL SELECT 'pjAdminVouchers', 'Voucher menu'
UNION ALL SELECT 'pjAdminVouchers_pjActionIndex', 'Voucher List'
UNION ALL SELECT 'pjAdminVouchers_pjActionCreate', 'Add voucher'
UNION ALL SELECT 'pjAdminVouchers_pjActionUpdate', 'Edit voucher'
UNION ALL SELECT 'pjAdminVouchers_pjActionDeleteVoucher', 'Delete single voucher'
UNION ALL SELECT 'pjAdminVouchers_pjActionDeleteVoucherBulk', 'Delete multiple vouchers'
UNION ALL SELECT 'pjAdminVouchers_pjActionGetVoucher', 'Show list elements'
UNION ALL SELECT 'pjAdminVouchers_pjActionSaveVoucher', 'Save voucher'
UNION ALL SELECT 'pjAdminVouchers_pjActionCheckCode', 'Check voucher code'
) t ON t.`k` = f.`key`;

-- the e-mail token legends also list the two new tokens {Discount} and {VoucherCode}
UPDATE `plugin_base_multi_lang` SET `content` = REPLACE(`content`, '<p>{Total}</p>', '<p>{Total}</p>
<p>{Discount}</p>
<p>{VoucherCode}</p>') WHERE `model` = 'pjBaseField' AND `field` = 'title' AND `foreign_id` IN (SELECT `id` FROM `plugin_base_fields` WHERE `key` = 'opt_o_reminder_body_text') AND `content` NOT LIKE '%{Discount}%';

UPDATE `plugin_base_multi_lang` SET `content` = REPLACE(`content`, '<div><small>{Total}</small></div>', '<div><small>{Total}</small></div>
<div><small>{Discount}</small></div>
<div><small>{VoucherCode}</small></div>') WHERE `model` = 'pjBaseField' AND `field` = 'title' AND `foreign_id` IN (SELECT `id` FROM `plugin_base_fields` WHERE `key` = 'opt_o_email_body_text') AND `content` NOT LIKE '%{Discount}%';

-- 5) label cache key
UPDATE `options` SET `value` = MD5(RAND()) WHERE `key` = 'o_fields_index';

COMMIT;