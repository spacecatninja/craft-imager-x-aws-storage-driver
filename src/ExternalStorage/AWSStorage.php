<?php
/**
 * AWS external storage driver for Imager X
 *
 * @link      https://www.spacecat.ninja/
 * @copyright Copyright (c) 2026 André Elvan
 */

namespace SpaceCatNinja\ImagerXAWSStorageDriver\ExternalStorage;

use Aws\CloudFront\CloudFrontClient;
use Aws\CloudFront\Exception\CloudFrontException;

use Aws\Credentials\CredentialProvider;
use Aws\Credentials\Credentials;
use Aws\Handler\Guzzle\GuzzleHandler;
use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;
use CraftCms\Cms\Support\Env;
use CraftCms\Cms\Support\File;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use SpaceCatNinja\ImagerX\ExternalStorage\ImagerStorageInterface;
use SpaceCatNinja\ImagerX\Services\ImagerService;

class AWSStorage implements ImagerStorageInterface
{
    /**
     * @throws \Exception
     */
    public static function upload(string $file, string $uri, bool $isFinal, array $settings): bool
    {
        $config = ImagerService::getConfig();

        $clientConfig = self::buildConfigArray($settings);

        try {
            $s3 = new S3Client($clientConfig);
        } catch (\InvalidArgumentException $invalidArgumentException) {
            Log::error('Invalid configuration of S3 Client: ' . $invalidArgumentException->getMessage());
            return false;
        }

        if (isset($settings['folder']) && $settings['folder'] !== '') {
            $uri = File::normalizePath($settings['folder'] . '/' . $uri);
        }

        // Always use forward slashes for S3
        $uri = str_replace('\\', '/', $uri);

        // Don't start with forward slashes
        $uri = ltrim($uri, '/');

        $opts = $settings['requestHeaders'] ?? [];
        $cacheDuration = $isFinal ? $config->cacheDurationExternalStorage : $config->cacheDurationNonOptimized;
        $visibility = !isset($settings['public']) || $settings['public'] === true ? 'public-read' : 'private';

        if (!isset($opts['CacheControl'])) {
            $opts['CacheControl'] = 'max-age=' . $cacheDuration . ', must-revalidate';
        }

        if (!isset($settings['disableACL']) || $settings['disableACL'] === false) {
            $opts['ACL'] = $visibility;
        }

        $opts = array_merge($opts, [
            'Bucket' => Env::parse($settings['bucket']),
            'Key' => $uri,
            'Body' => fopen($file, 'rb'),
            'StorageClass' => self::getAWSStorageClass($settings['storageType'] ?? 'standard'),
        ]);

        try {
            $s3->putObject($opts);
        } catch (S3Exception $s3Exception) {
            Log::error('An error occured while uploading to Amazon S3: ' . $s3Exception->getMessage());
            return false;
        }

        // Cloudfront invalidation
        if (isset($settings['cloudfrontInvalidateEnabled'], $settings['cloudfrontDistributionId']) && $settings['cloudfrontInvalidateEnabled'] === true) {
            try {
                $cloudfront = new CloudFrontClient($clientConfig);
            } catch (\InvalidArgumentException $s3Exception) {
                Log::error('Invalid configuration of CloudFront Client: ' . $s3Exception->getMessage());
                return false;
            }

            try {
                $cloudfront->createInvalidation([
                    'DistributionId' => $settings['cloudfrontDistributionId'],
                    'InvalidationBatch' => [
                        'Paths' => [
                            'Quantity' => 1,
                            'Items' => ['/' . $uri],
                        ],
                        'CallerReference' => md5($uri . random_int(111111, 999999)),
                    ],
                ]);
            } catch (CloudFrontException $cloudFrontException) {
                Log::error('An error occured while sending an Cloudfront invalidation request: ' . $cloudFrontException->getMessage());
            }
        }

        return true;
    }

    /**
     * Builds config array for S3 client.
     * Mostly lifted from https://github.com/craftcms/aws-s3/blob/a04ee659490d53da879e302e660ba3807532a926/src/Fs.php#L432
     *
     * @param array $settings
     *
     * @return array
     */
    public static function buildConfigArray(array $settings): array
    {
        $config = [
            'region' => Env::parse($settings['region']),
            'version' => 'latest',
        ];

        // Use Craft's HTTP client settings (config/craft/guzzle.php and httpProxy). Guzzle's http_errors must stay on,
        // since the AWS SDK only turns error responses into exceptions when Guzzle rejects them.
        $request = Http::create();
        $client = new Client(['handler' => $request->buildHandlerStack(), 'http_errors' => true] + $request->getOptions());
        $config['http_handler'] = new GuzzleHandler($client);

        if (isset($settings['useCredentialLessAuth']) && $settings['useCredentialLessAuth'] === true) {
            // Check for predefined access
            if (Env::get('AWS_WEB_IDENTITY_TOKEN_FILE') && Env::get('AWS_ROLE_ARN')) {
                // Check if anything is defined for a web identity provider (see: https://docs.aws.amazon.com/sdk-for-php/v3/developer-guide/guide_credentials_provider.html#assume-role-with-web-identity-provider)
                $provider = CredentialProvider::assumeRoleWithWebIdentityCredentialProvider();
                $provider = CredentialProvider::memoize($provider);
                $config['credentials'] = $provider;
            }
            // Check if running on ECS
            if (Env::get('AWS_CONTAINER_CREDENTIALS_RELATIVE_URI')) {
                // Check if anything is defined for an ecsCredentials provider
                $provider = CredentialProvider::ecsCredentials();
                $provider = CredentialProvider::memoize($provider);
                $config['credentials'] = $provider;
            }

            // If that didn't happen, assume we're running on EC2, and we have an IAM role assigned so no action required.
        } else {
            $credentials = new Credentials(Env::parse($settings['accessKey']), Env::parse($settings['secretAccessKey']));
            $config['credentials'] = $credentials;
        }

        return $config;
    }


    /**
     * @param $storageTypeString
     *
     * @return string
     */
    private static function getAWSStorageClass($storageTypeString): string
    {
        return match (mb_strtolower($storageTypeString)) {
            'rrs' => 'REDUCED_REDUNDANCY',
            'glacier' => 'GLACIER',
            default => 'STANDARD',
        };
    }
}
