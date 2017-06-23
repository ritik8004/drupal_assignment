<?php

namespace Drupal\alshaya_acm_checkout\Plugin\CheckoutPane;

use Drupal\acq_checkout\Plugin\CheckoutPane\CheckoutPaneBase;
use Drupal\acq_checkout\Plugin\CheckoutPane\CheckoutPaneInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\geolocation\GoogleMapsDisplayTrait;

/**
 * Provides the delivery CnC pane for guests.
 *
 * @ACQCheckoutPane(
 *   id = "guest_delivery_collect",
 *   label = @Translation("<h2>Click & Collect</h2><p>Collect your order in-store</p>"),
 *   defaultStep = "delivery",
 *   wrapperElement = "fieldset",
 * )
 */
class GuestDeliveryCollect extends CheckoutPaneBase implements CheckoutPaneInterface {
  // Add trait to get map url from getGoogleMapsApiUrl().
  use GoogleMapsDisplayTrait;

  /**
   * {@inheritdoc}
   */
  public function isVisible() {
    return \Drupal::currentUser()->isAnonymous();
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
    if (\Drupal::currentUser()->isAuthenticated()) {
      return $pane_form;
    }

    $cart = $this->getCart();

    $pane_form['store_finder'] = [
      '#type' => 'container',
      '#title' => t('store finder'),
      '#tree' => FALSE,
    ];

    $pane_form['store_finder']['store_location'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Find your closest collection point'),
    ];

    $pane_form['store_finder']['toggle_list_view'] = [
      '#markup' => $this->t('List view'),
    ];

    $pane_form['store_finder']['toggle_map_view'] = [
      '#markup' => $this->t('Map view'),
    ];

    $pane_form['store_finder']['list_view'] = [
      '#type' => 'container',
      '#id' => 'click-and-collect-list-view',
    ];

    $pane_form['store_finder']['map_view'] = [
      '#type' => 'container',
    ];

    $pane_form['store_finder']['map_view']['content'] = [
      '#markup' => $this->t('Map view'),
    ];

    $pane_form['selected_store'] = [
      '#type' => 'container',
      '#title' => t('selected store'),
      '#tree' => FALSE,
    ];

    $pane_form['selected_store']['content'] = [
      '#markup' => '<div id="selected-store-wrapper" class="selected-store-wrapper"></div>',
    ];
    $pane_form['#attached'] = [
      'drupalSettings' => [
        'geolocation' => [
          'google_map_url' => $this->getGoogleMapsApiUrl(),
        ],
        'alshaya_acm_checkout' => ['cart_id' => $cart->id()],
      ],
      'library' => [
        'alshaya_acm_checkout/click-and-collect',
      ],
    ];

    return $pane_form;
  }

}
