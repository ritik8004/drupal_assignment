<?php

namespace Drupal\alshaya_mobile_app\Plugin\rest\resource;

use Drupal\alshaya_acm_checkout\CheckoutOptionsManager;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\rest\ModifiedResourceResponse;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a resource to get delivery methods data.
 *
 * @RestResource(
 *   id = "delivery_methods",
 *   label = @Translation("Delivery Methods"),
 *   uri_paths = {
 *     "canonical" = "/rest/v1/delivery-methods"
 *   }
 * )
 */
class DeliveryMethodResource extends ResourceBase {

  /**
   * Delivery method term object.
   *
   * @var array
   */
  protected $deliveryTerms = [];

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
   * DeliveryMethodResource constructor.
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
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, array $serializer_formats, LoggerInterface $logger, EntityRepositoryInterface $entity_repository, CheckoutOptionsManager $checkout_option_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
    $this->entityRepository = $entity_repository;
    $this->checkoutOptionManager = $checkout_option_manager;
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
      $container->get('alshaya_acm_checkout.options_manager')
    );
  }

  /**
   * Responds to GET requests.
   *
   * Returns available delivery method data.
   *
   * @return \Drupal\rest\ResourceResponse
   *   The response containing delivery methods data.
   */
  public function get() {
    $response_data = [];
    /* @var \Drupal\taxonomy\TermInterface[] $delivery_method_terms*/
    $delivery_method_terms = $this->checkoutOptionManager->getAllShippingTerms();
    // If there any delivery method available.
    if (!empty($delivery_method_terms)) {
      foreach ($delivery_method_terms as $delivery_method_term) {
        $delivery_method_term = $this->entityRepository->getTranslationFromContext($delivery_method_term);
        // Prepare response data.
        $response_data[] = [
          'name' => $delivery_method_term->getName(),
          'order_description' => $delivery_method_term->get('field_shipping_method_desc')->getString(),
          'cart_description' => $delivery_method_term->get('field_shipping_method_cart_desc')->getString(),
          'codes' => [
            'method' => $delivery_method_term->get('field_shipping_method_code')->getString(),
            'carrier' => $delivery_method_term->get('field_shipping_carrier_code')->getString(),
            'code' => $delivery_method_term->get('field_shipping_code')->getString(),
          ],
        ];

        // Adding to property for using later to attach cacheable dependency.
        $this->deliveryTerms[] = $delivery_method_term;
      }

      $response = new ResourceResponse($response_data);
      $this->addCacheableTermDependency($response);
      return $response;
    }

    // Sending modified response so response is not cached when no delivery
    // option available.
    return (new ModifiedResourceResponse($response_data));
  }

  /**
   * Adding delivery method terms dependency to response.
   *
   * @param \Drupal\rest\ResourceResponse $response
   *   Response object.
   */
  protected function addCacheableTermDependency(ResourceResponse $response) {
    if (!empty($this->deliveryTerms)) {
      foreach ($this->deliveryTerms as $term) {
        $response->addCacheableDependency($term);
      }
    }
  }

}
