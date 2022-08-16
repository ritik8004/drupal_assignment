<?php

namespace Drupal\alshaya_seo\Decorator;

use Drupal\Component\Utility\Unicode;
use Drupal\pathauto\AliasUniquifier;

/**
 * Class Alshaya Seo Alias Uniquifier.
 *
 * @package Drupal\alshaya_seo
 */
class AlshayaSeoAliasUniquifier extends AliasUniquifier {

  /**
   * {@inheritdoc}
   */
  public function uniquify(&$alias, $source, $langcode) {
    if (!str_contains($alias, '.html')) {
      return parent::uniquify($alias, $source, $langcode);
    }

    if (!$this->isReserved($alias, $source, $langcode)) {
      return;
    }

    $config = $this->configFactory->get('pathauto.settings');

    // If the alias already exists, generate a new, hopefully unique, variant.
    $maxlength = min($config->get('max_length'), $this->aliasStorageHelper->getAliasSchemaMaxlength());
    $separator = $config->get('separator');

    // For aliases with .html, add the unique suffix before .html suffix.
    $original_alias = str_replace('.html', '', $alias);

    $i = 0;
    do {
      // Append an incrementing numeric suffix until we find a unique alias.
      $unique_suffix = $separator . $i . '.html';
      $alias = Unicode::truncate($original_alias, $maxlength - mb_strlen($unique_suffix), TRUE) . $unique_suffix;
      $i++;
    } while ($this->isReserved($alias, $source, $langcode));
  }

}
