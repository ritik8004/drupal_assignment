<?php

namespace Drupal\alshaya_acm_checkout\Plugin\CheckoutPane;

use Drupal\acq_checkout\Plugin\CheckoutPane\CheckoutPaneBase;
use Drupal\acq_checkout\Plugin\CheckoutPane\CheckoutPaneInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\user\Entity\User;

/**
 * Provides the final confirmation post payment.
 *
 * @ACQCheckoutPane(
 *   id = "confirmation",
 *   label = @Translation("Confirmation"),
 *   defaultStep = "confirmation",
 *   wrapperElement = "container",
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
    $order = _alshaya_acm_checkout_get_last_order_from_session();

    if (empty($order)) {
      return $pane_form;
    }

    \Drupal::moduleHandler()->loadInclude('alshaya_acm_customer', 'inc', 'alshaya_acm_customer.orders');
    \Drupal::moduleHandler()->loadInclude('alshaya_acm_product', 'inc', 'alshaya_acm_product.utility');

    // Build account details array.
    $account = [];

    $account['first_name'] = $order['firstname'];
    $account['last_name'] = $order['lastname'];
    $account['mail'] = $order['email'];

    if (\Drupal::currentUser()->isAnonymous()) {
      $print_link = Url::fromRoute('alshaya_acm_customer.print_last_order');
    }
    else {
      $user = User::load(\Drupal::currentUser()->id());
      $account['privilege_card_number'] = $user->get('field_privilege_card_number')->getString();

      $print_link = Url::fromRoute('alshaya_acm_customer.orders_print', [
        'user' => $user->id(),
        'order_id' => $order['increment_id'],
      ]);
    }

    $build = alshaya_acm_customer_build_order_detail($order);

    $build['#account'] = $account;
    $build['#barcode'] = alshaya_acm_customer_get_barcode($order);
    $build['#print_link'] = $print_link;
    $build['#vat_text'] = \Drupal::config('alshaya_acm_product.settings')->get('vat_text');
    $build['#theme'] = 'checkout_order_detail';

    $pane_form['summary'] = $build;

    $pane_form['continue_shopping'] = [
      '#type' => 'link',
      '#title' => $this->t('Continue shopping'),
      '#url' => Url::fromRoute('<front>'),
    ];

    return $pane_form;
  }

}
