# Imager Storage Driver for Amazon Web Services (S3)

## 1.0.1 - 2026-11-12

### Changed
- Changed the minimum `aws/aws-sdk-php` requirement to 3.342.6.

### Fixed
- Fixed a `Class "Aws\Handler\GuzzleV6\GuzzleHandler" not found` error when Composer's autoloader was built with `--classmap-authoritative` (thanks, @eirabben).

## 1.0.0 - 2026-03-02

### Added
- Initial release, refactored from Imager X core.
