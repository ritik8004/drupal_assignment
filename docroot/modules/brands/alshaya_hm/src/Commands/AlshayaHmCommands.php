<?php
/**
 * @file
 */

namespace Drupal\alshaya_hm\Commands;

use Drupal\acq_sku\Entity\SKU;
use Drupal\alshaya_acm_product\SkuManager;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Path\AliasManager;
use Drupal\redirect\Entity\Redirect;
use Drush\Commands\DrushCommands;
use Drupal\Core\Database\Connection;
use Drupal\node\NodeInterface;

/**
 * Class AlshayaHmCommands
 * @package Drupal\alshaya_hm\Commands
 */
class AlshayaHmCommands extends DrushCommands {

  /**
   * Database connection service.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * Sku Manager service.
   *
   * @var \Drupal\alshaya_acm_product\SkuManager
   */
  protected $skuManager;

  /**
   * Alias Manager service.
   *
   * @var \Drupal\Core\Path\AliasManager
   */
  protected $aliasManager;

  /**
   * Config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * AlshayaHmCommands constructor.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   Database connection service.
   * @param \Drupal\alshaya_acm_product\SkuManager $skuManager
   *   Sku Manager service.
   * @param \Drupal\Core\Path\AliasManager $aliasManager
   *   Alias Manager service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *
   */
  public function __construct(Connection $connection,
                              SkuManager $skuManager,
                              AliasManager $aliasManager,
                              ConfigFactoryInterface $configFactory) {
    $this->connection = $connection;
    $this->skuManager = $skuManager;
    $this->aliasManager = $aliasManager;
    $this->configFactory = $configFactory;
  }

  /**
   * Store url alias of all config products in the system before deleting them.
   *
   * @command alshaya_hm:store_temp_alias
   *
   * @validate-module-enabled alshaya_hm
   *
   * @aliases alshaya_hm_store_temp_alias
   */
  public function storeTempAlias() {
    $mappings = [];
    $query = $this->connection->select('acq_sku_field_data', 'asfd');
    $query->condition('asfd.type', 'configurable');
    $query->fields('asfd', ['sku', 'langcode']);
    $rows = $query->execute()->fetchAll();

    foreach ($rows as $row) {
      if (!empty($color_mapping = $this->fetchAliasColorCodeMapping($row->sku, $row->langcode))) {
        $mappings[] = $color_mapping;
      }
    }

    $mappings_json = JSON::encode($mappings);
    $mapping_file_path = $this->configFactory->get('alshaya_hm.settings')->get('alias_color_mapping_file_path');
    file_put_contents($mapping_file_path, $mappings_json);
  }

  protected function fetchAliasColorCodeMapping($sku, $langcode) {
    $sku_entity = SKU::loadFromSku($sku, $langcode);

    if (($node = $this->skuManager->getDisplayNode($sku, FALSE)) &&
      ($node instanceof NodeInterface)) {

      // If the display node fetched belongs to a different language compared to the langcode SKU is in, replace the
      // node object with its translation.
      if (($node->language()->getId() !== $langcode) &&
      ($node->hasTranslation($langcode) &&
        ($node_translation = $node->getTranslation($langcode)))) {
        $node = $node_translation;
      }

      // Fetch first child SKU from the parent SKU & store its color attribute. Do the same for both languages. Skip
      // this condition for child SKUs without a color label value.
      if (($firt_child_sku = $this->skuManager->getFirstChildForSku($sku_entity, 'article_castor_id')) &&
        ($firt_child_sku instanceof SKU) &&
        ($color_code = $firt_child_sku->get('attr_color_label')->getString())) {
        return [
          'alias' => $this->aliasManager->getAliasByPath('/node/' . $node->id(), $node->language()->getId()),
          'color_code' => $color_code,
          'langcode' => $node->language()->getId(),
        ];
      }
    }

    return [];
  }

  /**
   * Add redirect from nodes with old url alias to url alias-color
   *
   * @command alshaya_hm:add
   *
   * @validate-module-enabled alshaya_hm
   *
   * @aliases alshaya_hm_store_set_redirects
   */
  public function addAliasRedirects() {
    $mapping_file_path = $this->configFactory->getEditable('alshaya_hm.settings')->get('alias_color_mapping_file_path');
    $mapping = JSON::decode(file_get_contents($mapping_file_path));

    if ($mapping) {
      foreach ($mapping as $map) {
        print_r($map);
        Redirect::create([
          'redirect_source' => $map['alias'],
          'redirect_redirect' => $map['alias'] . '-' . $map['color_code'],
          'language' => $map['langcode'],
          'status_code' => '301',
        ])->save();
        exit;
      }
    }
  }

}
