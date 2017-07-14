<?php

namespace Drupal\alshaya_acm_checkout\Plugin\CheckoutPane;

use Drupal\acq_checkout\Plugin\CheckoutPane\CheckoutPaneBase;
use Drupal\acq_checkout\Plugin\CheckoutPane\CheckoutPaneInterface;
use Drupal\alshaya_acm_checkout\CheckoutDeliveryMethodTrait;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Provides the Delivery Tabs pane on Delivery page.
 *
 * @ACQCheckoutPane(
 *   id = "delivery_tabs",
 *   label = @Translation("Delivery Tabs"),
 *   defaultStep = "delivery",
 *   wrapperElement = "container",
 * )
 */
class DeliveryTabs extends CheckoutPaneBase implements CheckoutPaneInterface {
  // Add trait to get selected delivery method tab.
  use CheckoutDeliveryMethodTrait;

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'weight' => -20,
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildPaneForm(array $pane_form, FormStateInterface $form_state, array &$complete_form) {
    $show_only_buttons_mobile = $this->isMethodParamAvailable() ? 'show-form' : 'show-only-buttons';
    $complete_form['#attributes']['class'][] = $show_only_buttons_mobile;

    // Add hidden field to store currently selected tab class.
    $complete_form['selected_tab'] = [
      '#type' => 'hidden',
      '#default_value' => $this->getSelectedDeliveryMethodClass(),
      '#attributes' => [
        'id' => 'selected-tab',
      ],
      '#weight' => -99,
    ];

    $complete_form['actions']['back_to_basket'] = [
      '#type' => 'link',
      '#title' => $this->t('Back to basket'),
      '#url' => Url::fromRoute('acq_cart.cart'),
      '#attributes' => [
        'class' => ['back-to-basket'],
      ],
    ];

    $pane_form['select_transation_type'] = [
      '#markup' => '<div class="select-transaction-type">' . $this->t('select a transaction type') . '</div>',
    ];

    $selected_method = $this->getSelectedDeliveryMethod();

    $url = Url::fromRoute('acq_checkout.form', ['step' => 'delivery']);

    // Set hd as method in params for home delivery.
    $url->setRouteParameter('method', 'hd');

    $active_class = ($selected_method == 'hd') ? 'active--tab--head' : '';

    $home_delivery = '<div class="tab tab-home-delivery ' . $active_class . '" gtm-type="checkout-home-delivery">';
    $home_delivery .= '<a href="' . $url->toString() . '">';
    $home_delivery .= '<h2>' . $this->t('Home delivery') . '</h2>';
    $home_delivery .= '<p>' . $this->t('Standard delivery for purchases over KD 250') . '</p>';
    $home_delivery .= '</a></div>';

    $pane_form['home_deliery'] = [
      '#markup' => $home_delivery,
    ];

    $pane_form['separator'] = [
      '#markup' => '<div class="tab tab-separator">' . $this->t('OR') . '</div>',
    ];

    // Set cc as method in params for click and collect.
    $url->setRouteParameter('method', 'cc');

    $active_class = ($selected_method == 'cc') ? 'active--tab--head' : '';

    $click_collect = '<div class="tab tab-click-collect ' . $active_class . '" gtm-type="checkout-click-collect">';
    $click_collect .= '<a href="' . $url->toString() . '">';
    $click_collect .= '<h2>' . $this->t('Click & Collect') . '</h2>';
    $click_collect .= '<p>' . $this->t('Collect your order in-store') . '</p>';
    $click_collect .= '</a></div>';

    $pane_form['click_collect'] = [
      '#markup' => $click_collect,
    ];

    $pane_form['line_seperator'] = [
      '#markup' => '<hr />',
    ];

    return $pane_form;
  }

}
