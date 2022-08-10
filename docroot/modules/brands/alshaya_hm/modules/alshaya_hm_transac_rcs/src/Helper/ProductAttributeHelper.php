<?php

namespace Drupal\alshaya_hm_transac_rcs\Helper;

use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Product Attributes Helper for HM.
 *
 * @package Drupal\alshaya_hm_transac_rcs\Helper
 */
class ProductAttributeHelper {

  use StringTranslationTrait;

  /**
   * Get list of additional product attributes.
   *
   * @return array
   *   Returns list of product attributes.
   */
  public function getAttributes() {
    return [
      'product_designer_collection' => $this->t('DESIGNER COLLECTION'),
      'concept' => $this->t('CONCEPT'),
      'product_collection' => $this->t('COLLECTION'),
      'product_environment' => $this->t('ENVIRONMENT'),
      'product_quality' => $this->t('QUALITY'),
      'product_feature' => $this->t('FEATURE'),
      'function' => $this->t('FUNCTION'),
      'washing_instructions' => $this->t('WASHING INSTRUCTION'),
      'dry_cleaning_instructions' => $this->t('DRY CLEAN INSTRUCTION'),
      'style' => $this->t('STYLE'),
      'clothing_style' => $this->t('CLOTHING STYLE'),
      'collar_style' => $this->t('COLLAR STYLE'),
      'neckline_style' => $this->t('NECKLINE STYLE'),
      'accessories_style' => $this->t('ACCESSORIES STYLE'),
      'footwear_style' => $this->t('FOOTWEAR STYLE'),
      'fit' => $this->t('FIT'),
      'descriptive_length' => $this->t('DESCRIPTIVE LENGTH'),
      'garment_length' => $this->t('GARMENT LENGTH'),
      'sleeve_length' => $this->t('SLEEVE LENGTH'),
      'waist_rise' => $this->t('WAIST RISE'),
      'heel_height' => $this->t('HEEL HEIGHT'),
      'measurements_in_cm' => $this->t('MEASURMENTS IN CM'),
      'color_name' => $this->t('COLOR NAME'),
      'fragrance_name' => $this->t('FRAGRANCE NAME'),
      'article_fragrance_description' => $this->t('FRAGRANCE DESCRIPTION'),
      'article_pattern' => $this->t('PATTERN'),
      'article_visual_description' => $this->t('VISUAL DESCRIPTION'),
      'textual_print' => $this->t('TEXTUAL PRINT'),
      'article_license_company' => $this->t('LICENSE COMPANY'),
      'article_license_item' => $this->t('LICENSE ITEM'),
    ];
  }

  /**
   * Get attributes variables for product attributes graphql query.
   *
   * @return array
   *   Returns array of attributes graphql variable.
   */
  public function getAttributesVariable() {
    $attributes = $this->getAttributes();
    $attributes_variables = array_map(fn($attribute) => [
      'attribute_code' => $attribute,
      'entity_type' => 4,
    ], array_keys($attributes));
    return $attributes_variables;
  }

}
