<?php

namespace Drupal\alshaya_hm\Commands;

use Drupal\acq_sku\Entity\SKU;
use Drupal\alshaya_acm_product\SkuManager;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Path\AliasManager;
use Drupal\Core\State\StateInterface;
use Drupal\redirect\Entity\Redirect;
use Drush\Commands\DrushCommands;
use Drupal\Core\Database\Connection;
use Drupal\node\NodeInterface;

/**
 * Class AlshayaHmCommands.
 *
 * @package Drupal\alshaya_hm\Commands
 */
class AlshayaHmCommands extends DrushCommands {

  const TEMP_ALIAS_COLOR_MAPPING_STATE_KEY = 'temp_alias_color_mapping';

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
   * State service.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;

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
   *   Config Factory service.
   * @param \Drupal\Core\State\StateInterface $state
   *   State Manager service.
   */
  public function __construct(Connection $connection,
                              SkuManager $skuManager,
                              AliasManager $aliasManager,
                              ConfigFactoryInterface $configFactory,
                              StateInterface $state) {
    $this->connection = $connection;
    $this->skuManager = $skuManager;
    $this->aliasManager = $aliasManager;
    $this->configFactory = $configFactory;
    $this->state = $state;
  }

  /**
   * Store url alias of all config products in the system before deleting them.
   *
   * @command alshaya_hm:store_old_alias_sku_map
   *
   * @validate-module-enabled alshaya_hm
   *
   * @aliases alshaya_hm_store_old_alias_sku_map
   */
  public function storeTempAlias() {
    $mappings = [];
    $query = $this->connection->select('acq_sku_field_data', 'asfd');
    $query->condition('asfd.type', 'configurable');
    $query->fields('asfd', ['sku', 'langcode']);
    $rows = $query->execute()->fetchAll();

    // Fetch Alias - first child sku mapping for all configurable products.
    foreach ($rows as $row) {
      if (!empty($color_mapping = $this->fetchAliasFirstChildSkuMapping($row->sku, $row->langcode))) {
        $mappings[] = $color_mapping;
      }
    }

    $this->state->set(self::TEMP_ALIAS_COLOR_MAPPING_STATE_KEY, serialize($mappings));
  }

  /**
   * Helper function to create mapping between alias & first child sku.
   *
   * @param string $sku
   *   SKU code for which mapping needs to be derived.
   * @param string $langcode
   *   Langcode of the SKU entity.
   *
   * @return array
   *   Returns mapping array between color code & alias.
   */
  protected function fetchAliasFirstChildSkuMapping($sku, $langcode) {
    $sku_entity = SKU::loadFromSku($sku, $langcode);

    if (($node = $this->skuManager->getDisplayNode($sku, FALSE)) &&
      ($node instanceof NodeInterface)) {

      // If the display node fetched belongs to a different language compared to
      // the langcode SKU is in, replace the node object with its translation.
      if (($node->language()->getId() !== $langcode) &&
        ($node->hasTranslation($langcode) &&
          ($node_translation = $node->getTranslation($langcode)))) {
        $node = $node_translation;
      }

      // Fetch first child SKU from the parent SKU & store its color attribute.
      // Do the same for both languages.
      if (($firt_child_sku = $this->skuManager->getFirstChildForSku($sku_entity, 'article_castor_id')) &&
        ($firt_child_sku instanceof SKU)) {
        return [
          'alias' => $this->aliasManager->getAliasByPath('/node/' . $node->id(), $node->language()->getId()),
          'child_sku' => $firt_child_sku->getSku(),
          'langcode' => $node->language()->getId(),
        ];
      }
    }

    return [];
  }

  /**
   * Add redirect from nodes with old url alias to url alias-color.
   *
   * @command alshaya_hm:add
   *
   * @validate-module-enabled alshaya_hm
   *
   * @aliases alshaya_hm_set_redirects
   */
  public function addAliasRedirects() {
    $mapping = unserialize($this->state->get(self::TEMP_ALIAS_COLOR_MAPPING_STATE_KEY));

    foreach ($mapping as $map) {
      $node = $this->skuManager->getDisplayNode($map['child_sku']);

      // Create redirect from old alias to new nodes.
      Redirect::create([
        'redirect_source' => $map['alias'],
        'redirect_redirect' => '/node/' . $node->id(),
        'language' => $map['langcode'],
        'status_code' => '301',
      ])->save();
    }

  }

}
