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
		'MailblockTestFrom'              => 'Text',
		'MailblockTestTo'                => 'Text',
		'MailblockTestSubject'           => 'Text',
		'MailblockTestBody'              => 'Text',
		'MailblockTestCc'                => 'Text',
		'MailblockTestBcc'                => 'Text',
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

			$tabSet = new TabSet(
				'Mailblock',
				$settingsTab = $this->basicSettingsFields(),
				$advancedSettingsTab = $this
					->advancedSettingsFields($onMainSite, $currentSubsiteID),
				$testTab = $this->testEmailCMSFields()
			);
			$fields->addFieldToTab('Root', $tabSet);

			$hiddenFields = array(
				'MailblockEnabledOnLive',
				'MailblockOverrideConfiguration',
				'MailblockRecipients',
				'MailblockWhitelist',
			);
			if($subsites && $currentSubsiteID == 0) {
				$hiddenFields[] = 'MailblockApplyPerSubsite';
			}
			foreach ($hiddenFields as $field) {
				$field = $fields->dataFieldByName($field);
				$field->displayIf('MailblockEnabled')->isChecked();
			}
		}
	}

	public function updateCMSActions(FieldList $actions) {
		$testAction = FormAction::create('mailblockTestEmail', 'Send Test Email');
		$actions->push($testAction);
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

	private function basicSettingsFields() {
		$tab = Tab::create('BasicSettings',
			CheckboxField::create(
				'MailblockEnabled',
				_t('Mailblock.Enabled','Enable mailblock.')
			),
			TextareaField::create(
				'MailblockRecipients',
				_t('Mailblock.Recipients',
					'Recipient(s) for out-going mail'
				)
			)->setDescription(_t('Mailblock.RecipientsDescription',
				'Redirect messages sent via the MailblockMailer to these '
			  . 'addresses (one per line).'
			)),
			TextareaField::create(
				'MailblockWhitelist',
				_t('Mailblock.Whitelist',
						'Whitelist'
				)
			)->setDescription(_t('Mailblock.WhitelistDescription',
				'Permit delivery to these email addresses (one per line). '
			))
		);
		return $tab;
	}

	private function advancedSettingsFields($subsites, $currentSubsiteID) {
		$applyPerSubsite = '';
		if($subsites && $currentSubsiteID == 0) {
			$applyPerSubsite = CheckboxField::create(
				'MailblockApplyPerSubsite',
				_t('Mailblock.ApplyPerSubsite',
					'Apply mailblock settings per subsite.'
				)
			)->setDescription(
				_t('Mailblock.ApplyPerSubsiteDescription',
					'If ticked then different mailblock settings appply '
				  . 'per subsite rather than globally.'
				)
			);
		}

		$tab = Tab::create('AdvancedSettings',
			CheckboxField::create(
				'MailblockEnabledOnLive',
				_t('Mailblock.EnabledOnLive',
					'Enable mailblock on live site.'
				)
			)->setDescription(_t('Mailblock.EnabledOnLiveDescription',
					'Whether messages sent via the MailblockMailer should be '
					. 'redirected to the below recipient(s). Useful for prelive sites.'
			)),
			$overrideConfiguration = CheckboxField::create(
					'MailblockOverrideConfiguration',
					_t('Mailblock.OverrideConfiguration',
							'Override configuration settings.'
					)
			)->setDescription(_t('Mailblock.OverrideConfigurationDescription',
					'Whether mailblock should override the hard coded Email class '
					. '\'send_all_emails_to\' configuration setting.'
			)),
			$applyPerSubsite
		);

		return $tab;
	}

	private function testEmailCMSFields() {
		$tab = Tab::create('TestEmail',
			TextField::create(
				'MailblockTestTo',
				_t('Mailblock.TestTo',
					'To'
				)
			),
			TextField::create(
				'MailblockTestFrom',
				_t('Mailblock.TestFrom',
					'From'
				)
			),
			TextField::create(
				'MailblockTestCc',
				_t('Mailblock.TestCc',
					'Cc'
				)
			),
			TextField::create(
				'MailblockTestBcc',
				_t('Mailblock.TestBcc',
					'Bcc'
				)
			),
			TextField::create(
				'MailblockTestSubject',
				_t('Mailblock.TestSubject',
					'Subject'
				)
			),
			TextareaField::create(
				'MailblockTestBody',
				_t('Mailblock.TestBody',
					'Body'
				)
			)
		);


		return $tab;
	}
}
