# Mailblock

Adds email redirection options to the CMS.

## Requirements

* [SilverStripe admin ^5](https://github.com/silverstripe/silverstripe-admin)

SilverStripe 4 support is available in the [2.0 branch](https://github.com/signify-nz/silverstripe-mailblock/tree/2.0)
SilverStripe 3 support is available in the [1.0 branch](https://github.com/signify-nz/silverstripe-mailblock/tree/1.0)

## Installation

```
composer require signify-nz/silverstripe-mailblock
```

## Usage

All options are in the Settings -> Mailblock section of the CMS. Only users with the "Access to 'Mailblock' settings" permission will be able to configure Mailblock.

## Limitations

This module works by adding an additional Symfony mailer (the Mailer included with SilverStripe) event subscriber. If you are using a custom mailer, no email recipient rewriting will take place.

[User Guide](/docs/en/user_guide.md)
