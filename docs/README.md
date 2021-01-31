# Laravel Borica
Laravel wrapper for easy and seamless integration with Borica VPOS.

Made with love and code by [Fundamental Studio Ltd.](https://www.fundamental.bg)

## Installation

The package is compatible with Laravel 7+ version.

Via composer:
``` bash
$ composer require fmtl-studio/laravel-borica
```

### Pubslish the provider
After installing, the package should be auto-discovered by Laravel.
In order to configurate the package, you need to publish the config file using this command:
``` bash
$ php artisan vendor:publish --provider="Fundamental\Borica\BoricaServiceProvider"
```
### Config

After publishing the config file, you should either add the needed keys to the global .env Laravel file:
```
BORICA_TERMINAL_ID=XXXXXXXXXX # Terminal ID, obtained from your bank or Borica Service
BORICA_PRODUCTION=FALSE # Should the platform use the production or the test Borica endpoint
BORICA_PRIVATE_KEY= # Location of your private key file, make sure it is not available to public
BORICA_PRIVATE_KEY_PASS= # Location of your private key password, make sure it this file is not available to public
BORICA_CERTIFICATE= # Location of your certificate file, make sure it is not available to public
```

You are up & running and ready to go.

## Documentation and Usage instructions

The usage of our package is pretty seamless and easy.
First of all, you need to use the proper namespace for our package:
```
use Fundamental\Borica\Borica;
```

## Changelog
All changes are available in our Changelog file.

## Support
For any further questions, feature requests, problems, ideas, etc. you can create an issue tracker or drop us a line at support@fundamental.bg

## Contributing
Read the Contribution file for further information.

## Credits

- Konstantin Rachev
- Vanya Ananieva

The package is bundled and contributed to the community by Fundamental Studio Ltd.'s team.

## Issues
If you discover any issues, please use the issue tracker.

## Security
If your discover any security-related issues, please email konstantin@fundamental.bg or support@fundamental.bg instead of using the issue tracker.

## License
The MIT License(MIT). See License file for further information and reading.