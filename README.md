![Tests](https://github.com/dietercoopman/databasecomparer/workflows/tests/badge.svg)


# This tools compares the schema of two databases

This tool compares two database structures and gives you the possiblity to generate a sql file or synchronize the database structure from source to target.

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
    ],
    'sqlfile'     => 'database/comparison.sql'
];

```

## Usage

```bash
php artisan dbcomparer:compare
```

the tool has two options , if no options provided you will be asked if you want to synchronize the structure 

      --sql             show an sql statement as output
      --save            save the sql statements to an sql file
    
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
