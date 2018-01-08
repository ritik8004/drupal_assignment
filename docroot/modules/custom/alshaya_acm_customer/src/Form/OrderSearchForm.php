<?php

namespace Drupal\alshaya_acm_customer\Form;

use Drupal\Component\Utility\Html;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class CartConfigForm.
 */
class OrderSearchForm extends FormBase {

  /**
   * Module Handler service object.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Current request object.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $currentRequest;

  /**
   * OrderSearchForm constructor.
   *
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   Module Handler service object.
   * @param \Symfony\Component\HttpFoundation\Request $current_request
   *   Current request object.
   */
  public function __construct(ModuleHandlerInterface $module_handler,
                              Request $current_request) {
    $this->moduleHandler = $module_handler;
    $this->currentRequest = $current_request;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('module_handler'),
      $container->get('request_stack')->getCurrentRequest()
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'alshaya_acm_customer_order_list_search';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $this->moduleHandler->loadInclude('alshaya_acm_customer', 'inc', 'alshaya_acm_customer.orders');

    $form['search'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Search orders by'),
      '#required' => FALSE,
      '#placeholder' => $this->t('ID, name, SKU'),
      '#default_value' => $this->currentRequest->query->get('search'),
    ];

    $filterOptions = ['' => $this->t('All Orders')];

    $account = $this->currentRequest->attributes->get('user');
    $filterOptions += alshaya_acm_customer_get_available_user_order_status($account);

    $form['filter'] = [
      '#type' => 'select',
      '#weight' => 10,
      '#title' => $this->t('Show'),
      '#required' => FALSE,
      '#options' => $filterOptions,
      '#default_value' => $this->currentRequest->query->get('filter'),
    ];

    $form['actions'] = [
      '#type' => 'actions',
      '#weight' => 2,
    ];

    $form['actions']['submit'] = [
      // Prevent from showing up in \Drupal::request()->query.
      '#name' => '',
      '#type' => 'submit',
      '#value' => $this->t('Search'),
      '#id' => Html::getUniqueId('edit-submit-orders'),
    ];

    $form['#action'] = Url::fromRoute('<current>')->toString();
    $form['#method'] = 'get';

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
  }

}
