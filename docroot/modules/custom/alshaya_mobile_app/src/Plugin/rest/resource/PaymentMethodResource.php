<?php

namespace Drupal\alshaya_mobile_app\Plugin\rest\resource;

use Drupal\alshaya_acm_checkout\CheckoutOptionsManager;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\rest\ModifiedResourceResponse;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\acq_payment\PaymentMethodManager;

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
   * Payment method.
   *
   * @var \Drupal\acq_payment\PaymentMethodManager
   */
  protected $paymentMethodManager;

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
   * @param \Drupal\acq_payment\PaymentMethodManager $payment_plugins
   *   Payment plugins.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    array $serializer_formats,
    LoggerInterface $logger,
    EntityRepositoryInterface $entity_repository,
    CheckoutOptionsManager $checkout_option_manager,
    PaymentMethodManager $payment_plugins
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
    $this->entityRepository = $entity_repository;
    $this->checkoutOptionManager = $checkout_option_manager;
    $this->paymentMethodManager = $payment_plugins;
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
      $container->get('plugin.manager.acq_payment_method')
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
    foreach ($payment_plugins as $plugin) {
      $payment_method_term = $this->checkoutOptionManager->loadPaymentMethod(
        $plugin['id'],
        $plugin['label']->render()
      );

      // If payment method term exists.
      if (!empty($payment_method_term)) {
        $response_data[] = [
          'name' => $payment_method_term->getName(),
          'description' => $payment_method_term->getDescription(),
          'code' => $payment_method_term->get('field_payment_code')->getString(),
          'default' => ($payment_method_term->get('field_payment_default')->getString() == '1'),
        ];

        // Adding to property for using later to attach cacheable dependency.
        $this->paymetMethodTerms[] = $payment_method_term;
      }
    }

    if (empty($response_data)) {
      // Sending modified response so response is not cached when no payment
      // method available.
      return (new ModifiedResourceResponse($response_data));
    }

    $response = new ResourceResponse($response_data);
    $this->addCacheableTermDependency($response);
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
