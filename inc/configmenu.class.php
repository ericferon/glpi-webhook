<?php
/*
 -------------------------------------------------------------------------
 Webhook plugin for GLPI
 Copyright (C) 2020-2022 by Eric Feron.
 -------------------------------------------------------------------------

 LICENSE
      
 This file is part of Webhook.

 Webhook is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 at your option any later version.

 Webhook is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Webhook. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */
class PluginWebhookConfigMenu extends CommonGLPI {
   static $rightname = 'plugin_webhook_configuration';

   static function getMenuName() {
      return _n('Webhook configuration', 'Webhooks configuration', 2, 'webhook');
   }

   static function getMenuContent() {
      global $CFG_GLPI;

		$menu                                           = [];
		$menu['title']                                  = self::getMenuName();
		$menu['page']                                   = "/".Plugin::getWebDir('webhook', false)."/front/config.php";
		$menu['links']['search']                        = PluginWebhookConfig::getSearchURL(false);
		if (PluginWebhookConfig::canCreate()) {
			$menu['links']['add']                        = PluginWebhookConfig::getFormURL(false);
		}
		$menu['icon'] = self::getIcon();

		return $menu;
	}

	static function getIcon() {
		return "fas fa-share-alt";
	}

   static function removeRightsFromSession() {
      if (isset($_SESSION['glpimenu']['config']['types']['PluginWebhookConfigMenu'])) {
         unset($_SESSION['glpimenu']['config']['types']['PluginWebhookConfigMenu']); 
      }
      if (isset($_SESSION['glpimenu']['config']['content']['pluginwebhookconfigmenu'])) {
         unset($_SESSION['glpimenu']['config']['content']['pluginwebhookconfigmenu']); 
      }
   }
}
