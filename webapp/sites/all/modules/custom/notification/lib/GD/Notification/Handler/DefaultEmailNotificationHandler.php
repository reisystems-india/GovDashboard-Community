<?php
/*
 * Copyright 2014 REI Systems, Inc.
 * 
 * This file is part of GovDashboard.
 * 
 * GovDashboard is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * GovDashboard is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with GovDashboard.  If not, see <http://www.gnu.org/licenses/>.
 */


namespace GD\Notification\Handler;

use GD\Notification\Exception\NotificationException;

require_once libraries_get_path('swiftmailer').'/lib/swift_required.php';

class DefaultEmailNotificationHandler extends AbstractNotificationHandler {
    private $supportEmail = 'support@govdashboard.com';

    /**
     * Function to handle the core send implementation
     *
     * @param $settings
     *  Package containing configuration for notification
     * @throws \GD\Notification\Exception\NotificationException
     * @return mixed
     */
    public function send($settings) {
        $destination = $settings['destination'];
        $messageType = $settings['type'];
        $subject = $settings['subject'];
        $body = $settings['message'];
        $signature = isset($settings['signature']) ? $settings['signature'] : $this->getSignature();
        $from = isset($settings['from']) ? $settings['from'] : array('support@govdashboard.com' => 'GovDashboard');
        $sender = isset($settings['sender']) ? $settings['sender'] : 'support@govdashboard.com';
        $html = isset($settings['html']) ? 'text/html' : 'text/plain';

        if (!$destination) {
            throw new NotificationException('No destination specified.');
        } else if ($destination == GD_NOTIFICATION_SUPPORT) {
            $destination = $this->supportEmail;
        } else if (!is_array($destination)) {
            $destination = array($destination);
        }

        $to = array();
        foreach ($destination as $uid) {
            $account = user_load($uid);
            if ($account !== FALSE) {
                $to[$account->mail] = $account->fullname;
            }
        }

        //  If it is an urgent message, CC support team
        if ($messageType == GD_NOTIFICATION_URGENT) {
            $to[$this->supportEmail] = 'GovDashboard Support';
        }

        $header = $this->getHeader($destination);
        $message = \Swift_Message::newInstance()
          ->setSubject($subject)
          ->setFrom($from)
          ->setSender($sender)
          ->setTo($to)
          ->setBody($header . $body . $signature, $html);
        $transport = \Swift_MailTransport::newInstance();
        $mailer = \Swift_Mailer::newInstance($transport);
        return $mailer->send($message);
    }

    protected function getHeader($destination) {
        if (count($destination) > 1) {
            return "Dear users,\n\n";
        } else {
            $account = user_load($destination[0]);
            return "Dear " . $account->fullname . ",\n\n";
        }
    }

    protected function getSignature() {
        return "\n\nGovDashboard Team\n" . GOVDASH_HOST . "\n" . $this->supportEmail;
    }
}
