<?php

namespace Drupal\acq_checkout\Plugin\CheckoutFlow;

use Drupal\acq_commerce\Conductor\APIWrapper;
use Drupal\acq_commerce\Response\NeedsRedirectException;
use Drupal\acq_cart\CartStorageInterface;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\PluginBase;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Provides the base checkout flow class.
 *
 * Checkout flows should extend this class only if they don't want to use
 * checkout panes. Otherwise they should extend CheckoutFlowWithPanesBase.
 */
abstract class CheckoutFlowBase extends PluginBase implements CheckoutFlowInterface, ContainerFactoryPluginInterface {

  /**
   * The shopping cart.
   *
   * @var \Drupal\acq_cart\CartStorageInterface
   */
  protected $cartStorage;

  /**
   * The api wrapper.
   *
   * @var \Drupal\acq_commerce\Conductor\APIWrapper
   */
  protected $apiWrapper;

  /**
   * The event dispatcher.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  protected $eventDispatcher;

  /**
   * The current step ID.
   *
   * @var string
   */
  protected $stepId;

  /**
   * Constructs a new CheckoutFlowBase object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $event_dispatcher
   *   The event dispatcher.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The route match.
   * @param \Drupal\acq_cart\CartStorageInterface $cart_storage
   *   The cart storage.
   * @param \Drupal\acq_commerce\Conductor\APIWrapper $api_wrapper
   *   The api wrapper.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EventDispatcherInterface $event_dispatcher, RouteMatchInterface $route_match, CartStorageInterface $cart_storage, APIWrapper $api_wrapper) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->setConfiguration($configuration);
    $this->eventDispatcher = $event_dispatcher;
    $this->cartStorage = $cart_storage;
    $this->apiWrapper = $api_wrapper;
    $this->stepId = $this->processStepId($route_match->getParameter('step'));
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('event_dispatcher'),
      $container->get('current_route_match'),
      $container->get('acq_cart.cart_storage'),
      $container->get('acq_commerce.api')
    );
  }

  /**
   * Processes the requested step ID.
   *
   * @param string $requested_step_id
   *   The step ID.
   *
   * @return string
   *   The processed step ID.
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
  public function getCart() {
    return $this->cartStorage->getCart();
  }

  /**
   * {@inheritdoc}
   */
  public function getApiWrapper() {
    return $this->apiWrapper;
  }

  /**
   * {@inheritdoc}
   */
  public function getStepId() {
    return $this->stepId;
  }

  /**
   * {@inheritdoc}
   */
  public function getPreviousStepId() {
    $step_ids = array_keys($this->getVisibleSteps());
    $current_index = array_search($this->stepId, $step_ids);
    return $step_ids[$current_index - 1] ?? NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getNextStepId() {
    $step_ids = array_keys($this->getVisibleSteps());
    $current_index = array_search($this->stepId, $step_ids);
    return $step_ids[$current_index + 1] ?? NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function redirectToStep($step_id): never {
    $cart = $this->cartStorage->getCart();
    $cart->setCheckoutStep($step_id);
    throw new NeedsRedirectException(Url::fromRoute('acq_checkout.form', [
      'step' => $step_id,
    ])->toString());
  }

  /**
   * {@inheritdoc}
   */
  public function getSteps() {
    // Each checkout flow plugin defines its own steps.
    // These two steps are always expected to be present.
    return [
      'payment' => [
        'label' => $this->t('Payment'),
        'next_label' => $this->t('Pay and complete purchase'),
      ],
      'complete' => [
        'label' => $this->t('Complete'),
        'next_label' => $this->t('Pay and complete purchase'),
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getVisibleSteps() {
    // All steps are visible by default.
    return $this->getSteps();
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getConfiguration() {
    return $this->configuration;
  }

  /**
   * {@inheritdoc}
   */
  public function setConfiguration(array $configuration) {
    $this->configuration = NestedArray::mergeDeep($this->defaultConfiguration(), $configuration);
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'validate_current_step' => FALSE,
      'display_checkout_progress' => TRUE,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return $this->pluginId;
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $steps = $this->getVisibleSteps();

    $form['#tree'] = TRUE;
    $form['#theme'] = ['acq_checkout_form'];
    $form['#attached']['library'][] = 'acq_checkout/form';
    $form['#title'] = $steps[$this->stepId]['label'];
    $form['actions'] = $this->actions($form, $form_state);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {}

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    if ($next_step_id = $this->getNextStepId()) {
      $cart = $this->cartStorage->getCart();
      $cart->setCheckoutStep($next_step_id);
      $form_state->setRedirect('acq_checkout.form', [
        'step' => $next_step_id,
      ]);

      if ($next_step_id == 'complete') {
        $this->cartStorage->pushCart();
        $cart_id = $this->cartStorage->getCartId();
        // Place an order.
        $this->apiWrapper->placeOrder($cart_id);
      }
    }
  }

  /**
   * Builds the actions element for the current form.
   *
   * @param array $form
   *   The current form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current form state.
   *
   * @return array
   *   The actions element.
   */
  protected function actions(array $form, FormStateInterface $form_state) {
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
