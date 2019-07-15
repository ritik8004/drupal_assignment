<?php

namespace Drupal\alshaya_acm_checkout\Plugin\CheckoutPane;

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
    $request = \Drupal::request();

    $cart = $this->getCart();
    $cart->setExtension('customer_id', $cart->customerId());

    // GA / Tracking id added into form via javascript.
    $cart->setExtension('ga_client_id', $form_state->getValue('ga_client_id'));
    $cart->setExtension('tracking_id', $form_state->getValue('tracking_id'));

    // Add user agent from request headers, it won't be cached ever as it is
    // POST request.
    $cart->setExtension('user_agent', $request->headers->get('User-Agent', ''));

    // Use the last ip from client ips.
    if (isset($_ENV['AH_CLIENT_IP'])) {
      $ip = $_ENV['AH_CLIENT_IP'];
    }
    else {
      $ips = $request->getClientIps();
      $ip = end($ips);
    }
    $cart->setExtension('client_ip', $ip);
  }

}
