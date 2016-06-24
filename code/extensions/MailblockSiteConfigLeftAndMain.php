<?php

class MailblockSiteConfigLeftAndMain extends LeftAndMainExtension {
	public function init() {
		Requirements::javascript("silverstripe-mailblock/javascript/mailblock.js");
	}

	public function mailblockTestEmail($data, $form){
		if (class_exists('Subsite')) {
			$siteConfig = SiteConfig::get()->filter('SubsiteID', 0)->first();
		}
		else {
			$siteConfig = SiteConfig::current_site_config();
		}

		$to = $siteConfig->getField('MailblockTestTo');
		$from = $siteConfig->getField('MailblockTestFrom');
		$subject = $siteConfig->getField('MailblockTestSubject');
		$body = $siteConfig->getField('MailblockTestBody');
		$cc = $siteConfig->getField('MailblockTestCc');
		$bcc = $siteConfig->getField('MailblockTestBcc');
		$email = new Email($from, $to, $subject, $body, NULL, $cc, $bcc);
		$email->send();

		$this->owner->response->addHeader(
			'X-Status',
			rawurlencode('Test email sent!')
		);

		return $this->owner->getResponseNegotiator()
		->respond($this->owner->request);
	}
}