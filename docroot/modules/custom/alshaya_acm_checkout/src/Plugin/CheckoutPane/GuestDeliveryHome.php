<?php

namespace Drupal\alshaya_acm_checkout\Plugin\CheckoutPane;

use Drupal\acq_checkout\Plugin\CheckoutPane\CheckoutPaneBase;
use Drupal\acq_checkout\Plugin\CheckoutPane\CheckoutPaneInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides the delivery home pane for guests.
 *
 * @ACQCheckoutPane(
 *   id = "guest_delivery_home",
 *   label = @Translation("Home delivery"),
 *   default_step = "delivery",
 *   wrapper_element = "fieldset",
 * )
 */
class GuestDeliveryHome extends CheckoutPaneBase implements CheckoutPaneInterface {

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
      'weight' => 1,
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildPaneForm(array $pane_form, FormStateInterface $form_state, array &$complete_form) {
    $pane_form['guest_delivery_home']['summary'] = [
      '#markup' => 'Home delivery with address form will come here',
    ];

    $pane_form['guest_delivery_home']['next'] = [
      '#type' => 'submit',
      '#value' => $this->t('deliver to this address'),
    ];

    return $pane_form;
  }

}
