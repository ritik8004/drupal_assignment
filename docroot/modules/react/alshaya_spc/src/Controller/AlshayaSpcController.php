<?php

namespace Drupal\alshaya_spc\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\alshaya_spc\Wrapper\AlshayaSpcApiWrapper;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class AlshayaSpcController.
 */
class AlshayaSpcController extends ControllerBase {

  /**
   * Serializer.
   *
   * @var \Symfony\Component\Serializer\SerializerInterface
   */
  protected $serializer;

  /**
   * Alshaya spc api wrapper.
   *
   * @var \Drupal\alshaya_spc\Wrapper\AlshayaSpcApiWrapper
   */
  protected $spcApiWrapper;

  /**
   * AlshayaSpcController constructor.
   *
   * @param \Symfony\Component\Serializer\SerializerInterface $serializer
   *   Serializer.
   * @param \Drupal\alshaya_spc\Wrapper\AlshayaSpcApiWrapper $spc_api_wrapper
   *   Alshaya spc api wrapper.
   */
  public function __construct(SerializerInterface $serializer,
                              AlshayaSpcApiWrapper $spc_api_wrapper) {
    $this->serializer = $serializer;
    $this->spcApiWrapper = $spc_api_wrapper;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('serializer'),
      $container->get('alshaya_spc.api_wrapper')
    );
  }

  /**
   * API to get cart info.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   Request object.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse|mixed
   *   Response data.
   */
  public function getCart(Request $request) {
    try {
      $cart_id = $request->cookies->get('Drupal_visitor_acq_cart_id');

      // If no cart id available, don't process further.
      if (!$cart_id) {
        return new JsonResponse([]);
      }

      $cart = $this->spcApiWrapper->getCart($cart_id);
      $cart = $this->spcApiWrapper->prepareCartResponse($cart);
    }
    catch (\Exception $e) {
      // Exception handling here.
    }

    return new JsonResponse($cart);
  }

}
