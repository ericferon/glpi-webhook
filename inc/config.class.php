<?php
/*
 -------------------------------------------------------------------------
 Webhook plugin for GLPI
 Copyright (C) 2009-2018 by Eric Feron.
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
   die("Sorry. You can't access directly to this file");
}

class PluginWebhookConfig extends CommonDBTM {

   public $dohistory=true;
   static $rightname = "plugin_webhook_configuration";
   protected $usenotepad         = true;
   const WEBHOOK_TYPE = 99;
   
//   protected $plugin_webhook_configs_id_field = 'plugin_webhook_configs_id';

   static function getTypeName($nb=0) {

      return __('Webhooks Config', 'webhook');
   }

    /**
     * called by children (PluginWebhookConfigHeader)
     **/
/*    public function getPluginWebhookConfigIdField()
    {
        return $this->plugin_webhook_configs_id_field;
    }
*/

   // search fields from GLPI 9.3 on
   function rawSearchOptions() {

      $tab = [];
//      if (version_compare(GLPI_VERSION,'9.2','le')) return $tab;

      $tab[] = [
         'id'   => 'common',
         'name' => self::getTypeName(2)
      ];

      $tab[] = [
         'id'            => '2',
         'table'         => $this->getTable(),
         'field'         => 'name',
         'name'          => __('Name'),
         'datatype'		 => 'itemlink',
		 'massiveaction' => false
      ];

      $tab[] = [
         'id'            => '3',
         'table'         => $this->getTable(),
         'field'         => 'address',
         'name'          => __('Address'),
         'datatype'		 => 'text',
		 'massiveaction' => false
      ];

      $tab[] = [
         'id'       => '4',
         'table'    => PluginWebhookOperationType::getTable(),
         'field'    => 'name',
         'name'     => PluginWebhookOperationType::getTypeName(1),
         'datatype' => 'dropdown'
      ];

      $tab[] = [
         'id'       => '5',
         'table'    => PluginWebhookSecretType::getTable(),
         'field'    => 'name',
         'name'     => PluginWebhookSecretType::getTypeName(1),
         'datatype' => 'dropdown'
      ];

      $tab[] = [
         'id'       => '10',
         'table'    => $this->getTable(),
         'field'    => 'debug',
         'name'     => __('Debug mode', 'webhook'),
         'datatype' => 'bool'
      ];

       $tab[] = [
         'id'            => '72',
         'table'         => $this->getTable(),
         'field'         => 'id',
         'name'          => __('ID'),
         'datatype'      => 'number'
      ];

      return $tab;
   }

   //define header form
   function defineTabs($options=[]) {

      $ong = [];
      $this->addDefaultFormTab($ong);
//      $this->addStandardTab('PluginWebhookConfigRequest', $ong, $options);
      $this->addStandardTab('Log', $ong, $options);

      return $ong;
   }

   function showForm ($ID, $options=[]) {

      $this->initForm($ID, $options);
      $this->showFormHeader($options);

      echo "<tr class='tab_bg_1'>";
      //name
      echo "<td>".__('Name')."</td>";
      echo "<td colspan='3'>";
      echo Html::input('name',['value' => $this->fields['name'], 'id' => "name" , 'width' => '100%']);
      echo "</td>";
	  echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      //operation
      echo "<td>".__('REST Verb').": </td>";
      echo "<td>";
      Dropdown::show('PluginWebhookOperationtype', ['value' => $this->fields['plugin_webhook_operationtypes_id']]);
      echo "</td>";
      //address
      echo "<td>".__('Address', 'webhook')."</td>";
      echo "<td>";
      echo Html::input('address',['value' => $this->fields['address'], 'id' => "address" , 'width' => '100%']);
      echo "</td>";
	  echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      //type
      echo "<td>".__('Authentication Type').": </td>";
      echo "<td>";
      Dropdown::show('PluginWebhookSecrettype', ['value' => $this->fields['plugin_webhook_secrettypes_id']]);
      echo "</td>";
      //secret
      echo "<td>".__('Secret', 'webhook')."</td>";
      echo "<td>";
      echo Html::input('secret',['value' => $this->fields['secret'], 'id' => "secret" , 'width' => '100%']);
      echo "</td>";
	  echo "</tr>";
	  
      echo "<tr class='tab_bg_1'>";
      //debug mode
      echo "<td>".__('Debug mode', 'webhook')."</td>";
      echo "<td>";
      Dropdown::showYesNo('debug',$this->fields['debug']);
      echo "</td>";
      //user
      echo "<td>".__('User')."</td>";
      echo "<td>";
      echo Html::input('user',['value' => $this->fields['user'], 'id' => "user" , 'size' => 50]);
      echo "</td>";
	  echo "</tr>";

      $this->showFormButtons($options);

      return true;
   }
}

?>
