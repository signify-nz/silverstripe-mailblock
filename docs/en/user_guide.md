# Mailblock user guide

## Introduction
SilverStripe allows the redirection of emails by either defining the SS_SEND_ALL_EMAILS_TO environment constant or using the configuration API to set ‘send_all_emails_to’ on the Email class. These changes both need to be made in code or the _ss_environment.php file so deployments/modification of code needs to happen to update/add/remove the redirection email addresses. This module sets the redirection email address in the database so that it can be changed at any time.

## Permissions
The module adds a new "Access to 'Mailblock' settings" permission to the Security section of the CMS. Only administrators or users with this permission will be able to configure Mailblock settings.

## Settings
Mailblock settings are found in the Settings -> Mailblock section of the CMS.

### Enable mailblock
Enable this to activate mailblock. When disabled, no email redirection takes place under any circunstances.

### Apply mailblock settings per subsite
This setting only appears when the Subsite module exists. By default mailblock settings apply to both the main site and all subsites. Enabling this option allows mailblock to be configured on an individual subsite basis.

### Enable mailblock on live site
Enable this to activate mailblock on the live site. The 'live' site is determined by the value of the SS_ENVIRONMENT_TYPE constant. The intention is that mailblock can be set to enabled on all sites so that transferring databases between environments does not allow any emails being sent by mistake, but disabling this setting ensures it does not redirect emails on the live site.

Enabling this can be useful on prelive sites.

### Override configuration settings
Enable this to override any hard coded Email class 'send_all_emails_to' configuration settings. If this is disabled and the 'send_all_emails_to' configuration value is set, no additional redirection will take place.