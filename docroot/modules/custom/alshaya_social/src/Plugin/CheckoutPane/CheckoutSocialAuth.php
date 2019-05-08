<?php

namespace Drupal\alshaya_social\Plugin\CheckoutPane;

use Drupal\acq_checkout\Plugin\CheckoutPane\CheckoutPaneBase;
use Drupal\acq_checkout\Plugin\CheckoutPane\CheckoutPaneInterface;
use Drupal\alshaya_acm_checkout\CheckoutLoginTabsTrait;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides the login pane to checkout via social auth.
 *
 * @ACQCheckoutPane(
 *   id = "checkout_social_auth",
 *   label = @Translation("Sign in with social media"),
 *   defaultStep = "login",
 *   wrapperElement = "fieldset",
 * )
 */
class CheckoutSocialAuth extends CheckoutPaneBase implements CheckoutPaneInterface {

  use CheckoutLoginTabsTrait;

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
    if (
      \Drupal::configFactory()->get('alshaya_social.settings')->get('social_login')
      && !empty($social_networks = \Drupal::configFactory()->get('social_auth.settings')->get('auth'))
    ) {
      $pane_form['#attributes']['class'][] = 'social-signup-form';
      $pane_form['social_media_auth_links'] = [
        '#theme' => 'alshaya_social',
        '#social_networks' => $social_networks,
        '#form' => $this->getPluginId(),
        '#weight' => -1000,
      ];
    }
    $pane_form['#cache']['tags'][] = 'config:alshaya_social.settings';
    return $pane_form;
  }

}
