# Logx

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Total Downloads][ico-downloads]][link-downloads]
[![Build Status][ico-travis]][link-travis]


Record the log, display the class name and method name in the log, at the same time, it can conveniently display all kinds of data including JSON, array, etc. Take a look at [contributing.md](contributing.md) to see a to do list.

## Installation

Via Composer

``` bash
$ composer require tswtools/logx
```

## Usage
Then run these commands to publish assets and config：
``` bash
php artisan vendor:publish --provider="Tswtools\Logx\LogxServiceProvider" 
```

#Config
Filters allow you to filter specified classes or methods
``` bash
Specify the IP address that can be recorded
config/logx/ip:
include : x.x.x.x
exclude : x.x.x.x

Specify the class or method that can be recorded
config/logx/method:
include : XxxxController,XyyyController::*,*::index,show
exclude : XxxxController,XxxxController::*,*::index,show

'*' means that every class or method  is OK
```

#example
``` bash
[2020-12-25 13:09:50][127.0.0.1[TestController::index][26] "data1":{"a":1,"b":2}
[2020-12-25 13:10:36][127.0.0.1[TestController::index][25] "data2":[{"a":1,"b":2}]
```

## Change log

Please see the [changelog](changelog.md) for more information on what has changed recently.

## Contributing

Please see [contributing.md](contributing.md) for details and a todolist.

## Security

If you discover any security related issues, please email author email instead of using the issue tracker.

## Credits

- [twstools][link-author]
- [All Contributors][link-contributors]

## License

license. Please see the [license file](license.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/tswtools/logx.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/tswtools/logx.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/tswtools/logx/master.svg?style=flat-square
[ico-styleci]: https://styleci.io/repos/12345678/shield

[link-packagist]: https://packagist.org/packages/tswtools/logx
[link-downloads]: https://packagist.org/packages/tswtools/logx
[link-travis]: https://travis-ci.org/tswtools/logx
[link-styleci]: https://styleci.io/repos/12345678
[link-author]: https://github.com/tswtools
[link-contributors]: ../../contributors
