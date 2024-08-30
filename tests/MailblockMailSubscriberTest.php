<?php

namespace Mailblock\Tests;

use Mailblock\Email\MailblockMailSubscriber;
use SilverStripe\Control\Email\Email;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\SiteConfig\SiteConfig;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\Event\MessageEvent;
use Symfony\Component\Mime\Address;

class MailblockMailSubscriberTest extends SapphireTest
{
    protected static $fixture_file = 'fixtures.yml';

    protected const TEST_SUBJECT = 'Subject';
    protected const TEST_BODY = 'Hello world';
    protected const TEST_FROM = 'from@example.com';
    protected const TEST_TO = 'to@example.com';
    protected const TEST_CC = 'cc@example.com';
    protected const TEST_BCC = 'bcc@example.com';
    protected const TEST_OVERRIDE = 'override@example.com';

    public function testOverrideTrue()
    {
        $siteConfig = SiteConfig::current_site_config();
        $siteConfig->setField('MailblockEnabled', true);
        $siteConfig->setField('MailblockRecipients', self::TEST_OVERRIDE);

        $messageEvent = $this->generateTestMessageEvent();

        $mailSubscriber = new MailblockMailSubscriber();
        $mailSubscriber->onMessage($messageEvent);

        $message = $messageEvent->getMessage();

        $addressList = array_map(function ($item) {
            return $item->getAddress();
        }, $message->getTo());

        $this->assertEquals(
            $message->getSubject(),
            sprintf('%s [addressed to %s, cc to %s, bcc to %s]', self::TEST_SUBJECT, self::TEST_TO, self::TEST_CC, self::TEST_BCC)
        );
        $this->assertTrue(in_array(self::TEST_OVERRIDE, $addressList));
        $this->assertFalse(in_array(self::TEST_TO, $addressList));
    }

    public function testOverrideFalse()
    {
        $siteConfig = SiteConfig::current_site_config();
        $siteConfig->setField('MailblockEnabled', false);
        $siteConfig->setField('MailblockRecipients', self::TEST_OVERRIDE);

        $messageEvent = $this->generateTestMessageEvent();

        $mailSubscriber = new MailblockMailSubscriber();
        $mailSubscriber->onMessage($messageEvent);

        $message = $messageEvent->getMessage();

        $addressList = array_map(function ($item) {
            return $item->getAddress();
        }, $message->getTo());

        $this->assertEquals($message->getSubject(), self::TEST_SUBJECT);
        $this->assertTrue(in_array(self::TEST_TO, $addressList));
        $this->assertFalse(in_array(self::TEST_OVERRIDE, $addressList));
    }

    public function testWhitelisting()
    {
        $siteConfig = SiteConfig::current_site_config();
        $siteConfig->setField('MailblockEnabled', true);
        $siteConfig->setField('MailblockRecipients', self::TEST_OVERRIDE);
        $siteConfig->setField('MailblockWhitelist', self::TEST_TO);

        $messageEvent = $this->generateTestMessageEvent();

        $mailSubscriber = new MailblockMailSubscriber();
        $mailSubscriber->onMessage($messageEvent);

        $message = $messageEvent->getMessage();

        $addressList = array_map(function ($item) {
            return $item->getAddress();
        }, $message->getTo());

        $this->assertTrue(in_array(self::TEST_TO, $addressList));
        $this->assertTrue(in_array(self::TEST_OVERRIDE, $addressList));
    }

    protected function generateTestMessageEvent(): MessageEvent
    {
        $email = Email::create();
        $email->setSubject(self::TEST_SUBJECT);
        $email->setBody(self::TEST_BODY);
        $email->setFrom(self::TEST_FROM);
        $email->setTo(self::TEST_TO);
        $email->setCC(self::TEST_CC);
        $email->setBCC(self::TEST_BCC);

        $sender = new Address(self::TEST_FROM);
        $recipient = new Address(self::TEST_TO);
        $envelope = new Envelope($sender, [$recipient]);

        $messageEvent = new MessageEvent($email, $envelope, '');
        return $messageEvent;
    }
}
