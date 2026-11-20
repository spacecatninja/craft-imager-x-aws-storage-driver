<?php
/**
 * AWS external storage driver for Imager X
 *
 * @link      https://www.spacecat.ninja/
 * @copyright Copyright (c) 2026 André Elvan
 */

namespace SpaceCatNinja\ImagerXAWSStorageDriver;

use CraftCms\Cms\Plugin\Plugin;
use SpaceCatNinja\ImagerX\ExternalStorage\ExternalStorages;
use SpaceCatNinja\ImagerXAWSStorageDriver\ExternalStorage\AWSStorage;

/**
 * @author    SpaceCatNinja
 * @package   ImagerXAWSStorageDriver
 * @since     1.0.0
 *
 */
class ImagerXAWSStorageDriver extends Plugin
{
    public function boot(ExternalStorages $storages): void
    {
        // Register external storage in Imager X
        $storages->register('aws', AWSStorage::class);
    }
}
