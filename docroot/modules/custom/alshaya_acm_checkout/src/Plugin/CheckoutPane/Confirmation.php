<?php

namespace Drupal\alshaya_acm_checkout\Plugin\CheckoutPane;

use Drupal\acq_checkout\Plugin\CheckoutPane\CheckoutPaneBase;
use Drupal\acq_checkout\Plugin\CheckoutPane\CheckoutPaneInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

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
    \Drupal::moduleHandler()->loadInclude('alshaya_acm_customer', 'inc', 'alshaya_acm_customer.orders');
    \Drupal::moduleHandler()->loadInclude('alshaya_acm_product', 'inc', 'alshaya_acm_product.utility');

    $temp_store = \Drupal::service('user.private_tempstore')->get('alshaya_acm_checkout');
    $order_data = $temp_store->get('order');

    // Throw access denied if nothing in session.
    if (empty($order_data) || empty($order_data['id'])) {
      throw new AccessDeniedHttpException();
    }

    // @TODO: Remove the fix when we get the full order details.
    $order_id = str_replace('"', '', $order_data['id']);

    if (\Drupal::currentUser()->isAnonymous()) {
      $email = $temp_store->get('email');
    }
    else {
      $email = \Drupal::currentUser()->getEmail();
    }

    $orders = alshaya_acm_customer_get_user_orders($email);

    $order_index = array_search($order_id, array_column($orders, 'order_id'));

    if ($order_index === FALSE) {
      throw new NotFoundHttpException();
    }

    $order = $orders[$order_index];

    // Build account details array.
    $account = [];

    if (\Drupal::currentUser()->isAnonymous()) {
      // @TODO: Get privilege card number once integration done.
      $account['first_name'] = $order['firstname'];
      $account['last_name'] = $order['lastname'];
      $account['mail'] = $order['email'];

      $print_link = Url::fromRoute('alshaya_acm_customer.print_last_order');
    }
    else {
      $user = \Drupal::currentUser();
      $account['first_name'] = $user->get('field_first_name')->getString();
      $account['last_name'] = $user->get('field_last_name')->getString();
      $account['mail'] = $user->getEmail();
      $account['privilege_card_number'] = $user->get('field_privilege_card_number')->getString();

      $print_link = Url::fromRoute('alshaya_acm_customer.orders_print', ['user' => $user->id(), 'order_id' => $order['increment_id']]);
    }

    $build = alshaya_acm_customer_build_order_detail($order);
    $build['#account'] = $account;
    $build['#barcode'] = alshaya_acm_customer_get_barcode($order);
    $build['#print_link'] = $print_link;
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
