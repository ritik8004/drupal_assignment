<?php

namespace Drupal\alshaya_mobile_app\Plugin\rest\resource;

use Drupal\alshaya_acm_checkout\CheckoutOptionsManager;
use Drupal\alshaya_acm_customer\OrdersManager;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\rest\ModifiedResourceResponse;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\alshaya_spc\AlshayaSpcPaymentMethodManager;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;

/**
 * Provides a resource to get payment methods data.
 *
 * @RestResource(
 *   id = "payment_methods",
 *   label = @Translation("Payment Methods"),
 *   uri_paths = {
 *     "canonical" = "/rest/v1/payment-methods"
 *   }
 * )
 */
class PaymentMethodResource extends ResourceBase {

  /**
   * Payment method term object list.
   *
   * @var array
   */
  protected $paymetMethodTerms = [];

  /**
   * Entity repository.
   *
   * @var \Drupal\Core\Entity\EntityRepositoryInterface
   */
  protected $entityRepository;

  /**
   * Chekcout option manager.
   *
   * @var \Drupal\alshaya_acm_checkout\CheckoutOptionsManager
   */
  protected $checkoutOptionManager;

  /**
   * Payment method manager.
   *
   * @var \Drupal\alshaya_spc\AlshayaSpcPaymentMethodManager
   */
  protected $paymentMethodManager;

  /**
   * The config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The orders manager service.
   *
   * @var \Drupal\alshaya_acm_customer\OrdersManager
   */
  protected $ordersManager;

  /**
   * Module handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * PaymentMethodResource constructor.
   *
   * @param array $configuration
   *   Configuration array.
   * @param string $plugin_id
   *   Plugin id.
   * @param mixed $plugin_definition
   *   Plugin definition.
   * @param array $serializer_formats
   *   Serializer formats.
   * @param \Psr\Log\LoggerInterface $logger
   *   Logger channel.
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   *   Entity repository.
   * @param \Drupal\alshaya_acm_checkout\CheckoutOptionsManager $checkout_option_manager
   *   Checkout option manager.
   * @param \Drupal\alshaya_spc\AlshayaSpcPaymentMethodManager $payment_plugins
   *   Payment plugins.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory service.
   * @param \Drupal\alshaya_acm_customer\OrdersManager $orders_manager
   *   The orders manager service.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   Module handler service.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    array $serializer_formats,
    LoggerInterface $logger,
    EntityRepositoryInterface $entity_repository,
    CheckoutOptionsManager $checkout_option_manager,
    AlshayaSpcPaymentMethodManager $payment_plugins,
    ConfigFactoryInterface $config_factory,
    OrdersManager $orders_manager,
    ModuleHandlerInterface $module_handler
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
    $this->entityRepository = $entity_repository;
    $this->checkoutOptionManager = $checkout_option_manager;
    $this->paymentMethodManager = $payment_plugins;
    $this->configFactory = $config_factory;
    $this->ordersManager = $orders_manager;
    $this->moduleHandler = $module_handler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->getParameter('serializer.formats'),
      $container->get('logger.factory')->get('alshaya_mobile_app'),
      $container->get('entity.repository'),
      $container->get('alshaya_acm_checkout.options_manager'),
      $container->get('plugin.manager.alshaya_spc_payment_method'),
      $container->get('config.factory'),
      $container->get('alshaya_acm_customer.orders_manager'),
      $container->get('module_handler')
    );
  }

  /**
   * Responds to GET requests.
   *
   * Returns available payment method data.
   *
   * @return \Drupal\rest\ResourceResponse
   *   The response containing payment methods data.
   */
  public function get() {
    $response_data = [];

    $payment_plugins = $this->paymentMethodManager->getDefinitions();

    // Get the payment methods which have been excluded.
    $checkout_settings = $this->configFactory->get('alshaya_acm_checkout.settings');
    $exclude_payment_methods = array_filter($checkout_settings->get('exclude_payment_methods'));

    foreach ($payment_plugins as $plugin) {
      $payment_method_term = $this->checkoutOptionManager->loadPaymentMethod(
        $plugin['id'],
        $plugin['label']->render()
      );

      // If payment method term exists.
      if (!empty($payment_method_term)) {

        // Check if the payment method is visible in front end.
        $visibility = TRUE;
        if (isset($exclude_payment_methods[$plugin['id']])) {
          $visibility = FALSE;
        }
        else {
          /** @var \Drupal\alshaya_spc\AlshayaSpcPaymentMethodPluginBase $plugin */
          $plugin_instance = $this->paymentMethodManager->createInstance($plugin['id']);
          if (!($plugin_instance->isAvailable())) {
            $visibility = FALSE;
          }
        }
        $weight = ($payment_method_term->get('field_payment_default')->getString() == '1')
          ? -999
          : (int) $payment_method_term->getWeight();

        $payment_method_code = $payment_method_term->get('field_payment_code')->getString();

        $response_data[] = [
          'name' => $payment_method_term->getName(),
          'description' => $payment_method_term->getDescription(),
          'code' => $payment_method_code,
          'default' => ($payment_method_term->get('field_payment_default')->getString() == '1'),
          'visibility' => $visibility,
          'refund_text' => $this->ordersManager->getRefundText($payment_method_code),
          'weight' => $weight,
        ];

        // Adding to property for using later to attach cacheable dependency.
        $this->paymetMethodTerms[] = $payment_method_term;
      }
    }

    // Hook implementation to add pseudo payments like egift, aura.
    $this->moduleHandler->alter('alshaya_mobile_app_payment_method_api_response', $response_data, $exclude_payment_methods);

    $weight_data = array_column($response_data, 'weight');
    array_multisort($weight_data, SORT_ASC, $response_data);

    if (empty($response_data)) {
      // Sending modified response so response is not cached when no payment
      // method available.
      return (new ModifiedResourceResponse($response_data));
    }

    $response = new ResourceResponse($response_data);
    $this->addCacheableTermDependency($response);
    // Adding dependency on the config.
    $response->addCacheableDependency($checkout_settings);
    return $response;
  }

  /**
   * Adding payment method terms dependency to response.
   *
   * @param \Drupal\rest\ResourceResponse $response
   *   Response object.
   */
  protected function addCacheableTermDependency(ResourceResponse $response) {
    if (!empty($this->paymetMethodTerms)) {
      foreach ($this->paymetMethodTerms as $term) {
        $response->addCacheableDependency($term);
      }
    }
  }

}
