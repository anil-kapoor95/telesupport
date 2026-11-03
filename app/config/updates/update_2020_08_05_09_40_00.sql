START TRANSACTION;

ALTER TABLE `bookings` MODIFY COLUMN `booking_price` decimal(16,2);
ALTER TABLE `bookings` MODIFY COLUMN `booking_total` decimal(16,2);
ALTER TABLE `bookings` MODIFY COLUMN `booking_deposit` decimal(16,2);
ALTER TABLE `bookings` MODIFY COLUMN `booking_tax` decimal(16,2);

ALTER TABLE `bookings_services` MODIFY COLUMN `price` decimal(16,2);

ALTER TABLE `services` MODIFY COLUMN `price` decimal(16,2);


COMMIT;