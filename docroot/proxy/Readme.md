# SPC Proxy
Used on local development to proxy calls from the browser directly to Magento.
This is necessary to avoid CORS errors when trying to access urls that are not hosted on the
same domain as the application.

## Configuration
Add the following settings overrides on your project. i.e.

File: /factory-hooks/post-settings-php/zzz_proxy.php
```
<?php
  // Use proxy.
  if (isset($settings['alshaya_api.settings']['magento_host'])) {
    $settings['alshaya_api.settings']['alshaya_proxy'] = TRUE;
  }
```
