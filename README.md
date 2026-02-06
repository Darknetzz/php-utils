# php-utils
General-purpose PHP utility classes. Requires PHP 8.0+.


    # ────────────────────────────────────────────────────────── #
    #                                                            #
    #    $$\   $$\ $$$$$$$$\ $$$$$$\ $$\       $$$$$$\           #
    #    $$ |  $$ |\__$$  __|\_$$  _|$$ |     $$  __$$\          #
    #    $$ |  $$ |   $$ |     $$ |  $$ |     $$ /  \__|         #
    #    $$ |  $$ |   $$ |     $$ |  $$ |     \$$$$$$\           #
    #    $$ |  $$ |   $$ |     $$ |  $$ |      \____$$\          #
    #    $$ |  $$ |   $$ |     $$ |  $$ |     $$\   $$ |         #
    #    \$$$$$$  |   $$ |   $$$$$$\ $$$$$$$$\\$$$$$$  |         #
    #     \______/    \__|   \______|\________|\______/          #
    #                                                            #
    # ────────────────────────────────────────────────────────── #
    # ----[    General but useful PHP utilities. ]-------------  #
    # ----[    Made with ❤️ by darknetzz         ]-------------  #
    # ----[    https://github.com/Darknetzz/     ]-------------  #
    # ────────────────────────────────────────────────────────── #

This library includes a bunch of useful tools for PHP. I wrote this because I kept re-inventing the wheel every time I started a new PHP project.
There are other more complete alternatives out there (like Laravel), this was just made for fun and to learn.

# Get started
Simply open your PHP project and clone this repo.
```
git clone git@github.com:Darknetzz/php-utils.git
```

Then include the library. You can use Composer autoload (recommended) or `_All.php`:

**Using Composer (recommended):**
```bash
composer require darknetzz/php-utils
```
```php
require_once __DIR__ . '/vendor/autoload.php';

use PHPUtils\Crypto;
$crypto = new Crypto();
$hashedPassword = $crypto->hash("MyPassword123");
```

**Using _All.php (e.g. when cloned as a subfolder):**
```php
include_once("php-utils/PHPUtils/_All.php");  // adjust path if your folder name differs

use PHPUtils\Crypto;
$crypto = new Crypto();
$hashedPassword = $crypto->hash("MyPassword123");
```

# Modules
API documentation is generated in the `Docs/` folder. Class reference:

* [API](Docs/classes/API.html)
* [Auth](Docs/classes/Auth.html)
* [Calendar](Docs/classes/Calendar.html)
* [Crypto](Docs/classes/Crypto.html)
* [Debugger](Docs/classes/Debugger.html)
* [Files](Docs/classes/Files.html)
* [Funcs](Docs/classes/Funcs.html)
* [Images](Docs/classes/Images.html)
* [Navigation](Docs/classes/Navigation.html)
* [Network](Docs/classes/Network.html)
* [Random](Docs/classes/Random.html)
* [Resources](Docs/classes/Resources.html)
* [Session](Docs/classes/Session.html)
* [SQL](Docs/classes/SQL.html)
* [SQLite](Docs/classes/SQLite.html)
* [Strings](Docs/classes/Strings.html)
* [Times](Docs/classes/Times.html)
* [Vars](Docs/classes/Vars.html)

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for recent improvements (strict types, exception-based error handling, new tests, CI).

## Development

- **Tests:** `composer install && vendor/bin/phpunit`
- **CI:** GitHub Actions run PHPUnit on PHP 8.0–8.4 (see [.github/workflows/php.yml](.github/workflows/php.yml)).
