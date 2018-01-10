<?php

namespace Mailblock\Email;

use SilverStripe\SiteConfig\SiteConfig;
use SilverStripe\Control\Email\Email;
use SilverStripe\Control\Email\SwiftPlugin;
use SilverStripe\Control\Director;
use SilverStripe\Subsites\Model\Subsite;

/**
 * Mail plugin to rewrite email recipients depending on Mailblock SiteConfig settings.
 */
class MailblockPlugin extends SwiftPlugin
{
    /**
     * Before sending a message make sure all our overrides are taken into account
     *
     * @param \Swift_Events_SendEvent $evt
     */
    public function beforeSendPerformed(\Swift_Events_SendEvent $evt)
    {
        /** @var \Swift_Message $message */
        $message = $evt->getMessage();

        // Get the correct siteconfig.
        if (class_exists(Subsite::class)) {
            $mainSiteConfig = SiteConfig::get()->filter('SubsiteID', 0)->first();
        }
        else {
            $mainSiteConfig = SiteConfig::current_site_config();
        }
        if ($mainSiteConfig->getField('MailblockApplyPerSubsite')) {
            $siteConfig = SiteConfig::current_site_config();
        }
        else {
            $siteConfig = $mainSiteConfig;
        }

        // Get the mailblock configuration values.
        $enabled = $siteConfig->getField('MailblockEnabled');
        $enabledOnLive = $siteConfig->getField('MailblockEnabledOnLive');
        $overrideConfiguration = $siteConfig
            ->getField('MailblockOverrideConfiguration');
        $sendAllTo = Email::getSendAllEmailsTo();

        if($enabled && ($enabledOnLive || !Director::isLive())
            && (!$sendAllTo || $overrideConfiguration)
        ) {
            $recipients = '';
            $ccRecipients = '';
            $bccRecipients = '';

            $subject = $message->getSubject();
            if (!empty($to = $message->getTo())) {
                $recipients = implode(',', array_keys($to));
            }
            if (!empty($cc = $message->getCc())) {
                $ccRecipients = implode(',', array_keys($cc));
            }
            if (!empty($bcc = $message->getBcc())) {
                $bccRecipients = implode(',', array_keys($bcc));
            }

            $mailblockRecipients = $siteConfig->getField('MailblockRecipients');

            $subject .= " [addressed to $recipients";
            if ($ccRecipients) $subject .= ", cc to $ccRecipients";
            if ($bccRecipients) $subject .= ", bcc to $bccRecipients";
            $subject .= ']';
            $message->setSubject($subject);

            // If one of the orignial recipients is in the whitelist, add them
            // to the new recipients list.
            $mailblockWhitelist = $siteConfig->getField('MailblockWhitelist');
            $whitelist = preg_split("/\r\n|\n|\r/", $mailblockWhitelist);
            $cc = array();
            $bcc = array();
            foreach ($whitelist as $whiteListed) {
                if (!empty($whiteListed)) {
                    if (strpos($recipients, $whiteListed) !== false) {
                        $newRecipients[] = $whiteListed;
                    }
                    if (strpos($ccRecipients, $whiteListed) !== false) {
                        $cc[] = $whiteListed;
                    }
                    if (strpos($bccRecipients, $whiteListed) !== false) {
                        $bcc[] = $whiteListed;
                    }
                }
            }
            $newRecipients = preg_split("/\r\n|\n|\r/", $mailblockRecipients);
            $message->setTo($newRecipients);
            $message->setBcc($bcc);
            $message->setCc($cc);
        }
        else {
            if (!empty($sendAllTo)) {
                $this->setTo($message, $sendAllTo);
            }

            $ccAllTo = Email::getCCAllEmailsTo();
            if (!empty($ccAllTo)) {
                foreach ($ccAllTo as $address => $name) {
                    $message->addCc($address, $name);
                }
            }

            $bccAllTo = Email::getBCCAllEmailsTo();
            if (!empty($bccAllTo)) {
                foreach ($bccAllTo as $address => $name) {
                    $message->addBcc($address, $name);
                }
            }
        }

        $sendAllFrom = Email::getSendAllEmailsFrom();
        if (!empty($sendAllFrom)) {
            $this->setFrom($message, $sendAllFrom);
        }
    }
}