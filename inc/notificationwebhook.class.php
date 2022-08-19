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
 *  NotificationMailing class implements the NotificationInterface
**/
class PluginWebhookNotificationWebhook implements NotificationInterface {

   const MODE_WEBHOOK = 'webhook';
   /**
    * Check data
    *
    * @param mixed $url   The data to check
    * @param array $options Optional special options (may be needed)
    *
    * @return boolean
   **/
   static function check($url, $options = []) {
	$url = trim($url);

	return (
        ( strpos( $url, 'http://' ) === 0 || strpos( $url, 'https://' ) === 0 ) &&
        filter_var(
            $url,
            FILTER_VALIDATE_URL
        ) !== false
	);
   }

   static function testNotification() {
      global $CFG_GLPI;

	  return true;
   }


   function sendNotification($options = []) {

/*      $data = [];
      $data['itemtype']                             = $options['_itemtype'];
      $data['items_id']                             = $options['_items_id'];
      $data['notificationtemplates_id']             = $options['_notificationtemplates_id'];
      $data['entities_id']                          = $options['_entities_id'];

//      $data["headers"]['Auto-Submitted']            = "auto-generated";
//      $data["headers"]['X-Auto-Response-Suppress']  = "OOF, DR, NDR, RN, NRN";

//      $data['sender']                               = $options['from'];
//      $data['sendername']                           = $options['fromname'];

      $data['body_text']                            = $options['content_text'];
      if (!empty($options['content_html'])) {
         $data['body_html'] = $options['content_html'];
      }

      $data['recipient']                            = Toolbox::stripslashes_deep($options['to']);
      $data['recipientname']                        = $options['toname'];

      if (!empty($options['messageid'])) {
         $data['messageid'] = $options['messageid'];
      }

     $data['mode'] = self::MODE_WEBHOOK;

      $queue = new QueuedNotification();

      if (!$queue->add(Toolbox::addslashes_deep($data))) {
         Session::addMessageAfterRedirect(__('Error inserting webhook notification to queue'), true, ERROR);
         return false;
      } else {
         //TRANS to be written in logs %1$s is the to email / %2$s is the subject of the mail
         Toolbox::logInFile("webhook",
                            sprintf(__('%1$s: %2$s'),
                                    sprintf(__('A webhook notification to %s was added to queue'),
                                            $options['to']),
                                    $options['subject']."\n"));
      }
*/
      return true;
   }
}
