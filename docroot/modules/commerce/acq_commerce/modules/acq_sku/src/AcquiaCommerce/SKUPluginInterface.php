<?php

/**
 * @file
 * Contains \Drupal\acq_sku\AcquiaCommerce\SKUPluginInterface.
 */

namespace Drupal\acq_sku\AcquiaCommerce;

use Drupal\Core\Form\FormStateInterface;
use Drupal\acq_sku\Entity\SKU;

/**
 * Defines the required interface to create a SKU Type plugin.
 */
interface SKUPluginInterface {
  /**
   * Builds and returns the renderable array for this SKU Type plugin.
   *
   * @param array $form
   *   Drupal's initial render array for this array.
   *
   * @return array
   *   A renderable array representing the content of the SKU.
   */
  public function build($build);

  /**
   * Returns the form elements for adding this SKU Type to the cart.
   *
   * @param array $form
   *   The form definition array for the add to cart form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array $form
   *   The renderable form array representing the entire add to cart form.
   */
  public function addToCartForm($form, FormStateInterface $form_state, SKU $sku = NULL);

  /**
   * Adds validation for the add to cart form.
   *
   * @param array $form
   *   The form definition array for the full add to cart form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @see \Drupal\acq_sku\AcquiaCommerce\SKUPluginInterface::addToCartForm()
   * @see \Drupal\acq_sku\AcquiaCommerce\SKUPluginInterface::addToCartSubmit()
   */
  public function addToCartValidate(&$form, FormStateInterface $form_state);

  /**
   * Adds submission handling for the add to cart form.
   *
   * @param array $form
   *   The form definition array for the full add to cart form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @see \Drupal\acq_sku\AcquiaCommerce\SKUPluginInterface::addToCartForm()
   * @see \Drupal\acq_sku\AcquiaCommerce\SKUPluginInterface::addToCartValidate()
   */
  public function addToCartSubmit(&$form, FormStateInterface $form_state);

  /**
   * @param $sku - SKU to update
   * @param $product - Product array from the API
   * @return NULL
   */
  public function processImport($sku, $product);

  /**
   * Returns the SKUs cart name.
   *
   * @param $sku - SKU to get Cart Name
   * @param $cart - Item array from cart
   * @return string - Name
   */
  public function cartName($sku, $cart);

  /**
   * Returns the SKU's display node.
   *
   * @param \Drupal\acq_sku\Entity\SKU $sku
   * @return \Drupal\node\Entity\Node|null
   */
  public function getDisplayNode(SKU $sku);
}