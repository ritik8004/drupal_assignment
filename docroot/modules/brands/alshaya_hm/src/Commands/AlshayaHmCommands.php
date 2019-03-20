<?php

namespace Drupal\alshaya_hm\Commands;

use Drupal\acq_sku\Entity\SKU;
use Drupal\alshaya_acm_product\SkuManager;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Path\AliasManager;
use Drupal\Core\State\StateInterface;
use Drush\Commands\DrushCommands;
use Drupal\Core\Database\Connection;
use Drupal\node\NodeInterface;
use Symfony\Component\HttpFoundation\RequestStack;

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
   * Request stack object.
   *
   * @var \Symfony\Component\HttpFoundation\Request|null
   */
  protected $request;

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
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   Request stack object.
   */
  public function __construct(Connection $connection,
                              SkuManager $skuManager,
                              AliasManager $aliasManager,
                              ConfigFactoryInterface $configFactory,
                              StateInterface $state,
                              RequestStack $requestStack) {
    $this->connection = $connection;
    $this->skuManager = $skuManager;
    $this->aliasManager = $aliasManager;
    $this->configFactory = $configFactory;
    $this->state = $state;
    $this->request = $requestStack->getCurrentRequest();
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

    $this->state->set(self::TEMP_ALIAS_COLOR_MAPPING_STATE_KEY, serialize($mappings));
  }

  /**
   * Helper function to create mapping between alias & color code.
   *
   * @param string $sku
   *   SKU code for which mapping needs to be derived.
   * @param string $langcode
   *   Langcode of the SKU entity.
   *
   * @return array
   *   Returns mapping array between color code & alias.
   */
  protected function fetchAliasColorCodeMapping($sku, $langcode) {
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
      // Do the same for both languages. Skip this condition for child SKUs
      // without a color label value.
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
   * Add redirect from nodes with old url alias to url alias-color.
   *
   * @command alshaya_hm:add
   *
   * @validate-module-enabled alshaya_hm
   *
   * @aliases alshaya_hm_store_set_redirects
   */
  public function addAliasRedirects() {
    $mapping = unserialize($this->state->get(self::TEMP_ALIAS_COLOR_MAPPING_STATE_KEY));
    $host = $this->request->getHost();
    $redirect_map = [];

    if ($mapping) {
      foreach ($mapping as $map) {
        $sub_path = '/' . $map['langcode'] . $map['alias'];
        $host_key = str_replace('.', ':', $host);
        $redirect_map[$host_key][] = [
          'sub_path' => $sub_path,
          'destination' => $host . $sub_path . '-' . $map['color_code'],
        ];
      }
    }

    $this->configFactory->getEditable('redirect_domain.domains')->set('domain_redirects', $redirect_map)->save();
  }

}
