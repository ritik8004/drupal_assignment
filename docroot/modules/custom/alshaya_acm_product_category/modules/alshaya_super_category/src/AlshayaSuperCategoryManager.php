<?php

namespace Drupal\alshaya_super_category;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\alshaya_config\AlshayaConfigManager;
use Drupal\alshaya_acm_product_category\ProductCategoryTreeInterface;

/**
 * Class AlshayaSuperCategoryManager.
 */
class AlshayaSuperCategoryManager {

  /**
   * Product category tree.
   *
   * @var \Drupal\alshaya_acm_product_category\ProductCategoryTreeInterface
   */
  protected $productCategoryTree;

  /**
   * Config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Alshaya config manager.
   *
   * @var \Drupal\alshaya_config\AlshayaConfigManager
   */
  protected $alshayaConfigManager;

  /**
   * Module Handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * AlshayaSuperCategoryManager constructor.
   *
   * @param \Drupal\alshaya_acm_product_category\ProductCategoryTreeInterface $product_category_tree
   *   Product category tree.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config factory.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   Module handler.
   * @param \Drupal\alshaya_config\AlshayaConfigManager $alshaya_config_manager
   *   Alshaya config manager.
   */
  public function __construct(ProductCategoryTreeInterface $product_category_tree, ConfigFactoryInterface $config_factory, EntityTypeManagerInterface $entity_type_manager, ModuleHandlerInterface $module_handler, AlshayaConfigManager $alshaya_config_manager) {
    $this->productCategoryTree = $product_category_tree;
    $this->configFactory = $config_factory;
    $this->entityTypeManager = $entity_type_manager;
    $this->moduleHandler = $module_handler;
    $this->alshayaConfigManager = $alshaya_config_manager;
  }

  /**
   * Enable/Disable super category feature.
   *
   * @param bool $enable
   *   Super category status.
   * @param int $default_parent
   *   Default parent.
   */
  public function enableSuperCategory(bool $enable = TRUE, int $default_parent = 0) {
    $config = $this->configFactory->getEditable('alshaya_super_category.settings');
    $config->set('status', $enable);

    if ($enable) {
      if (empty($default_parent)) {
        $terms = $this->productCategoryTree->getCategoryRootTerms();
        $default_parent = key($terms);
      }
      $config->set('default_category_tid', $default_parent);
    }

    $config->save(TRUE);
  }

  /**
   * Enable/Disable the super category block.
   *
   * @param bool $status
   *   Super category status.
   */
  public function changeSuperCategoryMenuBlockStatus(bool $status = TRUE) {
    $this->configFactory->getEditable('block.block.supercategorymenu')->set('status', $status)->save();
  }

  /**
   * Change the meta title for the super category.
   *
   * @param bool $status
   *   Super category status.
   */
  public function changeSuperCategoryMetaTitle(bool $status = TRUE) {
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
          $this->configFactory->getEditable($meta_config)->set('tags.title', $config_data['tags']['title'])->save();
        }
      }
    }
  }

}
