# Upgrading to Imager X Storage Driver for AWS 2 (Craft 6)

This guide lists what changed in version 2 of the AWS (S3) storage driver for Imager X, and what you need to update in your project and custom code. It applies when upgrading from version 1 (Craft 5 and Imager X 6).

The driver is still enabled and configured in Imager X's `storages` and `storageConfig` settings, with the same options. Imager X 7 reads its config from `config/craft/imager-x.php`, see Imager X's upgrade guide. The class changes only affect custom code that uses the plugin's classes directly.

## Requirements

- Craft CMS 6.0.0 or later
- Imager X 7.0.0-beta.2 or later (Pro edition)
- PHP 8.5 or later
- The plugin doesn't need the Yii 2 adapter (`craftcms/yii2-adapter`).

## Upgrade steps

1. Upgrade your project to Craft 6, and Imager X to version 7.
2. Update the plugin: `composer require spacecatninja/imager-x-aws-storage-driver:^2.0.0`.
3. If you have a `config/guzzle.php` file, move it to `config/craft/guzzle.php` (see below).
4. Review the breaking changes below, and update your code.

## Breaking changes

### HTTP client settings

Requests to AWS use Craft's HTTP client settings, as before. In Craft 6, those are the options in `config/craft/guzzle.php` instead of `config/guzzle.php`, and Craft's `httpProxy` setting.

Requests now time out after 30 seconds, and after 10 seconds when connecting, Craft 6's defaults. Version 1 had no timeouts unless `config/guzzle.php` set them. To change them, set `timeout` or `connect_timeout` in `config/craft/guzzle.php`:

```php
return [
    'timeout' => 60,
];
```

### Namespace and classes

The plugin moved from `spacecatninja\imagerxawsstoragedriver` to the `SpaceCatNinja\ImagerXAWSStorageDriver` namespace, with Laravel-style folders.

| Before | After |
| --- | --- |
| `spacecatninja\imagerxawsstoragedriver\ImagerXAWSStorageDriver` | `SpaceCatNinja\ImagerXAWSStorageDriver\ImagerXAWSStorageDriver` |
| `spacecatninja\imagerxawsstoragedriver\externalstorage\AWSStorage` | `SpaceCatNinja\ImagerXAWSStorageDriver\ExternalStorage\AWSStorage` |

`AWSStorage` implements Imager X 7's `SpaceCatNinja\ImagerX\ExternalStorage\ImagerStorageInterface`. `AWSStorage::buildConfigArray()` returns the same S3 client config as before.

### Registering the driver under another handle

The plugin registers the driver with Imager X's `ExternalStorages` registry when it boots, instead of with Imager X's `EVENT_REGISTER_EXTERNAL_STORAGES` event. If your code also registers the driver, for example under another handle, use the registry.

Before:

```php
use spacecatninja\imagerx\events\RegisterExternalStoragesEvent;
use spacecatninja\imagerx\ImagerX;
use spacecatninja\imagerxawsstoragedriver\externalstorage\AWSStorage;
use yii\base\Event;

Event::on(ImagerX::class, ImagerX::EVENT_REGISTER_EXTERNAL_STORAGES, static function(RegisterExternalStoragesEvent $event) {
    $event->storages['myaws'] = AWSStorage::class;
});
```

After:

```php
use SpaceCatNinja\ImagerX\ExternalStorage\ExternalStorages;
use SpaceCatNinja\ImagerXAWSStorageDriver\ExternalStorage\AWSStorage;

app(ExternalStorages::class)->register('myaws', AWSStorage::class);
```
