
START TRANSACTION;
 
 INSERT INTO `options` (`foreign_id`, `key`, `tab_id`, `value`, `label`, `type`, `order`, `is_visible`, `style`) VALUES
(1, 'o_thankyou_page_1', 1, '', NULL, 'string', 10, 1, NULL);
--  INSERT INTO `options` (`foreign_id`, `key`, `tab_id`, `value`, `label`, `type`, `order`, `is_visible`, `style`) VALUES
-- (1, 'o_thankyou_page_2', 1, '', NULL, 'string', 10, 1, NULL);
 
 INSERT INTO `options` (`foreign_id`, `key`, `tab_id`, `value`, `label`, `type`, `order`, `is_visible`, `style`) VALUES
(1, 'o_cancel_payment_url_1', 1, '', NULL, 'string', 9, 1, NULL);
--  INSERT INTO `options` (`foreign_id`, `key`, `tab_id`, `value`, `label`, `type`, `order`, `is_visible`, `style`) VALUES
-- (1, 'o_cancel_payment_url_2', 1, '', NULL, 'string', 9, 1, NULL);

INSERT INTO `plugin_base_fields` VALUES (NULL, 'opt_o_cancel_payment_url', 'backend', 'Options / Cancel url', 'script', '2023-11-27 11:52:26');
SET @id := (SELECT LAST_INSERT_ID());
INSERT INTO `plugin_base_multi_lang` VALUES (NULL, @id, 'pjBaseField', '::LOCALE::', 'title', 'Cancel url', 'script');

COMMIT;