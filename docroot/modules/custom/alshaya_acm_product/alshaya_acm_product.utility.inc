<?php

/**
 * @file
 * Utility file to expose functions to get data related to product.
 */

use Drupal\acq_commerce\SKUInterface;
use Drupal\acq_sku\Entity\SKU;
use Drupal\acq_sku\Plugin\AcquiaCommerce\SKUType\Configurable;
use Drupal\acq_sku\ProductOptionsManager;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\image\Entity\ImageStyle;
use Drupal\node\Entity\Node;

/**
 * Utility function to return media files for a product.
 *
 * @param int $productId
 *   Product node id.
 * @param bool $first_image_only
 *   Flag to indicate if we want only the first image and not the whole array.
 *
 * @return array
 *   Array of media files.
 */
function alshaya_acm_product_get_product_media($productId, $first_image_only = FALSE) {
  // Load product from id.
  $product = Node::load($productId);

  if ($product instanceof Node) {
    // Get SKU from product.
    $sku = $product->get('field_skus')->getString();
    $sku_entity = SKU::loadFromSku($sku);

    // Get the processed media data for the SKU.
    if ($sku_entity instanceof SKU) {
      /** @var \Drupal\alshaya_acm_product\SkuImagesManager $skuImagesManager */
      $skuImagesManager = \Drupal::service('alshaya_acm_product.sku_images_manager');

      return $first_image_only
        ? $skuImagesManager->getFirstImage($sku_entity)
        : $skuImagesManager->getAllMediaItems($sku_entity);
    }
  }

  return NULL;
}

/**
 * Get Image tag from media item array.
 *
 * @param array $media
 *   Media array containing image details.
 * @param string $image_style
 *   Image style to apply to the image.
 * @param string $rel_image_style
 *   For some sliders we may want full/big image url in rel.
 *
 * @return array
 *   Image build array.
 */
function alshaya_acm_product_get_sku_image(array $media, $image_style = '', $rel_image_style = '') {
  $image = [
    '#theme' => 'image_style',
    '#style_name' => $image_style,
    '#uri' => $media['file']->getFileUri(),
    '#title' => $media['label'],
    '#alt' => $media['label'],
  ];

  if ($rel_image_style) {
    $image['#attributes']['rel'] = ImageStyle::load($rel_image_style)->buildUrl($image['#uri']);
  }

  return $image;
}

/**
 * Utility function to return configurable values for a SKU.
 *
 * @param mixed $sku
 *   SKU text or full entity object.
 *
 * @return array
 *   Array of configurable field values.
 */
function alshaya_acm_product_get_sku_configurable_values($sku) {
  $configurableFieldValues = [];
  $sku = $sku instanceof SKU ? $sku : SKU::loadFromSku($sku);

  if ($sku instanceof SKUInterface) {
    $skuManager = \Drupal::service('alshaya_acm_product.skumanager');
    // Get the parent SKU so that its configurable attributes can be fetched.
    if ($sku->bundle() !== 'configurable') {
      $parent_sku = $skuManager->getParentSkuBySku($sku);

      if ($parent_sku instanceof SKUInterface && $parent_sku->bundle() !== 'configurable') {
        return $configurableFieldValues;
      }
      elseif (!$parent_sku) {
        // In case for some reason no parent is available then error is not
        // thrown.
        return $configurableFieldValues;
      }
    }
    else {
      $parent_sku = $sku;
    }
    $configurable_codes = Configurable::getSortedConfigurableAttributes($parent_sku);

    // Get the actual SKU attributes like size, color etc.
    $attributes = $sku->get('attributes')->getValue();
    $attributes = array_column($attributes, 'value', 'key');

    $remove_not_required_option = $skuManager->isNotRequiredOptionsToBeRemoved();
    foreach ($configurable_codes as $code) {
      $fieldKey = 'attr_' . $code['code'];

      if ($sku->hasField($fieldKey)) {
        $value = $sku->get($fieldKey)->getString();

        if ($remove_not_required_option && $skuManager->isAttributeOptionToExclude($value)) {
          continue;
        }

        $configurableFieldValues[$fieldKey] = [
          'label' => $skuManager->getLabelFromParentSku($sku, $code['code']) ?? (string) $sku->get($fieldKey)
            ->getFieldDefinition()
            ->getLabel(),
          'value' => $sku->get($fieldKey)->getString(),
        ];
      }
    }
  }
  return $configurableFieldValues;
}

/**
 * Utility function to get parent SKU for a configurable child sku.
 *
 * @param mixed $sku
 *   SKU text or full entity object.
 * @param string $langcode
 *   Language code.
 *
 * @return \Drupal\acq_sku\Entity\SKU|null
 *   Loaded SKU entity.
 */
function alshaya_acm_product_get_parent_sku_by_sku($sku, $langcode = '') {
  /** @var \Drupal\alshaya_acm_product\SkuManager $skuManager */
  $skuManager = \Drupal::service('alshaya_acm_product.skumanager');
  return $skuManager->getParentSkuBySku($sku, $langcode);
}

/**
 * Utility function to get parent node of the sku.
 *
 * @param mixed $sku
 *   SKU name or full sku object.
 *
 * @return object
 *   Loaded node object.
 */
function alshaya_acm_product_get_display_node($sku) {
  /** @var \Drupal\alshaya_acm_product\SkuManager $skuManager */
  $skuManager = \Drupal::service('alshaya_acm_product.skumanager');
  return $skuManager->getDisplayNode($sku);
}

/**
 * Helper function to get the product display image.
 *
 * @param mixed $sku
 *   SKU text or full entity object.
 * @param string $image_style
 *   Image style to apply to the image.
 * @param string $context
 *   (optional) Context for image.
 *
 * @return array
 *   Return string of uri or Null if not found.
 */
function alshaya_acm_get_product_display_image($sku, $image_style = '', $context = '') {
  $sku = $sku instanceof SKUInterface ? $sku : SKU::loadFromSku($sku);

  if (!($sku instanceof SKUInterface)) {
    return [];
  }

  /** @var \Drupal\alshaya_acm_product\SkuImagesHelper $skuImagesHelper */
  $skuImagesHelper = \Drupal::service('alshaya_acm_product.sku_images_helper');

  /** @var \Drupal\alshaya_acm_product\SkuImagesManager $skuImagesManager */
  $skuImagesManager = \Drupal::service('alshaya_acm_product.sku_images_manager');

  // Load the first image.
  $media_image = $skuImagesManager->getFirstImage($sku, $context);
  $image = [];

  // If we have image for the product.
  if (!empty($media_image)) {
    $image = $skuImagesHelper->getSkuImage($media_image, $image_style);
  }

  // If still image is not available, set default one.
  if (empty($image)) {
    $default_image_url = $skuImagesManager->getProductDefaultImageUrl();

    if ($default_image_url) {
      $image = [
        '#theme' => 'image',
        '#attributes' => [
          'src' => $default_image_url,
          'title' => $sku->label(),
          'alt' => $sku->label(),
          'class' => [
            'product-default-image',
          ],
          '#skip_lazy_loading' => TRUE,
        ],
      ];
    }
  }

  return $image;
}

/**
 * Utility function to check if product is available for home delivery.
 *
 * @param mixed $sku
 *   SKU string or full object.
 *
 * @return bool
 *   Flag if the product is available for click and collect or not.
 */
function alshaya_acm_product_available_home_delivery($sku) {
  // Check MMCSP-903 comments for more details.
  return TRUE;
}

/**
 * Utility function to check if product is available for click and collect.
 *
 * @param mixed $sku
 *   SKU string or full object.
 *
 * @return bool
 *   Flag if the product is available for click and collect or not.
 */
function alshaya_acm_product_available_click_collect($sku) {
  $feature_status = \Drupal::config('alshaya_click_collect.settings')->get('feature_status');
  if ($feature_status === 'disabled') {
    return FALSE;
  }

  $sku_entity = $sku instanceof SKU ? $sku : SKU::loadFromSku($sku);

  // We always expect an entity when this function is called.
  // There may be cases were a product gets deleted so we add a check here
  // to avoid fatal errors.
  if (!($sku_entity instanceof SKU)) {
    return FALSE;
  }

  $sts = (int) $sku_entity->get('attr_ship_to_store')->getString();
  $rnc = (int) $sku_entity->get('attr_reserve_and_collect')->getString();

  // Product could be either available for ship to store or for reserve and
  // collect. In both cases click and collect option will be considered as
  // available.
  // Magento provides for 2 for disabled and 1 for enabled.
  return ($sts == 1 || $rnc == 1);
}

/**
 * Utility function to get brand logo image.
 *
 * @param mixed $sku
 *   SKU string or full object.
 *
 * @return array
 *   Image build array.
 */
function alshaya_acm_product_get_brand_logo($sku) {
  $sku_entity = $sku instanceof SKU ? $sku : SKU::loadFromSku($sku);
  $brand_logo = $sku_entity->get('attr_brand_logo')->getString();

  $cid = 'brand_logo:' . $brand_logo;
  if ($cache = \Drupal::cache('data')->get($cid)) {
    $image = $cache->data;
  }
  else {
    $term = \Drupal::service('acq_sku.product_options_manager')->loadProductOptionByOptionId(
      'brand_logo',
      $brand_logo,
      \Drupal::languageManager()->getCurrentLanguage()->getId()
    );

    if ($term && !empty($term->get('field_attribute_swatch_org_image')->getValue())) {
      $image = [
        '#theme' => 'image',
        '#uri' => $term->get('field_attribute_swatch_org_image')->entity->getFileUri(),
        '#title' => $term->label(),
        '#alt' => $term->label(),
      ];
      $expire = CacheBackendInterface::CACHE_PERMANENT;
    }
    else {
      \Drupal::logger('alshaya_acm_product')
        ->error(
          'Brand logo of title "@title" for SKU id @sku_id is missing.',
          [
            '@title' => $term ? $term->label() : $brand_logo,
            '@sku_id' => $sku->getSku(),
          ]
        );

      $image = [];

      // We cache this for 30 minutes to allow admins to correct the situation.
      $expire = \Drupal::time()->getRequestTime() + (60 * 30);
    }

    \Drupal::cache('data')->set($cid, $image, $expire);
  }

  return $image;
}

/**
 * Utility function to change cardinality for SKU field.
 *
 * @param string $field_name
 *   Field name prefixed with 'attr'.
 * @param string $attribute_name
 *   Attribute name as specified while creating the field via sku_base_fields.
 */
function alshaya_acm_product_update_field_cardinality_from_unlimited_to_unique($field_name, $attribute_name) {
  $entity_type_manager = \Drupal::entityTypeManager();

  // Get data table name for entity.
  $base_table = $entity_type_manager->getStorage('acq_sku')->getDataTable();
  $database = \Drupal::database();

  // Get field definitions on the entity.
  $field_definitions = \Drupal::service('entity_field.manager')->getFieldDefinitions('acq_sku', 'configurable');
  $storage = $entity_type_manager->getStorage('acq_sku');

  // Get table mapping for the field definitions.
  $table_mapping = $storage->getTableMapping();

  // Field table from which we need to migrate data.
  $field_table_name = $table_mapping->getFieldTableName($field_name);
  $field_value_column_name = $table_mapping->getFieldColumnName($field_definitions[$field_name], 'value');

  // Pull the data stored in the field, keyed by the id.
  $field_table_values = $database->select($field_table_name)
    ->fields($field_table_name, [
      'entity_id', 'langcode', $field_value_column_name,
    ])
    ->condition('delta', 0)
    ->execute()
    ->fetchAll();

  // Truncate the field table data to be able to update the entity definition
  // for the field. This is necessary else Entity update manager would complain
  // about existing data in the field.
  $database->delete($field_table_name)->execute();

  // Update the cardinality of article warning field to single-valued field.
  $sku_entity_config = \Drupal::configFactory()->getEditable('acq_sku.base_field_additions');
  $sku_entity_data = $sku_entity_config->getRawData();
  $sku_entity_data[$attribute_name]['cardinality'] = 1;
  $sku_entity_config->setData($sku_entity_data)->save();
  \Drupal::service('entity.definition_update_manager')->applyUpdates();

  // Restore data for warning field.
  foreach ($field_table_values as $value) {
    $database->update($base_table)
      ->fields([$field_name => $value->{$field_value_column_name}])
      ->condition('id', $value->entity_id)
      ->condition('langcode', $value->langcode)
      ->execute();
  }
}

/**
 * Utility function to return Youtube and Vimeo player URLs from web URls.
 *
 * @param string $url
 *   The URL received from MDC.
 * @param string $provider
 *   The video provider, expecting youtube/vimeo.
 *
 * @return string
 *   The player URL for embedding video in iframe.
 */
function alshaya_acm_product_generate_video_embed_url($url, $provider) {
  $embed_url = '';
  if (strpos($url, 'youtube.com/') == FALSE && strpos($url, 'vimeo.com/') == FALSE) {
    return '';
  }

  $video_id = $provider == 'youtube' ? explode('v=', $url)[1] : explode('vimeo.com/', $url)[1];

  if ($video_id) {
    $embed_url = ($provider == 'youtube') ? 'https://www.youtube.com/embed/' : 'https://player.vimeo.com/video/';
    $embed_url .= explode('&', $video_id)[0];
    $embed_url .= ($provider == 'youtube') ? '?enablejsapi=1' : '';
  }
  return $embed_url;
}

/**
 * Delete and resync taxonomy terms for given attribute code.
 *
 * @param string $attribute_code
 *   The attribute code to delete and resync.
 *
 * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
 * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
 * @throws \Drupal\Core\Entity\EntityStorageException
 */
function alshaya_acm_product_attribute_delete_and_resync(string $attribute_code) {
  // Get the terms for given attribute code.
  $tids = \Drupal::entityQuery('taxonomy_term')
    ->condition('vid', ProductOptionsManager::PRODUCT_OPTIONS_VOCABULARY)
    ->condition('field_sku_attribute_code', $attribute_code)
    ->execute();

  // Delete terms.
  $term_storage = \Drupal::entityTypeManager()->getStorage('taxonomy_term');
  $entities = $term_storage->loadMultiple($tids);
  $term_storage->delete($entities);

  // Sync product options for given attribute code.
  $languages = \Drupal::languageManager()->getLanguages();
  foreach ($languages as $langcode => $language) {
    \Drupal::service('alshaya_product_options.helper')->syncProductOption($attribute_code, $langcode);
  }
}
