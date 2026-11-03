
START TRANSACTION;

DELETE FROM `plugin_payment_options` WHERE `payment_method`='world_pay';

DELETE FROM `plugin_base_fields` WHERE `key` = 'payment_methods_ARRAY_world_pay';

COMMIT;