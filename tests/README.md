# PHPUtils Test Suite

This directory contains unit tests for the PHPUtils library.

## Setup

1. Install dependencies:
```bash
composer install
```

2. Run tests:
```bash
./vendor/bin/phpunit
```

Or if you have PHPUnit installed globally:
```bash
phpunit
```

## Test Coverage

### BaseTest
- Tests Base class abstract nature
- Tests dependency injection
- Tests property initialization

### SQLTest
- Tests connection management
- Tests query execution
- Tests parameter binding
- Tests return types (result vs id)
- Tests error handling
- Tests constants

### DebuggerTest
- Tests constructor and dependency injection
- Tests format methods (data vs HTML)
- Tests debug logging
- Tests output methods
- Tests exception throwing

### VarsTest
- Tests variable assertion
- Tests array operations
- Tests stringification
- Tests multi-dimensional array searching

### StringsTest
- Tests slugify function
- Tests string capping
- Tests URL parameter appending

### NetworkTest
- Tests CIDR to range conversion
- Tests IP range checking
- Tests user/server IP detection
- Tests reverse proxy detection

### CryptoTest
- Hash/verifyhash, genIV, encrypt/decrypt with and without IV

### FilesTest
- is_file, file_read (including exception for missing file)

### SQLiteTest
- res(), clean(), sqlite_create_db / select_db / drop_db

### TimesTest
- getCurrentTime, relativeTime

### RandomTest
- array_pick_random, roll, percentage, genStr

## Running Specific Tests

Run a specific test class:
```bash
./vendor/bin/phpunit tests/SQLTest.php
```

Run a specific test method:
```bash
./vendor/bin/phpunit --filter testExecuteQuery tests/SQLTest.php
```

## Test Requirements

- PHP 8.0 or higher
- PHPUnit 10.0 or 11.x
- Composer (preferred; bootstrap falls back to _All.php if vendor not present)
- MySQL server (for SQL tests only - those tests are skipped if not available)
- OpenSSL extension (for Crypto tests)

## Note on SQL Tests

Some SQL tests require a MySQL server connection. If MySQL is not available, those tests will be automatically skipped. The tests use a temporary connection and don't require a specific database setup.

