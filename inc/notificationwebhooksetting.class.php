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

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access this file directly");
}

/**
 *  This class manages the webhook notifications settings
 */
class PluginWebhookNotificationWebhookSetting extends NotificationSetting {

   static public function getTypeName($nb = 0) {
      return __('Webhook notifications configuration', 'webhook');
   }


   public function getEnableLabel() {
      return __('Enable notifications via webhook', 'webhook');
   }


   static public function getMode() {
      return NotificationWebhook::MODE_WEBHOOK;
   }


   function showFormConfig($options = []) {
      global $CFG_GLPI;

      if (!isset($options['display'])) {
         $options['display'] = true;
      }

	  $conf = Config::getConfigurationValues('plugin:webhook');
      $params = [
         'display'   => true
      ];
      $params = array_merge($params, $options);

      $out = "<form action='".Toolbox::getItemTypeFormURL(__CLASS__)."' method='post'>";
      $out .= Html::hidden('config_context', ['value' => 'plugin:webhook']);
      $out .= "<div>";
      $out .= "<input type='hidden' name='id' value='1'>";
      $out .= "<table class='tab_cadre_fixe'>";
      $out .= "<tr class='tab_bg_1'><th colspan='4'>"._n('Webhook notification', 'Webhook notifications', Session::getPluralNumber(), 'webhook')."</th></tr>";

      if ($CFG_GLPI['notifications_webhook']) {
         $out .= "<tr class='tab_bg_2'>";
         $out .= "<td><label for='webhook_max_retries'>" . __('Max. delivery retries') . "</label></td>";
         $out .= "<td><input type='text' name='webhook_max_retries' id='webhook_max_retries' size='5' value='" .
                       (isset($conf["webhook_max_retries"]) ? $conf["webhook_max_retries"] : $CFG_GLPI["smtp_max_retries"]) . "'></td>";
         $out .= "</tr>";

         $out .= "<tr class='tab_bg_2'>";
         $out .= "<td><label for='webhook_retry_time'>" . __('Try to deliver again in (minutes)') . "</label></td>";
         $out .= "<td>";
         $out .= Dropdown::showNumber('webhook_retry_time', [
                     'value'    => (isset($conf["webhook_retry_time"]) ? $conf["webhook_retry_time"] : $CFG_GLPI["smtp_retry_time"]),
                     'min'      => 0,
                     'max'      => 60,
                     'step'     => 1,
                     'display'  => false,
                 ]);
         $out .= "</td><td colspan='2'></td>";
         $out .= "</tr>";

       } else {
         $out .= "<tr><td colspan='4'>" . __('Notifications are disabled.')  . " <a href='{$CFG_GLPI['root_doc']}/front/setup.notification.php'>" . _('See configuration') .  "</td></tr>";
      }
      $options['candel']     = false;

      //Ignore display parameter since showFormButtons is now ready :/ (from all but tests)
      echo $out;

      $this->showFormButtons($options);

   }

}
