# SPC Proxy
Used on local development to proxy calls from the browser directly to Magento.
This is necessary to avoid CORS errors when trying to access urls that are not hosted on the
same domain as the application.

## Configuration
The configuration is automatically done when you run `blt local:sync` or `blt local:reset-local-settings` command.
The automated task will create the overrides:

File: /factory-hooks/post-settings-php/zzz_proxy.php
```
<?php
// Use proxy.
if (isset($settings['alshaya_api.settings']['magento_host'])) {
  $settings['alshaya_api.settings']['magento_host'] = '/spc/proxy?url=' . $settings['alshaya_api.settings']['magento_host'];
}
```

By default, proxy will only work on local environments. If you want to whitelist other domains, you can override it:
```
<php
$settings['spc_proxy_host_patterns'] = array(
  '^local.*\.com',
  '^example.*\.com',
);
```
