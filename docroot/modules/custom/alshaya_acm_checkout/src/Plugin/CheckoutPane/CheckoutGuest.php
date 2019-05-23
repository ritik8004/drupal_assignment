<?php

namespace Drupal\alshaya_acm_checkout\Plugin\CheckoutPane;

use Drupal\acq_checkout\Plugin\CheckoutPane\CheckoutPaneBase;
use Drupal\acq_checkout\Plugin\CheckoutPane\CheckoutPaneInterface;
use Drupal\alshaya_acm_checkout\CheckoutLoginTabsTrait;
use Drupal\block\BlockViewBuilder;
use Drupal\block\Entity\Block;
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

  use CheckoutLoginTabsTrait;

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
    if ($this->getSelectedTab() === 'login') {
      $pane_form['#attributes']['class'][] = 'above-mobile-block';
    }

    $config = \Drupal::config('alshaya_acm_checkout.settings');

    $checkout_guest_options = [
      'attributes' => [
        'gtm-type' => 'checkout-as-guest',
      ],
    ];

    $link = Link::createFromRoute(
      $this->t('checkout as guest'),
      'acq_checkout.form',
      ['step' => 'delivery'],
      $checkout_guest_options
    );

    $pane_form['checkout_as_guest'] = $link->toRenderable();
    $pane_form['checkout_as_guest']['#prefix'] = '<div class="above-mobile-block">';
    $pane_form['checkout_as_guest']['#suffix'] = '</div>';

    $pane_form['email_usage'] = [
      '#markup' => '<div class="checkout-guest-email-usage">' . $config->get('checkout_guest_email_usage.value') . '</div>',
    ];

    $pane_form['summary'] = [
      '#markup' => '<div class="checkout-guest-summary">' . $config->get('checkout_guest_summary.value') . '</div>',
    ];

    // Load the block 'youllbeableto' and render it.
    $blockContent = Block::load('youllbeableto');

    if ($blockContent instanceof Block && $blockContent->get('status')) {
      $block = BlockViewBuilder::lazyBuilder('youllbeableto', 'full');
      $block_markup = \Drupal::service('renderer')->renderPlain($block);
      if (!empty($block_markup)) {
        $pane_form['you_will_able_to'] = [
          '#markup' => $block_markup->__toString(),
          '#prefix' => '<div id="block-youllbeableto">',
          '#suffix' => '<div>',
        ];
      }
    }
    return $pane_form;
  }

}
