# Mailblock
Adds email redirection options to the CMS.

## Requirements

* [SilverStripe CMS ^3.2](https://github.com/silverstripe/silverstripe-cms/tree/3)

## Installation
__Composer (recommended):__
```
composer require signify-nz/silverstripe-mailblock
```

If you prefer you may also install manually:
* Download the module from here https://github.com/signify-nz/silverstripe-mailblock/archive/master.zip
* Extract the downloaded archive into your site root so that the destination folder is called silverstripe-mailblock.
* Run dev/build?flush to regenerate the manifest

## Usage
All options are in the Settings -> Mailblock section of the CMS. Only users with the "Access to 'Mailblock' settings" permission will be able to configure Mailblock.

## Limitations
This module works by setting the Mailer class to the MailblockMailer. If you are using a custom mailer, it will need to extend the MailblockMailer class for this module to work.

[User Guide](/docs/en/user_guide.md)
