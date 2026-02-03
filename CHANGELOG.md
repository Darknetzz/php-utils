# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/).

## [Unreleased]

### Added

- **Strict types**: `declare(strict_types=1);` in all PHP library and test files.
- **Vars::arrayContainsSubstring()**: Alias for `arrayInString()` with clearer naming.
- **Tests**: New test classes for Crypto, Files, SQLite, Times, and Random.
- **CI**: GitHub Actions workflow (`.github/workflows/php.yml`) running PHPUnit on PHP 8.0–8.4 for `main` and `dev`.
- **CHANGELOG.md**: This file.

### Changed

- **Error handling**: Replaced `die()` with exceptions in Network, Random, Files, and Debugger (bootstrap `_All.php` still uses `die()` for fatal startup failures).
  - `Network::getUserIP()` / `getServerIP()`: throw `RuntimeException` when `$die_if_empty` is true and IP cannot be determined.
  - `Random::genStr()`: throw `InvalidArgumentException` for invalid type instead of `die()`.
  - `Files::preventDirect()` default callback: throw `RuntimeException("404 Not found")` after setting 404 response code.
  - `Debugger::output(..., $die = true)`: echo then throw `RuntimeException` instead of `die()`.
- **Network::ipInRange()**: Removed `echo` on invalid IPs; still returns `null` for invalid input. Fixed check to use `=== false` for `ip2long()` so `0.0.0.0` is valid.
- **Vars::arrayInString()**: Documented behaviour (any element contains needle); added return type `bool`.
- **Images**: Docblocks use `\Imagick` and `\ImagickException`; `blur()` has explicit return type `string`.
- **Composer**: `composer.lock` is no longer ignored so dev and CI use reproducible dependency versions.

### Documentation

- **README**: Get started section documents both Composer and _All.php; module links point to `Docs/classes/*.html`.
- **TEST_SUMMARY.md**: Updated to include new test classes and strict types / exception behaviour.
- **REFACTORING_SUMMARY.md**: Can be updated to reference this changelog for recent improvements.

---

## Previous work (pre-changelog)

- OOP refactor: namespaces, Base/DI, SQL connection injection, identifier validation, Debugger data/HTML separation, visibility and return types (see `REFACTORING_SUMMARY.md` and `OOP_REVIEW.md`).
- Crypto: corrected `hash()` argument order, `verifyhash()` with `hash_equals()`, IV handling with embedded IV in ciphertext.
- Images/SQLite: `parent::__construct()`; SQLite visibility and `$this->res()`.
- SQL::search(): table and column names validated via `validateIdentifier()`.
- README and index.php: fixed doc and test links; bootstrap prefers Composer autoload.
