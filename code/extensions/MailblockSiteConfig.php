<?php
/**
 * TODO
 *
 * @package silverstripe-mailblock
 * @subpackage extensions
 */

class MailblockSiteConfig extends DataExtension {

	private static $db = array(
		'MailblockEnabled' => 'Boolean',
	);

	public function updateCMSFields(FieldList $fields) {

		$fields->addFieldToTab(
			'Root.Mailblock',
			$enable = CheckboxField::create(
				'MailblockEnabled',
				_t('MailblockConfig.Enabled','Enable mailblock.')
			)
		);
	}
}