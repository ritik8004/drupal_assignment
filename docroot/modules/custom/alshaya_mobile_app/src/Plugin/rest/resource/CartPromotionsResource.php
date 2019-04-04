<?php

namespace Drupal\alshaya_mobile_app\Plugin\rest\resource;

use Drupal\alshaya_acm_knet\KnetHelper;
use Drupal\alshaya_mobile_app\Service\MobileAppUtility;
use Drupal\rest\ModifiedResourceResponse;
use Drupal\rest\Plugin\ResourceBase;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityRepository;
use Drupal\Core\Language\LanguageManager;
use Drupal\node\Entity\Node;
use Drupal\alshaya_acm_promotion\AlshayaPromotionsManager;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\node\NodeInterface;

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
   * K-Net Helper.
   *
   * @var \Drupal\alshaya_acm_knet\KnetHelper
   */
  private $knetHelper;

  /**
   * The mobile app utility service.
   *
   * @var \Drupal\alshaya_mobile_app\Service\MobileAppUtility
   */
  private $mobileAppUtility;


  /**
   * Drupal\alshaya_acm_promotion\AlshayaPromotionsManager definition.
   *
   * @var \Drupal\alshaya_acm_promotion\AlshayaPromotionsManager
   */
  protected $alshayaAcmPromotionManager;

  /**
   * The Language Manager service.
   *
   * @var \Drupal\Core\Language\LanguageManager
   */
  protected $languageManager;

  /**
   * The Entity repository service.
   *
   * @var \Drupal\Core\Entity\EntityRepository
   */
  protected $entityRepository;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * KnetFinalizeRequestResource constructor.
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
   * @param \Drupal\alshaya_acm_knet\KnetHelper $knet_helper
   *   K-Net Helper.
   * @param \Drupal\alshaya_mobile_app\Service\MobileAppUtility $mobile_app_utility
   *   The mobile app utility service.
   * @param \Drupal\alshaya_acm_promotion\AlshayaPromotionsManager $alshaya_acm_promotion_manager
   *   The alshaya promotion manager service.
   * @param \Drupal\Core\Entity\EntityRepository $entityRepository
   *   The entity repository service.
   * @param \Drupal\Core\Language\LanguageManager $languageManager
   *   The language manager service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(array $configuration,
                              $plugin_id,
                              $plugin_definition,
                              array $serializer_formats,
                              LoggerInterface $logger,
                              KnetHelper $knet_helper,
                              MobileAppUtility $mobile_app_utility,
                              AlshayaPromotionsManager $alshaya_acm_promotion_manager,
                              EntityRepository $entityRepository,
                              LanguageManager $languageManager,
                              EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
    $this->knetHelper = $knet_helper;
    $this->mobileAppUtility = $mobile_app_utility;
    $this->alshayaAcmPromotionManager = $alshaya_acm_promotion_manager;
    $this->languageManager = $languageManager;
    $this->entityRepository = $entityRepository;
    $this->entityTypeManager = $entity_type_manager;
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
      $container->get('alshaya_acm_knet.helper'),
      $container->get('alshaya_mobile_app.utility'),
      $container->get('alshaya_acm_promotion.manager'),
      $container->get('entity.repository'),
      $container->get('language_manager'),
      $container->get('entity_type.manager')
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
   * @return \Drupal\rest\ModifiedResourceResponse
   *   Non-cacheable response object.
   */
  public function get(string $cart_id) {
    $cart_id = (int) $cart_id;
    $promotions = [];

    if (empty($cart_id) || !$this->knetHelper->validateCart($cart_id)) {
      $this->mobileAppUtility->throwException();
    }

    $blocks = $this->entityTypeManager->getStorage('block')
      ->loadByProperties([
        'plugin' => 'alshaya_cart_promotions_block',
        'status' => TRUE,
      ]);
    $block = reset($blocks);
    $selected_promotions = array_filter($block->get('settings')['promotions']);

    if (!empty($selected_promotions)) {
      foreach ($selected_promotions as $promotion_rule_id) {
        if ($promotion_rule_id) {
          $node = $this->alshayaAcmPromotionManager->getPromotionByRuleId($promotion_rule_id);

          if ($node instanceof NodeInterface && $node->isPublished()) {
            // Get translation if available.
            $node = $this->entityRepository->getTranslationFromContext($node);

            $message = $node->get('field_acq_promotion_label')->getString();

            if ($message) {
              $promotions[$promotion_rule_id] = ['#markup' => $message];
            }
          }
        }
      }
    }

    $cart = $this->mobileAppUtility->getCart($cart_id);

    // Get all the rules applied in cart.
    $cartRulesApplied = $cart->get('cart_rules');

    // Load all the promotions of the three specific types we check below.
    // We load only published promotions.
    $subTypePromotions = $this->alshayaAcmPromotionManager->getAllPromotions([
      [
        'field' => 'field_alshaya_promotion_subtype',
        'value' => [
          AlshayaPromotionsManager::SUBTYPE_FIXED_PERCENTAGE_DISCOUNT_ORDER,
          AlshayaPromotionsManager::SUBTYPE_FIXED_AMOUNT_DISCOUNT_ORDER,
          AlshayaPromotionsManager::SUBTYPE_FREE_SHIPPING_ORDER,
        ],
        'operator' => 'IN',
      ],
      [
        'field' => 'status',
        'value' => Node::PUBLISHED,
      ],
    ]);

    foreach ($subTypePromotions as $subTypePromotion) {
      $message = '';

      $promotion_rule_id = $subTypePromotion->get('field_acq_promotion_rule_id')
        ->getString();
      $sub_type = $subTypePromotion->get('field_alshaya_promotion_subtype')
        ->getString();

      // Special condition for free shipping type promotion.
      if ($sub_type == AlshayaPromotionsManager::SUBTYPE_FREE_SHIPPING_ORDER) {
        // For free shipping, we only show if it is applied.
        if (in_array($promotion_rule_id, $cartRulesApplied)) {
          $message = $this->t('Your order qualifies for free delivery.');
        }
      }
      // For the rest, we show only if they are not applied.
      elseif (!in_array($promotion_rule_id, $cartRulesApplied)) {
        // Get translation if available.
        $subTypePromotion = $this->entityRepository->getTranslationFromContext($subTypePromotion);

        // Get message from magento data stored in drupal.
        $message = $subTypePromotion->get('field_acq_promotion_label')
          ->getString();
      }

      if ($message) {
        $promotions[$promotion_rule_id] = [$message];
      }
    }

    $promotions = array_filter($promotions);

    return new ModifiedResourceResponse($promotions);
  }

}
