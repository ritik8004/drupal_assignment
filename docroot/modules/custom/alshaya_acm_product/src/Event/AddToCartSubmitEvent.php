<?php

namespace Drupal\alshaya_acm_product\Event;

use Drupal\Core\Entity\EntityInterface;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Response;

/**
 * Event that is fired when a add to cart is submitted.
 *
 * @package Drupal\alshaya_acm_product
 */
class AddToCartSubmitEvent extends Event {

  const EVENT_NAME = 'aad_to_cart_submit';

  /**
   * SKU Entity.
   *
   * @var \Drupal\acq_sku\Entity\SKU
   */
  private $sku;

  /**
   * Generated Response.
   *
   * @var \Symfony\Component\HttpFoundation\Response
   */
  private $response;

  /**
   * AddToCartSubmitEvent constructor.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   SKU Entity.
   * @param \Symfony\Component\HttpFoundation\Response $response
   *   Response Object.
   */
  public function __construct(EntityInterface $entity, Response $response) {
    $this->sku = $entity;
    $this->response = $response;
  }

  /**
   * Return SKU Entity.
   *
   * @return \Drupal\acq_sku\Entity\SKU|EntityInterface
   *   SKU Entity.
   */
  public function getSku() {
    return $this->sku;
  }

  /**
   * Return Response Data.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   Response Data.
   */
  public function getResponse() {
    return $this->response;
  }

}
