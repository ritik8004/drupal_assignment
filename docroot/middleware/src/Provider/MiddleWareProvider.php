<?php

namespace AlshayaMiddleware\Provider;

use AlshayaMiddleware\Magento\MagentoInfo;
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

    $app['get_cart'] = function (Container $app) {
      return new Cart($app['magento']);
    };
  }

}
