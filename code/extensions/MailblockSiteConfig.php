<?php
/**
 * Adds a mailblock section to the 'Settings' section of the CMS.
 *
 * @package silverstripe-mailblock
 * @subpackage extensions
 */

class MailblockSiteConfig extends DataExtension implements PermissionProvider {

	private static $db = array(
		'MailblockEnabled' => 'Boolean',
	);

	public function updateCMSFields(FieldList $fields) {
		if(Permission::checkMember($member, 'MANAGE_MAILBLOCK')) {
			$fields->addFieldToTab(
				'Root.Mailblock',
				$enable = CheckboxField::create(
					'MailblockEnabled',
					_t('MailblockConfig.Enabled','Enable mailblock.')
				)
			);
		}
	}

	/**
	 * Provide permissions to the CMS.
	 *
	 * @return array
	 */
	public function providePermissions() {
		return array(
			'MANAGE_MAILBLOCK' => array(
				'name'     => _t('Mailblock.ADMIN_PERMISSION',
					"Access to 'Mailblock' settings"
				),
				'category' => _t('Permission.CMS_ACCESS_CATEGORY',
					'CMS Access'
				),
				'sort'     => 100,
			),
		);
	}
}