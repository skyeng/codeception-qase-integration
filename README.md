# Codeception QASE Integration

This [Codeception](https://codeception.com) extension provides functionality for tests to report results to
[QASE](https://qase.io/) using the [HTTP API](https://developers.qase.io/docs).

**Note:** The extension currently only supports the `Cest` Codeception test format.  It cannot report PHPUnit or `Cept`
tests.

## Installation

```
composer require --dev skyeng/codeception-qase-integation:^1.0.0
```

## Configuration:

Please configure your extension with parameters like `enabled`, `token`, and more. These can vary from environment to environment.

```yaml
extensions:
  enabled:
    - Skyeng\Codeception\Qase\QaseExtension
  config:
    Skyeng\Codeception\Qase\QaseExtension:
      enabled: "%QASE_ENABLE%"
      token: "%QASE_TOKEN%"
      project: "%QASE_PROJECT%"
```

## Tests

All you need to do is to define what Codeception test equals what QASE test. Do this, by simply appending a new annotation to your tests.
The extension will now automatically look for this annotation, and send the test result of this ID to the Test Run.

```php
 /**
  * @qase-case 42
  */
 public function testMyProcess(...)
 {
     ...
 }
```
