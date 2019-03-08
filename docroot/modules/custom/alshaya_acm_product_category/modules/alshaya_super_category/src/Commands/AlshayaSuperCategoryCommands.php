<?php

namespace Drupal\alshaya_super_category\Commands;

use Drupal\alshaya_acm_product_category\ProductCategoryTree;
use Drupal\alshaya_config\AlshayaConfigManager;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drush\Commands\DrushCommands;
use Drush\Exceptions\UserAbortException;

/**
 * Class AlshayaSuperCategoryCommands.
 *
 * @package Drupal\alshaya_super_category\Commands
 */
class AlshayaSuperCategoryCommands extends DrushCommands {

  use StringTranslationTrait;

  /**
   * Config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  private $configFactory;

  /**
   * Product category tree.
   *
   * @var \Drupal\alshaya_acm_product_category\ProductCategoryTree
   */
  private $productCategoryTree;

  /**
   * Entity Type Manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private $entityTypeManager;

  /**
   * Module Handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  private $moduleHandler;

  /**
   * Alshaya Config Manager service.
   *
   * @var \Drupal\alshaya_config\AlshayaConfigManager
   */
  private $alshayaConfigManager;

  /**
   * AlshayaSuperCategoryCommands constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   Config factory.
   * @param \Drupal\alshaya_acm_product_category\ProductCategoryTree $productCategoryTree
   *   Product category tree.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   Entity Type Manager.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler
   *   Module Handler service.
   * @param \Drupal\alshaya_config\AlshayaConfigManager $alshayaConfigManager
   *   Alshaya Config Manager.
   */
  public function __construct(ConfigFactoryInterface $configFactory,
                              ProductCategoryTree $productCategoryTree,
                              EntityTypeManagerInterface $entityTypeManager,
                              ModuleHandlerInterface $moduleHandler,
                              AlshayaConfigManager $alshayaConfigManager
  ) {
    $this->configFactory = $configFactory;
    $this->productCategoryTree = $productCategoryTree;
    $this->entityTypeManager = $entityTypeManager;
    $this->moduleHandler = $moduleHandler;
    $this->alshayaConfigManager = $alshayaConfigManager;
  }

  /**
   * Enable or disable super category feature.
   *
   * @param array $options
   *   List of options supported by drush command.
   *
   * @throws UserAbortException
   *
   * @command alshaya_super_category:switch
   *
   * @option default_parent Default parent term id to render main menu
   *
   * @aliases alshaya-super-category-switch
   */
  public function enableDisableSuperCategory(
    array $options = ['default_parent' => 0]
  ) {
    $config = $this->configFactory->getEditable('alshaya_super_category.settings');

    $status = $config->get('status') ? FALSE : TRUE;
    $action = $status ? 'enable' : 'disable';

    $msg = dt('Are you sure you want to !action super category feature?', ['!action' => $action]);

    // Check path alter status to display message and trigger bulk alias
    // generate.
    $path_alter = $config->get('product_path_alter', TRUE);
    if ($path_alter && 'enable' == $action) {
      $msg = dt('Are you sure you want to !action super category feature and do bulk update on product aliases', ['!action' => $action]);
    }

    if (!$this->io()->confirm($msg)) {
      throw new UserAbortException();
    }

    // @todo: Validate the given default_parent exists and from the appropriate
    // vocabulary and is rootTerm.
    // Determine which default parent to use for main menu.
    $default_parent = $options['default_parent'];

    // Update alshaya_super_category.settings.
    $config->set('status', $status);
    if ($status) {
      if (empty($default_parent)) {
        $terms = $this->productCategoryTree->getCategoryRootTerms();
        $default_parent = key($terms);
      }
      $config->set('default_category_tid', $default_parent);
    }
    $config->save(TRUE);

    // Load super category menu block and change status.
    $blocks = $this->entityTypeManager->getStorage('block')->loadByProperties(['id' => 'supercategorymenu']);
    $block = reset($blocks);
    $block->setStatus($status);
    $block->save();
    if ($path_alter) {
      $this->productAliasBulkProcess();
    }

    $this->moduleHandler->invokeAll('alshaya_super_category_status_update', [
      $status,
      $default_parent,
      $path_alter,
    ]);

    // Update the meta title.
    if ($status) {
      $this->configFactory->getEditable('metatag.metatag_defaults.node__advanced_page')->save();
      $this->configFactory->getEditable('metatag.metatag_defaults.node__acq_product')->save();
      $this->configFactory->getEditable('metatag.metatag_defaults.node__acq_promotion')->save();
      $this->configFactory->getEditable('metatag.metatag_defaults.taxonomy_term__acq_product_category')->save();
    }
    else {
      $meta_configs = [
        'metatag.metatag_defaults.node__advanced_page',
        'metatag.metatag_defaults.node__acq_product',
        'metatag.metatag_defaults.node__acq_promotion',
        'metatag.metatag_defaults.taxonomy_term__acq_product_category',
      ];
      if ($this->moduleHandler->moduleExists('alshaya_seo_transac')) {
        // When super category feature is disabled, we just reset the tokens
        // from the module config YML.
        foreach ($meta_configs as $meta_config) {
          $config_data = $this->alshayaConfigManager->getDataFromCode($meta_config, 'alshaya_seo_transac', 'install');
          $this->configFactory->getEditable($meta_config)
            ->set('tags.title', $config_data['tags']['title'])->save();
        }
      }
    }

    // Clear cache to rebuild menu blocks.
    drupal_flush_all_caches();
    $this->output->writeln(dt('Successfully !action super category feature.', ['!action' => $action . 'd']));
  }

  /**
   * Enable or disable product alias alter based on super category status.
   *
   * @command alshaya_super_category:product-alias
   *
   * @aliases alshaya-super-category-product-alias
   */
  public function generateProductAlias() {
    $config = $this->configFactory->getEditable('alshaya_super_category.settings');
    $status = $config->get('product_path_alter') ? FALSE : TRUE;
    $action = $status ? 'true' : 'false';

    if (!$this->io()->confirm(dt('Are you sure you want to set product alias alter to !value, for super category feature?', ['!value' => $action]))) {
      throw new UserAbortException();
    }

    $config->set('product_path_alter', $status)->save();
    $this->productAliasBulkProcess();
  }

  /**
   * Product url alias bulk process.
   */
  public function productAliasBulkProcess() {
    $this->output->writeln(dt('Generating product aliases, please wait...'));

    // Set batch operation.
    $batch = [
      'title' => $this->t('Bulk updating product URL aliases'),
      'init_message' => $this->t('Product alias generating...'),
      'operations' => [
        ['\Drupal\pathauto\Form\PathautoBulkUpdateForm::batchStart', []],
        ['\Drupal\pathauto\Form\PathautoBulkUpdateForm::batchProcess',
          ['canonical_entities:node', 'all'],
        ],
      ],
      'progress_message' => $this->t('Processed @current out of @total.'),
      'error_message' => $this->t('Synced data could not be cleaned because an error occurred.'),
      // Drush doesn't support static method call for "finished".
      'finished' => '\Drupal\alshaya_super_category\Commands\AlshayaSuperCategoryCommands::productBulkAliasFinished',
    ];

    batch_set($batch);
    drush_backend_batch_process();
  }

  /**
   * Batch finished callback.
   *
   * @param bool $success
   *   Whether the batch job was successful.
   * @param \Drupal\security_review\CheckResult[] $results
   *   The results of the batch job.
   * @param array $operations
   *   The array of batch operations.
   */
  public static function productBulkAliasFinished($success, array $results, array $operations) {
    if ($success) {
      if ($results['updates']) {
        \Drupal::logger('alshaya_super_category')->info(\Drupal::translation()->formatPlural($results['updates'], 'Generated 1 URL alias.', 'Generated @count URL aliases.'));
      }
      else {
        \Drupal::logger('alshaya_super_category')->info(dt('No new URL aliases to generate.'));
      }
    }
    else {
      $error_operation = reset($operations);
      \Drupal::logger('alshaya_super_category')->error(dt('An error occurred while processing @operation with arguments : @args', [
        '@operation' => $error_operation[0],
        '@args' => print_r($error_operation[0], TRUE),
      ]));
    }
  }

}
