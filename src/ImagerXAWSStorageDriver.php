<?php
/**
 * AWS external storage driver for Imager X
 *
 * @link      https://www.spacecat.ninja/
 * @copyright Copyright (c) 2026 André Elvan
 */

namespace SpaceCatNinja\ImagerXAWSStorageDriver;

use craft\base\Plugin;
use SpaceCatNinja\ImagerXAWSStorageDriver\ExternalStorage\AWSStorage;
use yii\base\Event;

/**
 * @author    SpaceCatNinja
 * @package   ImagerXAWSStorageDriver
 * @since     1.0.0
 *
 */
class ImagerXAWSStorageDriver extends Plugin
{
    public function init(): void
    {
        parent::init();
        
        Event::on(\spacecatninja\imagerx\ImagerX::class,
            \spacecatninja\imagerx\ImagerX::EVENT_REGISTER_EXTERNAL_STORAGES,
            static function(\spacecatninja\imagerx\events\RegisterExternalStoragesEvent $event) {
                $event->storages['aws'] = AWSStorage::class;
            }
        );
    }
}
