<?php

namespace Drupal\alshaya_product_options;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\taxonomy\TermInterface;

/**
 * Class SwatchesHelper.
 *
 * @package Drupal\alshaya_product_options
 */
class SwatchesHelper {

  /**
   * Constant for identifying textual swatch type.
   */
  const SWATCH_TYPE_TEXTUAL = 0;

  /**
   * Constant for identifying visual swatch type with color number value.
   */
  const SWATCH_TYPE_VISUAL_COLOR = 1;

  /**
   * Constant for identifying visual swatch type with color number value.
   */
  const SWATCH_TYPE_VISUAL_IMAGE = 2;

  /**
   * Constant for identifying empty swatch type.
   */
  const SWATCH_TYPE_EMPTY = 3;

  /**
   * Entity Type Manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * File Storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $fileStorage;

  /**
   * Logger.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * SwatchesHelper constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity Type Manager.
   * @param \Drupal\Core\Logger\LoggerChannelInterface $logger
   *   Logger.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager,
                              LoggerChannelInterface $logger) {
    $this->entityTypeManager = $entity_type_manager;
    $this->fileStorage = $this->entityTypeManager->getStorage('file');
    $this->logger = $logger;
  }

  /**
   * Update Term with Attribute option value if changed.
   *
   * @param \Drupal\taxonomy\TermInterface $term
   *   Taxonomy term.
   * @param array $swatch_info
   *   Swatch info array received from API.
   */
  public function updateAttributeOptionSwatch(TermInterface $term, array $swatch_info) {
    $save_term = FALSE;

    // Save again if type changed.
    if ($swatch_info['swatch_type'] != $term->get('field_attribute_swatch_type')->getString()) {
      $save_term = TRUE;
    }
    // Save again if value changed.
    elseif ($term->get('field_attribute_swatch_value')->getString() != $swatch_info['swatch']) {
      $save_term = TRUE;
    }

    if ($save_term) {
      // Delete existing file first.
      if ($term->get('field_attribute_swatch_image')->first()) {
        $file_value = $term->get('field_attribute_swatch_image')->first()->getValue();
        $file = $this->fileStorage->load($file_value['target_id']);
        $file->delete();
      }

      // Reset all current values.
      $term->get('field_attribute_swatch_text')->setValue(NULL);
      $term->get('field_attribute_swatch_color')->setValue(NULL);
      $term->get('field_attribute_swatch_image')->setValue(NULL);

      // Saving in separate field to validate next time for change.
      $term->get('field_attribute_swatch_value')->setValue($swatch_info['swatch']);
      $term->get('field_attribute_swatch_type')->setValue($swatch_info['swatch_type']);

      switch ($swatch_info['swatch_type']) {
        case self::SWATCH_TYPE_TEXTUAL:
          $term->get('field_attribute_swatch_text')->setValue(
            $swatch_info['swatch']
          );
          break;

        case self::SWATCH_TYPE_VISUAL_COLOR:
          $term->get('field_attribute_swatch_color')->setValue(
            $swatch_info['swatch']
          );
          break;

        case self::SWATCH_TYPE_VISUAL_IMAGE:
          try {
            $file = $this->downloadSwatchImage($swatch_info['swatch']);
          }
          catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            continue;
          }

          $term->get('field_attribute_swatch_image')->setValue($file);
          break;
      }
    }

    if ($save_term) {
      $term->save();
    }
  }

  /**
   * Download swatch image to Drupal and create File entity.
   *
   * @param string $url
   *   Swatch image url.
   *
   * @return \Drupal\file\Entity\File
   *   File entity.
   *
   * @throws \Exception
   */
  private function downloadSwatchImage($url) {
    // Preparing args for all info/error messages.
    $args = ['@file' => $url];

    // Download the file contents.
    $file_data = file_get_contents($url);

    // Check to ensure errors like 404, 403, etc. are catched and empty file
    // not saved in SKU.
    if (empty($file_data)) {
      throw new \Exception(new FormattableMarkup('Failed to download file "@file".', $args));
    }

    // Get the path part in the url, remove hostname.
    $path = parse_url($url, PHP_URL_PATH);

    // Remove slashes from start and end.
    $path = trim($path, '/');

    // Get the file name.
    $file_name = basename($path);

    // Prepare the directory path.
    $directory = 'public://swatches/' . str_replace('/' . $file_name, '', $path);

    // Prepare the directory.
    file_prepare_directory($directory, FILE_CREATE_DIRECTORY);

    // Save the file as file entity.
    /** @var \Drupal\file\Entity\File $file */
    if ($file = file_save_data($file_data, $directory . '/' . $file_name, FILE_EXISTS_REPLACE)) {
      return $file;
    }
    else {
      throw new \Exception(new FormattableMarkup('Failed to save file "@file".', $args));
    }
  }

}
