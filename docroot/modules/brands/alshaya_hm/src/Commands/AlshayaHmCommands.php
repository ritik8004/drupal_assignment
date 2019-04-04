<?php

namespace Drupal\alshaya_hm\Commands;

use Drupal\acq_commerce\SKUInterface;
use Drupal\acq_sku\Entity\SKU;
use Drupal\acq_sku\SKUFieldsManager;
use Drupal\alshaya_config\AlshayaConfigManager;
use Drupal\redirect\Entity\Redirect;
use Drush\Commands\DrushCommands;
use Drupal\node\NodeInterface;

/**
 * Class AlshayaHmCommands.
 *
 * @package Drupal\alshaya_hm\Commands
 */
class AlshayaHmCommands extends DrushCommands {

  const TEMP_ALIAS_COLOR_MAPPING_STATE_KEY = 'temp_alias_color_mapping';

  /**
   * Alshaya config Manager.
   *
   * @var \Drupal\alshaya_config\AlshayaConfigManager
   */
  protected $alshayaConfigManager;

  /**
   * SKU fields manager.
   *
   * @var \Drupal\acq_sku\SKUFieldsManager
   */
  protected $skuFieldsManager;

  /**
   * AlshayaHmCommands constructor.
   *
   * @param \Drupal\alshaya_config\AlshayaConfigManager $alshayaConfigManager
   *   Alshaya config manager.
   * @param \Drupal\acq_sku\SKUFieldsManager $skuFieldsManager
   *   SKU fields manager.
   */
  public function __construct(AlshayaConfigManager $alshayaConfigManager,
                              SKUFieldsManager $skuFieldsManager) {
    $this->alshayaConfigManager = $alshayaConfigManager;
    $this->skuFieldsManager = $skuFieldsManager;
  }

  /**
   * Add configurations needed for catalog restructuring.
   *
   * @command alshaya_hm:enable_catalog_restructure
   *
   * @validate-module-enabled alshaya_hm
   *
   * @aliases alshaya_hm_enable_catalog_restructure
   */
  public function enableCatalogRestructure() {
    // Hide product color field from Form display.
    $this->alshayaConfigManager->updateConfigs(['core.entity_form_display.node.acq_product.default'], 'acq_sku');

    // Add style code attribute to SKU entity.
    alshaya_config_install_configs(['alshaya_acm_product.sku_base_fields'], 'alshaya_acm_product');
    $this->skuFieldsManager->addFields();

    // Update url alias for product nodes to append color label as suffix.
    $this->alshayaConfigManager->updateConfigs(['pathauto.pattern.product_pathauto'], 'alshaya_hm');
    $this->alshayaConfigManager->updateConfigs(['pathauto.pattern.content_pathauto'], 'alshaya_acm', 'install', AlshayaConfigManager::MODE_REPLACE);

    // Set Additional configurable attribute for H&M.
    $this->alshayaConfigManager->updateConfigs(['acq_sku.configurable_form_settings'], 'alshaya_hm', 'optional');

    // Update Alshaya display settings to hide swatches.
    $this->alshayaConfigManager->updateConfigs(['alshaya_acm_product.display_settings'], 'alshaya_acm_product');
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
    $batch = [
      'title' => 'Add temporary alias sku mapping',
      'init_message' => 'Processing SKUs & adding mapping for their alias...',
      'progress_message' => 'Processed @current out of @total.',
      'error_message' => 'Error occurred while processing SKUs, please check logs.',
      'operations' => [
        [[__CLASS__, 'storeAliasFirstChildSkuMapping'], []],
      ],
    ];

    batch_set($batch);

    // Process the batch.
    drush_backend_batch_process();

    $message = 'Added mapping for all SKUs & their alias to state variable: ' . self::TEMP_ALIAS_COLOR_MAPPING_STATE_KEY;
    $this->logger->info($message);
    $this->say($message);
  }

  /**
   * Batch process callback for storing Alias & child sku mapping.
   *
   * @param mixed $context
   *   Batch context.
   */
  public static function storeAliasFirstChildSkuMapping(&$context) {
    $connection = \Drupal::database();

    if (empty($context['sandbox'])) {
      $query = $connection->select('acq_sku_field_data', 'asfd');
      $query->condition('asfd.type', 'configurable');
      $query->fields('asfd', ['sku', 'langcode']);
      $context['sandbox']['result'] = array_chunk($query->execute()->fetchAll(), 100);
      $context['sandbox']['max'] = count($context['sandbox']['result']);
      $context['sandbox']['current'] = 0;
    }

    if (empty($context['sandbox']['result'])) {
      $context['finished'] = 1;
      return;
    }

    /** @var \Drupal\alshaya_acm_product\SkuManager $skuManager */
    $sku_manager = \Drupal::service('alshaya_acm_product.skumanager');

    /** @var \Drupal\Core\Path\AliasManager $alias_manager */
    $alias_manager = \Drupal::service('path.alias_manager');

    $rows = array_shift($context['sandbox']['result']);
    $mappings = [];

    foreach ($rows as $row) {
      $sku_entity = SKU::loadFromSku($row->sku, $row->langcode);

      if (($sku_entity instanceof SKUInterface) &&
        ($node = $sku_manager->getDisplayNode($sku_entity, FALSE)) &&
        ($node instanceof NodeInterface)) {

        // If the display node fetched belongs to a different language
        // compared to the langcode SKU is in, replace the node object with
        // its translation.
        if (($node->language()->getId() !== $row->langcode) &&
          ($node->hasTranslation($row->langcode) &&
          ($node_translation = $node->getTranslation($row->langcode)))) {
          $node = $node_translation;
        }

        // Fetch first child SKU from the parent SKU & store its color
        // attribute. Do the same for both languages.
        if (($firt_child_sku = $sku_manager->getFirstChildForSku($sku_entity, 'article_castor_id')) &&
          ($firt_child_sku instanceof SKU)) {
          $mappings[] = [
            'alias' => $alias_manager->getAliasByPath('/node/' . $node->id(), $node->language()->getId()),
            'child_sku' => $firt_child_sku->getSku(),
            'langcode' => $node->language()->getId(),
            'parent_sku' => $sku_entity->getSku(),
          ];
        }
      }
    }

    $existing_mapping = \Drupal::state()->get(self::TEMP_ALIAS_COLOR_MAPPING_STATE_KEY, []);
    $merged_mapping = array_merge($existing_mapping, $mappings);

    // Set updated temp store mapping in state variable.
    \Drupal::state()->set(self::TEMP_ALIAS_COLOR_MAPPING_STATE_KEY, $merged_mapping);

    $context['sandbox']['current']++;
    $context['finished'] = $context['sandbox']['current'] / $context['sandbox']['max'];
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
    $batch = [
      'title' => 'Add redirects based on the temporary data stored.',
      'init_message' => 'Processing state data & adding redirects.',
      'progress_message' => 'Processed @current out of @total.',
      'error_message' => 'Error occurred while adding redirects, please check logs.',
      'operations' => [
        [[__CLASS__, 'addRedirectsFromTempstore'], []],
      ],
    ];

    batch_set($batch);

    // Process the batch.
    drush_backend_batch_process();

    $message = 'Added redirects for all the mappings store in state variable: ' . self::TEMP_ALIAS_COLOR_MAPPING_STATE_KEY;
    $this->logger->info($message);
    $this->say($message);
  }

  /**
   * Batch process callback for adding redirects based on temp store.
   *
   * @param mixed $context
   *   Batch Context.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public static function addRedirectsFromTempstore(&$context) {
    /** @var \Drupal\redirect\RedirectRepository $redirect_repository */
    $redirect_repository = \Drupal::service('redirect.repository');

    if (empty($context['sandbox'])) {
      $mapping = \Drupal::state()->get(self::TEMP_ALIAS_COLOR_MAPPING_STATE_KEY);

      $context['sandbox']['result'] = array_chunk($mapping, 100);
      $context['sandbox']['max'] = count($context['sandbox']['result']);
      $context['sandbox']['current'] = 0;
    }

    if (empty($context['sandbox']['result'])) {
      $context['finished'] = 1;
      return;
    }

    /** @var \Drupal\alshaya_acm_product\SkuManager $skuManager */
    $sku_manager = \Drupal::service('alshaya_acm_product.skumanager');

    $map_chunk = array_shift($context['sandbox']['result']);
    foreach ($map_chunk as $map) {
      $node = $sku_manager->getDisplayNode($map['child_sku']);

      // Skip adding redirect if the old child SKU is not linked with a node
      // yet.
      if (!$node instanceof NodeInterface) {
        \Drupal::logger('alshaya_hm')->alert('Display node not found for SKU @sku.', ['@sku' => $map['child_sku']]);
        continue;
      }

      // Skip adding redirect for cases where the old style level SKU exists.
      // Migration is supposed to disable the old style level SKU on MDC.
      if (($style_parent_sku = SKU::loadFromSku($map['parent_sku'])) &&
      ($style_parent_sku instanceof SKU)) {
        \Drupal::logger('alshaya_hm')->alert('SKU has not been migrated yet. Skipping adding redirect for @sku.', ['@sku' => $map['child_sku']]);
        continue;
      }

      // Add redirect only if the child SKU is available & old style level
      // parent SKU is not available i.e., disabled after migration on MDC.
      if (($child_sku = SKU::loadFromSku($map['child_sku'], $map['langcode'])) &&
        ($child_sku instanceof SKU)) {
        $redirect = $redirect_repository->findMatchingRegdirect($map['alias'], [], $map['langcode']);

        // Create redirect from old alias to new nodes. Avoid errors if the
        // redirect already exists.
        if (!$redirect instanceof Redirect) {
          Redirect::create([
            'redirect_source' => $map['alias'],
            'redirect_redirect' => '/node/' . $node->id(),
            'language' => $map['langcode'],
            'status_code' => '301',
          ])->save();
        }
        else {
          \Drupal::logger('alshaya_hm')->alert('Skipping adding redirect. Redirect already found for the source: @alias', ['@alias' => $map['alias']]);
        }
      }
      else {
        \Drupal::logger('alshaya_hm')->alert('Skipping adding redirect. SKU @sku not found or is missing style code.', ['@sku' => $map['child_sku']]);
      }
    }

    $context['sandbox']['current']++;
    $context['finished'] = $context['sandbox']['current'] / $context['sandbox']['max'];
  }

}
