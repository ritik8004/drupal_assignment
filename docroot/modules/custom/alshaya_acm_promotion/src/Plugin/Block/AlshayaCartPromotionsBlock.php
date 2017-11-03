<?php

namespace Drupal\alshaya_acm_promotion\Plugin\Block;

use Drupal\acq_cart\CartStorageInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityRepository;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Language\LanguageManager;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Driver\Exception\Exception;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\alshaya_acm_promotion\AlshayaPromotionsManager;
use Drupal\Core\Cache\Cache;

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
      foreach ($promotion_nodes as $key => $promotion_node) {
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
    $build = [];
    $selected_promotions = $this->configuration['promotions'];
    if (!empty($selected_promotions)) {
      foreach ($selected_promotions as $key => $promotion_rule_id) {
        if ($promotion_rule_id) {
          $node = $this->alshayaAcmPromotionManager->getPromotionByRuleId($promotion_rule_id);
          $langcode = $this->languageManager->getCurrentLanguage(LanguageInterface::TYPE_CONTENT)
            ->getId();
          if ($node) {
            // Get the promotion with language fallback, if it did not have a
            // translation for $langcode.
            $node = $this->entityRepository->getTranslationFromContext($node, $langcode);
            if ($node) {
              $promotions[] = [
                '#markup' => $node->get('field_acq_promotion_label')->getString(),
              ];
            }
          }
        }
      }
    }

    $cartRulesApplied = $this->cartStorage->getCart(FALSE)->getCart()->cart_rules;
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
    ]);
    foreach ($subTypePromotions as $subTypePromotion) {
      // Add the label to the promotions array.
      if (in_array($subTypePromotion->get('field_acq_promotion_rule_id')->getString(), $cartRulesApplied)) {
        if ($subTypePromotion->get('field_alshaya_promotion_subtype')->getString() == AlshayaPromotionsManager::SUBTYPE_FREE_SHIPPING_ORDER) {
          $promotions[] = [
            '#markup' => \Drupal::config('alshaya_acm_promotion.config')->get('free_shipping_order')['label'],
          ];
        }
        else {
          $promotions[] = [
            '#markup' => $subTypePromotion->get('field_acq_promotion_label')->getString(),
          ];
        }
      }
    }

    $promotions = array_filter($promotions);
    if (!empty($promotions)) {
      $build = [
        '#theme' => 'cart_top_promotions',
        '#promotions' => $promotions,
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
  public function getCacheTags() {
    return Cache::mergeTags(parent::getCacheTags(), ['cart:' . $this->cartStorage->getCart(FALSE)->id()]);
  }

}
