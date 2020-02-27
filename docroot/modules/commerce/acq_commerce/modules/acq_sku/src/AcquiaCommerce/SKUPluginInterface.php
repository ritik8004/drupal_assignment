<?php

namespace Drupal\acq_sku\AcquiaCommerce;

use Drupal\acq_commerce\SKUInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\acq_sku\Entity\SKU;

/**
 * Defines the required interface to create a SKU Type plugin.
 */
interface SKUPluginInterface {

  /**
   * Builds and returns the renderable array for this SKU Type plugin.
   *
   * @param array $build
   *   Drupal's initial render array for this array.
   *
   * @return array
   *   A renderable array representing the content of the SKU.
   */
  public function build(array $build);

  /**
   * Returns the form elements for adding this SKU Type to the cart.
   *
   * @param array $form
   *   The form definition array for the add to cart form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   * @param \Drupal\acq_sku\Entity\SKU $sku
   *   The object of product we want to add to cart.
   *
   * @return array
   *   The renderable form array representing the entire add to cart form.
   */
  public function addToCartForm(array $form, FormStateInterface $form_state, SKU $sku = NULL);

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
  public function addToCartValidate(array &$form, FormStateInterface $form_state);

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
  public function addToCartSubmit(array &$form, FormStateInterface $form_state);

  /**
   * Process import function.
   *
   * @param object $sku
   *   SKU to update.
   * @param array $product
   *   Product array from the API.
   */
  public function processImport($sku, array $product);

  /**
   * Returns the SKUs cart name.
   *
   * @param \Drupal\acq_sku\Entity\SKU $sku
   *   SKU to get Cart Name.
   * @param array $cart
   *   Item array from cart.
   * @param bool $asString
   *   Indicates if function should return a string or a renderable array.
   *
   * @return mixed
   *   Name as string or a renderable object
   */
  public function cartName(SKU $sku, array $cart, $asString = FALSE);

  /**
   * Returns the SKU's display node.
   *
   * @param \Drupal\acq_sku\Entity\SKU $sku
   *   The object of product.
   * @param bool $check_parent
   *   Flag to check for parent sku or not (for configurable products).
   * @param bool $create_translation
   *   Flag to create translation if node available and translation not
   *   available. Used during sync.
   *
   * @return \Drupal\node\Entity\Node|null
   *   Return object of Node or null if not found.
   */
  public function getDisplayNode(SKU $sku, $check_parent = TRUE, $create_translation = FALSE);

  /**
   * Returns the SKU's display node id.
   *
   * @param \Drupal\acq_sku\Entity\SKU $sku
   *   The object of product.
   * @param bool $check_parent
   *   Flag to check for parent sku or not (for configurable products).
   *
   * @return int|string|null
   *   Return node id if found or null.
   */
  public function getDisplayNodeId(SKU $sku, $check_parent = TRUE);

  /**
   * Check if product is in stock.
   *
   * @param \Drupal\acq_sku\Entity\SKU $sku
   *   SKU Entity.
   *
   * @return bool
   *   TRUE if product is in stock.
   */
  public function isProductInStock(SKU $sku);

  /**
   * Returns the stock for the given sku.
   *
   * @param string|\Drupal\acq_sku\Entity\SKU $sku
   *   SKU code of the product.
   *
   * @return int
   *   Available stock quantity.
   */
  public function getStock($sku);

  /**
   * Refresh stock for particular SKU.
   *
   * @param \Drupal\acq_sku\Entity\SKU $sku
   *   SKU Entity.
   */
  public function refreshStock(SKU $sku);

  /**
   * Get parent of current product.
   *
   * @param \Drupal\acq_sku\Entity\SKU $sku
   *   Current product.
   *
   * @return \Drupal\acq_sku\Entity\SKU|null
   *   First Parent sku entity if found or null.
   */
  public function getParentSku(SKU $sku);

  /**
   * Get all parent skus.
   *
   * @param string $sku
   *   SKU as string.
   *
   * @return array
   *   Array containing parent entity id as key and sku as value for all
   *   parent sku found for the given sku.
   */
  public function getAllParentIds(string $sku): array;

  /**
   * Get all available children ids.
   *
   * @param \Drupal\acq_commerce\SKUInterface $sku
   *   SKU as string.
   *
   * @return array
   *   Array containing child entity id as key and sku as value for all
   *   children found for the given sku.
   */
  public function getAvailableChildrenIds(SKUInterface $sku);

  /**
   * Returns the max sale qty for the given sku.
   *
   * @param string|\Drupal\acq_sku\Entity\SKU $sku
   *   SKU code of the product.
   *
   * @return int
   *   Available max sale quantity.
   */
  public function getMaxSaleQty(SKU $sku);

}
