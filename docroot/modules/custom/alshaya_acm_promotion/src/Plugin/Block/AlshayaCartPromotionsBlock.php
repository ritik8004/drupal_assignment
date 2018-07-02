<?php

namespace Drupal\alshaya_acm_promotion\Plugin\Block;

use Drupal\acq_cart\CartStorageInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Entity\EntityRepository;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageManager;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Driver\Exception\Exception;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\alshaya_acm_promotion\AlshayaPromotionsManager;

/**
 * Provides a 'AlshayaCartPromotionsBlock' block.
 *
 * @Block(
 *  id = "alshaya_cart_promotions_block",
 *  admin_label = @Translation("Alshaya cart promotions block"),
 * )
 */
class AlshayaCartPromotionsBlock extends BlockBase implements ContainerFactoryPluginInterface {

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
   * The cart storage service.
   *
   * @var \Drupal\acq_cart\CartStorageInterface
   */
  protected $cartStorage;

  /**
   * Constructs a new AlshayaCartPromotionsBlock object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param string $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\alshaya_acm_promotion\AlshayaPromotionsManager $alshaya_acm_promotion_manager
   *   The alshaya promotion manager service.
   * @param \Drupal\Core\Entity\EntityRepository $entityRepository
   *   The entity repository service.
   * @param \Drupal\Core\Language\LanguageManager $languageManager
   *   The language manager service.
   * @param \Drupal\acq_cart\CartStorageInterface $cartSessionStorage
   *   The cart storage service.
   */
  public function __construct(
        array $configuration,
        $plugin_id,
        $plugin_definition,
        AlshayaPromotionsManager $alshaya_acm_promotion_manager,
        EntityRepository $entityRepository,
        LanguageManager $languageManager,
        CartStorageInterface $cartSessionStorage
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->alshayaAcmPromotionManager = $alshaya_acm_promotion_manager;
    $this->languageManager = $languageManager;
    $this->entityRepository = $entityRepository;
    $this->cartStorage = $cartSessionStorage;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('alshaya_acm_promotion.manager'),
      $container->get('entity.repository'),
      $container->get('language_manager'),
      $container->get('acq_cart.cart_storage')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'promotions' => [],
    ] + parent::defaultConfiguration();

  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $promotion_nodes = $this->alshayaAcmPromotionManager->getAllPromotions();
    $options = [];

    if (!empty($promotion_nodes)) {
      foreach ($promotion_nodes as $promotion_node) {
        // Only allow promotions with value "other".
        if ($promotion_node->get('field_alshaya_promotion_subtype')->getString() == AlshayaPromotionsManager::SUBTYPE_OTHER) {
          $promotion_rule_id = $promotion_node->get('field_acq_promotion_rule_id')->first()->getValue();
          $options[$promotion_rule_id['value']] = $promotion_node->getTitle();
        }
      }
    }

    $form['promotions'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Promotions'),
      '#description' => $this->t('Selection promotions to display in block.'),
      '#options' => $options,
      '#default_value' => $this->configuration['promotions'],
      '#weight' => '0',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['promotions'] = $form_state->getValue('promotions');
  }

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  public function build() {
    $promotions = [];
    $free_shipping = [];

    $build = [
      // We need empty markup to ensure wrapper div is always available.
      '#markup' => '',
    ];

    // This is for R1 and all promotions except for the three types for which
    // we check for conditions below.
    $selected_promotions = $this->configuration['promotions'];

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

    // Get all the rules applied in cart.
    $cartRulesApplied = $this->cartStorage->getCart(FALSE)->getCart()->cart_rules;

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

      $promotion_rule_id = $subTypePromotion->get('field_acq_promotion_rule_id')->getString();
      $sub_type = $subTypePromotion->get('field_alshaya_promotion_subtype')->getString();

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
        $message = $subTypePromotion->get('field_acq_promotion_label')->getString();
      }

      if ($message) {
        $promotions[$promotion_rule_id] = ['#markup' => $message];
      }
    }

    $promotions = array_filter($promotions);

    if (!empty($promotions) || !empty($free_shipping)) {
      $build = [
        '#theme' => 'cart_top_promotions',
        '#promotions' => $promotions,
        '#free_shipping' => $free_shipping,
      ];
    }

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account) {
    try {
      $cart = $this->cartStorage->getCart(FALSE);

      if ($cart) {
        $count = $cart->getCartItemsCount();
        if ($count > 0) {
          return AccessResult::allowed();
        }
      }

      return AccessResult::forbidden();
    }
    catch (Exception $e) {
      return AccessResult::forbidden();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    // Cart will be different for every session, even guests will have session
    // as soon as they add something to cart.
    return Cache::mergeContexts(parent::getCacheContexts(), ['cookies:Drupal_visitor_acq_cart_id']);
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    $cacheTags = [];

    // It depends on cart id of the user.
    if ($cart = $this->cartStorage->getCart(FALSE)) {
      $cacheTags[] = 'cart:' . $cart->id();
    }

    // It depends on promotions content.
    $cacheTags[] = 'node_type:acq_promotion';

    return Cache::mergeTags(parent::getCacheTags(), $cacheTags);
  }

}
