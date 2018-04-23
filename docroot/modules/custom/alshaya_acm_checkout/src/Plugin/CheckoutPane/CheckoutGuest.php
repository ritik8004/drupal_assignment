<?php

namespace Drupal\alshaya_acm_checkout\Plugin\CheckoutPane;

use Drupal\acq_checkout\Plugin\CheckoutPane\CheckoutPaneBase;
use Drupal\acq_checkout\Plugin\CheckoutPane\CheckoutPaneInterface;
use Drupal\block\BlockViewBuilder;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;

/**
 * Provides the login pane to checkout as guest.
 *
 * @ACQCheckoutPane(
 *   id = "checkout_guest",
 *   label = @Translation("new customers"),
 *   defaultStep = "login",
 *   wrapperElement = "fieldset",
 * )
 */
class CheckoutGuest extends CheckoutPaneBase implements CheckoutPaneInterface {

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
    $config = \Drupal::config('alshaya_acm_checkout.settings');

    $checkout_guest_options = [
      'attributes' => [
        'gtm-type' => 'checkout-as-guest',
      ],
    ];

    $pane_form['checkout_as_guest'] = Link::createFromRoute($this->t('checkout as guest'), 'acq_checkout.form', ['step' => 'delivery'], $checkout_guest_options)->toRenderable();

    $pane_form['email_usage'] = [
      '#markup' => '<div class="checkout-guest-email-usage">' . $config->get('checkout_guest_email_usage.value') . '</div>',
    ];

    $pane_form['summary'] = [
      '#markup' => '<div class="checkout-guest-summary">' . $config->get('checkout_guest_summary.value') . '</div>',
    ];

    // Load the block 'youllbeableto' and render it.
    $block = BlockViewBuilder::lazyBuilder('youllbeableto', 'full');
    $block_markup = \Drupal::service('renderer')->renderPlain($block);
    $pane_form['you_will_able_to'] = [
      '#markup' => $block_markup->__toString(),
      '#prefix' => '<div id="block-youllbeableto">',
      '#suffix' => '<div>',
    ];

    return $pane_form;
  }

}
