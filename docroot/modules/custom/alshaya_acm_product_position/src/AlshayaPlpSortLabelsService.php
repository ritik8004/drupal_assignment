<?php

namespace Drupal\alshaya_acm_product_position;

use Drupal\alshaya_custom\AlshayaDynamicConfigValueBase;

/**
 * Class AlshayaPlpSortLabelsService.
 */
class AlshayaPlpSortLabelsService extends AlshayaPlpSortOptionsBase {

  /**
   * Get the available labels for plp sorting options.
   *
   * @return array
   *   Return array of plp sort labels.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  public function getSortOptionsLabels(): array {
    static $labels;

    if (!empty($labels)) {
      return $labels;
    }

    // Try to load from term in Route.
    if (($term = $this->getTermForRoute())) {
      $labels = $this->getPlpSortConfigForTerm($term, 'labels');
    }

    // Load defaults from config.
    if (empty($labels)) {
      $labels = AlshayaDynamicConfigValueBase::schemaArrayToKeyValue(
        (array) $this->configSortOptions->get('sort_options_labels')
      );
    }

    $labels = array_filter($labels);

    return $labels;
  }

  /**
   * Get the all available sort options' labels.
   */
  public function rawSortOptionsLabels() {
    return AlshayaDynamicConfigValueBase::schemaArrayToKeyValue(
      (array) $this->configSortOptions->get('sort_options_labels')
    );
  }

}
