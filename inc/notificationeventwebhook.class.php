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
use Glpi\Toolbox\Sanitizer;

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access this file directly");
}

function str_replace_deep($search, $replace, $subject) {
	if (is_null($subject))
		return '';
    if (is_array($subject))
    {
        foreach($subject as &$oneSubject)
            $oneSubject = str_replace_deep($search, $replace, $oneSubject);
        unset($oneSubject);
        return $subject;
    } else {
        return str_replace($search, $replace, $subject);
    }
}	

class PluginWebhookNotificationEventWebhook extends NotificationEventAbstract implements NotificationEventInterface {

   static public function getTargetFieldName() {
      return 'name';
   }


   static public function getTargetField(&$data) {
      $field = self::getTargetFieldName();

      if (!isset($data[$field])
      && isset($data['id'])) {
               $data[$field] = 'Webhook '.$data['id'];
      }

      if (!isset($data[$field])) {
         //Missing field; set to null
         $data[$field] = null;
      }

      return $field;
   }


   /**
    * Validate send before doing it (may be overloaded : exemple for private tasks or followups)
    *
    * @since 0.84 (new parameter)
    *
    * @param string  $event     notification event
    * @param array   $infos     destination of the notification
    * @param boolean $notify_me notify me on my action ?
    *                           ($infos contains users_id to check if the target is me)
    *                           (false by default)
    * @param mixed $emitter     if this action is executed by the cron, we can
    *                           supply the id of the user (or the email if this
    *                           is an anonymous user with no account) who
    *                           triggered the event so it can be used instead of
    *                           getLoginUserID
    *
    * @return boolean
   **/
   static public function validateSendTo($event, $infos) {
	if (is_array($infos) && isset($infos['additionnaloption']))
	{
		$url = $infos['additionnaloption']['address'];
		return (
			( strpos( $url, 'http://' ) === 0 || strpos( $url, 'https://' ) === 0 ) &&
			filter_var(
				$url,
				FILTER_VALIDATE_URL
			) !== false
		);
	}
	else
		return false;
   }

   static public function canCron() {
      return true;
   }


   static public function getAdminData() {
      global $CFG_GLPI;

	  return false;
   }


   static public function getEntityAdminsData($entity) {
      global $DB, $CFG_GLPI;

         return false;
   }

static public function extraRaise($params) {
		$notificationtargetclass = get_class($params['notificationtarget']);
		$entity = $params['notificationtarget']->entity;
		$event = $params['event'];
		$object = $params['notificationtarget']->obj;
		$options = $params['options'];
		$notificationtarget = new $notificationtargetclass($entity, $event, $object, $options);
		$notificationtarget->setEvent('PluginWebhookNotificationEventWebhook');
		$notificationtarget->setMode($params['options']['mode']);
		$notificationtarget->data = isset($params['notificationtarget']->data) ? $params['notificationtarget']->data : [];
		$notificationtarget->tag_descriptions = isset($params['notificationtarget']->tag_descriptions) ? $params['notificationtarget']->tag_descriptions : [];
		$notificationtarget->events = isset($params['notificationtarget']->events) ? $params['notificationtarget']->events : [];
		$notificationtarget->target = isset($params['notificationtarget']->target) ? $params['notificationtarget']->target : [];
		$template = new NotificationTemplate;
		$template = $params['template'];
		$targets = getAllDataFromTable(
            'glpi_notificationtargets',
            ['notifications_id' => $params['data']['id']]
		);
		if (isset($params['options'])
		&& isset($params['options']['mode'])
		&& $params['options']['mode'] == PluginWebhookNotificationWebhook::MODE_WEBHOOK)
		{
			foreach ($targets as $target)
            //Get all webhooks affected by this notification
			{
				if ($target['type'] == PluginWebhookConfig::WEBHOOK_TYPE)
				{	
					$notificationtarget->addForTarget($target, $params['options']);

					foreach ($notificationtarget->getTargets() as $webhook_infos) {
						if (isset($webhook_infos['additionnaloption'])
						&& isset($webhook_infos['additionnaloption']['address'])
						&& PluginWebhookNotificationEventWebhook::validateSendTo($event, $webhook_infos))
						{
							if (!isset($options['additionnaloption']))
								$options['additionnaloption'] = $webhook_infos['additionnaloption'];
							$data = &$notificationtarget->getForTemplate($event, $options);
							$key = $webhook_infos[static::getTargetFieldName()];
							$url = $webhook_infos['additionnaloption']['address']; 
							$url = NotificationTemplate::process($webhook_infos['additionnaloption']['address'], $data); // substitute variables in url
//							$url = str_replace(["\n", "\r", "\t"], ['', '', ''], htmlentities($url)); // translate HTML-significant characters and suppress remaining escape characters
							$url = str_replace(["\n", "\r", "\t"], ['', '', ''], html_entity_decode($url)); // suppress remaining escape characters
							if ($template_datas = $template->getByLanguage($webhook_infos['language']))
							{
								$template_datas  = Sanitizer::unsanitize($template_datas); // unescape html from DB
								$data = Sanitizer::unsanitize($data);
								if (isset($template_datas['content_text']) && !empty($template_datas['content_text']))
									$template = $template_datas['content_text'];
								else
									$template = $template_datas['content_html'];

								// escape double quotes (as the LF, CR and TAB characters)
								$data = str_replace_deep(["\\","\n", "\r", "\t", '"'], ['\\\\', '\\n', '\\r', '\\t', '\\"'], $data);

								$content = NotificationTemplate::process($template, $data);
								$curl = curl_init($url);
								$secrettype = $webhook_infos['additionnaloption']['plugin_webhook_secrettypes_id']; 
								$headers = array();
								switch ($secrettype)
								{
									case 1: // No Authentication
									$headers = 
										[
										'Content-type: application/json'
										];
									break;

									case 2: // Basic Authentication
									$headers = 
										[
										'Content-type: application/json',
										'Authorization: Basic '.$webhook_infos['additionnaloption']['user'] .":".$webhook_infos['additionnaloption']['secret']
										];
									break;

									case 3: // Basic Authentication with base64 encoding
									$headers = 
										[
										'Content-type: application/json',
										'Authorization: Basic '.base64_encode(htmlspecialchars_decode($webhook_infos['additionnaloption']['user']).":".htmlspecialchars_decode($webhook_infos['additionnaloption']['secret']))
										];
									break;

									case 4: // JSON Web Token
									break;
								}
								
								curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
								curl_setopt($curl, CURLOPT_HEADER, false);
								curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
								curl_setopt($curl, CURLOPT_POST, true);
								curl_setopt($curl, CURLOPT_POSTFIELDS, $content);

								$json_response = curl_exec($curl);

								$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

								if ( $status < 200 || $status >= 300) {
									Session::addMessageAfterRedirect("<font color='red'>"."Error: call to URL $url failed with status $status, response $json_response, curl_error " . curl_error($curl) . ", curl_errno " . curl_errno($curl)."</font>", false, ERROR);
									Toolbox::logInFile("webhook", "Error : call to URL $url failed with status $status, response $json_response, curl_error " . curl_error($curl) . ", curl_errno " . curl_errno($curl).PHP_EOL."HTTP Headers : ".print_r($headers,true).PHP_EOL."POST Content : ".print_r($content,true).PHP_EOL);
								}
								else if ( $webhook_infos['additionnaloption']['debug'] ) {
									Toolbox::logInFile("webhook", "Debug : call to URL $url returned status $status and response $json_response" .PHP_EOL."HTTP Headers : ".print_r($headers,true).PHP_EOL."POST Content : ".print_r($content,true).PHP_EOL);
								}

								curl_close($curl);

//								$response = json_decode($json_response, true);
							}
						}
					}
				}
			}
		}
	}
   
   static public function send(array $data) {
      global $CFG_GLPI, $DB;

      Toolbox::logInFile("webhook", "send : data ".print_r($data,true));
      $processed = [];

      foreach ($data as $row) {
      Toolbox::logInFile("webhook", "send : row".print_r($row,true));
/*		$current = new QueuedNotification();
		$current->getFromResultSet($row);
		$url = "your url";    
		$content = json_encode(Html::entity_decode_deep($current->fields['body_html']);

		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_HTTPHEADER,
			array("Content-type: application/json"));
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $content);

		$json_response = curl_exec($curl);

		$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

		if ( $status != 201 ) {
            Session::addMessageAfterRedirect("Error: call to URL $url failed with status $status, response $json_response, curl_error " . curl_error($curl) . ", curl_errno " . curl_errno($curl), true, ERROR);

            $retries = $CFG_GLPI['smtp_max_retries'] - $current->fields['sent_try'];
            Toolbox::logInFile("webhook-error",
                              sprintf(__('%1$s. Message: %2$s, Error: %3$s'),
                                       sprintf(__('Warning: a webhook notification was undeliverable to %s with %d retries remaining'),
                                                $current->fields['recipient'], $retries),
                                       $current->fields['name'],
                                       Error: call to URL $url failed with status $status, response $json_response, curl_error " . curl_error($curl) . ", curl_errno " . curl_errno($curl)."\n"));

            if ($retries <= 0) {
               Toolbox::logInFile("webhook-error",
                                 sprintf(__('%1$s: %2$s'),
                                          sprintf(__('Fatal error: giving up delivery of webhook notification to %s'),
                                                $current->fields['recipient']),
                                          $current->fields['name']."\n"));
               $current->delete(['id' => $current->fields['id']]);
            }
            $input = [
                'id'        => $current->fields['id'],
                'sent_try'  => $current->fields['sent_try'] + 1
            ];

            if ($CFG_GLPI["webhook_retry_time"] > 0) {
               $input['send_time'] = date("Y-m-d H:i:s", strtotime('+' . $CFG_GLPI["smtp_retry_time"] . ' minutes')); //Delay X minutes to try again
            }
            $current->update($input);
		}
		else {
            //TRANS to be written in logs %1$s is the webhook url / %2$s is the body of the notification
            Toolbox::logInFile("webhook",
                               sprintf(__('%1$s: %2$s'),
                                        sprintf(__('A webhook notification was sent to %s'),
                                                $current->fields['recipient']),
                                        $current->fields['name']."\n"));
            $processed[] = $current->getID();
            $current->update(['id'        => $current->fields['id'],
                                'sent_time' => $_SESSION['glpi_currenttime']]);
            $current->delete(['id'        => $current->fields['id']]);
         }

		curl_close($curl);

		$response = json_decode($json_response, true);

*/      }

      return count($processed);
   }

}
