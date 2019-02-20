<?php

namespace Drupal\alshaya_sitemap\Plugin\simple_sitemap\UrlGenerator;

use Drupal\simple_sitemap\Plugin\simple_sitemap\UrlGenerator\EntityUrlGenerator;

/**
 * Class AlshayaTaxonomyTermUrlGenerator.
 *
 * @UrlGenerator(
 *   id = "alshaya_entity_taxonomy_term",
 *   title = @Translation("Taxonomy term URL generator"),
 *   description = @Translation("Generates Taxononmy Term URLs by overriding the 'entity' URL generator."),
 *   weight = 5,
 *   settings = {
 *     "instantiate_for_each_data_set" = true,
 *     "overrides_entity_type" = "taxonomy_term",
 *   },
 * )
 */
class AlshayaTaxonomyTermUrlGenerator extends EntityUrlGenerator {

  /**
   * Add custom status field for acq_product_category to filter out links.
   *
   * @inheritdoc
   */
  public function getDataSets() {
    $data_sets = [];
    $sitemap_entity_types = $this->entityHelper->getSupportedEntityTypes();

    $bundle_settings = $this->generator->getBundleSettings();
    if (!empty($bundle_settings['taxonomy_term'])) {
      foreach ($bundle_settings['taxonomy_term'] as $bundle_name => $settings) {
        if ($settings['index']) {
          $keys = $sitemap_entity_types['taxonomy_term']->getKeys();
          if ($bundle_name == 'acq_product_category') {
            $keys += ['status' => 'field_commerce_status'];
          }

          $data_sets[] = [
            'bundle_settings' => $bundle_settings,
            'bundle_name' => $bundle_name,
            'entity_type_name' => 'taxonomy_term',
            'keys' => $keys,
          ];
        }
      }
    }

    return $data_sets;
  }

}
