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
		// Prepare plain text body
		$fullBody = $this->encodeMessage($plainContent, $this->getMessageEncoding());
		$headers["Content-Type"] = "text/plain; charset=utf-8";
		$headers["Content-Transfer-Encoding"] = $this->getMessageEncoding();

		$to = $this->mailblockRecipients($to);

		// Send prepared message
		return $this->sendPreparedMessage($to, $from, $subject, $attachedFiles,
			$customHeaders, $fullBody, $headers
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
		// Prepare both Plain and HTML components and merge
		$plainPart = $this->preparePlainSubmessage($plainContent, $htmlContent);
		$htmlPart = $this->prepareHTMLSubmessage($htmlContent);
		list($fullBody, $headers) = $this->encodeMultipart(
				array($plainPart, $htmlPart),
				"multipart/alternative"
		);

		$to = $this->mailblockRecipients($to);

		// Send prepared message
		return $this->sendPreparedMessage($to, $from, $subject, $attachedFiles,
			$customHeaders, $fullBody, $headers
		);
	}

	/**
	 * Replace the recipients with the recipients entered in Mailblock.
	 *
	 * @param string $recipients Original email recipients.
	 * @return string New email recients.
	 */
	private function mailblockRecipients($recipients) {
		$siteConfig = SiteConfig::current_site_config();
		$enabled = $siteConfig->getField('MailblockEnabled');
		if ($enabled) {
			$mailblockRecipients = $siteConfig->getField('MailblockRecipients');
			if (!empty($mailblockRecipients)) {
				$recipients = $mailblockRecipients;
			}
		}

		return $recipients;
	}
}