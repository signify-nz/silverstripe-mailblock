<?php

namespace Mailblock\Extensions;

use SilverStripe\View\Requirements;
use SilverStripe\SiteConfig\SiteConfig;
use SilverStripe\Control\Email\Email;
use SilverStripe\Admin\LeftAndMainExtension;
use SilverStripe\Subsites\Model\Subsite;

/**
 * Adds a mailblock test email action to the SiteConfig menu.
 */
class MailblockSiteConfigLeftAndMain extends LeftAndMainExtension
{
    public function subsiteCMSShowInMenu(){
        if ($subsites = class_exists(Subsite::class)) {
            return true;
        }
    }

    public function init() {
        Requirements::javascript("signify-nz/silverstripe-mailblock:javascript/mailblock.js");
    }

    public function mailblockTestEmail($data, $form){
        if (class_exists(Subsite::class)) {
            $siteConfig = SiteConfig::get()->filter('SubsiteID', 0)->first();
        }
        else {
            $siteConfig = SiteConfig::current_site_config();
        }

        $to = $siteConfig->getField('MailblockTestTo') ?? '';
        $from = $siteConfig->getField('MailblockTestFrom') ?? '';
        $subject = $siteConfig->getField('MailblockTestSubject') ?? '';
        $body = $siteConfig->getField('MailblockTestBody') ?? '';
        $cc = $siteConfig->getField('MailblockTestCc') ?? '';
        $bcc = $siteConfig->getField('MailblockTestBcc') ?? '';
        $email = new Email($from, $to, $subject, $body, $cc, $bcc);
        $email->send();

        $this->owner->response->addHeader(
            'X-Status',
            rawurlencode('Test email sent!')
        );

        return $this->owner->getResponseNegotiator()
            ->respond($this->owner->request);
    }
}
