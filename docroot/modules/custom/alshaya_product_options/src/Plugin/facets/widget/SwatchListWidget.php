<?php

namespace Drupal\alshaya_product_options\Plugin\facets\widget;

use Drupal\alshaya_product_options\SwatchesHelper;
use Drupal\facets\FacetInterface;
use Drupal\facets\Plugin\facets\widget\LinksWidget;
use Drupal\taxonomy\TermInterface;

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

    $langcode = \Drupal::languageManager()->getCurrentLanguage()->getId();

    /** @var \Drupal\acq_sku\SKUFieldsManager $skuFieldsManager */
    $skuFieldsManager = \Drupal::service('acq_sku.fields_manager');
    $fields = $skuFieldsManager->getFieldAdditions();
    $field_code = str_replace('attr_', '', $facet->getFieldIdentifier());
    $attribute_code = $fields[$field_code]['source'] ?? $field_code;

    /** @var \Drupal\acq_sku\ProductOptionsManager $optionsManager */
    $optionsManager = \Drupal::service('acq_sku.product_options_manager');

    /** @var \Drupal\file\FileStorage $fileStorage */
    $fileStorage = \Drupal::entityTypeManager()->getStorage('file');

    foreach ($items as $index => $item) {
      if (isset($item['#title'], $item['#title']['#value'])) {
        $term = $optionsManager->loadProductOptionByOptionId($attribute_code, $item['#title']['#value'], $langcode);

        if ($term instanceof TermInterface) {
          $swatchType = $term->get('field_attribute_swatch_type')->getString();

          // 0 is valid type, chech specifically for empty/null values.
          if ($swatchType === NULL or $swatchType === '') {
            continue;
          }

          switch ($swatchType) {
            case SwatchesHelper::SWATCH_TYPE_TEXTUAL:
              $item['#title']['#swatch_text'] = $term->get('field_attribute_swatch_text')->getString();;
              break;

            case SwatchesHelper::SWATCH_TYPE_VISUAL_COLOR:
              $item['#title']['#swatch_color'] = $term->get('field_attribute_swatch_color')->getString();;
              break;

            case SwatchesHelper::SWATCH_TYPE_VISUAL_IMAGE:
              if ($term->get('field_attribute_swatch_image')->first()) {
                $file_value = $term->get('field_attribute_swatch_image')->first()->getValue();
                /** @var \Drupal\file\Entity\File $file */
                $file = $fileStorage->load($file_value['target_id']);
                $item['#title']['#swatch_image'] = file_create_url($file->getFileUri());
              }
              break;

            default:
              continue;
          }

          $item['#title']['#theme'] = 'facets_result_item_with_swatch';
          $item['#title']['#value'] = $term->getName();

          $items[$index] = $item;
        }
      }
    }

    $build['#items'] = $items;

    return $build;
  }

}
