<?php

namespace Drupal\alshaya_acm_checkout;

use Drupal\acq_commerce\Response\NeedsRedirectException;
use Drupal\Core\Url;

/**
 * Trait Checkout Login Tabs Trait.
 *
 * @package Drupal\alshaya_acm_checkout
 *
 * @ingroup alshaya_acm_checkout
 */
trait CheckoutLoginTabsTrait {

  /**
   * Selected tab.
   *
   * @var string
   */
  protected static $selected;

  /**
   * Function to check if parameter in query is available of not.
   *
   * @return bool
   *   True if method available in request param and has value.
   */
  protected function isTabParamAvailable() {
    return (bool) \Drupal::request()->get('tab');
  }

  /**
   * Function to get selected tab code.
   *
   * @return mixed|string
   *   Selected tab code.
   *
   * @throws \Drupal\acq_commerce\Response\NeedsRedirectException
   */
  protected function getSelectedTab() {
    if (!isset(self::$selected)) {
      // Check if we have selected tab in query.
      $tab = \Drupal::request()->get('tab');

      $allowed_tabs = ['login'];

      // We tab is not allowed (someone trying to trick the system), redirect
      // to default or cart tab.
      if ($tab && !in_array($tab, $allowed_tabs)) {
        $redirect_url = Url::fromRoute(
          'acq_checkout.form', ['step' => 'login']
        );

        throw new NeedsRedirectException($redirect_url->toString());
      }

      self::$selected = $tab;
    }

    return self::$selected;
  }

  /**
   * Function to get class for selected delivery method as string.
   *
   * @return string
   *   Return class for selected delivery method.
   */
  protected function getSelectedTabClass() {
    $tab = $this->getSelectedTab();

    $classes = [
      '' => 'checkout-guest',
      'login' => 'checkout-login',
    ];

    return $classes[$tab];
  }

}
