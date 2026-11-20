# Imager Storage Driver for Amazon Web Services (S3)

## 2.0.0 - Unreleased

> [!IMPORTANT]
> Version 2 requires Craft CMS 6, Imager X 7 and PHP 8.5, and there are breaking changes for code that uses the plugin's classes. See [UPGRADE.md](UPGRADE.md).

### Changed
- The plugin now requires Craft CMS 6, Imager X 7.0.0-beta.2 or later, and PHP 8.5, and uses Craft 6's native plugin APIs. It doesn't need the Yii 2 adapter.
- Classes moved to the `SpaceCatNinja\ImagerXAWSStorageDriver` namespace, and to Laravel-style folders.
- The storage driver is now registered with Imager X's `ExternalStorages` registry.
- Requests to AWS now use Craft 6's HTTP client settings, from `config/craft/guzzle.php` and the `httpProxy` setting. They time out after 30 seconds, and after 10 seconds when connecting, by default.

## 1.0.1 - 2026-11-12

### Changed
- Changed the minimum `aws/aws-sdk-php` requirement to 3.342.6.

### Fixed
- Fixed a `Class "Aws\Handler\GuzzleV6\GuzzleHandler" not found` error when Composer's autoloader was built with `--classmap-authoritative` (thanks, @eirabben).

## 1.0.0 - 2026-03-02

### Added
- Initial release, refactored from Imager X core.
