<?php

namespace Drupal\alshaya_acm_checkout\Plugin\CheckoutFlow;

use Drupal\acq_checkout\Plugin\CheckoutFlow\CheckoutFlowWithPanesBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Routing\RedirectDestinationTrait;
use Drupal\Core\Url;
use Drupal\user\Entity\User;
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
  public function processForm(array $form, FormStateInterface $form_state) {
    return $form;
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
    $cart = $this->cartStorage->getCart(FALSE);

    // Redirect user to basket page if there are no items in cart and user is
    // trying to checkout.
    if ($requested_step_id != 'confirmation' && (empty($cart) || !$cart->items())) {
      $response = new RedirectResponse(Url::fromRoute('acq_cart.cart')->toString());
      $response->send();
    }

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

      // We don't want to allow access to /cart/checkout without step id.
      // This is required for proper templates to get applied.
      if (empty($requested_step_id)) {
        $this->redirectToStep($step_id);
      }

      // If the requested step is not valid we redirect them to proper URL.
      // This will mostly happen when user logs in.
      if (!empty($requested_step_id) && $requested_step_id != $step_id) {
        $this->redirectToStep($step_id);
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
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $panes = $this->getPanes($this->stepId);
    foreach ($panes as $pane_id => $pane) {
      if ($pane->isVisible()) {
        $pane->validatePaneForm($form[$pane_id], $form_state, $form);
      }
    }

    if ($form_state->getErrors()) {
      return;
    }

    if ($form_state->getTriggeringElement()['#parents'][0] == 'actions') {
      // We submit panes in validate itself to allow setting form errors.
      foreach ($panes as $pane_id => $pane) {
        if ($pane->isVisible()) {
          $pane->submitPaneForm($form[$pane_id], $form_state, $form);
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    if ($form_state->getTriggeringElement()['#parents'][0] != 'actions') {
      return;
    }

    if ($next_step_id = $this->getNextStepId()) {
      $current_step_id = $this->getStepId();
      try {
        /** @var \Drupal\acq_cart\Cart $cart */
        $cart = \Drupal::service('acq_cart.cart_storage')->updateCart();
      }
      catch (\Exception $e) {
        \Drupal::logger('alshaya_acm_checkout')->error('Error while updating cart in @step: @message', [
          '@step' => $this->stepId,
          '@message' => $e->getMessage(),
        ]);

        // @TODO: RELYING ON ERROR MESSAGE FROM MAGENTO.
        if ($e->getMessage() == $this->t('This product is out of stock.')->render()
          || $e->getMessage() == $this->t('Some of the products are out of stock.')->render()
          || $e->getMessage() == $this->t('Not all of your products are available in the requested quantity.')->render()
          || strpos($e->getMessage(), $this->t("We don't have as many")->render()) !== FALSE) {

          $response = new RedirectResponse(Url::fromRoute('acq_cart.cart')->toString());
          $response->send();
          exit;
        }

        // Show message from Magento to user if allowed in config.
        if (\Drupal::config('alshaya_acm_checkout.settings')->get('checkout_display_magento_error')) {
          drupal_set_message($e->getMessage(), 'error');
        }
        else {
          drupal_set_message($this->t('Something looks wrong, please try again later.'), 'error');
        }

        $this->redirectToStep($current_step_id);
      }

      $cart->setCheckoutStep($next_step_id);
      $form_state->setRedirect('acq_checkout.form', [
        'step' => $next_step_id,
      ]);

      if ($next_step_id == 'confirmation') {
        $cart = $this->cartStorage->getCart();
        $cart_id = $this->cartStorage->getCartId();

        try {
          // Place an order.
          $response = $this->apiWrapper->placeOrder($cart_id);
        }
        catch (\Exception $e) {
          drupal_set_message($e->getMessage(), 'error');
          $this->redirectToStep('payment');
        }

        // Store the order details from response in tempstore.
        $temp_store = \Drupal::service('user.private_tempstore')->get('alshaya_acm_checkout');
        $temp_store->set('order', $response['order']);

        $current_user_id = 0;

        // Clear orders list cache if user is logged in.
        if (\Drupal::currentUser()->isAnonymous()) {
          // Store the email address of customer in tempstore.
          $email = $cart->customerEmail();
          $temp_store->set('email', $email);
        }
        else {
          $email = \Drupal::currentUser()->getEmail();
          $current_user_id = \Drupal::currentUser()->id();

          // Update user's mobile number if empty.
          $account = User::load($current_user_id);

          if (empty($account->get('field_mobile_number')->getString())) {
            $billing = (array) $cart->getBilling();
            $account->get('field_mobile_number')->setValue($billing['telephone']);
            $account->save();
          }
        }

        /** @var \Drupal\alshaya_acm_customer\OrdersManager $orders_manager */
        $orders_manager = \Drupal::service('alshaya_acm_customer.orders_manager');
        $orders_manager->clearOrderCache($email, $current_user_id);

        // Create a new cart now.
        $this->cartStorage->createCart();
      }
    }
  }

}
