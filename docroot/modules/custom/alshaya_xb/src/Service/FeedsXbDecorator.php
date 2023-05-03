<?php

namespace Drupal\alshaya_xb\Service;

use Drupal\alshaya_feed\AlshayaFeed;

/**
 * Class feeds Helper XB decorator.
 *
 * @package Drupal\alshaya_xb\Service
 */
class FeedsXbDecorator extends AlshayaFeed {

  /**
   * {@inheritDoc}
   */
  public function process(array $nids, &$context) {
    $context['results']['count'] += count($nids);

    foreach ($nids as $nid) {
      $product = $this->feedSkuInfoHelper->prepareFeedData($nid);
      if (empty($product)) {
        continue;
      }

      foreach ($product as $lang => $items) {
        foreach ($items as $item) {
          $file_content = PHP_EOL;
          if (!isset($context['results']['files'][$lang])) {
            $file_content = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<feed>\n<products>" . PHP_EOL;
            $schema = $this->configFactory->get('system.file')->get('default_scheme');
            $context['results']['files'][$lang] = file_create_url($this->fileSystem->realpath($schema . "://feed_{$lang}_wip.xml"));
          }

          if (!empty($item['attr_fixed_price'])) {
            $attr_price = json_decode($item['attr_fixed_price'], TRUE);
            foreach ($attr_price as $key => $value) {
              $item['fixed_prices'][] = [
                'price' => $value['price'] ?? '',
                'special_price' => $value['special_price'] ?? '',
                'currency_code' => $this->getcurrencycode($key) ?? $key,
                'code' => $key,
              ];
            }
          }

          $file_content .= $this->twig
            ->loadTemplate($context['results']['feed_template'])
            ->render(['product' => $item]) . PHP_EOL;

          if (!file_put_contents($context['results']['files'][$lang], $file_content, FILE_APPEND)) {
            $this->logger->error('could not create feed file: @file', ['@file' => $context['results']['files'][$lang]]);
          }
        }
      }
    }
    $context['message'] = $this->t('Updated feeds for @count out of @total.', [
      '@count' => $context['results']['count'],
      '@total' => $context['results']['total'],
    ]);
  }

  /**
   * Get currency code.
   *
   * @param string $country_code
   *   Country code.
   *
   * @return string|null
   *   Currency code or null if the config doesn't exist.
   */
  private function getcurrencycode($country_code) {
    static $currencyCode;

    if (!empty($currencyCode[$country_code])) {
      return $currencyCode[$country_code];
    }

    $config = $this->configFactory->get('alshaya_xb.settings');
    $domainMappings = $config->get('domain_mapping');
    foreach ($domainMappings as $key => $value) {
      $currencyCode[$value['code']] = $value['currencyCode'];
    }

    if (!empty($currencyCode[$country_code])) {
      return $currencyCode[$country_code];
    }
  }

}
