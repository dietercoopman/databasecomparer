# This tools compares the schema of two databases

This is where your description should go. Limit it to a paragraph or two. Consider adding a small example.

## Installation

You can install the package via composer:

```bash
composer require dietercoopman/databasecomparer
```

You can publish the config file with:
```bash
php artisan vendor:publish --provider="DieterCoopman\DatabaseComparer\DatabaseComparerServiceProvider" --tag="databasecomparer-config"
```

This is the contents of the published config file:

```php
return [
    'connections' => [
        'source' => 'mysql',
        'target' => 'mysql'
    ]
];

```

## Usage


## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Dieter Coopman](https://github.com/dietercoopman)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
