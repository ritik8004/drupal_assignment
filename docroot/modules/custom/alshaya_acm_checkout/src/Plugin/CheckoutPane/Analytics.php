<?php

namespace Drupal\alshaya_acm_checkout\Plugin\CheckoutPane;

use Drupal\acq_cart\CartInterface;
use Drupal\acq_checkout\Plugin\CheckoutPane\CheckoutPaneBase;
use Drupal\acq_checkout\Plugin\CheckoutPane\CheckoutPaneInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides the contact information pane.
 *
 * @ACQCheckoutPane(
 *   id = "acm_analytics",
 *   label = @Translation("Analytics"),
 *   defaultStep = "payment",
 *   wrapperElement = "container",
 * )
 */
class Analytics extends CheckoutPaneBase implements CheckoutPaneInterface {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'weight' => -1,
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildPaneSummary() {
    return '';
  }

  /**
   * {@inheritdoc}
   */
  public function buildPaneForm(array $pane_form, FormStateInterface $form_state, array &$complete_form) {
    $pane_form['#attached']['library'][] = 'alshaya_acm_checkout/analytics';

    $pane_form['ga_client_id'] = [
      '#type' => 'hidden',
      '#tree' => FALSE,
      '#attributes' => [
        'id' => ['acm-ga-client-id'],
      ],
    ];

    $pane_form['tracking_id'] = [
      '#type' => 'hidden',
      '#tree' => FALSE,
      '#attributes' => [
        'id' => ['acm-ga-tracking-id'],
      ],
    ];

    return $pane_form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitPaneForm(array &$pane_form, FormStateInterface $form_state, array &$complete_form) {
    self::addAnalyticsInfoToCart(
      $this->getCart(),
      $form_state->getValue('ga_client_id'),
      $form_state->getValue('tracking_id')
    );
  }

  /**
   * Add analytics data to cart.
   *
   * @param \Drupal\acq_cart\CartInterface $cart
   *   Cart object.
   * @param string $client_id
   *   Client ID.
   * @param string $tracking_id
   *   Tracking ID.
   */
  public static function addAnalyticsInfoToCart(CartInterface $cart, $client_id, $tracking_id) {
    $request = \Drupal::request();

    $cart->setExtension('user_id', $cart->customerId());
    $cart->setExtension('user_type', \Drupal::currentUser()->id() ? 'Logged in User' : 'Guest User');

    // GA / Tracking id added into form via javascript.
    $cart->setExtension('ga_client_id', $client_id);
    $cart->setExtension('tracking_id', $tracking_id);

    // Add user agent from request headers, it won't be cached ever as it is
    // POST request.
    $cart->setExtension('user_agent', $request->headers->get('User-Agent', ''));

    // Use the IP address from Acquia Cloud ENV variable.
    $cart->setExtension('client_ip', $_ENV['AH_CLIENT_IP'] ?? '');
  }

}
