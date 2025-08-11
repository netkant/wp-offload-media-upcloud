# WP Offload Media - UpCloud

Tweaks "[WP Offload Media](https://deliciousbrains.com/wp-offload-media/)" to use UpCloud Object Storage, which works seamlessly with the Amazon S3 API.

## Managed Settings

The plugin can setup the recommended settings for you, what we call "managed settings".

| Constant | Default | Required | Example |
| -------- | ------- | ------- | ------- |
| AS3CF_UPCLOUD_ENDPOINT | *null* | yes | https://ab1c2.upcloudobjects.com |
| AS3CF_UPCLOUD_REGION | *null* | yes | europe-2 |
| AS3CF_UPCLOUD_ACCESS_ID | *null* | yes | AEIABA1... |
| AS3CF_UPCLOUD_SECRET_KEY | *null* | yes | uyC5Bx3... |
| AS3CF_UPCLOUD_BUCKET | sanitize_title($host) | no | example-com |
| AS3CF_UPCLOUD_DOMAIN | $host | no | example.com |
| AS3CF_UPCLOUD_DEBUG | false | no | true |

All settings can be set using `define($constant_name)` in *wp-config.php*  (see CONSTANTS.txt) or using `add_filter($hook_name, $callback)` where `$hook_name` is the contant name in lower case (e.g. `as3cf_upcloud_bucket`). An additional `as3cf_upcloud_settings` hook is provided where all settings can be modified before `AS3CF_SETTINGS` is defined.

Example, set endpoint via filter:

```php
add_filter('as3cf_upcloud_endpoint', function($endpoint) {
    return 'https://ab1c2.upcloudobjects.com';
});
```

Example, set region and keys via filter:

```php
add_filter('as3cf_upcloud_settings', function($settings) {
    return array_merge($settings, [
        'region'            => 'europe-2',
        'access-key-id'     => 'AEIABA1...',
        'secret-access-key' => 'uyC5Bx3...',
    ]);
});
```

## User-controlled Settings

If you want to control the settings yourself, feel free to define `AS3CF_SETTINGS` in *wp-config.php* - but **remember**, the `AS3CF_UPCLOUD_ENDPOINT` is still required for the plugin to work with UpCloud Object Storage.

| Constant | Default | Required | Example |
| -------- | ------- | ------- | ------- |
| AS3CF_UPCLOUD_ENDPOINT | *null* | yes | https://ab1c2.upcloudobjects.com |
| AS3CF_SETTINGS | *null* | yes | See: [WP Offload Media Docs](https://deliciousbrains.com/wp-offload-media/doc/settings-constants/) |

