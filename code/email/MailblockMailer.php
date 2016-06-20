<?php

/**
 * Adds a mailblock section to the 'Settings' section of the CMS.
 *
 * @package silverstripe-mailblock
 * @subpackage extensions
 */
class MailblockMailer extends Mailer {

	/**
	 * Send a plain-text email.
	 *
	 * @param string $to Email recipient
	 * @param string $from Email from
	 * @param string $subject Subject text
	 * @param string $plainContent Plain text content
	 * @param array $attachedFiles List of attached files
	 * @param array $customHeaders List of custom headers
	 * @return mixed Return false if failure, or list of arguments if success
	 */
	public function sendPlain($to, $from, $subject, $plainContent,
		$attachedFiles = array(), $customHeaders = array()
	) {
		$to = $this->mailblockRewrite($to);
		parent::sendPlain($to, $from, $subject, $plainContent, $attachedFiles,
			$customHeaders
		);
	}

	/**
	 * Sends an email as a both HTML and plaintext
	 *
	 * @param string $to Email recipient
	 * @param string $from Email from
	 * @param string $subject Subject text
	 * @param string $htmlContent HTML Content
	 * @param array $attachedFiles List of attachments
	 * @param array $customHeaders User specified headers
	 * @param string $plainContent Plain text content.
	 * 							   If omitted, will be generated from $htmlContent
	 * @return mixed Return false if failure, or list of arguments if success
	 */
	public function sendHTML($to, $from, $subject, $htmlContent,
		$attachedFiles = array(), $customHeaders = array(), $plainContent = ''
	) {
		$to = $this->mailblockRewrite($to);
		parent::sendHTML($to, $from, $subject, $htmlContent, $attachedFiles,
			$customHeaders, $plainContent
		);
	}

	/**
	 * Replace the recipients with the recipients entered in Mailblock.
	 *
	 * @param string $recipients Original email recipients.
	 * @param string $subject Original email subject.
	 * @return string New email recients.
	 */
	private function mailblockRewrite($recipients, $subject = '') {
		$siteConfig = SiteConfig::current_site_config();
		$enabled = $siteConfig->getField('MailblockEnabled');
		$enabledOnLive = $siteConfig->getField('MailblockEnabledOnLive');
		$environment = SS_ENVIRONMENT_TYPE;

		if ($enabled && ($enabledOnLive || $environment != 'live')) {
			$mailblockRecipients = $siteConfig->getField('MailblockRecipients');
			if (!empty($mailblockRecipients)) {
				$recipients = implode(', ', preg_split("/\r\n|\n|\r/",
					$mailblockRecipients
				));
			}
		}
		return $recipients;
	}
}
