
START TRANSACTION;

INSERT IGNORE INTO `plugin_payment_options` (`foreign_id`, `payment_method`, `is_active`) VALUES (1, 'invoice', 1);

INSERT INTO `plugin_base_fields` VALUES (NULL, 'plugin_payment_allow_invoice', 'backend', 'plugin_payment_allow_invoice', 'plugin', NULL);
SET @id := (SELECT LAST_INSERT_ID());
INSERT INTO `plugin_base_multi_lang` VALUES (NULL, @id, 'pjField', '::LOCALE::', 'title', 'Allow invoice payments', 'plugin');

INSERT INTO `plugin_base_fields` VALUES (NULL, 'payment_methods_ARRAY_invoice', 'arrays', 'payment_methods_ARRAY_invoice', 'script', NULL);
SET @id := (SELECT LAST_INSERT_ID());
INSERT INTO `plugin_base_multi_lang` VALUES (NULL, @id, 'pjBaseField', '::LOCALE::', 'title', 'Invoice', 'script');

INSERT INTO `options` (`foreign_id`, `key`, `tab_id`, `value`, `label`, `type`, `order`, `is_visible`, `style`) VALUES
(1, 'o_allow_invoice', 2, '1|0::1', NULL, 'bool', 25, 1, NULL);

INSERT INTO `plugin_base_fields` VALUES (NULL, 'opt_o_allow_invoice', 'backend', 'Options / Allow payments with Invoice', 'script', '2023-11-27 11:52:26');
SET @id := (SELECT LAST_INSERT_ID());
INSERT INTO `plugin_base_multi_lang` VALUES (NULL, @id, 'pjBaseField', '::LOCALE::', 'title', 'Allow payments with Invoice', 'script');

COMMIT;