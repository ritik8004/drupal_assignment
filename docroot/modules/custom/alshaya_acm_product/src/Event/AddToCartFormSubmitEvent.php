<?php

namespace Drupal\alshaya_acm_product\Event;

use Drupal\acq_commerce\SKUInterface;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Response;

/**
 * Event that is fired when a add to cart form is submitted.
 *
 * @package Drupal\alshaya_acm_product
 */
class AddToCartFormSubmitEvent extends Event {

  const EVENT_NAME = 'add_to_cart_form_submit';

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
   * AddToCartFormSubmitEvent constructor.
   *
   * @param \Drupal\acq_commerce\SKUInterface $entity
   *   SKU Entity.
   * @param \Symfony\Component\HttpFoundation\Response $response
   *   Response Object.
   */
  public function __construct(SKUInterface $entity, Response $response) {
    $this->sku = $entity;
    $this->response = $response;
  }

  /**
   * Return SKU Entity.
   *
   * @return \Drupal\acq_commerce\SKUInterface
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
