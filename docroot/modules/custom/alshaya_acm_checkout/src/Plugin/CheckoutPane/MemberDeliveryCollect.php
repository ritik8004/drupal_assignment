<?php

namespace Drupal\alshaya_acm_checkout\Plugin\CheckoutPane;

use Drupal\acq_checkout\Plugin\CheckoutPane\CheckoutPaneBase;
use Drupal\acq_checkout\Plugin\CheckoutPane\CheckoutPaneInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides the delivery CnC pane for members.
 *
 * @ACQCheckoutPane(
 *   id = "member_delivery_collect",
 *   label = @Translation("Click and Collect"),
 *   defaultStep = "delivery",
 *   wrapperElement = "fieldset",
 * )
 */
class MemberDeliveryCollect extends CheckoutPaneBase implements CheckoutPaneInterface {

  /**
   * {@inheritdoc}
   */
  public function isVisible() {
    return \Drupal::currentUser()->isAuthenticated();
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'weight' => 2,
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildPaneForm(array $pane_form, FormStateInterface $form_state, array &$complete_form) {
    $cart = $this->getCart();

    // Get the stores from Magento.
    $api_wrapper = \Drupal::service('alshaya_api.api');
    $stores = $api_wrapper->getCartStores($cart->id());

    // Add missing information to store data.
    array_walk($stores, function (&$store) {
      $nodes = \Drupal::entityTypeManager()
        ->getStorage('node')
        ->loadByProperties(['type' => 'store', 'field_store_locator_id' => $store['code']]);

      if ($node = reset($nodes)) {
        $store['address'] = $node->get('field_store_address')->getString();
        $store['open_hours'] = $node->get('field_store_open_hours')->getString();
        $store['title'] = $node->get('title')->getString();
      }
    });

    $pane_form['guest_delivery_collect']['text_filter'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Find your closest collection point'),
      '#prefix' => '<div class="label-store-location">' . $this->t('Find your closest collection point') . '</div>',
    ];

    $pane_form['guest_delivery_collect']['toggle_list_view'] = [
      '#markup' => $this->t('List view'),
    ];

    $pane_form['guest_delivery_collect']['toggle_map_view'] = [
      '#markup' => $this->t('Map view'),
    ];

    $pane_form['guest_delivery_collect']['list_view'] = [
      '#type' => 'container',
    ];

    $pane_form['guest_delivery_collect']['list_view']['content'] = [
      '#theme' => 'click_collect_stores_list',
      '#title' => $this->t('Available at @count stores', ['@count' => count($stores)]),
      '#stores' => $stores,
    ];

    $pane_form['guest_delivery_collect']['map_view'] = [
      '#type' => 'container',
    ];

    $pane_form['guest_delivery_collect']['map_view']['content'] = [
      '#markup' => $this->t('Map view'),
    ];

    return $pane_form;
  }

}
