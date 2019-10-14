<?php

namespace AlshayaMiddleware\Provider;

use AlshayaMiddleware\Magento\MagentoInfo;
use AlshayaMiddleware\Drupal\DrupalInfo;
use AlshayaMiddleware\Drupal\Drupal;
use AlshayaMiddleware\Magento\Cart;
use Pimple\ServiceProviderInterface;
use Pimple\Container;

/**
 * Class MiddleWareProvider.
 */
class MiddleWareProvider implements ServiceProviderInterface {

  /**
   * {@inheritdoc}
   */
  public function register(Container $app) {
    $app['magento'] = function (Container $app) {
      return new MagentoInfo();
    };

    $app['drupal_info'] = function (Container $app) {
      return new DrupalInfo();
    };

    $app['drupal'] = function (Container $app) {
      return new Drupal($app['drupal_info']);
    };

    $app['get_cart'] = function (Container $app) {
      return new Cart($app['magento']);
    };
  }

}
