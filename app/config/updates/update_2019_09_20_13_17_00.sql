
START TRANSACTION;

INSERT INTO `plugin_base_fields` VALUES (NULL, 'prices_invalid_price', 'backend', 'Error / Please enter a valid number.', 'script', '2018-09-19 07:20:53');
SET @id := (SELECT LAST_INSERT_ID());
INSERT INTO `plugin_base_multi_lang` VALUES (NULL, @id, 'pjBaseField', '::LOCALE::', 'title', 'Please enter a valid number.', 'script');

INSERT INTO `plugin_base_fields` VALUES (NULL, 'prices_invalid_max_price', 'backend', 'Error / Invalid max price.', 'script', '2018-09-19 07:20:53');
SET @id := (SELECT LAST_INSERT_ID());
INSERT INTO `plugin_base_multi_lang` VALUES (NULL, @id, 'pjBaseField', '::LOCALE::', 'title', 'Please enter a value less than or equal to %s', 'script');

COMMIT;