<?php

namespace Drupal\alshaya_advanced_page\Brand;

use Drupal\Core\Database\Connection;

/**
 * Class AlshayaBrandListHelper.
 */
class AlshayaBrandListHelper {

  /**
   * Brand cache tag.
   */
  const BRAND_CACHETAG = 'alshaya-brand-list';

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
   */
  public function getBrandTerms() {
    $query = $query = $this->connection->select('taxonomy_term_field_data', 'ttfd');
    $query->fields('ttfd', ['tid', 'name']);
    $query->innerJoin('taxonomy_term__field_sku_attribute_code', 'ttac', 'ttac.entity_id = ttfd.tid');
    $query->innerJoin('taxonomy_term__field_attribute_swatch_image', 'ttasi', 'ttasi.entity_id = ttfd.tid');
    $query->innerJoin('file_managed', 'fm', 'ttasi.field_attribute_swatch_image_target_id = fm.fid AND fm.status = 1');
    $query->addField('fm', 'uri');
    $query->condition('ttac.field_sku_attribute_code_value', self::BRAND_ATTRIBUTE_CODE);
    $query->condition('ttfd.default_langcode', 1);
    $query->condition('ttfd.vid', self::BRAND_VID);
    $query->orderBy('ttfd.weight', 'ASC');

    $terms = $query->execute()->fetchAll();

    return $terms;
  }

}
