<?php

/**
 * Mailblock mailer test.
 *
 * @package silverstripe-mailblock
 * @subpackage test
 */
class MailblockMailerTest extends SapphireTest {

	protected static $fixture_file = "MailblockMailerTest.yml";

	public function setUp() {
		parent::setUp();
		$this->mailer = new TestMailblockMailer();
		Injector::inst()->registerService($this->mailer, 'Mailer');
	}

	public function setUpEmail() {
		$email = new Email();
		$email->To = 'unblocked@example.com';
		$email->Subject = 'Subject';
		$email->Body = 'Content.';

		return $email;
	}

	/**
	 * Test that no email redirection occurs when the module is disabled.
	 */
	public function testDisabled() {
		$siteConfig = $this->objFromFixture('SiteConfig', 'default');
		$siteConfig->setField('MailblockEnabled', FALSE);
		$siteConfig->write();
		$email = $this->setUpEmail();
		$email->send();
		$this->assertEmailSent('unblocked@example.com', null, null, null);
	}

	/**
	 * Test that redirection occurs to the specified address.
	 */
	public function testRecipientRewrite() {
		$siteConfig = $this->objFromFixture('SiteConfig', 'default');
		$email = $this->setUpEmail();
		$email->send();
		$this->assertEmailSent('blocked@example.com', null, null, null);
	}
}

/**
 * Test mailblock mailer.
 * Based on {$link MailblockMailer} but prevents actually sending emails as
 * {@link SapphireTest::clearEmails()} does.
 *
 * @package silverstripe-mailblock
 * @subpackage test
 */
class TestMailblockMailer extends MailblockMailer {

	protected $emailsSent = array();

	public function sendPlain($to, $from, $subject, $plainContent, $attachedFiles = FALSE, $customHeaders = FALSE) {
		$rewrites = $this->mailblockRewrite($to, $subject);

		$this->emailsSent[] = array(
			'type' => 'plain',
			'to' => $rewrites['to'],
			'from' => $from,
			'subject' => $rewrites['subject'],

			'content' => $plainContent,
			'plainContent' => $plainContent,

			'attachedFiles' => $attachedFiles,
			'customHeaders' => $customHeaders,
		);

		return true;
	}

	public function sendHTML($to, $from, $subject, $htmlContent,
		$attachedFiles = FALSE, $customHeaders = FALSE, $plainContent = FALSE
	) {
		$rewrites = $this->mailblockRewrite($to, $subject);

		$this->emailsSent[] = array(
			'type' => 'html',
			'to' => $rewrites['to'],
			'from' => $from,
			'subject' => $rewrites['subject'],

			'content' => $htmlContent,
			'plainContent' => $plainContent,
			'htmlContent' => $htmlContent,

			'attachedFiles' => $attachedFiles,
			'customHeaders' => $customHeaders,
		);

		return true;
	}

	/**
	 * Clear the log of emails sent
	 *
	 * @see TestMailer::clearEmails()
	 */
	public function clearEmails() {
		$this->emailsSent = array();
	}

	/**
	 * Search for an email that was sent.
	 * All of the parameters can either be a string, or, if they start with "/", a PREG-compatible regular expression.
	 * @param $to
	 * @param $from
	 * @param $subject
	 * @param $content
	 * @return array Contains the keys: 'type', 'to', 'from', 'subject', 'content', 'plainContent', 'attachedFiles',
	 *               'customHeaders', 'htmlContent', 'inlineImages'
	 *
	 * @see TestMailer::findEmail()
	 */
	public function findEmail($to, $from = null, $subject = null, $content = null) {
		foreach($this->emailsSent as $email) {
			$matched = true;

			foreach(array('to','from','subject','content') as $field) {
				if($value = $$field) {
					if($value[0] == '/') $matched = preg_match($value, $email[$field]);
					else $matched = ($value == $email[$field]);
					print_r('cc' . $value . ' ' . $email[$field]);
					if(!$matched) break;
				}
			}

			if($matched) return $email;
		}
	}
}