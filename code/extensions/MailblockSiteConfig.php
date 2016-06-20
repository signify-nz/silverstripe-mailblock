<?php

/**
 * Adds a mailblock section to the 'Settings' section of the CMS.
 *
 * @package silverstripe-mailblock
 * @subpackage extensions
 */
class MailblockSiteConfig extends DataExtension implements PermissionProvider {

	private static $db = array(
		'MailblockEnabled'    => 'Boolean',
		'MailblockRecipients' => 'Text',
	);

	public function validate(ValidationResult $validationResult) {
		$mailblockRecipients = $this->owner->getField('MailblockRecipients');
		if (!empty($mailblockRecipients)) {
			$recipients = preg_split("/\r\n|\n|\r/", $mailblockRecipients);
			foreach ($recipients as $recipient) {
				if (!Email::validEmailAddress($recipient)) {
					$validationResult->error(_t('MailblockConfig.RecipientError',
						'All Mailblock recipients are not valid email addresses.'
					));
				}
			}
		}
	}

	public function updateCMSFields(FieldList $fields) {
		if(Permission::checkMember($member, 'MANAGE_MAILBLOCK')) {
			$fields->addFieldToTab(
				'Root.Mailblock',
				$enable = CheckboxField::create(
					'MailblockEnabled',
					_t('MailblockConfig.Enabled','Enable mailblock.')
				)
			);

			$fields->addFieldToTab(
				'Root.Mailblock',
				$recipients = TextareaField::create(
					'MailblockRecipients',
					_t('MailblockConfig.Recipients',
						'Recipient(s) for out-going mail'
					)
				)
			);
			$recipients->setDescription(_t('MailblockConfig.Recipients',
				'Redirect messages sent via the MailblockMailer to these '
			  . 'addresses (one per line).'
			));
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
