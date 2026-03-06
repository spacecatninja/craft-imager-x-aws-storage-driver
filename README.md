# Imager X Storage Driver for AWS (S3)

External storage driver for [Imager X](https://plugins.craftcms.com/imager-x) that uploads transformed images to Amazon S3. Supports CloudFront cache invalidation and credential-less authentication for ECS/EC2 environments.

## Requirements

- Craft CMS 5.0.0 or later
- Imager X 6.0.0 or later (Pro edition)

## Installation

```bash
composer require spacecatninja/imager-x-aws-storage-driver
php craft plugin/install imager-x-aws-storage-driver
```

## Configuration

Add an `aws` key to `storageConfig` in your `config/imager-x.php`:

```php
'storages' => ['aws'],

'storageConfig' => [
    'aws' => [
        'accessKey' => '',
        'secretAccessKey' => '',
        'region' => 'eu-west-1',
        'bucket' => 'my-bucket',
        'folder' => 'transforms',
        'storageType' => 'standard',
        'requestHeaders' => [],
        'disableACL' => false,
        'public' => true,
        'cloudfrontInvalidateEnabled' => false,
        'cloudfrontDistributionId' => '',
    ],
],
```

Point `imagerUrl` at your bucket (or CloudFront distribution):

```php
'imagerUrl' => 'https://my-bucket.s3.eu-west-1.amazonaws.com/transforms/',
```

Always flush your Imager transforms cache when adding or removing external storages, as existing cached transforms will not be re-uploaded.

## Configuration options

| Option | Default | Description |
|---|---|---|
| `accessKey` | `''` | AWS access key ID. Supports environment variables (e.g. `$AWS_ACCESS_KEY`). |
| `secretAccessKey` | `''` | AWS secret access key. Supports environment variables. |
| `region` | `''` | AWS region (e.g. `eu-west-1`). |
| `bucket` | `''` | S3 bucket name. Supports environment variables. |
| `folder` | `''` | Folder prefix within the bucket. |
| `storageType` | `'standard'` | S3 storage class. Accepts `standard`, `rrs` (Reduced Redundancy), or `glacier`. |
| `requestHeaders` | `[]` | Additional headers merged into the S3 `putObject` request (e.g. `Content-Encoding`). |
| `disableACL` | `false` | Set to `true` to skip setting an ACL on upload (required for buckets with ACLs disabled). |
| `public` | `true` | When ACL is enabled, sets objects to `public-read`. Set to `false` for `private`. |
| `cloudfrontInvalidateEnabled` | `false` | Send a CloudFront invalidation request after each upload. |
| `cloudfrontDistributionId` | `''` | CloudFront distribution ID to invalidate. |
| `useCredentialLessAuth` | `false` | Skip access key credentials and rely on IAM roles instead (web identity, ECS, or EC2 instance profile). |

## Credential-less authentication

When running on AWS infrastructure (ECS, EC2, or with web identity federation), you can omit `accessKey` and `secretAccessKey` and let the SDK resolve credentials from the environment automatically:

```php
'aws' => [
    'useCredentialLessAuth' => true,
    'region' => 'eu-west-1',
    'bucket' => 'my-bucket',
    'folder' => 'transforms',
],
```

The driver checks for `AWS_WEB_IDENTITY_TOKEN_FILE`/`AWS_ROLE_ARN` (web identity), `AWS_CONTAINER_CREDENTIALS_RELATIVE_URI` (ECS), and falls back to the EC2 instance metadata service.

## Price, license and support

The plugin is released under the MIT license. It requires Imager X Pro, which is a commercial plugin [available in the Craft plugin store](https://plugins.craftcms.com/imager-x).
