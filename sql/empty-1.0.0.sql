-- -----------------------------------------------------
-- Table `glpi_plugin_webhook_configs`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `glpi_plugin_webhook_configs`;
CREATE  TABLE `glpi_plugin_webhook_configs` (
  `id` INT(11) NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(45) NOT NULL ,
  `plugin_webhook_operationtypes_id` INT(11) NOT NULL default '0' COMMENT 'operation type : POST, PUT, ...' ,
  `address` TEXT NULL ,
  `plugin_webhook_secrettypes_id` INT(11) NOT NULL default '0' COMMENT 'secret type : Basic Authentication, Personal Access Token, OAuth, ...' ,
  `secret` TEXT NULL ,
  `user` TEXT NULL ,
  `language` VARCHAR(10) NULL ,
  PRIMARY KEY (`id`) ,
  UNIQUE KEY `unicity` (`name`))
 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginWebhookConfig','2','1','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginWebhookConfig','3','2','0');

-- -----------------------------------------------------
-- Table `glpi_plugin_webhook_secrettypes`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `glpi_plugin_webhook_secrettypes`;
CREATE  TABLE `glpi_plugin_webhook_secrettypes` (
  `id` INT(11) NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(45) NOT NULL ,
  `comment` VARCHAR(45) NULL ,
  PRIMARY KEY (`id`) ,
  UNIQUE INDEX `glpi_plugin_webhook_secrettype_name` (`name` ASC) )
 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `glpi_plugin_webhook_secrettypes` VALUES (1,'Basic Authentication','Login:Password');
INSERT INTO `glpi_plugin_webhook_secrettypes` VALUES (2,'Encoded Basic Authentication','Base64-encoded Login:Password ');
-- INSERT INTO `glpi_plugin_webhook_secrettypes` VALUES (3,'JWT','JSON Web Token');

-- -----------------------------------------------------
-- Table `glpi_plugin_webhook_verbtypes`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `glpi_plugin_webhook_operationtypes`;
CREATE  TABLE `glpi_plugin_webhook_operationtypes` (
  `id` INT(11) NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(45) NOT NULL ,
  `comment` VARCHAR(45) NULL ,
  PRIMARY KEY (`id`) ,
  UNIQUE INDEX `glpi_plugin_webhook_operationtype_name` (`name` ASC) )
 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `glpi_plugin_webhook_operationtypes` VALUES (NULL,'POST','Create new item');
INSERT INTO `glpi_plugin_webhook_operationtypes` VALUES (NULL,'PUT','Update existing item');
