<?php

namespace Drupal\alshaya_social\Plugin\CheckoutPane;

use Drupal\acq_checkout\Plugin\CheckoutFlow\CheckoutFlowInterface;
use Drupal\acq_checkout\Plugin\CheckoutPane\CheckoutPaneBase;
use Drupal\acq_checkout\Plugin\CheckoutPane\CheckoutPaneInterface;
use Drupal\alshaya_acm_checkout\CheckoutLoginTabsTrait;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides the login pane to checkout via social auth.
 *
 * @ACQCheckoutPane(
 *   id = "checkout_social_auth",
 *   label = @Translation("sign in with social media"),
 *   defaultStep = "login",
 *   wrapperElement = "fieldset",
 * )
 */
class CheckoutSocialAuth extends CheckoutPaneBase implements CheckoutPaneInterface {

  use CheckoutLoginTabsTrait;

  /**
   * Social helper.
   *
   * @var \Drupal\alshaya_social\AlshayaSocialHelper
   */
  protected $socialHelper;

  /**
   * Constructs a new CheckoutPaneBase object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\acq_checkout\Plugin\CheckoutFlow\CheckoutFlowInterface $checkout_flow
   *   The parent checkout flow.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, CheckoutFlowInterface $checkout_flow) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $checkout_flow);
    $this->socialHelper = \Drupal::service('alshaya_social.helper');
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
    $complete_form['#attributes']['class'][] = 'social-signin-enabled';
    $pane_form['#attributes']['class'][] = 'social-signup-form';
    $pane_form['social_media_auth_links'] = [
      '#theme' => 'alshaya_social',
      '#social_networks' => $this->socialHelper->getSocialNetworks(),
      '#weight' => -1000,
    ];
    $pane_form['#prefix'] = '<div class="checkout-login-separator order-1"><span>' . $this->t('or') . '</span></div>';
    $pane_form['#cache']['tags'][] = 'config:alshaya_social.settings';
    return $pane_form;
  }

  /**
   * {@inheritdoc}
   */
  public function isVisible() {
    return $this->socialHelper->getStatus();
  }

}
