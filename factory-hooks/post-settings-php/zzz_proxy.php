<?php
// @codingStandardsIgnoreFile

if ($env === 'local' && isset($settings['alshaya_api.settings']['magento_host'])) {
  $settings['alshaya_api.settings']['magento_host'] = '/proxy/?url=' . $settings['alshaya_api.settings']['magento_host'];
}
