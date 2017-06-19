<?php

namespace Drupal\alshaya_acm_checkout\Controller;

use Drupal\acq_cart\CartStorageInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\profile\Entity\Profile;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides additional urls for checkout pages.
 */
class CheckoutController implements ContainerInjectionInterface {

  /**
   * The cart session.
   *
   * @var \Drupal\acq_cart\CartStorageInterface
   */
  protected $cartStorage;

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityManager;

  /**
   * Constructs a new CheckoutController object.
   *
   * @param \Drupal\acq_cart\CartStorageInterface $cart_storage
   *   The cart session.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_manager
   *   Entity type manager.
   */
  public function __construct(CartStorageInterface $cart_storage, EntityTypeManagerInterface $entity_manager) {
    $this->cartStorage = $cart_storage;
    $this->entityManager = $entity_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('acq_cart.cart_storage'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * Function to update cart with selected address.
   *
   * @param \Drupal\profile\Entity\Profile $profile
   *   Profile object.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   AjaxResponse object.
   */
  public function useAddress(Profile $profile) {
    $response = new AjaxResponse();

    $cart = $this->cartStorage->getCart();

    $address = [];

    // @TODO: Get address array from entity.
    $cart->setShipping($address);

    return $response;
  }

}
