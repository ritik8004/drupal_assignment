<?php
// @codingStandardsIgnoreFile

if ($env === 'local' && isset($settings['alshaya_api.settings']['magento_host']) && $settings['commerce_backend']['version'] == 2) {
  $settings['alshaya_api.settings']['magento_host'] = '/proxy/?url=' . $settings['alshaya_api.settings']['magento_host'];
}
