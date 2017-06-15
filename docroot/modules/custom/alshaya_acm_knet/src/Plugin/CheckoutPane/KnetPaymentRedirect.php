<?php

namespace Drupal\alshaya_acm_knet\Plugin\CheckoutPane;

use Drupal\acq_checkout\Plugin\CheckoutPane\CheckoutPaneBase;
use Drupal\acq_checkout\Plugin\CheckoutPane\CheckoutPaneInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\alshaya_acm_knet\E24PaymentPipe;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Provides the final confirmation post payment.
 *
 * @ACQCheckoutPane(
 *   id = "knet_payment_redirect",
 *   label = @Translation("Knet Payment Redirect"),
 *   defaultStep = "confirmation",
 *   wrapperElement = "container",
 * )
 */
class KnetPaymentRedirect extends CheckoutPaneBase implements CheckoutPaneInterface {

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
      'weight' => -100,
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildPaneForm(array $pane_form, FormStateInterface $form_state, array &$complete_form) {
    $order = _alshaya_acm_checkout_get_last_order_from_session();

    $temp_store = \Drupal::service('user.private_tempstore')->get('alshaya_acm_checkout');
    if (\Drupal::currentUser()->isAnonymous()) {
      $email = $temp_store->get('email');
    }
    else {
      $email = \Drupal::currentUser()->getEmail();
    }

    // No order found, we will handle errors in confirmation plugin.
    if (empty($order)) {
      return $pane_form;
    }

    $knet_settings = \Drupal::config('alshaya_acm_knet.settings');

    // We want to redirect to K-Net only if payment method is K-Net and payment
    // status is pending_payment.
    if ($order['payment']['method_code'] != 'knet' || $order['status'] != $knet_settings->get('payment_pending')) {
      return $pane_form;
    }

    $pipe = new E24PaymentPipe();

    $pipe->setCurrency(KNET_CURRENCY_KWD);
    $pipe->setLanguage(KNET_LANGUAGE_EN);

    \Drupal::logger('alshaya_acm_knet')->info(Url::fromRoute('alshaya_acm_knet.response', [], ['absolute' => TRUE, 'https' => FALSE])->toString());
    $pipe->setResponseUrl(Url::fromRoute('alshaya_acm_knet.response', [], ['absolute' => TRUE, 'https' => FALSE])->toString());
    $pipe->setErrorUrl(Url::fromRoute('alshaya_acm_knet.error', ['order_id' => $order['order_id']], ['absolute' => TRUE])->toString());

    $pipe->setAmt($order['totals']['grand']);

    // Set resource path.
    $pipe->setResourcePath($knet_settings->get('resource_path'));

    // Set your alias name here.
    $pipe->setAlias($knet_settings->get('alias'));

    $pipe->setTrackId($order['order_id']);

    $pipe->setUdf1(\Drupal::currentUser()->id());
    $pipe->setUdf2($order['customer_id']);
    $pipe->setUdf3($order['increment_id']);
    $pipe->setUdf4($email);

    // Get results.
    if ($pipe->performPaymentInitialization()) {
      \Drupal::logger('alshaya_acm_knet')->info('Payment id for order id @order_id is @payment_id', ['@order_id' => $order['increment_id'], '@payment_id' => $pipe->getPaymentId()]);
      $response = new RedirectResponse($pipe->getRedirectUrl());
      $response->send();
      exit;
    }
    else {
      \Drupal::logger('alshaya_acm_knet')->critical($pipe->getErrorMsg());
      die();
    }

    return $pane_form;
  }

}
