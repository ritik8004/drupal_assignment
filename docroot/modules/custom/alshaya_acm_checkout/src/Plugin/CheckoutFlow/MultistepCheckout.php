<?php

namespace Drupal\alshaya_acm_checkout\Plugin\CheckoutFlow;

use Drupal\acq_checkout\Plugin\CheckoutFlow\CheckoutFlowWithPanesBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Routing\RedirectDestinationTrait;
use Drupal\Core\Url;

/**
 * Provides the default multistep checkout flow.
 *
 * @ACQCheckoutFlow(
 *   id = "multistep_checkout",
 *   label = "Multistep Checkout",
 * )
 */
class MultistepCheckout extends CheckoutFlowWithPanesBase {

  use RedirectDestinationTrait;

  /**
   * {@inheritdoc}
   */
  public function getSteps() {
    $steps = [];
    if (\Drupal::currentUser()->isAnonymous()) {
      $steps['login'] = [
        'label' => $this->t('Login'),
        'previous_label' => $this->t('Return to login'),
      ];
    }

    $steps['delivery'] = [
      'label' => $this->t('Choose delivery'),
      'next_label' => $this->t('Continue to delivery options'),
      'previous_label' => $this->t('Return to delivery options'),
    ];

    $steps['payment'] = [
      'label' => $this->t('Make payment'),
      'next_label' => $this->t('Continue to payment'),
    ];

    $steps['confirmation'] = [
      'label' => $this->t('Order confirmation'),
      'next_label' => $this->t('View order confirmation'),
    ];

    return $steps;
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    $panes = $this->getPanes($this->stepId);
    foreach ($panes as $pane_id => $pane) {
      $form[$pane_id] = [
        '#parents' => [$pane_id],
        '#type' => $pane->getWrapperElement(),
        '#title' => $pane->getLabel(),
        '#access' => $pane->isVisible(),
      ];
      $form[$pane_id] = $pane->buildPaneForm($form[$pane_id], $form_state, $form);
    }

    // For login we want user to start again with checkout after login.
    if ($this->stepId == 'login') {
      $form['#action'] = Url::fromRoute('<current>', [], ['query' => $this->getDestinationArray(), 'external' => FALSE])->toString();
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  protected function processStepId($requested_step_id) {
    $cart = $this->cartStorage->getCart();
    $cart_step_id = $cart->getCheckoutStep();
    $step_ids = array_keys($this->getVisibleSteps());
    $step_id = $requested_step_id;
    if (empty($step_id) || !in_array($step_id, $step_ids)) {
      // Take the step ID from the cart, or default to the first one.
      $step_id = $cart_step_id;
      if (empty($step_id)) {
        $step_id = reset($step_ids);
      }
    }

    $config = $this->getConfiguration();
    $validate_current_step = $config['validate_current_step'];
    if (empty($validate_current_step)) {
      return $step_id;
    }

    // Get the current step id.
    $current_step_id = $this->getStepId();

    // We don't care about login step, we can allow user to go directly to
    // delivery.
    if ($step_id == 'delivery' && (empty($current_step_id) || $current_step_id == 'login')) {
      return $step_id;
    }

    // If user is on a certain step in their cart, check that the step being
    // processed is not further along in the checkout process then their last
    // completed step. If they haven't started the checkout yet, make sure they
    // can't get past the first step.
    $step_index = array_search($step_id, $step_ids);
    if (empty($cart_step_id)) {
      $first_step = reset($step_ids);
      if ($step_index > $first_step) {
        return $this->redirectToStep($first_step);
      }
    }
    else {
      $cart_step_index = array_search($cart_step_id, $step_ids);
      // Step being processed is further along than they should be, redirect
      // back to step they still need to complete.
      if ($step_index > $cart_step_index) {
        return $this->redirectToStep($cart_step_id);
      }
    }

    return $step_id;
  }

  /**
   * {@inheritdoc}
   */
  protected function actions(array $form, FormStateInterface $form_state) {
    // We don't want any actions to display for login step.
    if ($this->getStepId() == 'login') {
      return [];
    }

    $steps = $this->getVisibleSteps();
    $next_step_id = $this->getNextStepId();
    $previous_step_id = $this->getPreviousStepId();
    $has_next_step = $next_step_id && isset($steps[$next_step_id]['next_label']);
    $has_previous_step = $previous_step_id && isset($steps[$previous_step_id]['previous_label']);

    $actions = [
      '#type' => 'actions',
      '#access' => $has_next_step,
    ];

    if ($has_next_step) {
      $actions['next'] = [
        '#type' => 'submit',
        '#value' => $steps[$next_step_id]['next_label'],
        '#button_type' => 'primary',
        '#submit' => ['::submitForm'],
      ];

      if ($has_previous_step) {
        $label = $steps[$previous_step_id]['previous_label'];
        $actions['next']['#suffix'] = Link::createFromRoute($label, 'acq_checkout.form', [
          'step' => $previous_step_id,
        ])->toString();
      }
    }

    return $actions;
  }

}
