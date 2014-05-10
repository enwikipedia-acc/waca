CREATE TABLE `welcomequeue` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `user` INT NOT NULL,
  `request` INT NOT NULL,
  `status` VARCHAR(10) NOT NULL DEFAULT 'Open',
  PRIMARY KEY (`id`));
