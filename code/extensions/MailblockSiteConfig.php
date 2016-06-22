<?php

/**
 * Adds a mailblock section to the 'Settings' section of the CMS.
 *
 * @package silverstripe-mailblock
 * @subpackage extensions
 */
class MailblockSiteConfig extends DataExtension implements PermissionProvider {

	private static $db = array(
		'MailblockEnabled'               => 'Boolean',
		'MailblockApplyPerSubsite'       => 'Boolean',
		'MailblockEnabledOnLive'         => 'Boolean',
		'MailblockOverrideConfiguration' => 'Boolean',
		'MailblockRecipients'            => 'Text',
		'MailblockWhitelist'             => 'Text',
	);

	public function validate(ValidationResult $validationResult) {
		$mailblockRecipients = $this->owner->getField('MailblockRecipients');
		if (!$this->validateEmailAddresses($mailblockRecipients)) {
			$validationResult->error(_t('Mailblock.RecipientError',
				'There are invalid email addresses in the Recipient(s) field.'
			));
		}

		$whitelist = $this->owner->getField('MailblockWhitelist');
		if (!$this->validateEmailAddresses($whitelist)) {
			$validationResult->error(_t('Mailblock.WhitelistError',
				'There are invalid email addresses in the Whitelist field.'
			));
		}
	}

	protected function validateEmailAddresses($emails) {
		if (!empty($emails)) {
			$recipients = preg_split("/\r\n|\n|\r/", $emails);
			foreach ($recipients as $recipient) {
				if (!Email::validEmailAddress($recipient)) {
					return FALSE;
				}
			}
		}
		return TRUE;
	}

	public function updateCMSFields(FieldList $fields) {
		$subsites = class_exists('Subsite');
		$onMainSite = TRUE;
		$currentSubsiteID = 0;
		if ($subsites) {
			$currentSubsiteID = Subsite::currentSubsiteID();
			if ($currentSubsiteID) {
				$onMainSite = FALSE;
			}
			$mainSiteConfig = SiteConfig::get()->filter('SubsiteID', 0)->first();
		}
		else {
			$mainSiteConfig = SiteConfig::current_site_config();
		}

		// Add mailblock CMS fields.
		if(Permission::check('MANAGE_MAILBLOCK')
		   && ($mainSiteConfig->getField('MailblockApplyPerSubsite') || $onMainSite)
		) {
			$fields->addFieldsToTab(
				'Root.Mailblock',
				$basicSettings = LiteralField::create(
					'MailblockBasicSettings',
					'<h3>' . _t('Mailblock.BasicSettings',
						'Basic Settings'
					) . '</h3>'
				)
			);

			$fields->addFieldToTab(
				'Root.Mailblock',
				$enable = CheckboxField::create(
					'MailblockEnabled',
					_t('Mailblock.Enabled','Enable mailblock.')
				)
			);

			$fields->addFieldToTab(
				'Root.Mailblock',
				$recipients = TextareaField::create(
					'MailblockRecipients',
					_t('Mailblock.Recipients',
						'Recipient(s) for out-going mail'
					)
				)
			);
			$recipients->setDescription(_t('Mailblock.RecipientsDescription',
					'Redirect messages sent via the MailblockMailer to these '
					. 'addresses (one per line).'
			));

			$fields->addFieldToTab(
					'Root.Mailblock',
					$whitelist = TextareaField::create(
							'MailblockWhitelist',
							_t('Mailblock.Whitelist',
									'Whitelist'
							)
					)
			);
			$whitelist->setDescription(_t('Mailblock.WhitelistDescription',
					'Permit delivery to these email addresses (one per line). '
			));

			$fields->addFieldsToTab(
				'Root.Mailblock',
				$advancedSettings =  DisplayLogicWrapper::create(LiteralField::create(
					'MailblockAdvancedSettings',
					'<h3>' . _t('Mailblock.AdvancedSettings',
						'Advanced Settings'
					) . '</h3>'
				))
			);

			if($subsites) {
				if($currentSubsiteID == 0) {
					$fields->addFieldToTab(
						'Root.Mailblock',
						$applyPerSubsite = CheckboxField::create(
							'MailblockApplyPerSubsite',
							_t('Mailblock.ApplyPerSubsite',
									'Apply mailblock settings per subsite.'
							)
						)
					);
					$applyPerSubsite->setDescription(
						_t('Mailblock.ApplyPerSubsiteDescription',
							'If ticked then different mailblock settings appply '
						  . 'per subsite rather than globally.'
						)
					)->displayIf('MailblockEnabled')->isChecked();
				}
			}

			$fields->addFieldToTab(
				'Root.Mailblock',
				$enableOnLive = CheckboxField::create(
					'MailblockEnabledOnLive',
					_t('Mailblock.EnabledOnLive',
						'Enable mailblock on live site.'
					)
				)
			);
			$enableOnLive->setDescription(_t('Mailblock.EnabledOnLiveDescription',
				'Whether messages sent via the MailblockMailer should be '
			  . 'redirected to the below recipient(s). Useful for prelive sites.'
			));

			$fields->addFieldToTab(
				'Root.Mailblock',
				$overrideConfiguration = CheckboxField::create(
					'MailblockOverrideConfiguration',
					_t('Mailblock.OverrideConfiguration',
						'Override configuration settings.'
					)
				)
			);
			$overrideConfiguration->setDescription(_t('Mailblock.OverrideConfigurationDescription',
				'Whether mailblock should override the hard coded Email class '
			  . '\'send_all_emails_to\' configuration setting.'
			));

			$hiddenFields = array(
				$enableOnLive,
				$overrideConfiguration,
				$recipients,
				$advancedSettings,
			);
			foreach ($hiddenFields as $field) {
				$field->displayIf('MailblockEnabled')->isChecked();
			}
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
					'Access to \'Mailblock\' settings'
				),
				'category' => _t('Permission.CMS_ACCESS_CATEGORY',
					'CMS Access'
				),
				'sort'     => 100,
			),
		);
	}
}
