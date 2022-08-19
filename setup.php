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

// Init the hooks of the plugins -Needed
function plugin_init_webhook() {
	global $PLUGIN_HOOKS, $DB;

	$PLUGIN_HOOKS['csrf_compliant']['webhook'] = true;
	$PLUGIN_HOOKS['change_profile']['webhook'] = ['PluginWebhookProfile', 'initProfile'];
	$PLUGIN_HOOKS['item_add_targets']['webhook']['PluginStatecheckNotificationTargetRule'] = 'plugin_webhook_add_targets';
	$PLUGIN_HOOKS['item_action_targets']['webhook']['PluginStatecheckNotificationTargetRule'] = 'plugin_webhook_action_targets';
	$plugin = new Plugin();
	if ($plugin->isActivated('webhook')) {
		Notification_NotificationTemplate::registerMode(
         'webhook',							//mode itself
         __('Webhook', 'webhook'),			//label
         'webhook'							//plugin name
		);
		Plugin::registerClass('PluginWebhookProfile',
                         ['addtabon' => 'Profile']);
                         
   //add menu to config form
		if (Session::getLoginUserID()) {

			if (Session::haveRight("plugin_webhook", READ)
			|| Session::haveRight("config", UPDATE)) {
				$PLUGIN_HOOKS['config_page']['webhook'] = 'front/config.php';
			}
			if (Session::haveRight("plugin_webhook", READ)) {
				$PLUGIN_HOOKS['menu_toadd']['webhook'] = ["config" => 'PluginWebhookConfigMenu'];
			}
		}
	}
}

// Get the name and the version of the plugin - Needed
function plugin_version_webhook() {

   return [
      'name' => _n('Webhook', 'Webhooks', 2, 'webhook'),
      'version' => '1.0.1',
      'author'  => "Eric Feron",
      'license' => 'GPLv2+',
      'homepage'=> 'https://github.com/ericferon/glpi-webhook',
      'requirements' => [
         'glpi' => [
            'min' => '9.5',
            'dev' => false
         ]
      ]
   ];

}

// Optional : check prerequisites before install : may print errors or add to message after redirect
function plugin_webhook_check_prerequisites() {
   if (version_compare(GLPI_VERSION, '9.5', 'lt')
       || version_compare(GLPI_VERSION, '10.1', 'ge')) {
      if (method_exists('Plugin', 'messageIncompatible')) {
         echo Plugin::messageIncompatible('core', '9.5');
      }
      return false;
   }
   return true;
}

// Uninstall process for plugin : need to return true if succeeded : may display messages or add to message after redirect
function plugin_webhook_check_config() {
   return true;
}

   /**
    * @param $entity
   **/
function plugin_webhook_add_targets($notificationtarget) {
      global $DB;

      // Filter webhooks which can be notified
      $iterator = $DB->request([
         'SELECT' => ['id', 'name'],
         'FROM'   => PluginWebhookConfig::getTable(),
         'ORDER'  => 'name'
      ]);

      while ($data = $iterator->next()) {
         //Add webhook
         $notificationtarget->addTarget($data["id"], sprintf(__('%1$s: %2$s'), __('Webhook', 'webhook'), $data["name"]),
                          PluginWebhookConfig::WEBHOOK_TYPE);
      }
   }

   function plugin_webhook_action_targets($notificationtarget) {
	global $DB, $CFG_GLPI;

	// Filter webhooks which can be notified
	if (isset($notificationtarget->data) && isset($notificationtarget->data['type']) && $notificationtarget->data['type'] == PluginWebhookConfig::WEBHOOK_TYPE)
	{
      $new_lang = '';

		$iterator = $DB->request([
			'SELECT' => [],
			'FROM'   => PluginWebhookConfig::getTable(),
			'WHERE'	 => ['id' => isset($notificationtarget->data['items_id'])?$notificationtarget->data['items_id']:'%'],
			'ORDER'  => 'name'
		]);

		while ($data = $iterator->next()) {
			//Add webhook
			if (isset($data['language'])) {
				$new_lang = trim($data['language']);
			}
			$target_field = PluginWebhookNotificationEventWebhook::getTargetField($data);
			$notificationoption = ['usertype' => PluginWebhookConfig::WEBHOOK_TYPE];
			$notificationoption = array_merge($data,
                                        $notificationoption);
			$param = [
				'language'				=> (empty($new_lang) ? $CFG_GLPI["language"] : $new_lang),
				'additionnaloption' 	=> $notificationoption/*,
				'username'				=> '',
				'users_id'				=> $data['id'],*/
			];
			$notificationtarget->target[$data[$target_field]] = $param;
		}
	}
}

?>
