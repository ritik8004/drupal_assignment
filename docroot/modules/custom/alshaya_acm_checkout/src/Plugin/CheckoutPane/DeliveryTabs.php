<?php

namespace Drupal\alshaya_acm_checkout\Plugin\CheckoutPane;

use Drupal\acq_checkout\Plugin\CheckoutPane\CheckoutPaneBase;
use Drupal\acq_checkout\Plugin\CheckoutPane\CheckoutPaneInterface;
use Drupal\Core\Form\FormStateInterface;

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
    // @TODO: We can think of making this dynamic if time permits.
    $pane_form['home_deliery'] = [
      '#markup' => '<div class="tab tab-home-delivery"><h2>' . $this->t('Home delivery') . '</h2><p>' . $this->t('Standard delivery for purchases over KD 250') . '</p></div>',
    ];

    $pane_form['separator'] = [
      '#markup' => '<div class="tab tab-separator">' . $this->t('OR') . '</div>',
    ];

    $pane_form['click_collect'] = [
      '#markup' => '<div class="tab tab-click-collect"><h2>' . $this->t('Click & Collect') . '</h2><p>' . $this->t('Collect your order in-store') . '</p></div>',
    ];

    return $pane_form;
  }

}
