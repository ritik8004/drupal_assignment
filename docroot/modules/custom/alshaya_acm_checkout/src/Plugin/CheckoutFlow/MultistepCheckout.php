<?php

namespace Drupal\alshaya_acm_checkout\Plugin\CheckoutFlow;

use Drupal\acq_checkout\Plugin\CheckoutFlow\CheckoutFlowWithPanesBase;
use Drupal\acq_commerce\Response\NeedsRedirectException;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Logger\LoggerChannelTrait;
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
  use LoggerChannelTrait;

  use RedirectDestinationTrait;

  /**
   * {@inheritdoc}
   */
  public function getSteps() {
    $steps = [];
    if (\Drupal::currentUser()->isAnonymous()) {
      $steps['login'] = [
        'label' => $this->t('secure checkout'),
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
   * Update Cart using Cart Storage service.
   *
   * @return \Drupal\acq_cart\Cart
   *   Updated Cart object.
   */
  public function updateCart() {
    return $this->cartStorage->updateCart(FALSE);
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
    $steps = $this->getVisibleSteps();

    // Add current step id to allow other modules to use it in alter hook.
    $form_state->setTemporaryValue('step_id', $this->stepId);

    // Never cache the checkout form. We never know, anything can change here.
    $form['#cache'] = ['max-age' => 0];

    // We always want this cache context and tag.
    $form['#cache']['contexts'][] = 'cookies:Drupal_visitor_acq_cart_id';

    // On final step we won't have cart to add the cache tag.
    if ($this->stepId != 'confirmation') {
      $cart = $this->getCart();
      $form['#cache']['tags'][] = 'cart:' . $cart->id();
    }

    $form['#tree'] = TRUE;
    $form['#theme'] = ['acq_checkout_form'];
    $form['#attached']['library'][] = 'acq_checkout/form';
    $form['#title'] = $steps[$this->stepId]['label'];
    $form['actions'] = $this->actions($form, $form_state);

    // Disable autocomplete.
    $form['#attributes']['autocomplete'] = 'off';

    $panes = $this->getPanes($this->stepId);
    foreach ($panes as $pane_id => $pane) {
      $visible = $pane->isVisible();

      $form[$pane_id] = [
        '#parents' => [$pane_id],
        '#type' => $pane->getWrapperElement(),
        '#title' => $pane->getLabel(),
        '#access' => $visible,
      ];

      // We will only build form if visible.
      if ($visible) {
        $form[$pane_id] = $pane->buildPaneForm(
          $form[$pane_id], $form_state, $form
        );
      }
    }

    // For login we want user to start again with checkout after login.
    if ($this->stepId == 'login') {
      // Todo: This needs to be changed from hardcode value.
      // This is happening because in url, we have not correctly formed query
      // string. Its like '/cart/checkout/login?cart/checkout/login?tab=login'
      // We can change/replace and adjust url again but then,
      // UrlHelper::buildQuery() called by Url()->toString() encodes the url.
      $destination = ['tab' => 'login'];
      $form['#action'] = Url::fromRoute('<current>', [], ['query' => $destination, 'external' => FALSE])->toString();
    }

    $form['#attached']['library'][] = 'alshaya_acm_checkout/checkout_flow';

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  protected function processStepId($requested_step_id) {
    $cart = $this->cartStorage->getCart(FALSE);

    $session = \Drupal::request()->getSession();

    // We need to show confirmation step even after cart is cleared.
    if (empty($cart) && $requested_step_id == 'confirmation') {
      $step_id = $requested_step_id;

      if (!empty($session->get('last_order_id'))) {
        return $step_id;
      }
    }

    // Redirect to confirmation page if cart is empty and we have last order id
    // in session.
    if (empty($cart) && !empty($session->get('last_order_id'))) {
      $this->redirectToStep('confirmation');
    }

    // Redirect user to basket page if there are no items in cart and user is
    // trying to checkout.
    if (empty($cart) || !$cart->items()) {
      $response = new RedirectResponse(Url::fromRoute('acq_cart.cart')->toString());
      $response->send();
      exit;
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

      if ($next_step_id === 'confirmation') {
        $actions['next']['#attribtues']['class'][] = 'payment-place-order';
      }

      if ($has_previous_step) {
        $label = $steps[$previous_step_id]['previous_label'];

        if ($previous_step_id === 'delivery') {
          $checkout_next_link_options['attributes']['class'][] = 'payment-return-to-delivery';
        }

        $checkout_next_link = Link::createFromRoute($label, 'acq_checkout.form', ['step' => $previous_step_id], $checkout_next_link_options)->toString();
        $actions['next']['#suffix'] = $checkout_next_link;
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
  public function getCart() {
    $cart = $this->cartStorage->getCart(FALSE);

    if (empty($cart)) {
      $response = new RedirectResponse(Url::fromRoute('acq_cart.cart')->toString());
      $response->send();
      exit;
    }

    return $cart;
  }

  /**
   * Get checkout helper service object.
   *
   * @return \Drupal\alshaya_acm_checkout\CheckoutHelper
   *   Checkout Helper service object.
   */
  public function getCheckoutHelper() {
    static $helper;

    if (empty($helper)) {
      /** @var \Drupal\alshaya_acm_checkout\CheckoutHelper $helper */
      $helper = \Drupal::service('alshaya_acm_checkout.checkout_helper');
    }

    return $helper;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    if ($form_state->getTriggeringElement()['#parents'][0] != 'actions') {
      return;
    }

    $cart = $this->getCart();

    if ($next_step_id = $this->getNextStepId()) {
      $current_step_id = $this->getStepId();
      try {

        if ($next_step_id == 'confirmation') {
          $method = $cart->getShippingMethodAsString();

          if (empty($method)) {
            $this->getLogger('alshaya_acm_checkout')->error('User tried to place order with empty delivery method: @data', [
              '@data' => $cart->getDataToLog(),
            ]);

            return $this->redirectToStep('delivery');
          }

          // User has pressed "place order" button.
          // Set the attempted payment flag and push to Magento.
          $cart->setExtension('attempted_payment', 1);
        }

        /** @var \Drupal\acq_cart\Cart $cart */
        $cart = \Drupal::service('acq_cart.cart_storage')->updateCart();
      }
      catch (NeedsRedirectException $e) {
        // Do nothing, let it redirect.
      }
      catch (\Exception $e) {
        $this->getLogger('alshaya_acm_checkout')->error('Error while updating cart in @step: @message', [
          '@step' => $this->stepId,
          '@message' => $e->getMessage(),
        ]);

        if (_alshaya_acm_is_out_of_stock_exception($e)) {
          if ($cart = $this->getCart()) {
            $cart->setCheckoutStep('');
            $this->getCheckoutHelper()->refreshStockForProductsInCart($cart);
          }

          throw new NeedsRedirectException(Url::fromRoute('acq_cart.cart')->toString());
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
        try {
          // Validate if reserve order id is not set in cart.
          if (!$this->getCheckoutHelper()->isReservedOrderSetInCart($cart)) {
            throw new \Exception(acq_commerce_api_down_global_error_message());
          }
          // Invoke hook to allow other modules to process before order is
          // finally placed.
          \Drupal::moduleHandler()->invokeAll('alshaya_acm_checkout_pre_place_order', [$cart]);

          // Place an order.
          $this->getCheckoutHelper()->placeOrder($cart);
        }
        catch (\Exception $e) {
          drupal_set_message($e->getMessage(), 'error');
          $this->redirectToStep('payment');
        }
      }
    }
  }

}
