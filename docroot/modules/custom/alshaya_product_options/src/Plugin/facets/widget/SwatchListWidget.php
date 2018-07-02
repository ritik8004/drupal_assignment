<?php

namespace Drupal\alshaya_product_options\Plugin\facets\widget;

use Drupal\alshaya_product_options\SwatchesHelper;
use Drupal\facets\FacetInterface;
use Drupal\facets\Plugin\facets\widget\LinksWidget;

/**
 * The Swatch list widget.
 *
 * @FacetsWidget(
 *   id = "swatch_list",
 *   label = @Translation("List of facets with swatches"),
 *   description = @Translation("Widget that shows a swatch image/color before title."),
 * )
 */
class SwatchListWidget extends LinksWidget {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'granularity' => 20,
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function build(FacetInterface $facet) {
    $build = parent::build($facet);

    $items = $build['#items'];

    /** @var \Drupal\acq_sku\SKUFieldsManager $skuFieldsManager */
    $skuFieldsManager = \Drupal::service('acq_sku.fields_manager');
    $fields = $skuFieldsManager->getFieldAdditions();
    $field_code = str_replace('attr_', '', $facet->getFieldIdentifier());
    $attribute_code = $fields[$field_code]['source'] ?? $field_code;

    /** @var \Drupal\alshaya_product_options\SwatchesHelper $swatchesManager */
    $swatches = \Drupal::service('alshaya_product_options.swatches');

    foreach ($items as $index => $item) {
      if (isset($item['#title'], $item['#title']['#value'])) {
        $swatch = $swatches->getSwatch($attribute_code, $item['#title']['#value']);

        if ($swatch) {
          switch ($swatch['type']) {
            case SwatchesHelper::SWATCH_TYPE_TEXTUAL:
              $item['#title']['#swatch_text'] = $swatch['swatch'];
              break;

            case SwatchesHelper::SWATCH_TYPE_VISUAL_COLOR:
              $item['#title']['#swatch_color'] = $swatch['swatch'];
              break;

            case SwatchesHelper::SWATCH_TYPE_VISUAL_IMAGE:
              $item['#title']['#swatch_image'] = $swatch['swatch'];
              break;

            default:
              continue;
          }

          $item['#title']['#theme'] = 'facets_result_item_with_swatch';
          $item['#title']['#value'] = $swatch['name'];

          $items[$index] = $item;
        }
      }
    }

    $build['#items'] = $items;

    return $build;
  }

}
