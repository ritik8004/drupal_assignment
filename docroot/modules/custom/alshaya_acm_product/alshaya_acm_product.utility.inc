<?php

/**
 * @file
 * Utility file to expose functions to get data related to product.
 */

use Drupal\acq_commerce\SKUInterface;
use Drupal\acq_sku\Entity\SKU;
use Drupal\Component\Utility\Html;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\image\Entity\ImageStyle;
use Drupal\node\Entity\Node;
use Drupal\file\FileInterface;

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
    $sku_entity = $product->get('field_skus')
      ->first()
      ->get('entity')
      ->getValue();

    // Get the processed media data for the SKU.
    if ($sku_entity instanceof SKU) {
      return alshaya_acm_product_get_sku_media($sku_entity, $first_image_only);
    }
  }

  return NULL;
}

/**
 * Utility function to return media files for a SKU.
 *
 * @param mixed $sku
 *   SKU text or full entity object.
 * @param bool $first_image_only
 *   Flag to indicate if we want only the first image and not the whole array.
 *
 * @return array
 *   Array of media files.
 */
function alshaya_acm_product_get_sku_media($sku, $first_image_only = FALSE) {
  static $media = [];
  static $thumbnail_media = [];

  $sku_entity = $sku instanceof SKU ? $sku : SKU::loadFromSku($sku);

  if (!($sku_entity instanceof SKU)) {
    return [];
  }

  $langcode = $sku_entity->language()->getId();

  if ($first_image_only) {
    if (!isset($thumbnail_media[$sku_entity->id()], $thumbnail_media[$sku_entity->id()][$langcode])) {
      $thumbnail_media[$sku_entity->id()][$langcode] = $sku_entity->getThumbnail();
    }

    return $thumbnail_media[$sku_entity->id()][$langcode];
  }

  if (!isset($media[$sku_entity->id()], $media[$sku_entity->id()][$langcode])) {
    $media[$sku_entity->id()][$langcode] = $sku_entity->getMedia();
  }

  return $media[$sku_entity->id()][$langcode];
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

  $sku_entity = $sku instanceof SKU ? $sku : SKU::loadFromSku($sku);

  if ($sku_entity) {
    $fields = \Drupal::config('alshaya_acm_product.sku_base_fields')->get('fields');
    $configurableFields = array_filter($fields, function ($field) {
      return (bool) $field['configurable'];
    });

    foreach ($configurableFields as $key => $field) {
      $fieldKey = 'attr_' . $key;

      if ($sku_entity->get($fieldKey)->getString()) {
        $configurableFieldValues[$fieldKey] = [
          'label' => $sku_entity->get($fieldKey)->getFieldDefinition()->getLabel(),
          'value' => $sku_entity->get($fieldKey)->getString(),
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
 * @return \Drupal\acq_sku\Entity\SKU
 *   Loaded SKU entity.
 */
function alshaya_acm_product_get_parent_sku_by_sku($sku, $langcode = '') {
  /** @var \Drupal\alshaya_acm_product\SkuManager $skuManager */
  $skuManager = \Drupal::service('alshaya_acm_product.skumanager');
  return $skuManager->getParentSkuBySku($sku, $langcode);
}

/**
 * Helper function to get gallery and zoom for particular SKU.
 *
 * @param array $build
 *   Build array to add the gallery and zoom elements to.
 * @param \Drupal\acq_commerce\SKUInterface $sku_entity
 *   SKU Entity for which we want to display the media.
 */
function alshaya_acm_product_get_gallery(array &$build, SKUInterface $sku_entity) {
  \Drupal::moduleHandler()->loadInclude('alshaya_product_zoom', 'inc', 'alshaya_product_zoom.utility');

  // Fetch media.
  $media = alshaya_acm_product_get_sku_media($sku_entity);

  $main_image = [];
  $thumbnails = [];

  // Avoid notices and warnings in local.
  if (empty($media)) {
    // Check once if there is parent SKU available, use media files of parent.
    if ($parent_sku = alshaya_acm_product_get_parent_sku_by_sku($sku_entity)) {
      // Give preference to child products to alter before checking for image in
      // parent. Alter hook to allow all modules to update the element.
      \Drupal::moduleHandler()->alter('acq_sku_pdp_gallery_media', $main_image, $thumbnails, $sku_entity);
      if (!$main_image) {
        return alshaya_acm_product_get_gallery($build, $parent_sku);
      }
    }
  }

  // Fetch settings.
  $settings = alshaya_product_zoom_cloudzoom_default_settings();
  $thumbnail_style = $settings['thumb_style'];
  $zoom_style = $settings['zoom_style'];
  $slide_style = $settings['slide_style'];

  // Create our thumbnails to be rendered for zoom.
  if (!empty($media)) {
    foreach ($media as $media_item) {
      if (isset($media_item['media_type']) && $media_item['media_type'] == 'image') {

        $file_uri = $media_item['file']->getFileUri();

        // Show original full image in the modal inside a draggable container.
        $original_image = $media_item['file']->url();

        $image_small = ImageStyle::load($thumbnail_style)->buildUrl($file_uri);
        $image_zoom = ImageStyle::load($zoom_style)->buildUrl($file_uri);
        $image_medium = ImageStyle::load($slide_style)->buildUrl($file_uri);

        if (empty($main_image)) {
          $main_image = [
            'zoomurl' => $image_zoom,
            'mediumurl' => $image_medium,
            'label' => $media_item['label'],
          ];
        }

        $thumbnails[] = [
          'thumburl' => $image_small,
          'mediumurl' => $image_medium,
          'zoomurl' => $image_zoom,
          'fullurl' => $original_image,
          'label' => $media_item['label'],
          'type' => 'image',
        ];
      }
      elseif (isset($media_item['media_type']) && $media_item['media_type'] == 'external-video') {
        // @TODO:
        // Receiving video_provider as NULL, should be set to youtube or vimeo.
        // Till then using $type as provider flag.
        $type = strpos($media_item['video_url'], 'youtube') ? 'youtube' : 'vimeo';
        $thumbnails[] = [
          'thumburl' => $media_item['file'],
          'url' => alshaya_acm_product_generate_video_embed_url($media_item['video_url'], $type),
          'video_title' => $media_item['video_title'],
          'video_desc' => $media_item['video_description'],
          'type' => $type,
          // @TODO: should this be config?
          'width' => 81,
          // @TODO: should this be config?
          'height' => 81,
        ];
      }
    }
  }

  // Invoke the alter hook to allow all modules to update the element.
  \Drupal::moduleHandler()->alter('acq_sku_pdp_gallery_media', $main_image, $thumbnails, $sku_entity);

  if (empty($main_image)) {
    return [];
  }
  else {
    $pdp_gallery_pager_limit = \Drupal::config('alshaya_acm_product.settings')->get('pdp_gallery_pager_limit');
    $pager_flag = count($thumbnails) > $pdp_gallery_pager_limit ? 'pager-yes' : 'pager-no';

    $build['gallery'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['gallery-wrapper'],
      ],
    ];

    $skuManager = \Drupal::service('alshaya_acm_product.skumanager');
    $sku_identifier = strtolower(Html::cleanCssIdentifier($sku_entity->getSku()));

    $labels = [
      '#theme' => 'product_labels',
      '#labels' => $skuManager->getLabels($sku_entity, 'pdp'),
      '#sku' => $sku_identifier,
      '#mainsku' => $sku_identifier,
      '#type' => 'pdp',
    ];

    // Add PDP slider position class in template.
    $pdp_image_slider_position = \Drupal::config('alshaya_acm_product.settings')->get('image_slider_position_pdp');

    $build['gallery']['product_zoom'] = [
      '#theme' => 'product_zoom',
      '#mainImage' => $main_image,
      '#thumbnails' => $thumbnails,
      '#pager_flag' => $pager_flag,
      '#properties' => alshaya_product_zoom_get_rel_cloudzoom($settings),
      '#labels' => $labels,
      '#image_slider_position_pdp' => 'slider-position-' . $pdp_image_slider_position,
      '#attached' => [
        'library' => [
          'alshaya_product_zoom/product.cloud_zoom',
        ],
      ],
    ];
  }
}

/**
 * Utility function to get parent node of the sku.
 *
 * @param mixed $sku
 *   SKU name or full sku object.
 * @param string $langcode
 *   Language code.
 *
 * @return object
 *   Loaded node object.
 */
function alshaya_acm_product_get_display_node($sku, $langcode = '') {
  /** @var \Drupal\alshaya_acm_product\SkuManager $skuManager */
  $skuManager = \Drupal::service('alshaya_acm_product.skumanager');
  return $skuManager->getDisplayNode($sku, $langcode);
}

/**
 * Helper function to get the product display image.
 *
 * @param mixed $sku
 *   SKU text or full entity object.
 * @param string $image_style
 *   Image style to apply to the image.
 *
 * @return array
 *   Return string of uri or Null if not found.
 */
function alshaya_acm_get_product_display_image($sku, $image_style = '') {
  // Load the first image.
  $media_image = alshaya_acm_product_get_sku_media($sku, TRUE);
  $image = [];

  // If we have image for the product.
  if (!empty($media_image)) {
    if ($media_image['file'] instanceof FileInterface) {
      $image = alshaya_acm_product_get_sku_image($media_image, $image_style);
    }
  }
  // If no image found, check once for parent product as well.
  elseif ($parent_sku = alshaya_acm_product_get_parent_sku_by_sku($sku)) {
    // Fire alter hook for cart image before looking for the image in the
    // parent product.
    \Drupal::moduleHandler()->alter('acq_sku_cart_media', $sku, $image);

    if (!$image) {
      $image = alshaya_acm_get_product_display_image($parent_sku, $image_style);
    }
  }

  // Alter cart product image found in child sku in case of config products.
  \Drupal::moduleHandler()->alter('acq_sku_cart_media', $sku, $image);

  return $image;
}

/**
 * Get the url of the image of SKU.
 *
 * @param \Drupal\acq_sku\Entity\SKU $sku
 *   SKU entity object.
 *
 * @return string
 *   Url of the image.
 */
function _alshaya_acm_get_product_display_image_url(SKU $sku) {
  // Load the first image.
  $media_image = alshaya_acm_product_get_sku_media($sku, TRUE);

  // If we have image for the product.
  if (!empty($media_image)) {
    if ($media_image['file'] instanceof FileInterface) {
      $uri = $media_image['file']->getFileUri();
      return file_create_url($uri);
    }
  }

  return '';
}

/**
 * Helper function to get child skus for a configurable sku from tree.
 *
 * @param array $tree
 *   Tree containing configurable options and SKU objects.
 * @param array $configurables
 *   Configurable values array.
 *
 * @return null|SKU
 *   Null or SKU instance.
 */
function alshaya_acm_product_get_selected_option_sku(array $tree, array $configurables) {
  $tree_pointer = &$tree['options'];

  foreach ($configurables as $key => $value) {
    if (empty($value)) {
      continue;
    }

    // Move the tree pointer if the selection is valid.
    if (isset($tree_pointer["$key:$value"])) {
      return $tree_pointer["$key:$value"];
    }
  }

  return NULL;
}

/**
 * Helper function to get product tree based on the conf attributes values.
 *
 * @param array $tree
 *   Config product tree.
 * @param array $configurables
 *   Config values keyed by the attributec code as selected by the end-users.
 *
 * @return array
 *   Array pointing to the tree state based on the options selected.
 */
function alshaya_acm_product_fetch_selected_option_tree(array $tree, array $configurables) {
  $tree_pointer = $tree['options'];
  $tree_config_options = [];
  foreach ($configurables as $key => $value) {
    if (($value) && ($tree_pointer["$key:$value"])) {
      $tree_pointer = $tree_pointer["$key:$value"];
    }
  }

  if ($tree_pointer instanceof SKU) {
    $tree_config_options["$key:$value"] = $tree_pointer;
  }
  else {
    // Filter out all non-empty config attributes from the list.
    foreach ($tree_pointer as $key => $value) {
      if (!empty($value) && ($key !== '#available_config')) {
        $tree_config_options[$key] = $value;
      }
    }
  }

  return $tree_config_options;
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
  $sku_entity = $sku instanceof SKU ? $sku : SKU::loadFromSku($sku);

  // We always expect an entity when this function is called.
  // There may be cases were a product gets deleted so we add a check here
  // to avoid fatal errors.
  if (!($sku_entity instanceof SKU)) {
    return 0;
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

  $title = $sku_entity->get('attr_brand_logo')->getString();

  if (empty($title)) {
    return [];
  }

  $cid = 'brand_logo:' . Html::cleanCssIdentifier($title);

  if ($cache = \Drupal::cache('data')->get($cid)) {
    $image = $cache->data;
  }
  else {
    // Preparing args for all info/error messages.
    $args = ['@title' => $title, '@sku_id' => $sku_entity->id()];

    $host = \Drupal::config('alshaya_api.settings')->get('magento_host');
    $media_path = \Drupal::config('alshaya_acm_product.settings')->get('brand_logo_base_path');
    $extension = \Drupal::config('alshaya_acm_product.settings')->get('brand_logo_extension');

    $directory = $media_path;

    // Prepare file name by replacing spaces with underscores and lower case.
    $file_name = strtolower(str_replace(' ', '_', $title));

    for ($i = 0; $i < 2 && $i <= strlen($title); $i++) {
      $directory .= '/' . substr($file_name, $i, 1);
    }

    $directory_uri = 'public://brand-logo/' . $directory;
    $image_uri = $directory_uri . '/' . $file_name . '.' . $extension;
    $image_url = $host . '/' . $directory . '/' . $file_name . '.' . $extension;

    $file_storage = \Drupal::entityTypeManager()->getStorage('file');

    if ($files = $file_storage->loadByProperties(['uri' => $image_uri])) {
      $file = reset($files);
    }
    else {
      // Download the file contents.
      $file_data = file_get_contents($image_url);

      // Check to ensure errors like 404, 403, etc. are catched and empty file
      // not saved in SKU.
      if (empty($file_data)) {
        \Drupal::logger('alshaya_acm_product')->error('Failed to download brand logo of title "@title" for SKU id @sku_id.', $args);
      }

      // Get the file name.
      $file_name = basename($image_url);

      // Prepare the directory.
      file_prepare_directory($directory_uri, FILE_CREATE_DIRECTORY);

      // Save the file as file entity.
      /** @var \Drupal\file\Entity\File $file */
      $file = file_save_data($file_data, $image_uri, FILE_EXISTS_REPLACE);

      if (empty($file)) {
        \Drupal::logger('alshaya_acm_product')->error('Failed to save brand logo of title "@title" for SKU id @sku_id.', $args);
      }
    }

    if ($file) {
      $image = [
        '#theme' => 'image',
        '#uri' => $file->getFileUri(),
        '#title' => $title,
        '#alt' => $title,
      ];

      $expire = CacheBackendInterface::CACHE_PERMANENT;
    }
    else {
      $image = [];

      // We cache this for 30 minutes to allow admins to correct the situation.
      $expire = \Drupal::time()->getRequestTime() + (60 * 30);
    }

    \Drupal::cache('data')->set($cid, $image, $expire);
  }

  return $image;
}

/**
 * Helper function to update config attributes depending on the selection made.
 *
 * @param array $form_parts
 *   Configurable parts of the form.
 * @param \Drupal\Core\Form\FormStateInterface $form_state
 *   Form state Object.
 *   contain the SKUs depending on the tree path traversed.
 * @param array $configurables
 *   List of values selected keyed by the config attribute.
 * @param bool $deselect_leaf_options
 *   Clear the leaf attribute selected while selecting the parent config.
 */
function alshaya_acm_product_update_config_options(array &$form_parts, FormStateInterface &$form_state, array $configurables, $deselect_leaf_options = TRUE) {
  $enabled_options = [];
  $product_tree = $form_state->get('tree');
  $leaf_attribute = end($product_tree['configurables'])['code'];

  // To de-select all leaf options on parent change & not re-calculate
  // availability of parent attributes in the opposite direction.
  if ($deselect_leaf_options && !empty($configurables[$leaf_attribute])) {
    foreach ($form_parts['configurables'][$leaf_attribute]['#options'] as $key => $value) {
      // Deselect the leaf option once clicked on a non-leaf term.
      if (($key == $configurables[$leaf_attribute]) &&
        ($form_state->getTriggeringElement()['#name'] == 'configurables[' . $leaf_attribute . ']')) {
        continue;
      }
      elseif ($form_state->getTriggeringElement()['#name'] != 'configurables[' . $leaf_attribute . ']') {
        $form_parts['configurables'][$leaf_attribute]['#value'] = '';
      }
    }
    unset($configurables[$leaf_attribute]);

    $form_state->setValue('configurables', $configurables);
    $user_input = $form_state->getUserInput();
    $user_input['configurables'] = $configurables;
    $form_state->setUserInput($user_input);
  }

  $tree_pointer_parent = alshaya_acm_product_fetch_selected_option_tree($product_tree, $configurables);

  // Items are keyed by their attribute codes.
  foreach ($tree_pointer_parent as $key => $value) {
    $attribute = explode(':', $key);
    $attribute_code = $attribute[0];
    $enabled_options[] = $attribute[1];
  }

  $options = $form_parts['configurables'][$attribute_code]['#options'];

  // Disable options which don't exist for the choosen config.
  foreach ($options as $key => $value) {
    if ($key && !in_array($key, $enabled_options)) {
      unset($form_parts['configurables'][$attribute_code]['#options'][$key]);
    }
  }

  // If leaf attribute only contains 1 option, mark this option as selected.
  if (count($form_parts['configurables'][$attribute_code]['#options']) == 1) {
    $configurables[$attribute_code] = key($form_parts['configurables'][$attribute_code]['#options']);
    $form_state->setValue('configurables', $configurables);
    $user_input = $form_state->getUserInput();
    $user_input['configurables'] = $configurables;
    $form_state->setUserInput($user_input);
    // Setting value for the element.
    $form_parts['configurables'][$attribute_code]['#value'] = key($form_parts['configurables'][$attribute_code]['#options']);
  }
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
  }
  return $embed_url;
}
