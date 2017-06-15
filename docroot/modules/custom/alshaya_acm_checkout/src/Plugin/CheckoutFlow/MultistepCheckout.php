<?php

namespace Drupal\alshaya_acm_checkout\Plugin\CheckoutFlow;

use Drupal\acq_checkout\Plugin\CheckoutFlow\CheckoutFlowWithPanesBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Routing\RedirectDestinationTrait;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\RedirectResponse;

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
        'label' => $this->t('Welcome'),
      ];
    }

    $steps['delivery'] = [
      'label' => $this->t('secure checkout'),
      'title' => $this->t('Choose delivery'),
      'next_label' => $this->t('Continue to delivery options'),
      'previous_label' => $this->t('Return to delivery options'),
    ];

    $steps['payment'] = [
      'label' => $this->t('secure checkout'),
      'title' => $this->t('Make payment'),
      'next_label' => $this->t('proceed to payment'),
    ];

    $steps['confirmation'] = [
      'label' => $this->t('secure checkout'),
      'title' => $this->t('Order confirmation'),
      'next_label' => $this->t('place order'),
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

    $form['#attached']['library'][] = 'alshaya_acm_checkout/checkout_flow';

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
        $this->redirectToStep($step_id);
      }

      // If the requested step is not valid we redirect them to proper URL.
      // This will mostly happen when user logs in.
      if (!empty($requested_step_id)) {
        $this->redirectToStep($step_id);
      }
    }

    // Redirect user to basket page if there are no items in cart and user is
    // trying to checkout.
    if ($step_id != 'confirmation' && !$cart->items()) {
      $response = new RedirectResponse(Url::fromRoute('acq_cart.cart')->toString());
      $response->send();
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

    // We need to show confirmation step even after cart is cleared.
    if ($step_id == 'confirmation') {
      $temp_store = \Drupal::service('user.private_tempstore')->get('alshaya_acm_checkout');
      $order_data = $temp_store->get('order');

      if (!empty($order_data) && !empty($order_data['id'])) {
        return $step_id;
      }
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

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    if ($next_step_id = $this->getNextStepId()) {
      if ($next_step_id == 'confirmation') {
        $cart = $this->cartStorage->getCart();

        // Invoke hook to allow other modules to process before order is finally
        // placed.
        \Drupal::moduleHandler()->invokeAll('alshaya_acm_checkout_pre_place_order', [$cart]);

        $this->cartStorage->pushCart();
        $cart_id = $this->cartStorage->getCartId();

        // Place an order.
        $response = $this->apiWrapper->placeOrder($cart_id);

        // Store the order details from response in tempstore.
        $temp_store = \Drupal::service('user.private_tempstore')->get('alshaya_acm_checkout');
        $temp_store->set('order', $response['order']);

        // Clear orders list cache if user is logged in.
        if (\Drupal::currentUser()->isAnonymous()) {
          // Store the email address of customer in tempstore.
          $email = $cart->customerEmail();
          $temp_store->set('email', $email);
        }
        else {
          $email = \Drupal::currentUser()->getEmail();
        }

        \Drupal::cache()->invalidate('orders_list_' . $email);

        // @TODO: Remove this code once Magento has placed the observer.
        $order = _alshaya_acm_checkout_get_last_order_from_session();

        if ($order['payment']['method_code'] == 'knet') {
          $this->apiWrapper->updateOrderStatus($order['order_id'], 'pending_payment', 'Remove code in Drupal to update status to pending_payment once Magento observer is in place');

          // Clear the cache again as we just created above while getting
          // last order from session.
          \Drupal::cache()->invalidate('orders_list_' . $email);
        }

        // Create a new cart now.
        $this->cartStorage->createCart();
      }
    }
  }

}
