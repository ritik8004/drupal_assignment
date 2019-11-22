<?php

namespace Drupal\alshaya_acm_knet;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderInterface;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Registers services in the container.
 */
class AlshayaAcmKnetServiceProvider implements ServiceProviderInterface {

  /**
   * {@inheritdoc}
   */
  public function register(ContainerBuilder $container) {
    $modules = $container->getParameter('container.modules');
    // Register service in the container if `alshaya_knet` module is enabled.
    if (isset($modules['alshaya_knet'])) {
      $container->register('alshaya_acm_knet.helper', AlshayaAcmKnetHelper::class)
        ->setDecoratedService('alshaya_knet.helper', 'alshaya_acm_knet.helper.inner')
        ->addArgument(new Reference('alshaya_acm_knet.helper.inner'))
        ->addArgument(new Reference('config.factory'))
        ->addArgument(new Reference('tempstore.shared'))
        ->addArgument(new Reference('logger.channel.alshaya_acm_knet'))
        ->addArgument(new Reference('acq_commerce.api'))
        ->addArgument(new Reference('alshaya_api.api'))
        ->addArgument(new Reference('acq_cart.cart_storage'))
        ->addArgument(new Reference('alshaya_acm_checkout.checkout_helper'))
        ->addArgument(new Reference('alshaya_acm.cart_helper'))
        ->addArgument(new Reference('module_handler'));
    }
  }

}
