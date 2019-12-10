<?php

namespace Drupal\alshaya_advanced_page\Brand;

use Drupal\Core\Database\Connection;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\StreamWrapper\StreamWrapperManagerInterface;

/**
 * Class AlshayaBrandListHelper.
 */
class AlshayaBrandListHelper {

  /**
   * Taxonomy used for product brand.
   */
  const BRAND_VID = 'sku_product_option';

  /**
   * Attribute code of the product brand.
   */
  const BRAND_ATTRIBUTE_CODE = 'brand_logo';

  /**
   * Database Connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  private $connection;

  /**
   * LanguageManagerInterface.
   *
   * @var Drupal\Core\Language\LanguageManagerInterface
   */
  private $languageManager;

  /**
   * StreamWrapperManagerInterface.
   *
   * @var \Drupal\Core\StreamWrapper\StreamWrapperManagerInterface
   */
  private $streamWrapperManager;

  /**
   * AlshayaBrandListHelper constructor.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   Database Connection.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   Language Manager.
   * @param \Drupal\Core\StreamWrapper\StreamWrapperManagerInterface $stream_wrapper_manager
   *   StreamWrapper Manager.
   */
  public function __construct(Connection $connection, LanguageManagerInterface $language_manager, StreamWrapperManagerInterface $stream_wrapper_manager) {
    $this->connection = $connection;
    $this->languageManager = $language_manager;
    $this->streamWrapperManager = $stream_wrapper_manager;
  }

  /**
   * Load all product brand terms.
   */
  public function loadBrandTerms() {
    $langcode = $this->languageManager->getCurrentLanguage()->getId();

    $query = $query = $this->connection->select('taxonomy_term_field_data', 'ttfd');
    $query->fields('ttfd', ['tid', 'name']);
    $query->innerJoin('taxonomy_term__field_sku_attribute_code', 'ttac', 'ttac.entity_id = ttfd.tid AND ttac.langcode = ttfd.langcode');
    $query->innerJoin('taxonomy_term__field_attribute_swatch_image', 'ttasi', 'ttasi.entity_id = ttfd.tid AND ttasi.langcode = ttfd.langcode');
    $query->innerJoin('file_managed', 'fm', 'ttasi.field_attribute_swatch_image_target_id = fm.fid AND fm.langcode = ttfd.langcode AND fm.status = 1');
    $query->addField('fm', 'uri');
    $query->condition('ttac.field_sku_attribute_code_value', self::BRAND_ATTRIBUTE_CODE);
    $query->condition('ttfd.langcode', $langcode);
    $query->condition('ttfd.vid', self::BRAND_VID);
    $query->orderBy('ttfd.weight', 'ASC');

    $terms = $query->execute()->fetchAll();

    return $terms;
  }

  /**
   * Get brand images for terms.
   */
  public function getBrandImages() {
    $brand_images = [];
    $terms = $this->loadBrandTerms();
    $image_url = '';
    if (!empty($terms)) {
      foreach ($terms as $term) {
        if ($wrapper = $this->streamWrapperManager->getViaUri($term->uri)) {
          $image_url = $wrapper->getExternalUrl();
        }
        $brand_images[$term->tid] = [
          'image' => $image_url,
          'title' => $term->name,
          'link' => '/search?keywords=' . $term->name,
        ];
      }
    }
    return $brand_images;
  }

}
