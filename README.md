# Convert a php array into an includeable php file

[![Latest Version on Packagist](https://img.shields.io/packagist/v/kfriars/php-array-to-file.svg?style=flat-square)](https://packagist.org/packages/kfriars/php-array-to-file)
[![Total Downloads](https://img.shields.io/packagist/dt/kfriars/php-array-to-file.svg?style=flat-square)](https://packagist.org/packages/kfriars/php-array-to-file)
[![GitHub Tests Action Status](https://github.com/kfriars/php-array-to-file/workflows/Tests/badge.svg
)](https://github.com/kfriars/php-array-to-file/actions?query=workflow%3Arun-tests+branch%3Amaster)
[![Test Coverage](https://api.codeclimate.com/v1/badges/9a15cbdfb616e078db23/test_coverage)](https://codeclimate.com/github/kfriars/php-array-to-file/test_coverage)
[![Maintainability](https://api.codeclimate.com/v1/badges/9a15cbdfb616e078db23/maintainability)](https://codeclimate.com/github/kfriars/php-array-to-file/maintainability)

The purpose of this package is to print an array to a file in a reader-friendly format, that can later be included as php. The package supports deeply nested arrays, with numeric, string, boolean and object values.

## Installation

You can install the package via composer:

```bash
composer require kfriars/php-array-to-file
```

## Usage
You can use the static method ```toFile(...)``` on ```Kfriars\ArrayToFile\ArrayWriter``` for convenient use, or you can inject the ```Kfriars\ArrayToFile\ArrayToFile``` class as a dependency, and use ```write(...)```.

An example of use:
``` php
ArrayWriter::toFile([1, 2, 3], '/absolute/path/to/file.php');
```

Would create ```/absolute/path/to/file.php``` with the contents:
```
<?php

return [
    1,
    2,
    3,
];

```

The package also allows you to transform the values in your array by passing in a callable. The callable receives the value before it is written to the file, and should return the value you desire to have written. You can use it like:
``` php
function save(ArrayToFile $a2f)
{
    $a2f->write([0, 1, '', ' '], '/absolute/path/to/file.php', function ($value) {
        return (bool) $value;
    });
}
```

Which will create ```/absolute/path/to/file.php``` with the contents:
```
<?php

return [
    false,
    true,
    false,
    true,
];

```

## Testing

``` bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email nyxsoft.inc@gmail.com instead of using the issue tracker.

## Credits

- [Kurt Friars](https://github.com/kfriars)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
