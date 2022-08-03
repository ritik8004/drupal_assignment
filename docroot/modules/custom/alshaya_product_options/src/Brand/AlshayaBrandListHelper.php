<?php

namespace Drupal\alshaya_product_options\Brand;

use Drupal\Core\Database\Connection;
use Drupal\Core\Site\Settings;

/**
 * Class Alshaya Brand List Helper.
 */
class AlshayaBrandListHelper {

  /**
   * Brand cache tag.
   */
  public const BRAND_CACHETAG = 'alshaya-brand-list';

  /**
   * Taxonomy used for product brand.
   */
  public const BRAND_VID = 'sku_product_option';

  /**
   * Database Connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  private $connection;

  /**
   * AlshayaBrandListHelper constructor.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   Database Connection.
   */
  public function __construct(Connection $connection) {
    $this->connection = $connection;
  }

  /**
   * Load all product brand terms.
   *
   * @param string $langcode
   *   The langcode.
   *
   * @return array
   *   Return the array of brand name from brand logo attribute.
   */
  public function getBrandTerms($langcode = 'en') {
    $logo_attribute = self::getLogoAttribute();
    $terms = [];
    if ($logo_attribute) {
      $query = $query = $this->connection->select('taxonomy_term_field_data', 'ttfd');
      $query->fields('ttfd', ['tid', 'name']);
      $query->innerJoin('taxonomy_term__field_sku_attribute_code', 'ttac', 'ttac.entity_id = ttfd.tid');
      $query->innerJoin('taxonomy_term__field_attribute_swatch_org_image', 'ttasoi', 'ttasoi.entity_id = ttfd.tid');
      $query->innerJoin('file_managed', 'fm', 'ttasoi.field_attribute_swatch_org_image_target_id = fm.fid AND fm.status = 1');
      $query->addField('fm', 'uri');
      $query->condition('ttac.field_sku_attribute_code_value', $logo_attribute);
      $query->condition('ttfd.langcode', $langcode);
      $query->condition('ttfd.vid', self::BRAND_VID);
      $query->orderBy('ttfd.weight', 'ASC');

      $terms = $query->execute()->fetchAll();
    }

    return $terms;
  }

  /**
   * Get Logo Attribute.
   */
  public static function getLogoAttribute() {
    return Settings::get('brand_logo_block')['logo_attribute'] ?? '';
  }

  /**
   * Get Brand Attribute.
   */
  public static function getBrandAttribute() {
    return Settings::get('brand_logo_block')['brand_attribute'] ?? '';
  }

}
