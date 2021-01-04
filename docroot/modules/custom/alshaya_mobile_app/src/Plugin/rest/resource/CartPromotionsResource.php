<?php

namespace Drupal\alshaya_mobile_app\Plugin\rest\resource;

use Drupal\alshaya_mobile_app\Service\MobileAppUtility;
use Drupal\block\BlockInterface;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\rest\ResourceResponse;
use Drupal\rest\Plugin\ResourceBase;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\alshaya_acm_promotion\AlshayaPromotionsManager;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\acq_commerce\Conductor\APIWrapper;

/**
 * Provides a resource to init k-net request and get url.
 *
 * @RestResource(
 *   id = "cart_promotions",
 *   label = @Translation("Get all promotions for cart."),
 *   uri_paths = {
 *     "canonical" = "/rest/v1/promotion/cart/{cart_id}"
 *   }
 * )
 */
class CartPromotionsResource extends ResourceBase {

  /**
   * The mobile app utility service.
   *
   * @var \Drupal\alshaya_mobile_app\Service\MobileAppUtility
   */
  protected $mobileAppUtility;


  /**
   * Drupal\alshaya_acm_promotion\AlshayaPromotionsManager definition.
   *
   * @var \Drupal\alshaya_acm_promotion\AlshayaPromotionsManager
   */
  protected $alshayaAcmPromotionManager;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * API Wrapper object.
   *
   * @var \Drupal\acq_commerce\Conductor\APIWrapper
   */
  protected $apiWrapper;

  /**
   * CartPromotionsResource constructor.
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
   * @param \Drupal\alshaya_mobile_app\Service\MobileAppUtility $mobile_app_utility
   *   The mobile app utility service.
   * @param \Drupal\alshaya_acm_promotion\AlshayaPromotionsManager $alshaya_acm_promotion_manager
   *   The alshaya promotion manager service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\acq_commerce\Conductor\APIWrapper $api_wrapper
   *   ApiWrapper object.
   */
  public function __construct(array $configuration,
                              $plugin_id,
                              $plugin_definition,
                              array $serializer_formats,
                              LoggerInterface $logger,
                              MobileAppUtility $mobile_app_utility,
                              AlshayaPromotionsManager $alshaya_acm_promotion_manager,
                              EntityTypeManagerInterface $entity_type_manager,
                              APIWrapper $api_wrapper) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
    $this->mobileAppUtility = $mobile_app_utility;
    $this->alshayaAcmPromotionManager = $alshaya_acm_promotion_manager;
    $this->entityTypeManager = $entity_type_manager;
    $this->apiWrapper = $api_wrapper;
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
      $container->get('alshaya_mobile_app.utility'),
      $container->get('alshaya_acm_promotion.manager'),
      $container->get('entity_type.manager'),
      $container->get('acq_commerce.api'),
      $container->get('alshaya_acm_product.context_manager')
    );
  }

  /**
   * Responds to GET requests.
   *
   * Get all promotions for cart.
   *
   * @param string $cart_id
   *   Cart ID.
   *
   * @return \Drupal\rest\ResourceResponse
   *   Cacheable response object.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function get(string $cart_id) {
    $cart_id = (int) $cart_id;

    if (empty($cart_id)) {
      $this->mobileAppUtility->throwException();
    }

    // Get all selected rules in block.
    $blocks = $this->entityTypeManager->getStorage('block')
      ->loadByProperties([
        'plugin' => 'alshaya_cart_promotions_block',
        'status' => TRUE,
      ]);
    $block = reset($blocks);

    if ($block instanceof BlockInterface) {
      $selected_promotions = array_filter($block->get('settings')['promotions']);

      // Get all the rules applied in cart.
      $cart = $this->apiWrapper->getCart($cart_id);
      $cartRulesApplied = $cart['cart_rules'];

      $promotions = $this->alshayaAcmPromotionManager->getAllCartPromotions($selected_promotions, $cartRulesApplied, 'app');

      $response = new ResourceResponse($promotions);
      $response->addCacheableDependency(CacheableMetadata::createFromRenderArray([
        '#cache' => [
          'contexts' => [
            'url.query_args:context',
          ],
          'tags' => [
            'node_type:acq_promotion',
            'cart:' . $cart_id,
          ],
        ],
      ]));

      return $response;
    }
  }

}
