UPDATE `glpi_plugin_webhook_secrettypes` SET id = 3, name = '3 - Encoded Basic Authentication' WHERE id = 2;
UPDATE `glpi_plugin_webhook_secrettypes` SET id = 2, name = '2 - Basic Authentication' WHERE id = 1;
INSERT INTO `glpi_plugin_webhook_secrettypes` VALUES (1,'1 - None','No Authentication');
