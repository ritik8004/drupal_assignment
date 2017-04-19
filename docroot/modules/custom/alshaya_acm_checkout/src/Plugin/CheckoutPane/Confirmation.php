<?php

namespace Drupal\alshaya_acm_checkout\Plugin\CheckoutPane;

use Drupal\acq_checkout\Plugin\CheckoutPane\CheckoutPaneBase;
use Drupal\acq_checkout\Plugin\CheckoutPane\CheckoutPaneInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\image\Entity\ImageStyle;

/**
 * Provides the final confirmation post payment.
 *
 * @ACQCheckoutPane(
 *   id = "confirmation",
 *   label = @Translation("Confirmation"),
 *   default_step = "confirmation",
 *   wrapper_element = "container",
 * )
 */
class Confirmation extends CheckoutPaneBase implements CheckoutPaneInterface {

  /**
   * {@inheritdoc}
   */
  public function isVisible() {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'weight' => 1,
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildPaneForm(array $pane_form, FormStateInterface $form_state, array &$complete_form) {
    \Drupal::moduleHandler()->loadInclude('alshaya_acm_customer', 'inc', 'alshaya_acm_customer.orders');
    \Drupal::moduleHandler()->loadInclude('alshaya_acm_product', 'inc', 'alshaya_acm_product.utility');

    $temp_store = \Drupal::service('user.private_tempstore')->get('alshaya_acm_checkout');
    $order_data = $temp_store->get('order');

    // @TODO: Remove the fix when we get the full order details.
    $order_id = str_replace('"', '', $order_data['id']);
    $order_id = str_pad($order_id, 9, '0', STR_PAD_LEFT);

    if (\Drupal::currentUser()->isAnonymous()) {
      $email = $temp_store->get('email');
    }
    else {
      $email = \Drupal::currentUser()->getEmail();
    }

    $orders = alshaya_acm_customer_get_user_orders($email);

    $order_index = array_search($order_id, array_column($orders, 'increment_id'));

    if ($order_index === FALSE) {
      throw new NotFoundHttpException();
    }

    $order = $orders[$order_index];

    $products = [];
    foreach ($order['items'] as $item) {
      $product = $item;
      $product['total'] = number_format($item['ordered'] * $item['price'], 3);

      try {
        // Check if we can find a parent SKU for this.
        $parentSku = alshaya_acm_product_get_parent_sku_by_sku($item['sku']);

        // We will use the parent SKU name for display.
        $product['name'] = $parentSku->label();

        // Try to find attributes to display for this product.
        $product['attributes'] = alshaya_acm_product_get_sku_configurable_values($item['sku']);
      }
      catch (\Exception $e) {
        // Current SKU seems to be a simple one, we don't need to do anything.
      }

      $product['image'] = '';

      // Load sku from item_id that we have in $item.
      $media = alshaya_acm_product_get_sku_media($item['sku']);

      // If we have image for the product.
      if (!empty($media)) {
        $image = array_shift($media);
        $file_uri = $image->getFileUri();
        $product['image'] = ImageStyle::load('checkout_summary_block_thumbnail')->buildUrl($file_uri);
      }

      $products[] = $product;
    }

    $build = [];
    $build['#order'] = alshaya_acm_customer_get_processed_order_summary($order);
    $build['#order_details'] = alshaya_acm_customer_get_processed_order_details($order);
    $build['#products'] = $products;
    // @TODO: MMCPA-641.
    $build['#delivery_detail_notice'] = $this->t('Your order will be delivered between 1 and 3 days');
    $build['#currency_code'] = \Drupal::config('acq_commerce.currency')->get('currency_code');
    $build['#currency_code_position'] = \Drupal::config('acq_commerce.currency')->get('currency_code_position');
    $build['#theme'] = 'user_order_detail';

    $pane_form['summary'] = $build;

    // Create a new cart now.
    $this->cartStorage->createCart();

    return $pane_form;
  }

}
