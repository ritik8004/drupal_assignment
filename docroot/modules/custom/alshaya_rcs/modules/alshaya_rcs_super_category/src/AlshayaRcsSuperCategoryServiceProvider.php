<?php

namespace Drupal\alshaya_rcs_super_category;

use Drupal\alshaya_rcs_super_category\Service\AlshayaRcsSuperCategoryManager;
use Drupal\alshaya_rcs_super_category\Service\RcsProductCategoryTree;
use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceModifierInterface;
use Drupal\Core\DependencyInjection\ServiceProviderBase;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Definition;

/**
 * Class Rcs Service Provider.
 */
class AlshayaRcsSuperCategoryServiceProvider extends ServiceProviderBase implements ServiceModifierInterface {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    // Override the ProductCategoryTree service.
    $defination = new Definition(RcsProductCategoryTree::class);
    $defination
      ->setArguments(
        [
          new Reference('request_stack'),
          new Reference('language_manager'),
          new Reference('entity_type.manager'),
          new Reference('cache.product_category_tree'),
        ]
      );
    $defination->setPublic(TRUE);
    $container->setDefinition('alshaya_super_category.product_category_tree', $defination);
    $container->setDefinition('alshaya_acm_product_category.product_category_tree', $defination);
  }

}
