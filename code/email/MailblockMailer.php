<?php

/**
 * Mailblock mailer that allows email message redirection.
 *
 * @package silverstripe-mailblock
 * @subpackage email
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
		$rewrites = $this->mailblockRewrite($to, $subject);
		parent::sendPlain($rewrites['to'], $from, $rewrites['subject'],
			$plainContent, $attachedFiles, $customHeaders
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
		$rewrites = $this->mailblockRewrite($to, $subject);
		parent::sendHTML($rewrites['to'], $from, $rewrites['subject'],
			$htmlContent, $attachedFiles, $customHeaders, $plainContent
		);
	}

	/**
	 * Replace the recipients with the recipients entered in Mailblock.
	 *
	 * @param string $recipients Original email recipients.
	 * @param string $subject Original email subject.
	 * @return array Rewritten subject and recipients.
	 */
	protected function mailblockRewrite($recipients, $subject) {
		if (class_exists('Subsite')) {
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

		$enabled = $siteConfig->getField('MailblockEnabled');
		$enabledOnLive = $siteConfig->getField('MailblockEnabledOnLive');
		$overrideConfiguration = $siteConfig
			->getField('MailblockOverrideConfiguration');
		$configuration = Config::inst()->get('Email', 'send_all_emails_to');

		if($enabled && ($enabledOnLive || SS_ENVIRONMENT_TYPE != 'live')
		   && (!$configuration || $overrideConfiguration)
		) {
			$mailblockRecipients = $siteConfig->getField('MailblockRecipients');
			// Rewrite subject if 'send_all_emails_to' is not set.
			// If it is set, the subject has already been rewritten.
			if(!$configuration) {
				$subject .= " [addressed to $recipients";
				// @TODO BCC/CC
				$subject .= ']';
			}
			$recipients = implode(', ', preg_split("/\r\n|\n|\r/",
				$mailblockRecipients
			));
		}

		$rewrites = array(
			'to'      => $recipients,
			'subject' => $subject,
		);
		return $rewrites;
	}
}
