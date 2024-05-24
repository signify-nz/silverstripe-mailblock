<?php

namespace Mailblock\Email;

use SilverStripe\Control\Director;
use SilverStripe\Control\Email\Email;
use SilverStripe\SiteConfig\SiteConfig;
use SilverStripe\Subsites\Model\Subsite;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\Event\MessageEvent;

/**
 * See https://symfony.com/doc/current/mailer.html#mailer-events for further info
 */
class MailblockMailSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            MessageEvent::class => 'onMessage',
        ];
    }

    public function onMessage(MessageEvent $event): void
    {
        /** @var Email $message */
        $message = $event->getMessage();

        // Get the correct siteconfig.
        if (class_exists(Subsite::class)) {
            $mainSiteConfig = SiteConfig::get()->filter('SubsiteID', 0)->first();
        } else {
            $mainSiteConfig = SiteConfig::current_site_config();
        }
        if ($mainSiteConfig->getField('MailblockApplyPerSubsite')) {
            $siteConfig = SiteConfig::current_site_config();
        } else {
            $siteConfig = $mainSiteConfig;
        }

        // Get the mailblock configuration values.
        $enabled = $siteConfig->getField('MailblockEnabled');
        $enabledOnLive = $siteConfig->getField('MailblockEnabledOnLive');
        $overrideConfiguration = $siteConfig->getField('MailblockOverrideConfiguration');
        $sendAllTo = Email::getSendAllEmailsTo();

        if (
            $enabled
            && ($enabledOnLive || !Director::isLive())
            && (!$sendAllTo || $overrideConfiguration)
        ) {
            $recipients = [];
            $ccRecipients = [];
            $bccRecipients = [];

            $subject = $message->getSubject();
            foreach ($message->getTo() as $to) {
                $recipients[] = $to->getAddress();
            }
            foreach ($message->getCc() as $cc) {
                $ccRecipients[] = $cc->getAddress();
            }
            foreach ($message->getBcc() as $bcc) {
                $bccRecipients[] = $bcc->getAddress();
            }

            $recipients = implode(',', $recipients);
            $ccRecipients = implode(',', $ccRecipients);
            $bccRecipients = implode(',', $bccRecipients);

            $mailblockRecipients = $siteConfig->getField('MailblockRecipients');

            $subject .= " [addressed to $recipients";
            if ($ccRecipients) $subject .= ", cc to $ccRecipients";
            if ($bccRecipients) $subject .= ", bcc to $bccRecipients";
            $subject .= ']';
            $message->setSubject($subject);

            // If one of the orignial recipients is in the whitelist, add them
            // to the new recipients list.
            $mailblockWhitelist = $siteConfig->getField('MailblockWhitelist');
            $whitelist = !empty($mailblockWhitelist) ? preg_split("/\r\n|\n|\r/", $mailblockWhitelist) : [];
            $newRecipients = !empty($mailblockRecipients) ? preg_split("/\r\n|\n|\r/", $mailblockRecipients) : [];
            $cc = [];
            $bcc = [];
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
            $message->setTo($newRecipients);
            $message->setBcc($bcc);
            $message->setCc($cc);
        }
    }
}
