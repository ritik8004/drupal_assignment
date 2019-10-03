<?php

/**
 * @file
 * Middleware entry point.
 */

use AlshayaMiddleware\Provider\MiddleWareProvider;
use AlshayaMiddleware\Controller\CartController;
use Silex\Application;
use Silex\Provider\ServiceControllerServiceProvider;
use Symfony\Component\HttpFoundation\Request;

require 'vendor/autoload.php';

$app = new Application();

// For the post data.
$app->before(function (Request $request) {
  $method = $request->getMethod();
  /* POST, PUT, PATCH */
  if (in_array($method, [Request::METHOD_POST])) {
    if (0 === strpos($request->headers->get('Content-Type'), 'application/json')) {
      $data = json_decode($request->getContent(), TRUE);
      $request->request->replace(is_array($data) ? $data : []);
    }
  }
});

// Registering the app provider for injecting custom DIs.
$app->register(new MiddleWareProvider());

$app->register(new ServiceControllerServiceProvider());

$app['cart.controller'] = function () use ($app) {
  return new CartController($app['magento'], $app['get_cart']);
};

$app->get('/cart/{cart_id}', 'cart.controller:getCart');

$app->post('/update-cart', 'cart.controller:updateCart');

$app->run();
