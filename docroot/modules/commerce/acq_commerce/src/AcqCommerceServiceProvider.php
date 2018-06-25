<?php

namespace Drupal\acq_commerce;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceModifierInterface;
use Drupal\Core\DependencyInjection\ServiceProviderBase;

/**
 * Class AcqCommerceServiceProvider.
 */
class AcqCommerceServiceProvider extends ServiceProviderBase implements ServiceModifierInterface {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    try {
      // Add cart id cookie context to auto_placeholder_conditions.
      $renderer = $container->getParameter('renderer.config');
      $renderer['auto_placeholder_conditions']['contexts'][] = 'cookies:Drupal_visitor_acq_cart_id';
      $container->setParameter('renderer.config', $renderer);
    }
    catch (\Exception $e) {
      // Do nothing, system might still be installing.
    }
  }

}
