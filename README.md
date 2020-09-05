# Mailblock
Adds email redirection options to the CMS.

## Requirements
* [SilverStripe CMS ^4](https://github.com/silverstripe/silverstripe-cms)

SilverStripe 3 support is available in the [1.0 branch](https://github.com/signify-nz/silverstripe-mailblock/tree/1.0)

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
This module works by adding an additional SwiftMailer (the Mailer included with SilverStripe) plugin. If you are using a custom mailer, no email recipient rewritting will take place.

[User Guide](/docs/en/user_guide.md)
