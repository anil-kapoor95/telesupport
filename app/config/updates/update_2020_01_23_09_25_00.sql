START TRANSACTION;

  SET @id := (SELECT `id` FROM `plugin_auth_permissions` WHERE `key` = "pjAdminBookings");
  INSERT INTO `plugin_auth_permissions` (`id`, `parent_id`, `inherit_id`, `key`, `is_shown`) VALUES (NULL, @id, NULL, 'pjAdminBookings_pjActionExport', 'T');
  SET @id := (SELECT LAST_INSERT_ID());
  INSERT INTO `plugin_auth_roles_permissions` (`id`, `role_id`, `permission_id`) VALUES (NULL, '1', @id);
  INSERT INTO `plugin_base_multi_lang` VALUES (NULL, @id, 'pjAuthPermission', '::LOCALE::', 'title', 'Booking Export', 'data');

  INSERT INTO `plugin_base_fields` VALUES (NULL, 'pjAdminBookings_pjActionExport', 'backend', 'Label / Booking Export', 'script', NULL);
  SET @id := (SELECT LAST_INSERT_ID());
  INSERT INTO `plugin_base_multi_lang` VALUES (NULL, @id, 'pjBaseField', '::LOCALE::', 'title', 'Booking Export', 'script');

COMMIT;